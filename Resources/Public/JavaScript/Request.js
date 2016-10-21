/**
 * Request
 *
 * Library for Ajax Calls
 *
 * @copyright Copyright (c) 2014 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * @license http://www.gnu.org/licenses/lgpl.html
 * 			GNU Lesser General Public License, version 3 or later
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

		// Paremeter und URL sammeln.
		if (!_request.isObjectJQuery(urlOrElement)) {
			parameters.href = urlOrElement;
		}
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
				type : "POST", // make configurable
				dataType : "html", // make configurable
				data : parameters,
				success : function(data) {
					// Cachen!
					if (cacheable) {
						cache.setData(cacheId, data);
					}
					_request.handleHistoryOnSuccess(parameters);
					return _request.onSuccess(data, parameters);
				},
				error : function() {
					return _request.onFailure(arguments, parameters);
				},
				complete : function() {
					return _request.onComplete(arguments, parameters);
				}
			};

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

				parameters.href += parameters.href.indexOf("?") >= 0 ? "&" : "?1=1";
				// Parameter des Forumars sammeln
				$.each(params, function(index, object){
					if (isGet) {
						parameters.href += "&" + object.name + "=" + object.value;
					} else if (!_request.isDefined(parameters[object.name])) {
						parameters[object.name] = object.value;
					}
				});
				// Den Wert des aktuellen Submit-Buttons mitsenden!
				if (_request.isString(submitName) && submitName.length > 0) {
					if (isGet) {
						parameters.href += "&" + submitName + "=" + urlOrElement.prop("value");
					} else if (typeof object != 'undefined' && !_request.isDefined(parameters[object.name])) {
						parameters[submitName] = urlOrElement.prop("value");
					}
				}
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
	Request.prototype.onSuccess = function(data, parameters) {};
	// Wird im Fehlerfall ausgerufen
	Request.prototype.onFailure = function(data, parameters) {};
	// Wird immer nach onStart nach abschluss eines Calls aufgerufen
	Request.prototype.onComplete = function(data, parameters) {
		this.getLoader().hide();
	};

	// add lib to basic library
	DMK.Libraries.add(Request);

})(DMK, window, jQuery);
