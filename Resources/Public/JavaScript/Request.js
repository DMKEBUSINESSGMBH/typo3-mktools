/**
 * Request
 *
 * Library for Ajax Calls
 *
 * @copyright Copyright (c) 2014 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * @license http://www.gnu.org/licenses/lgpl.html
 *             GNU Lesser General Public License, version 3 or later
 * @version 0.2.0
 * @requires Base, Registry
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */

(function(DMK, w, d){
    "use strict";

    var Request, VERSION = "0.2.0",
        FORM_FIELDS = "form, input, select";

    Request = DMK.Base.extend(
        function Request() {
            this.setData("version", VERSION);
        }
    );

    // Liefert das zugehoerige Formular, wenn das Element ein Formular oder ein Formularfeld ist.
    Request.prototype.getForm = function(element) {
        if (!this.isElement(element) || !element.matches(FORM_FIELDS)) {
            return null;
        }
        return element.matches("form") ? element : element.closest("form");
    };

    // Wir Führen einen Ajax Call aus!
    Request.prototype.doCall = function(urlOrElement, parameters) {
        var
            _request = this,
            cache = this.getCache(),
            cacheable = this.isObject(cache) && cache !== null,
            cacheId = "",
            url, options, response
        ;

        urlOrElement = this.toElement(urlOrElement);

        // Parameter und URL sammeln.
        if (!_request.isObject(parameters) || parameters === null) {
            parameters = {};
        }
        _request.prepareParameters(urlOrElement, parameters);

        // Event Triggern
        _request.onStart({}, parameters);

        cacheId = cacheable ? cache.buildCacheId(parameters) : cacheId;
        // den Cache nach einem bereits getaetigten Request fragen.
        if (cacheable && cache.hasData(cacheId)) {
            _request.onSuccess(cache.getData(cacheId), parameters);
            _request.handleHistoryOnSuccess(parameters);
            _request.onComplete({}, parameters);
            return true;
        }

        // Den Ajax Request absenden.
        url = parameters.href;
        options = {
            method : parameters.requestType || "GET",
            headers : {"X-Requested-With" : "XMLHttpRequest", "Accept" : "text/html, */*; q=0.01"},
            credentials : "same-origin"
        };
        if (
            !_request.isElement(urlOrElement) ||
            !urlOrElement.classList.contains("ajax-dont-add-parameters-to-request")
        ) {
            var query = _request.buildQuery(parameters);
            if (options.method.toUpperCase() === "GET") {
                url += (url.indexOf("?") === -1 ? "?" : "&") + query;
            } else {
                options.body = query;
                options.headers["Content-Type"] = "application/x-www-form-urlencoded; charset=UTF-8";
            }
        }

        var complete = function() {
            return _request.onComplete(
                [response], parameters, response, response && response.ok ? "success" : "error"
            );
        };

        return w.fetch(url, options)
            .then(function(res) {
                response = res;
                return res.text();
            })
            .then(
                function(data) {
                    if (!response.ok) {
                        return _request.onFailure([response], parameters, response, "error", response.statusText);
                    }
                    // Cachen!
                    if (cacheable) {
                        cache.setData(cacheId, data);
                    }
                    _request.handleHistoryOnSuccess(parameters);
                    return _request.onSuccess(data, parameters, "success", response);
                },
                // Netzwerkfehler
                function(error) {
                    return _request.onFailure([error], parameters, response, "error", error);
                }
            )
            .then(complete, function(error) {
                complete(); // Loader auch bei Fehlern in den Callbacks ausblenden
                throw error;
            });
    };

    // Baut aus den Parametern einen urlencoded Query String
    Request.prototype.buildQuery = function(parameters) {
        var query = new w.URLSearchParams();
        Object.keys(parameters).forEach(function(key) {
            if (typeof parameters[key] !== "undefined" && parameters[key] !== null) {
                query.append(key, parameters[key]);
            }
        });
        return query.toString();
    };

    // Die URL fuer den Request suchen
    Request.prototype.getUrl = function(urlOrElement) {
        var url = urlOrElement, form;
        if (this.isElement(urlOrElement)) {
            // Wir haben einen Link und nutzen dessen href
            if (urlOrElement.matches("a")) {
                url = urlOrElement.href;
            }
            // Wir haben ein Formular, und besorgen uns dessen action
            else if ((form = this.getForm(urlOrElement))) {
                url = form.action;
            }
        }
        // what todo, if no url was found? use w.location.href?
        return url;
    };
    // Alle Parameter fuer den Request zusammen suchen.
    Request.prototype.prepareParameters = function(urlOrElement, parameters) {
        var _request = this,
            indexesByParameters = {},
            form;
        // Die URL fuer den Request bauen
        if (!_request.isDefined(parameters.href)) {
            parameters.href = _request.getUrl(urlOrElement);
        }
        if (_request.isElement(urlOrElement)) {
            if ((form = _request.getForm(urlOrElement))) {
                var isGet = (form.getAttribute("method") || "get").toLowerCase() === "get",
                    submitName = urlOrElement.matches("input[type=submit]") ? urlOrElement.name : "",
                    isFirstParameter = true;

                // Parameter des Formulars sammeln
                new w.FormData(form).forEach(function(value, name) {
                    // Dateien koennen nicht per urlencoded uebertragen werden
                    if (typeof value !== "string") {
                        return;
                    }
                    if (isGet) {
                        var parameterGlue = '&';
                        if (isFirstParameter && parameters.href.indexOf("?") === -1) {
                            parameterGlue = '?';
                        }
                        parameters.href += parameterGlue + name + "=" + encodeURIComponent(value);
                    } else if (!_request.isDefined(parameters[name])) {
                        // The [] at the end of the parameter name means we have a multi-select or multi-checkbox
                        // without dedicated indexes for each option like tx_news_pi1[search][articletype][]
                        // if multiple options have been selected only the first one get's
                        // added to the parameter array as the name would be the same
                        // for all options if they don't have indexes.
                        // That's why we insert an index to make sure every option has it's own index
                        // and unique parameter name like
                        // tx_news_pi1[search][articletype][9], tx_news_pi1[search][articletype][10] etc.
                        // We mimic the behaviour of browsers and start the index per parameter
                        // at 0 and increment step by step.
                        if (name.endsWith("[]")) {
                            indexesByParameters[name] = _request.isDefined(indexesByParameters[name])
                                ? indexesByParameters[name] + 1
                                : 0;
                            name = name.replace("[]", '[' + indexesByParameters[name] + ']');
                        }
                        parameters[name] = value;
                    }
                    isFirstParameter = false;
                });
                // Den Wert des aktuellen Submit-Buttons mitsenden!
                if (isGet && submitName.length > 0) {
                    parameters.href += "&" + submitName + "=" + urlOrElement.value;
                }

                parameters.requestType = isGet ? 'GET' : 'POST';
            } else {
                parameters.requestType = urlOrElement.classList.contains('ajax-get-request') ? 'GET' : 'POST';
            }
        }
        return parameters;
    };
    // Wir erzeugen einen loader, fuer den asyncronen Call.
    Request.prototype.getLoader = function() {
        var loader = d.querySelector('body > .waiting');
        // Nix da? Wir bauen einen neuen!
        if (loader === null) {
            loader = d.createElement("div");
            loader.className = "waiting";
            loader.style.display = "none";
            d.body.prepend(loader);
        }
        return loader;
    };
    // Liefert den Cache
    Request.prototype.getCache = function() {
        return DMK.Registry;
    };
    // Wird beim Start des Calls aufgerufen
    Request.prototype.handleHistoryOnSuccess = function(parameters) {
        // browser url anpassen?
        if (
            this.isDefined(parameters.useHistory)
            && parameters.useHistory
            && DMK.Base.isObject(DMK.History)
        ) {
            DMK.History.setHistoryUrl(parameters.href);
        }
    };
    // Wird beim Start des Calls aufgerufen
    Request.prototype.onStart = function(data, parameters) {
        DMK.Base.show(this.getLoader());
    };
    // Wird bei erfolgreichem Call aufgerufen
    Request.prototype.onSuccess = function(data, parameters, textStatus, response) {};
    // Wird im Fehlerfall ausgerufen
    Request.prototype.onFailure = function(data, parameters, response, textStatus, errorThrown) {};
    // Wird immer nach onStart nach abschluss eines Calls aufgerufen
    Request.prototype.onComplete = function(data, parameters, response, textStatus) {
        var location = response && response.headers ? response.headers.get('Mktools_Location') : null;
        if (location !== null) {
            // @todo make it possible to open the location in a new tab instead of the current one.
            w.location = location;
        } else {
            DMK.Base.hide(this.getLoader());
        }
        // Ersatz fuer jQuerys ajaxComplete: $(document).ajaxComplete() wird von fetch nicht mehr ausgeloest.
        d.dispatchEvent(new w.CustomEvent("dmk:ajaxComplete", {
            detail : {response : response, parameters : parameters, textStatus : textStatus}
        }));
    };

    // add lib to basic library
    DMK.Libraries.add(Request);

})(DMK, window, document);
