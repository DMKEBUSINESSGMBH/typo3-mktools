/**
 * ModalBootstrap
 *
 * Lightbox library which uses bootstrap modal
 *
 * @copyright Copyright (c) 2014 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 * @version 0.1.0
 * @requires Base, Request
 * Bootstrap 5 (vanilla) oder - falls vorhanden - das Bootstrap 3/4 jQuery Plugin.
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */

/*
 * Sample to set the PageType:
 * DMK.ModalBootstrap.setData("pageType", 99);
 */
/*
 * Sample to override the RequestCall:
 *     DMK.Objects.ModalBootstrapAjaxRequest.prototype.onStart = function(data, parameters) {
 *         // do some thinks
 *     }
 */
(function(DMK, w, d){
    "use strict";
    var AjaxRequest, ModalBootstrap, VERSION = "0.1.0";

    // Ajax Request definieren
    AjaxRequest = DMK.Request.extend(function AjaxRequest() {});

    // die modalbox
    ModalBootstrap = function ModalBootstrap(options) {
        this.setData(
            Object.assign(
                {
                    // Das Template. {modal-id} wird durch eine eindeutige ID ersetzt.
                    template :
                        '<div class="modal fade modal-ajax" id="{modal-id}" tabindex="-1" role="dialog" aria-labelledby="{modal-id}-label" aria-hidden="true">' +
                            '<div class="modal-dialog">' +
                                '<div class="modal-content">' +
                                    '<div class="modal-header">' +
                                        '<a type="button" title="schließen" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-hidden="true"><span aria-hidden="true">&times;</span></a>' +
                                    '</div>' +
                                    '<div class="modal-body" id="{modal-id}-body"></div>' +
                                '</div>' +
                            '</div>' +
                        '</div>',
                    // Option singlebox:    true    =>    jeder Call landet in der gleichen Box
                    //                         false    =>    jeder Call landet in einer separaten Box
                    singlebox : true,
                    // Zahl muss mit PAGE.typeNum im TS uebereinstimmen
                    pageType : 9266
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

    // wir initialisieren alle links, welche im popup geoeffnet werden sollen.
    ModalBootstrap.prototype.initLinks = function () {
        var _self = this;
        this.delegate(d.body, 'click', 'a.modal-ajax:not(.no-ajax)', function(event, element) {
            event.preventDefault();
            _self.open(element);
        }, 'dmk.modal');
    };

    // wir initialisieren alle Formulare, fuers popup
    ModalBootstrap.prototype.initForms = function (box) {
        var _self = this;
        box = typeof box === "undefined" ? this.getBox() : this.toElement(box);
        this.delegate(box, 'submit', 'form:not(.no-ajax)', function(event, element) {
            event.preventDefault();
            _self.open(element);
        }, 'dmk.modal');
    };

    // wir oeffnen das popup, bzw machen den ajax call
    ModalBootstrap.prototype.open = function(el) {
        var _self = this,
            _request = DMK.Objects.getInstance("ModalBootstrapAjaxRequest"),
            parameters = {type : this.getData("pageType")}
        ;
        el = this.toElement(el);
        _request.onSuccess = function(data, parameters) {
            _self.updateContent(data, parameters);
            this.parent().onSuccess.call(this, data, parameters);
        };
        //test if href of element is an image, if so put a <img> tag around it and return
        if (el.matches("a") &&
            /\.(jpg|jpeg|gif|png|tiff|bmp)$/.test(el.href.toLowerCase())
        ) {
            _request.onStart({}, parameters);
            _request.onSuccess('<img src="'+ el.href + '"/>', parameters);
            _request.onComplete('<img src="'+ el.href + '"/>', parameters);
        } else {
            _request.doCall(el, parameters);
        }
    };

    // wir ersetzen den inhalt vom calls und oeffnen die box
    ModalBootstrap.prototype.updateContent = function(data, parameters) {
        var box = this.getBox(),
            body = box.querySelector(".modal-body")
        ;
        body.replaceChildren(this.parseHtml(data));
        // alle bisherigen modalboxen schliessen, wenn wir keine singlebox haben.
        if (!this.getData("singlebox")) {
            d.querySelectorAll('.modal-ajax').forEach(function(modal) {
                if (modal !== box && modal.getClientRects().length > 0) {
                    ModalBootstrap.prototype.toggle(modal, "hide");
                }
            });
        }
        this.toggle(box, "show");
    };

    // zeigt / versteckt eine Modalbox (Bootstrap 5 oder jQuery Plugin von Bootstrap 3/4)
    ModalBootstrap.prototype.toggle = function(box, action) {
        if (w.bootstrap && w.bootstrap.Modal) {
            w.bootstrap.Modal.getOrCreateInstance(box)[action]();
        } else if (w.jQuery && w.jQuery.fn && w.jQuery.fn.modal) {
            w.jQuery(box).modal(action);
        } else {
            throw "DMK.ModalBootstrap requires Bootstrap (bootstrap.Modal)";
        }
    };

    // wir schliessen das popup
    ModalBootstrap.prototype.close = function(box) {
        box = this.isElement(this.toElement(box)) ? this.toElement(box) : this.getBox();
        this.toggle(box, "hide");
    };

    // liefert / erzeugt das html fuer's popup
    ModalBootstrap.prototype.getBox = function () {
        var id = 'modal-ajax',
            box = d.getElementById(id)
        ;
        // we need a new box for each call,
        // if the current box allready is opened
        // this is needed for foundation reveal
        if (box && box.getClientRects().length > 0) {
            if (this.getData("singlebox")) {
                return box;
            }
            id = id + '-' + new Date().getTime();
            box = null;
        }
        // modalbox erzeugen
        if (!box) {
            box = this.parseHtml(this.getData("template").replace(/{modal-id}/g, id)).firstElementChild;
            d.body.appendChild(box);
            // formulare fuer jede neue box initialisieren!
            this.initForms(box);
        }
        return box;
    };

    // wir registrieren unsere lib
    DMK.Objects.add(AjaxRequest, "ModalBootstrapAjaxRequest");
    DMK.Libraries.add(ModalBootstrap);

})(DMK, window, document);
