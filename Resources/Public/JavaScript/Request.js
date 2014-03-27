/**
 * Request
 * 
 * Library for Ajax Calls
 * 
 * @copyright Copyright (c) 2014 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 * @version 0.1.0
 * @requires Base, Registry 
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */

(function(DMK, w, $){
	"use strict";
	
	var Request = DMK.Base.extend(
		function Request() {
		}
	);
	Request.prototype.doCall = function(urlOrElement, parameters) {
		var
			_request = this,
			cache = this.getCache(),
			cacheable = this.isObject(cache),
			cacheId = "",
			query = ""
		;
			
		if (_request.isObject(urlOrElement) && !_request.isObjectJQuery(urlOrElement)) {
			parameters = urlOrElement;
		} else {
			if (!_request.isObject(parameters)) {
				parameters = {};
			}
			parameters.href = _request.getUrl(urlOrElement);
		}
			
		
		
		_request.onStart({}, parameters);
		
		query = parameters.href.indexOf("?") >= 0 ? "&" : "?";
		query = query + "type=9267";
		
		cacheId = cacheable ? cache.buildCacheId(parameters) : cacheId;
		if (cacheable && cache.hasData(cacheId)) {
			_request.onSuccess(cache.getData(cacheId), parameters);
			_request.onComplete({}, parameters);
		}
		else {
			$.ajax(
				{
					url : parameters.href + query,
					type : "POST",
					dataType : "html",
					data : parameters,
					success : function(data) {
						if (cacheable) {
							cache.setData(cacheId, data);
						}
						return _request.onSuccess(data, parameters);
					},
					error : function() {
						return _request.onFailure(arguments, parameters);
					},
					complete : function() {
						return _request.onComplete(arguments, parameters);
					}
				}
			);
		}
		return true;
	};
	
	Request.prototype.getUrl = function(urlOrElement) {
		var url = urlOrElement
		if (this.isObjectJQuery(urlOrElement)) {
			if (urlOrElement.is("a")) {
				url = urlOrElement.get(0).href;
			}
			else if(urlOrElement.is("form, input, select")) {
				var isForm = urlOrElement.is("form"),
					form = isForm ? urlOrElement : urlOrElement.parents("form").first(),
					href = isForm ? form.prop("action") : url,
					params = href.indexOf("?") >= 0 ? "&" : "?"
				;
				params = params + form.serialize();
				url = href + params;
			}
		}
		return url;
	}
	Request.prototype.getLoader = function() {
		var $loader = $('body > .waiting');
		if ($loader.length === 0) {
			$loader = $("<div>").addClass("waiting");
			$('body').prepend($loader.hide());
		}
		return $loader;
	};
	Request.prototype.getCache = function() {
		return DMK.Registry;
	};
	Request.prototype.onStart = function(data, parameters) {
		this.getLoader().show();
	};
	Request.prototype.onSuccess = function(data, parameters) {};
	Request.prototype.onFailure = function(data, parameters) {};
	Request.prototype.onComplete = function(data, parameters) {
		this.getLoader().hide();
	};
	
	// add lib to basic library
	DMK.Libraries.add(Request);
	
})(DMK, window, jQuery);
