/**
 * Request
 *
 * Library for Ajax calls.
 *
 * @copyright Copyright (c) 2014 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 * @version 0.1.1
 * @requires Base, Registry, Fetch API
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */

import {Base, DMK} from "./Base.js";
import AjaxForm from "./jquery.form.min.js";

export class Request extends Base {
    constructor() {
        super({version: "0.1.1"});
        this.form = null;
        this.submitter = null;
    }

    async doCall(urlOrElement, parameters = {}) {
        let response = null;
        let requestStarted = false;
        let data = null;

        try {
            if (!this.isObject(parameters)) {
                parameters = {};
            }
            this.prepareParameters(urlOrElement, parameters);
            await this.onStart({}, parameters);
            requestStarted = true;

            const builtRequest = this.buildRequest(urlOrElement, parameters);
            parameters.href = builtRequest.url;
            const cache = this.getCache();
            const cacheable = cache && typeof cache.buildCacheId === "function"
                && typeof cache.hasData === "function";
            const cacheParameters = builtRequest.cacheKey
                ? {...parameters, submittedForm: builtRequest.cacheKey}
                : parameters;
            const cacheId = cacheable ? cache.buildCacheId(cacheParameters) : "";

            if (cacheable && cache.hasData(cacheId)) {
                data = cache.getData(cacheId);
                this.handleHistoryOnSuccess(parameters);
                await this.onSuccess(data, parameters);
                return data;
            }

            response = await fetch(builtRequest.url, {
                method: parameters.requestType,
                body: builtRequest.body,
                headers: {Accept: "text/html"},
                credentials: "same-origin"
            });

            data = await response.text();
            if (!response.ok) {
                const error = new Error(`Ajax request failed with HTTP ${response.status}.`);
                error.response = response;
                error.responseText = data;
                throw error;
            }

            if (cacheable && this.getCache() === cache) {
                cache.setData(cacheId, data);
            }
            this.handleHistoryOnSuccess(parameters);
            await this.onSuccess(data, parameters, response.status, response);
            return data;
        } catch (error) {
            try {
                await this.onFailure(error, parameters, error.response || response, error.message);
            } catch (callbackError) {
                console.error("DMK.Request: failure callback failed.", callbackError);
            }
            console.error("DMK.Request: Ajax request failed.", error);
            return null;
        } finally {
            if (requestStarted) {
                try {
                    await this.onComplete(data, parameters, response, response?.statusText);
                } catch (error) {
                    console.error("DMK.Request: completion callback failed.", error);
                }
            }
        }
    }

    getUrl(urlOrElement) {
        if (typeof urlOrElement === "string" || urlOrElement instanceof URL) {
            return String(urlOrElement);
        }
        if (!(urlOrElement instanceof Element)) {
            return globalThis.location?.href || "";
        }
        if (urlOrElement.matches("a")) {
            return urlOrElement.href;
        }
        const form = this.getForm(urlOrElement);
        return form?.action || globalThis.location?.href || "";
    }

    getForm(element) {
        if (!(element instanceof Element)) {
            return null;
        }
        if (element instanceof HTMLFormElement) {
            return element;
        }
        return element.closest("form");
    }

    prepareParameters(urlOrElement, parameters) {
        if (!this.isDefined(parameters.href)) {
            parameters.href = this.getUrl(urlOrElement);
        }

        this.form = this.getForm(urlOrElement);
        const previousSubmitter = this.submitter;
        this.submitter = urlOrElement instanceof HTMLButtonElement
            || urlOrElement instanceof HTMLInputElement
            ? urlOrElement
            : previousSubmitter?.form === this.form ? previousSubmitter : null;

        if (this.form) {
            parameters.requestType = (this.form.method || "get").toUpperCase();
        } else if (urlOrElement instanceof Element) {
            parameters.requestType = parameters.requestType
                || (urlOrElement.classList.contains("ajax-get-request") ? "GET" : "POST");
        } else {
            parameters.requestType = parameters.requestType || "POST";
        }

        if (!this.form && urlOrElement instanceof Element
            && urlOrElement.classList.contains("ajax-get-request")) {
            parameters.requestType = "GET";
        }
        return parameters;
    }

    buildRequest(urlOrElement, parameters) {
        const url = new URL(parameters.href || this.getUrl(urlOrElement), document.baseURI);
        const method = parameters.requestType.toUpperCase();
        const skipParameters = urlOrElement instanceof Element
            && urlOrElement.classList.contains("ajax-dont-add-parameters-to-request");
        let body = null;
        let cacheKey = null;

        if (method === "GET") {
            if (this.form) {
                const formData = new AjaxForm(this.form).createFormData(
                    skipParameters ? {} : parameters,
                    this.submitter
                );
                for (const [name, value] of formData.entries()) {
                    if (typeof File !== "undefined" && value instanceof File) {
                        continue;
                    }
                    url.searchParams.append(name, String(value));
                }
            } else if (!skipParameters) {
                this.appendParameters(url.searchParams, parameters);
            }
        } else if (this.form) {
            body = new AjaxForm(this.form).createFormData(
                skipParameters ? {} : parameters,
                this.submitter
            );
            cacheKey = Array.from(body.entries(), ([name, value]) => [
                name,
                typeof File !== "undefined" && value instanceof File
                    ? {name: value.name, size: value.size, type: value.type, lastModified: value.lastModified}
                    : String(value)
            ]);
        } else if (!skipParameters) {
            body = this.createUrlSearchParams(parameters);
        }

        return {url: url.href, body, cacheKey};
    }

    appendParameters(searchParams, parameters) {
        for (const [name, value] of Object.entries(parameters)) {
            if (name === "href" || name === "requestType" || value === undefined || value === null) {
                continue;
            }
            for (const item of Array.isArray(value) ? value : [value]) {
                searchParams.append(name, String(item));
            }
        }
    }

    createUrlSearchParams(parameters) {
        const body = new URLSearchParams();
        this.appendParameters(body, parameters);
        return body;
    }

    getLoader() {
        let loader = document.querySelector("body > .waiting");
        if (!loader) {
            loader = document.createElement("div");
            loader.classList.add("waiting");
            loader.hidden = true;
            document.body.prepend(loader);
        }
        return loader;
    }

    getCache() {
        return DMK.Registry;
    }

    handleHistoryOnSuccess(parameters) {
        if (parameters.useHistory && DMK.History instanceof Base) {
            DMK.History.setHistoryUrl(parameters.href);
        }
    }

    onStart() {
        const loader = this.getLoader();
        if (loader) {
            loader.hidden = false;
            loader.style.display = "";
            if (getComputedStyle(loader).display === "none") {
                loader.style.display = "block";
            }
            loader.setAttribute("aria-hidden", "false");
        }
    }

    onSuccess() {}

    onFailure() {}

    onComplete(data, parameters, response) {
        const redirect = response?.headers?.get("Mktools_Location");
        if (redirect) {
            // @todo make it possible to open the location in a new tab instead of the current one.
            window.location.assign(redirect);
            return;
        }

        const loader = this.getLoader();
        if (loader) {
            loader.hidden = true;
            loader.style.display = "none";
            loader.setAttribute("aria-hidden", "true");
        }
    }
}

DMK.Libraries.add(Request);

export default Request;
