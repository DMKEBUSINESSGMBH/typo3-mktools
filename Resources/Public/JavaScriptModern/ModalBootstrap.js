/**
 * ModalBootstrap
 *
 * Lightbox library using the native Bootstrap Modal API.
 *
 * @copyright Copyright (c) 2014 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 * @version 0.1.0
 * @requires Base, Request, Bootstrap Modal
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */

import {Base, DMK} from "./Base.js";
import {Request} from "./Request.js";

/**
 * Sample to set the page type:
 * DMK.ModalBootstrap.setData("pageType", 99);
 *
 * To customize requests, extend DMK.Objects.ModalBootstrapAjaxRequest.
 */

class ModalBootstrapAjaxRequest extends Request {}

export class ModalBootstrap extends Base {
    constructor(options = {}) {
        super({
            // {modal-id} is replaced with a unique ID.
            template:
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
            // true: all calls use one modal. false: each call gets a separate modal.
            singlebox: true,
            // This number must match PAGE.typeNum in TypoScript.
            pageType: 9266,
            ...options
        });
        this.setData("version", "0.1.0");
        this._eventController = null;
        this._formBoxes = new WeakSet();
        this._idCounter = 0;
        this._onFormSubmit = this.onFormSubmit.bind(this);
        this._onModalClick = this.onModalClick.bind(this);
    }

    init() {
        this.initLinks();
        this.initForms(this.getBox());
    }

    // Initialize all links which should open in a modal.
    initLinks() {
        try {
            this._eventController?.abort();
            this._eventController = new AbortController();
            document.body.addEventListener("click", (event) => {
                const link = event.target.closest?.("a.modal-ajax:not(.no-ajax)");
                if (!link) {
                    return;
                }
                event.preventDefault();
                this.open(link);
            }, {signal: this._eventController.signal});
        } catch (error) {
            console.error("DMK.ModalBootstrap: unable to initialize links.", error);
        }
    }

    // Initialize all forms inside the modal.
    initForms(box) {
        try {
            box = box || this.getBox();
            if (!box || this._formBoxes.has(box)) {
                return;
            }
            box.addEventListener("submit", this._onFormSubmit);
            box.addEventListener("click", this._onModalClick);
            this._formBoxes.add(box);
        } catch (error) {
            console.error("DMK.ModalBootstrap: unable to initialize modal forms.", error);
        }
    }

    onFormSubmit(event) {
        try {
            const form = event.target.closest?.("form:not(.no-ajax)");
            if (!form) {
                return;
            }
            event.preventDefault();
            this.open(form, event.submitter);
        } catch (error) {
            console.error("DMK.ModalBootstrap: unable to submit modal form.", error);
        }
    }

    onModalClick(event) {
        if (event.target.closest?.("[data-dismiss='modal']")) {
            event.preventDefault();
            this.close(event.currentTarget);
        }
    }

    // Open the modal or make the Ajax call.
    async open(element, submitter = null) {
        try {
            const request = DMK.Objects.getInstance("ModalBootstrapAjaxRequest");
            if (!request) {
                throw new Error("ModalBootstrapAjaxRequest is not registered.");
            }
            request.submitter = submitter;
            const parameters = {type: this.getData("pageType")};
            const baseOnSuccess = request.onSuccess.bind(request);
            request.onSuccess = async (data, requestParameters, textStatus, response) => {
                this.updateContent(data, requestParameters);
                await baseOnSuccess(data, requestParameters, textStatus, response);
            };

            // Display image links directly in the modal.
            if (element.matches("a")) {
                const path = new URL(element.href, document.baseURI).pathname.toLowerCase();
                if (/\.(jpg|jpeg|gif|png|tiff|bmp)$/.test(path)) {
                    await request.onStart({}, parameters);
                    await request.onSuccess(`<img src="${this.escapeAttribute(element.href)}" alt="">`, parameters);
                    await request.onComplete(null, parameters, null, null);
                    return true;
                }
            }

            return await request.doCall(element, parameters);
        } catch (error) {
            console.error("DMK.ModalBootstrap: unable to open modal content.", error);
            return null;
        }
    }

    escapeAttribute(value) {
        const element = document.createElement("span");
        element.textContent = value;
        return element.innerHTML.replace(/"/g, "&quot;");
    }

    // Replace the response content and show the modal.
    updateContent(data) {
        try {
            const box = this.getBox();
            const body = box.querySelector(".modal-body");
            if (!body) {
                throw new Error("The modal template has no .modal-body element.");
            }
            body.innerHTML = data;

            if (!this.getData("singlebox")) {
                document.querySelectorAll(".modal-ajax.show").forEach((visibleBox) => {
                    this.hideModal(visibleBox);
                });
            }
            this.showModal(box);
        } catch (error) {
            console.error("DMK.ModalBootstrap: unable to update modal content.", error);
        }
    }

    showModal(box) {
        const Modal = window.bootstrap?.Modal;
        if (!Modal) {
            throw new Error("Bootstrap Modal is required to display modal-ajax elements.");
        }
        Modal.getOrCreateInstance(box).show();
    }

    hideModal(box) {
        const Modal = window.bootstrap?.Modal;
        if (Modal) {
            Modal.getInstance(box)?.hide();
        } else {
            box.classList.remove("show");
            box.style.display = "none";
            box.setAttribute("aria-hidden", "true");
        }
    }

    close(box) {
        try {
            box = box instanceof Element ? box : this.getBox();
            this.hideModal(box);
        } catch (error) {
            console.error("DMK.ModalBootstrap: unable to close modal.", error);
        }
    }

    // Return or create the modal HTML.
    getBox() {
        try {
            let id = "modal-ajax";
            let box = document.getElementById(id);

            // Create a separate modal when the current one is already open.
            if (box?.classList.contains("show")) {
                if (this.getData("singlebox")) {
                    return box;
                }
                id = `${id}-${Date.now()}-${++this._idCounter}`;
                box = null;
            }

            if (!box) {
                const template = this.getData("template").replaceAll("{modal-id}", id);
                const fragment = document.createRange().createContextualFragment(template);
                box = fragment.firstElementChild;
                document.body.append(box);
                this.initForms(box);
            }
            return box;
        } catch (error) {
            console.error("DMK.ModalBootstrap: unable to create modal.", error);
            throw error;
        }
    }
}

DMK.Objects.add(ModalBootstrapAjaxRequest, "ModalBootstrapAjaxRequest");
DMK.Libraries.add(ModalBootstrap);

export default ModalBootstrap;
