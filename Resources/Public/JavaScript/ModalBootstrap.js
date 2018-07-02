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
 * Sample to set the PageType:
 * DMK.ModalBootstrap.setData("pageType", 99);
 */
/*
 * Sample to override the RequestCall:
 *     DMK.Objects.ModalBootstrapAjaxRequest.prototype.onStart = function(data, parameters) {
 *         // do some thinks
 *     }
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
                                        '<a type="button" title="schließen" class="close" data-dismiss="modal" aria-hidden="true"><span aria-hidden="true">&times;</span></a>' +
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
            _request = DMK.Objects.getInstance("ModalBootstrapAjaxRequest"),
            parameters = {type : this.getData("pageType")}
        ;
        _request.onSuccess = function(data, parameters) {
            _self.updateContent(data, parameters);
            this.parent().onSuccess.call(this, data, parameters);
        };
        //test if href of element is an image, if so put a <img> tag around it and return
        if (el.is("a") &&
            /\.(jpg|jpeg|gif|png|tiff|bmp)$/.test(el.get(0).href) == true
        ) {
            _request.onStart({}, parameters);
            _request.onSuccess('<img src="'+ el.get(0).href + '"/>', parameters);
            _request.onComplete('<img src="'+ el.get(0).href + '"/>', parameters);
        } else {
            _request.doCall(el, parameters);
        }
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
