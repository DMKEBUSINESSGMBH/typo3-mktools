(function(DMK, w){
	"use strict";
	var ContentLoader = function ContentLoader() {
		var
			// only a reference 
			_self = this,
			// private methods 
			_ajaxCache, _handleAjaxClick
		;
		
		this.init = function() {
			jQuery("#website").on(
				"click",
				".ajax-links a, a.ajax-link, form input[type=submit].ajax-link",
				_handleAjaxClick
			);
		};
		
		this.Request = {
			updateContent : function($trigger, $container, parameters) {
				var $linkwrap = $trigger.parent();
				if (typeof parameters !== "object") {
					parameters = {};
				}
				parameters.href = window.location.href;
				
				if ($trigger.is("a")) {
					parameters.href = $trigger.get(0).href;
				}
				if($trigger.is("input")) {
					var form = $trigger.get(0).form;
					var params = parameters.href.indexOf("?") >= 0 ? "&" : "?";
					params = params + $(form).serialize();
					parameters.href = parameters.href + params;
				}
				
				if ($container.data("ajaxreplaceid")) {
					parameters.contentid = $container.data("ajaxreplaceid").slice(1);
				}
				else {
					parameters.contentid = $container.attr("id").slice(1);
				}
						
				if ($linkwrap.hasClass("ajax-link-next") || $linkwrap.hasClass("browse_next")) {
					parameters.page = "next";
				}
				else if ($linkwrap.hasClass("ajax-link-prev") || $linkwrap.hasClass("browse_prev")) {
					parameters.page = "prev";
				}
				else {
					// try to fetch page
					parameters.page = "";
				}
				
				if (this.doCall(parameters)) {
					return true;
				}
				
				return false;
			},
			
			doCall : function(parameters) {
				var
					_request = this,
					cache = _ajaxCache,
					cacheId = "",
					query = ""
				;
				
				this.onStart({}, parameters);
				
				query = parameters.href.indexOf("?") >= 0 ? "&" : "?";
				query = query + "type=9267";
				
				cacheId = cache.getCacheId(parameters);
				if (0 && cache.has(cacheId)) {
					this.onSuccess(cache.get(cacheId), parameters);
					this.onComplete({}, parameters);
				}
				else {
					jQuery.ajax(
						{
							url : parameters.href + query,
							type : "POST",
							dataType : "html",
							data : parameters,
							success : function(data) {
								cache.set(cacheId, data);
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
			},
			
			// loader anzeigen
			onStart : function(data, parameters){
				jQuery("#c" + parameters.contentid + " .waiting").clearQueue().fadeIn();
			},
			// content ersetzen
			onSuccess : function(data, parameters){
				var from = 0, to = 0;
				
				if (parameters.page === "next") {
					to = 1;
				}
				else if (parameters.page === "prev") {
					from = 1;
				}
				_self.replaceContent(parameters.contentid, data, from, to);
			},
			// what todo?
			onFailure : function(data, parameters){
				console.log(arguments);
			},
			// loader ausblenden
			onComplete: function(data, parameters){
				jQuery("#c" + parameters.contentid + " .waiting").clearQueue().fadeOut();
			}
		};
		
		_ajaxCache = {
			caches : {},
			getCacheId : function(params) {
				return JSON.stringify(params);
			},
			get : function(cid) {
				return this.caches[cid];
			},
			set : function(cid, data) {
				this.caches[cid] = data;
			},
			has : function(cid) {
				return typeof this.caches[cid] !== "undefined";
			}
		};
		
		_handleAjaxClick = function(event, element) {
			var $el, $content;
			
			if (typeof element === "undefined") {
				element = this;
			}
			
			// do request only if there is no target attribute
			if (element.tagName === "a") {
				if (element.target.length > 0) {
					return ;
				}
			}
			
			$el = jQuery(element);
			// wir suchen die contentid! (id="c516")
			$el.parents("div[data-ajaxreplaceid^='c']").each(
				function(index, element) {
					$content = jQuery(element);
					if (_self.fn.isNumeric($content.data("ajaxreplaceid").slice(1))) {
						return false;
					}
					return true;
				}
			);
			if ($content !== "undefined") {
				// Abbruch bei nicht vorhandenem Element
				$content = jQuery("#" + $content.data("ajaxreplaceid"));
				if ($content === "undefined") {
					return false;
				}
			}
			else {
				$el.parents("div[id^='c']").each(
					function(index, element) {
						$content = jQuery(element);
						if (_self.fn.isNumeric($content.attr("id").slice(1))) {
							return false;
						}
						return true;
					}
				);
			}
			// kein content element gefunden, wir ersetzen nichts!
			if (typeof $content !== "object" || $content.length === 0) {
				return ;
			}
			
			$content.addClass("ajax-content");
			if ($content.find(".waiting").length === 0) {
				$content.append(jQuery("<div>").addClass("waiting").hide());
			}
			
			if (_self.Request.updateContent($el, $content)) {
				event.preventDefault();
			}
		};
		
		this.replaceContent = function(contentId, html, from, to) {
			var $cOld = jQuery("#c" + contentId), $cNew,
				animateTime = 1000;
			if ($cOld.length === 0) {
				return;
			}
			// wir sliden
			if (from != to) {
				var $slidebox = $cOld.parent(),
					left = from < to, // true > slide to left, false > slide to right
					old = { width : $cOld.width(), height : $cOld.height() };
				// slidebox wrap erzeugen, falls nicht existent.
				if (!$slidebox.hasClass("ajax-wrap")) {
					$cOld.wrap(jQuery("<div>").addClass("ajax-wrap"));
					$slidebox = $cOld.parent();
					$slidebox.css({"position" : "relative", "overflow" : "hidden"});
				}
				$slidebox.width(old.width).height(old.height);
				$cOld.css({"position" : "absolute", "top" : 0, "left" : 0, "width" : old.width});
				// das alte element
				$cOld.attr('id', contentId + '-old').addClass("ajax-content-old");
				$cNew = left ? jQuery(html).insertAfter($cOld) : jQuery(html).insertBefore($cOld);
				if ($cNew.length === 0) {
					from = to = 0;
				}
				else {
					$cNew.css({"position" : "absolute", "top" : 0, "left" : old.width * (left ? +1 : -1), "width" : old.width});
					$slidebox.find(".waiting").clearQueue().fadeOut();
					$slidebox.animate({"height" : $cNew.height()}, animateTime);
					$cOld.animate({"left" : old.width * (left ? -1 : +1)}, animateTime);
					$cNew.animate({"left" : 0}, animateTime, function () {
						window.setTimeout(function() {
							$cOld.remove();
						}, 250);
					});
				}
				
			}
			
			// normales ersetzen
			if (from == to) {
				$cOld.replaceWith(html);
			}
		};
		
		this.fn = {
			isNumeric : function(num) {
				return !isNaN(parseFloat(num)) && isFinite(num);
			}
		};
		
		
	}; // End of DMK.ContentLoader 
	
	DMK.Libraries.add(
			DMK.Base.extend(ContentLoader), "ContentLoader"
		);
	
})(DMK, window);
