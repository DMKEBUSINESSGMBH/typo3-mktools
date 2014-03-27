/**
 * TODO
 * REFECTOR!
 * SOLLTE SCHNELL UMGEBAUT WERDEN!
 * SEE: >>> ModalBootstrap.js <<<
 */

if (typeof DMK !== "object") {
	var DMK = {};
}
(function(DMK, w, $){
	"use strict";
	var ContentLoader = function ContentLoader() {
		var
			// only a reference 
			_self = this,
			// private methods 
			_ajaxCache, _handleAjaxClick
		;
		
		this.init = function() {
			// click events
			$("body")
				.off("click.contentloader")
				.on(
					"click.contentloader",
					".ajax-links a, a.ajax-link",
					_handleAjaxClick
				);
			// submit events
			$("body")
				.off("submit.contentloader")
				.on(
					"submit.contentloader",
					"form.ajax-form",
					_handleAjaxClick
				);
			// autotriger for forms
			$("body")
				.off("click.contentloader")
				.on(
					"click.contentloader",
					"form.ajax-autotrigger input:not(:text)",
					_handleAjaxClick
				)
				.off("change.contentloader")
				.on(
					"change.contentloader",
					"form.ajax-autotrigger select",
					_handleAjaxClick
				);
		};
		
		this.Request = {
			updateContent : function($trigger, $container, parameters) {
				var $linkwrap = $trigger.parent();
				if (typeof parameters !== "object") {
					parameters = {};
				}
				parameters.href = w.location.href;
				
				if ($trigger.is("a")) {
					parameters.href = $trigger.get(0).href;
				}
				else if($trigger.is("form, input, select")) {
					var isForm = $trigger.is("form"),
						$form = isForm ? $trigger : $trigger.parents("form").first(),
						href = isForm ? $trigger.prop("action") : parameters.href,
						params = href.indexOf("?") >= 0 ? "&" : "?"
					;
					params = params + $form.serialize();
					parameters.href = href + params;
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
				if (cache.has(cacheId)) {
					this.onSuccess(cache.get(cacheId), parameters);
					this.onComplete({}, parameters);
				}
				else {
					$.ajax(
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
				$("#c" + parameters.contentid + " .waiting").clearQueue().fadeIn();
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
				$("#c" + parameters.contentid + " .waiting").clearQueue().fadeOut();
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
			
			$el = $(element);
			// wir suchen die contentid! (id="c516")
			if (_self.fn.isNumeric($el.data("ajaxreplaceid").slice(1))) {
				$content = $el;
			}

			if (typeof $content !== "undefined") {
				// Abbruch bei nicht vorhandenem Element
				$content = $("#" + $content.data("ajaxreplaceid"));
				if (typeof $content === "undefined") {
					return false;
				}
			}
			else {
				$el.parents("div[id^='c']").each(
					function(index, element) {
						$content = $(element);
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
				$content.append($("<div>").addClass("waiting").hide());
			}
			
			if (_self.Request.updateContent($el, $content)) {
				event.preventDefault();
			}
		};
		
		this.replaceContent = function(contentId, html, from, to) {
			var $cOld = $("#c" + contentId), $cNew,
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
					$cOld.wrap($("<div>").addClass("ajax-wrap"));
					$slidebox = $cOld.parent();
					$slidebox.css({"position" : "relative", "overflow" : "hidden"});
				}
				$slidebox.width(old.width).height(old.height);
				$cOld.css({"position" : "absolute", "top" : 0, "left" : 0, "width" : old.width});
				// das alte element
				$cOld.attr('id', contentId + '-old').addClass("ajax-content-old");
				$cNew = left ? $(html).insertAfter($cOld) : $(html).insertBefore($cOld);
				if ($cNew.length === 0) {
					from = to = 0;
				}
				else {
					$cNew.css({"position" : "absolute", "top" : 0, "left" : old.width * (left ? +1 : -1), "width" : old.width});
					$slidebox.find(".waiting").clearQueue().fadeOut();
					$slidebox.animate({"height" : $cNew.height()}, animateTime);
					$cOld.animate({"left" : old.width * (left ? -1 : +1)}, animateTime);
					$cNew.animate({"left" : 0}, animateTime, function () {
						w.setTimeout(function() {
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
	
	// add lib to basic library
	if (typeof DMK.Libraries === "object") {
		DMK.Libraries.add(
			DMK.Base.extend(ContentLoader), "ContentLoader"
		);
	}
	// fallback, add singelton to window.
	else {
		DMK.ContentLoader = new ContentLoader();
		$(DMK.ContentLoader.init);
	}
	
})(DMK, window, jQuery);
