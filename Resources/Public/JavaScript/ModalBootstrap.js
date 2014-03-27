/**
 * ModalBootstrap
 * 
 * Lightbox library which uses bootstrap modal
 * 
 * @copyright Copyright (c) 2014 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 * @version 0.1.0
 * @requires jQuery, Base, Request
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */

/*
 * Sample to override the RequestCall:
 * DMK.Objects.extend(
	"ModalBootstrapAjaxRequest",
	function MyRequest() {
		this.onStart = function(data, parameters) {
			// do some thinks and then call the parent!
			this.parent().onStart.call(this, data, parameters);
		}
	}
);
 */
(function(DMK, $){
	"use strict";
	var AjaxRequest, ModalBootstrap, VERSION = "0.1.0";
	
	// Ajax Request definieren
	AjaxRequest = DMK.Request.extend(function AjaxRequest() {});
	
	// die modalbox
	ModalBootstrap = function ModalBootstrap(options) {
		this.setData(
			$.extend(
				{
					// Das Template. {modal-id} wird durch eine eindeutige ID ersetzt.
					template :
						'<div class="modal fade modal-ajax" id="{modal-id}" tabindex="-1" role="dialog" aria-labelledby="{modal-id}-label" aria-hidden="true">' +
							'<div class="modal-dialog">' +
								'<div class="modal-content">' +
									'<div class="modal-header">' +
										'<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>' +
										'<h4 class="modal-title" id="{modal-id}-label"></h4>' +
									'</div>' +
									'<div class="modal-body" id="{modal-id}-body"></div>' +
								'</div>' +
							'</div>' +
						'</div>',
					// singlebox? soll alles in einer modalmox ablaufen,
					// oder fuer jeden call eine eigene box?
					singlebox : true
				},
				options
			)
		);
		this.setData("version", VERSION);
	};
	
	// ModalBootstrap extends Base
	ModalBootstrap = DMK.Base.extend(ModalBootstrap);
	
	// wir initialisieren das DOM
	ModalBootstrap.prototype.init = function() {
		this.initLinks();
		this.initForms();
	};

	// wir initialisieren alle links, welche im popup geoeffnet werde sollen.
	ModalBootstrap.prototype.initLinks = function () {
		var _self = this;
		$('body')
			.off('.dmk.modal')
			.on('click.dmk.modal',
				'a.modal-ajax:not(.no-ajax)', function(event) {
				var element = $(this);
				if (element.hasClass('no-ajax')) {
					return true;
				}
				event.preventDefault();
				_self.open(element);
			});
	};

	// wir initialisieren alle Formulare, fuers popup
	ModalBootstrap.prototype.initForms = function (box) {
		var _self = this;
		box = typeof box === "undefined" ? this.getBox() : box;
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

	// wir oeffnen das popup, bzw machen den ajax call
	ModalBootstrap.prototype.open = function(el) {
		var _self = this,
			_request = DMK.Objects.getInstance("ModalBootstrapAjaxRequest")
		;
		_request.onSuccess = function(data, parameters){
			_self.updateContent(data, parameters);
		}; 
		_request.doCall(el);
	};

	// wir ersetzen den inhalt vom calls und oeffnen die box
	ModalBootstrap.prototype.updateContent = function(data, parameters) {
		var $box = this.getBox(),
			$body = $box.find(".modal-body")
		;
		$body.html(data);
		// alle bisherigen modalboxen schliessen, wenn wir keine singlebox haben.
		if (!this.getData("singlebox")) {
			$('.modal-ajax:visible').modal("hide");
		}
		$box.modal("show");
	};
	
	// wir schliessen das popup
	ModalBootstrap.prototype.close = function(box) {
		box = this.isObjectJQuery(box) ? box : this.getBox();
		box.modal("hide");
	};

	// liefert / erzeugt das html fuer's popup
	ModalBootstrap.prototype.getBox = function () {
		var id = 'modal-ajax',
			$box = $('#'+id)
		;
		// we need a new box for each call,
		// if the current box allready is opened
		// this is needed for foundation reveal
		if ($box.length > 0 && $box.is(':visible')) {
			if (this.getData("singlebox")) {
				return $box.first();
			}
			id = id + '-' + new Date().getTime();
			$box = $([]);
		}
		// modalbox erzeugen
		if ($box.length === 0) {
			$box = $(this.getData("template").replace(/{modal-id}/g, id));
			$('body').append($box);
			// formulare fuer jede neue box initialisieren!
			this.initForms($box);
		}
		return $box;
	};
	
	// wir registrieren unsere lib
	DMK.Objects.add(AjaxRequest, "ModalBootstrapAjaxRequest");
	DMK.Libraries.add(ModalBootstrap);
	
})(DMK, jQuery);