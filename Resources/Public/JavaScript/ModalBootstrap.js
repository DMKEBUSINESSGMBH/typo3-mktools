/**
 * ModalBootstrap
 *
 * Native lightbox with Bootstrap modal markup
 *
 * @copyright Copyright (c) 2014 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 * @version 0.1.0
 * @requires Base, Request
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
(function(DMK, w){
    "use strict";
    var AjaxRequest = DMK.Request.extend(function AjaxRequest() {}), nextId = 0,
        ModalBootstrap = DMK.Base.extend(function ModalBootstrap(options) {
            this.setData(Object.assign({
                template:
                    '<div class="modal fade modal-ajax" id="{modal-id}" tabindex="-1" role="dialog" aria-label="Dialog" aria-hidden="true">' +
                        '<div class="modal-dialog"><div class="modal-content">' +
                            '<div class="modal-header">' +
                                '<button type="button" title="schließen" class="close" data-dismiss="modal" aria-label="schließen"><span aria-hidden="true">&times;</span></button>' +
                            '</div>' +
                            '<div class="modal-body" id="{modal-id}-body"></div>' +
                        '</div></div>' +
                    '</div>',
                singlebox: true,
                pageType: 9266
            }, options));
            this.setData("version", "0.1.0");
        });

    ModalBootstrap.prototype.init = function() {
        this.initLinks();
        this.initForms();
    };
    ModalBootstrap.prototype.initLinks = function() {
        var self = this;
        this.delegate(w.document.body, "click", "dmk.modal.bootstrap", "a.modal-ajax:not(.no-ajax)", function(event, element) {
            event.preventDefault();
            self.open(element);
        });
    };
    ModalBootstrap.prototype.initForms = function(box) {
        var self = this;
        box = box || this.getBox();
        this.delegate(box, "submit", "dmk.modal.bootstrap", "form:not(.no-ajax)", function(event, element) {
            event.preventDefault();
            self.open(element, event.submitter);
        });
        this.delegate(box, "click", "dmk.modal.bootstrap.close", "[data-dismiss='modal'], [data-bs-dismiss='modal']", function(event) {
            event.preventDefault();
            self.close(box);
        });
    };
    ModalBootstrap.prototype.open = function(element, submitter) {
        var self = this, request = DMK.Objects.getInstance("ModalBootstrapAjaxRequest"),
            parameters = {type: this.getData("pageType")};
        request.onSuccess = function(data, parameters, textStatus, xhr) {
            self.updateContent(data, parameters);
            this.parent().onSuccess.call(this, data, parameters, textStatus, xhr);
        };
        if (element.matches("a") && /\.(jpg|jpeg|gif|png|tiff|bmp)$/i.test(new w.URL(element.href).pathname)) {
            var image = w.document.createElement("img");
            image.src = element.href;
            image.alt = element.getAttribute("title") || "";
            request.onStart({}, parameters);
            request.onSuccess(image.outerHTML, parameters);
            request.onComplete({}, parameters);
            return true;
        }
        return request.doCall(element, parameters, submitter);
    };
    ModalBootstrap.prototype.updateContent = function(data, parameters) {
        var self = this, box = this.getBox(), body = box.querySelector(".modal-body"),
            range = w.document.createRange();
        range.selectNodeContents(body);
        body.replaceChildren(range.createContextualFragment(data));
        if (!this.getData("singlebox")) {
            w.document.querySelectorAll(".modal-ajax.open").forEach(function(openBox) {
                self.close(openBox);
            });
        }
        this.showModal(box);
    };
    ModalBootstrap.prototype.close = function(box) {
        this.hideModal(this.isElement(box) ? box : this.getBox());
    };
    ModalBootstrap.prototype.getBox = function() {
        var id = "modal-ajax", box = w.document.getElementById(id);
        if (box && box.classList.contains("open") && !this.getData("singlebox")) {
            id += "-" + (++nextId);
            box = null;
        }
        if (!box) {
            var template = w.document.createElement("template");
            template.innerHTML = this.getData("template").replace(/{modal-id}/g, id);
            box = template.content.firstElementChild;
            w.document.body.append(box);
            this.initForms(box);
        }
        return box;
    };

    DMK.Objects.add(AjaxRequest, "ModalBootstrapAjaxRequest");
    DMK.Libraries.add(ModalBootstrap);
})(DMK, window);
