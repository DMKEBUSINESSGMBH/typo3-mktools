
/**
 * TODO / FIXME
 * NOCH NICHT VERWENDEN!
 * DAS MUSS ERST UMGEBAUT WERDEN
 * SEE: >>> ModalBootstrap.js <<<
 */

if (typeof DMK !== "object") {
	var DMK = {};
}

// Lightbox  Library which uses foundatioin reval
(function(DMK, $){
	"use strict";
	var LightBox = function LightBox() {
		var _self = this;

		this.init = function() {
			this.initLinks();
			this.initForms();
		};

		this.initLinks = function () {
			// Lightbox mit Ajax-Content
			$('body')
				.off('.dmk.modal')
				.on('click.dmk.modal', 'a.modal-ajax:not(.no-ajax)', function(event) {
					var element = $(this);
					if (element.hasClass('no-ajax')) {
						return true;
					}
					event.preventDefault();
					_self.open(element);
				});
		};

		this.initForms = function (box) {
			box = typeof box === "undefined" ? this.getBox() : box;
			// Lightbox mit Ajax-Content
			box
				.off('.dmk.modal')
				.on('submit.dmk.modal', 'form:not(.no-ajax)', function(event) {
					var element = $(this);
					if (element.hasClass('no-ajax')) {
						return true;
					}
					event.preventDefault();
					_self.open(element);
				});
		};

		this.open = function(el) {
			var $box = this.getBox(),
				data = el.serializeArray(),
				url = el.attr('href') || el.attr('action')
			;
			data.push({'name' : 'type', 'value' : '400'});
			if (el.hasClass('external-modal-ajax')) {
				data.push({'name' : 'external', 'value' : 'true'});
			};
			$box.foundation('reveal', 'open', {
				'url' : url,
				'data' : $.param(data)
			});
		};

		this.close = function() {
			var $box = this.getBox();
			if ($box.hasClass('open')) {
				$box.foundation('reveal', 'close');
			}
		};

		this.getBox = function () {
			var id = 'reveal-ajax',
				$box = $('#'+id)
			;
			// we need a new box for each call,
			// if the current box allready is opened
			// this is needed for foundation reveal
			if ($box.length > 0 && $box.hasClass('open')) {
				id = 'reveal-ajax-' + new Date().getTime();
				$box = $([]);
			}
			// modalbox erzeugen
			if ($box.length === 0) {
				$box = $('<div id="'+id+'" class="reveal-modal medium">');
				$box.append($('<a class="close-reveal-modal">').html('&times;'));
				$('body').append($box);
				$box = $('#'+id);
				$box.bind('opened', function() {
					$box.append($('<a class="close-reveal-modal">').html('&times;'));
				});
				// formulare für jede neue box initialisieren
				this.initForms($box);
			}
			return $box;
		};
	};
	
	// add lib to basic library
	if (typeof DMK.Libraries === "object") {
		DMK.Libraries.add(
			DMK.Base.extend(LightBox), "LightBox"
		);
	}
	// fallback, add singelton to window.
	else {
		DMK.LightBox = new LightBox();
		$(DMK.LightBox.init);
	}
})(DMK, jQuery);