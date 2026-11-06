/**
 * Request
 *
 * Library for Ajax Calls
 *
 * @copyright Copyright (c) 2014 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * @license http://www.gnu.org/licenses/lgpl.html
 *             GNU Lesser General Public License, version 3 or later
 * @version 0.1.1
 * @requires Base, Registry
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */

(function(DMK, w){
    "use strict";

    var Request = DMK.Base.extend(function Request() {
        this.setData("version", "0.1.1");
    });

    Request.prototype.getForm = function(element) {
        if (!this.isElement(element)) {
            return null;
        }
        return element.matches("form") ? element : element.form || null;
    };

    Request.prototype.getFormData = function(element, submitter) {
        var form = this.getForm(element);
        submitter = submitter || element;
        if (submitter && submitter.form === form && submitter.matches("button[type=submit], button:not([type]), input[type=submit], input[type=image]")) {
            return new w.FormData(form, submitter);
        }
        return new w.FormData(form);
    };

    // Keep the request hooks and return an abortable XMLHttpRequest.
    Request.prototype.doCall = function(urlOrElement, parameters, submitter) {
        var request = this,
            form = this.getForm(urlOrElement),
            formData = form ? this.getFormData(urlOrElement, submitter) : null,
            files = formData ? Array.from(formData).filter(function(entry) {
                return entry[1] instanceof w.File;
            }) : [],
            cache = this.getCache(),
            cacheable = cache && this.isFunction(cache.buildCacheId)
                && !(this.isElement(urlOrElement) && urlOrElement.classList.contains("notcachable"))
                && !files.some(function(entry) {
                return entry[1].name !== "";
            }),
            cacheId, xhr, data, url, method, addParameters;

        parameters = parameters && this.isObject(parameters) ? parameters : {};
        this.prepareParameters(urlOrElement, parameters, submitter, formData);
        this.onStart({}, parameters);
        cacheId = cacheable ? cache.buildCacheId(parameters) : "";
        if (cacheable && cache.hasData(cacheId)) {
            this.onSuccess(cache.getData(cacheId), parameters);
            this.handleHistoryOnSuccess(parameters);
            this.onComplete({}, parameters);
            return true;
        }

        url = new w.URL(parameters.href, w.document.baseURI);
        method = parameters.requestType.toUpperCase();
        addParameters = !this.isElement(urlOrElement) || !urlOrElement.classList.contains("ajax-dont-add-parameters-to-request");
        data = new w.URLSearchParams();
        if (addParameters) {
            Object.keys(parameters).forEach(function(name) {
                request.appendParameter(data, name, parameters[name]);
            });
        } else if (form && method !== "GET" && method !== "HEAD") {
            formData.forEach(function(value, name) {
                if (!(value instanceof w.File)) {
                    data.append(name, value);
                }
            });
        }
        if (method === "GET" || method === "HEAD") {
            data.forEach(function(value, name) {
                url.searchParams.append(name, value);
            });
            data = null;
        } else if (form && (files.length > 0 || form.enctype === "multipart/form-data")) {
            var multipart = new w.FormData();
            data.forEach(function(value, name) {
                multipart.append(name, value);
            });
            // File fields are never converted to strings or sent twice.
            files.forEach(function(entry) {
                multipart.append(entry[0], entry[1]);
            });
            data = multipart;
        }

        xhr = new w.XMLHttpRequest();
        xhr.open(method, url.href, true);
        if (url.origin === w.location.origin) {
            xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        }
        if (data instanceof w.URLSearchParams) {
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8");
            data = data.toString();
        }
        xhr.setRequestHeader("Accept", "text/html, */*; q=0.01");
        function complete(status, error) {
            try {
                if (status === "success") {
                    if (cacheable) {
                        cache.setData(cacheId, xhr.responseText);
                    }
                    request.handleHistoryOnSuccess(parameters);
                    request.onSuccess(xhr.responseText, parameters, status, xhr);
                } else {
                    request.onFailure([xhr, status, error], parameters, xhr, status, error);
                }
            } finally {
                request.onComplete([xhr, status], parameters, xhr, status);
            }
        }
        xhr.onload = function() {
            complete((xhr.status >= 200 && xhr.status < 300) || xhr.status === 304 ? "success" : "error", xhr.statusText);
        };
        xhr.onerror = function() { complete("error", xhr.statusText); };
        xhr.onabort = function() { complete("abort", "abort"); };
        xhr.ontimeout = function() { complete("timeout", "timeout"); };
        xhr.send(data);
        return xhr;
    };

    Request.prototype.appendParameter = function(data, name, value) {
        var request = this;
        if (Array.isArray(value)) {
            value.forEach(function(item, index) {
                request.appendParameter(data, name + (item && typeof item === "object" ? "[" + index + "]" : "[]"), item);
            });
        } else if (value && typeof value === "object") {
            Object.keys(value).forEach(function(key) {
                request.appendParameter(data, name + "[" + key + "]", value[key]);
            });
        } else {
            data.append(name, value == null ? "" : value);
        }
    };

    Request.prototype.getUrl = function(urlOrElement, submitter) {
        if (!this.isElement(urlOrElement)) {
            return urlOrElement;
        }
        if (urlOrElement.matches("a")) {
            return urlOrElement.href;
        }
        var form = this.getForm(urlOrElement);
        if (form) {
            submitter = submitter || urlOrElement;
            return new w.URL(submitter.getAttribute("formaction") || form.getAttribute("action") || w.location.href, w.document.baseURI).href;
        }
        return w.location.href;
    };

    Request.prototype.prepareParameters = function(urlOrElement, parameters, submitter, formData) {
        var request = this, form = this.getForm(urlOrElement), indexes = Object.create(null);
        if (!this.isDefined(parameters.href)) {
            parameters.href = this.getUrl(urlOrElement, submitter);
        }
        if (form) {
            submitter = submitter || urlOrElement;
            var method = submitter.getAttribute("formmethod") || form.getAttribute("method") || "get",
                isGet = method.toLowerCase() === "get",
                url = new w.URL(parameters.href, w.document.baseURI);
            (formData || this.getFormData(urlOrElement, submitter)).forEach(function(value, name) {
                if (value instanceof w.File) {
                    if (isGet) {
                        url.searchParams.append(name, value.name);
                    }
                    return;
                }
                if (isGet) {
                    url.searchParams.append(name, value);
                } else if (!request.isDefined(parameters[name])) {
                    if (name.slice(-2) === "[]") {
                        var index = indexes[name] || 0;
                        indexes[name] = index + 1;
                        name = name.slice(0, -2) + "[" + index + "]";
                    }
                    parameters[name] = value;
                }
            });
            if (isGet) {
                parameters.href = url.href;
            }
            parameters.requestType = isGet ? "GET" : "POST";
        } else if (this.isElement(urlOrElement)) {
            parameters.requestType = urlOrElement.classList.contains("ajax-get-request") ? "GET" : "POST";
        } else {
            parameters.requestType = parameters.requestType || "GET";
        }
        return parameters;
    };

    Request.prototype.getLoader = function() {
        var loader = w.document.querySelector("body > .waiting");
        if (!loader) {
            loader = w.document.createElement("div");
            loader.className = "waiting";
            loader.style.display = "none";
            w.document.body.prepend(loader);
        }
        return loader;
    };
    Request.prototype.getCache = function() {
        return DMK.Registry;
    };
    Request.prototype.handleHistoryOnSuccess = function(parameters) {
        if (parameters.useHistory && DMK.History) {
            DMK.History.setHistoryUrl(parameters.href);
        }
    };
    Request.prototype.onStart = function(data, parameters) {
        var loader = this.getLoader();
        if (loader) {
            loader.style.display = "block";
        }
    };
    Request.prototype.onSuccess = function(data, parameters, textStatus, xhr) {};
    Request.prototype.onFailure = function(data, parameters, xhr, textStatus, errorThrown) {};
    Request.prototype.onComplete = function(data, parameters, xhr, textStatus) {
        var redirect = xhr && xhr.getResponseHeader("Mktools_Location"), loader = this.getLoader();
        if (redirect) {
            w.location.href = redirect;
        } else if (loader) {
            loader.style.display = "none";
        }
    };

    DMK.Libraries.add(Request);
})(DMK, window);
