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

(function(DMK, w, $){
    "use strict";

    var Request, VERSION = "0.1.1";

    Request = DMK.Base.extend(
        function Request() {
            this.setData("version", VERSION);
        }
    );
    // Wir Führen einen Ajax Call aus!
    Request.prototype.doCall = function(urlOrElement, parameters) {
        var
            _request = this,
            cache = this.getCache(),
            cacheable = this.isObject(cache),
            cacheId = ""
        ;

        // Parameter und URL sammeln.
        if (!_request.isObject(parameters)) {
            parameters = {};
        }
        _request.prepareParameters(urlOrElement, parameters),

        // Event Triggern
        _request.onStart({}, parameters);

        cacheId = cacheable ? cache.buildCacheId(parameters) : cacheId;
        // den Cache nach einem bereits getaetigten Request fragen.
        if (cacheable && cache.hasData(cacheId)) {
            _request.onSuccess(cache.getData(cacheId), parameters);
            _request.handleHistoryOnSuccess(parameters);
            _request.onComplete({}, parameters);
        }
        // Den Ajax Request absenden.
        else {
            var ajaxOptions =
            {
                url : parameters.href,
                type : parameters.requestType,
                dataType : "html", // make configurable
                success : function(data, textStatus, jqXHR) {
                    // Cachen!
                    if (cacheable) {
                        cache.setData(cacheId, data);
                    }
                    _request.handleHistoryOnSuccess(parameters);
                    return _request.onSuccess(data, parameters, textStatus, jqXHR);
                },
                error : function(jqXHR, textStatus, errorThrown) {
                    return _request.onFailure(arguments, parameters, jqXHR, textStatus, errorThrown);
                },
                complete : function(jqXHR, textStatus) {
                    return _request.onComplete(arguments, parameters, jqXHR, textStatus);
                }
            };

            if (
                !_request.isObjectJQuery(urlOrElement) ||
                !urlOrElement.hasClass('ajax-dont-add-parameters-to-request')
            ) {
                ajaxOptions.data = parameters;
            }

            // haben wir ein Formular?
            if (
                _request.isObjectJQuery(urlOrElement) &&
                urlOrElement.is("form, input, select") &&
                this.isFunction($.fn.ajaxForm)
            ){
                var form = urlOrElement.is("form") ? urlOrElement : urlOrElement.parents("form").first();
                form.ajaxSubmit(ajaxOptions);
            } else {
                return $.ajax(ajaxOptions);
            }
        }
        return true;
    };

    // Die URL fuer den Request suchen
    Request.prototype.getUrl = function(urlOrElement) {
        var url = urlOrElement;
        if (this.isObjectJQuery(urlOrElement)) {
            // Wir haben einen Link und nutzen dessen href
            if (urlOrElement.is("a")) {
                url = urlOrElement.get(0).href;
            }
            // Wir haben ein Formular, und besorgen uns dessen action
            else if(urlOrElement.is("form, input, select")) {
                var form = urlOrElement.is("form") ? urlOrElement : urlOrElement.parents("form").first(),
                    href = form.is("form") ? form.prop("action") : url
                ;
                url = href;
            }
        }
        // what todo, if no url was found? use w.location.href?
        return url;
    };
    // Alle Parameter fuer den Request zusammen suchen.
    Request.prototype.prepareParameters = function(urlOrElement, parameters) {
        var _request = this;
        var indexesByParameters = [];
        // Die URL fuer den Request bauen
        if (!_request.isDefined(parameters.href)) {
            parameters.href = _request.getUrl(urlOrElement);
        }
        if (_request.isObjectJQuery(urlOrElement)) {
            if(urlOrElement.is("form, input, select")) {
                var form = urlOrElement.is("form") ? urlOrElement : urlOrElement.parents("form").first(),
                    isGet = form.attr("method").toLowerCase() === "get",
                    params = form.serializeArray(),
                    submitName = urlOrElement.is("input[type=submit]") ? urlOrElement.prop("name") : false;

                // Parameter des Formulars sammeln
                var isFirstParameter = true;
                $.each(params, function(index, object){
                    if (isGet) {
                        var parameterGlue = '&';
                        if (isFirstParameter && parameters.href.indexOf("?") == -1) {
                            parameterGlue = '?';
                        }
                        parameters.href += parameterGlue + object.name + "=" + object.value;
                    } else if (!_request.isDefined(parameters[object.name])) {
                        // The [] at the end of the parameter name means we have a multi-select or multi-checkbox
                        // without dedicated indexes for each option like tx_news_pi1[search][articletype][]
                        // if multiple options have been selected only the first one get's
                        // added to the parameter array as the object.name would be the same
                        // for all options if they don't have indexes.
                        // That's why we insert an index to make sure every option has it's own index
                        // and unique parameter name like
                        // tx_news_pi1[search][articletype][9], tx_news_pi1[search][articletype][10] etc.
                        // We mimic the behaviour of browsers and start the index per parameter
                        // at 0 and increment step by step.
                        // @todo use object.name.endsWith('[]') when support for browsers
                        // without ECMAScript 6 is dropped.
                        if (object.name.substring(object.name.length - 2, object.name.length) === "[]") {
                            if (!_request.isDefined(indexesByParameters[object.name])) {
                                indexesByParameters[object.name] = 0;
                            } else {
                                indexesByParameters[object.name]++;
                            }
                            object.name = object.name.replace("[]", '[' + indexesByParameters[object.name] + ']')
                        }
                        parameters[object.name] = object.value;
                    }
                    isFirstParameter = false;
                });
                // Den Wert des aktuellen Submit-Buttons mitsenden!
                if (_request.isString(submitName) && submitName.length > 0) {
                    if (isGet) {
                        parameters.href += "&" + submitName + "=" + urlOrElement.prop("value");
                    } else if (typeof object != 'undefined' && !_request.isDefined(parameters[object.name])) {
                        parameters[submitName] = urlOrElement.prop("value");
                    }
                }

                parameters.requestType = isGet ? 'GET' : 'POST';
            } else {
                parameters.requestType = urlOrElement.hasClass('ajax-get-request') ? 'GET' : 'POST';
            }
        }
        return parameters;
    };
    // Wir erzeugen einen loader, fuer den asyncronen Call.
    Request.prototype.getLoader = function() {
        var $loader = $('body > .waiting');
        // Nix da? Wir bauen einen neuen!
        if ($loader.length === 0) {
            $loader = $("<div>").addClass("waiting");
            $('body').prepend($loader.hide());
        }
        return $loader;
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
        this.getLoader().show();
    };
    // Wird bei erfolgreichem Call aufgerufen
    Request.prototype.onSuccess = function(data, parameters, textStatus, jqXHR) {};
    // Wird im Fehlerfall ausgerufen
    Request.prototype.onFailure = function(data, parameters, jqXHR, textStatus, errorThrown) {};
    // Wird immer nach onStart nach abschluss eines Calls aufgerufen
    Request.prototype.onComplete = function(data, parameters, jqXHR, textStatus) {
        if (typeof jqXHR !== "undefined" && jqXHR.getResponseHeader('Mktools_Location') !== null) {
            // @todo make it possible to open the location in a new tab instead of the current one.
            window.location = jqXHR.getResponseHeader('Mktools_Location');
        } else {
            this.getLoader().hide();
        }
    };

    // add lib to basic library
    DMK.Libraries.add(Request);

})(DMK, window, jQuery);
