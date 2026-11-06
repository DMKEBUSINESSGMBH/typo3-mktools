/**
 * Legacy Foundation Reveal lightbox
 *
 * This file keeps the LightBox class name and selectors. Foundation's old
 * plugin integration has been replaced with native DOM and Fetch APIs.
 *
 * @copyright Copyright (c) 2014 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 * @version 0.1.0
 * @requires Base, Request, Fetch API
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */

import {Base, DMK} from "./Base.js";
import {Request} from "./Request.js";

class LightBoxAjaxRequest extends Request {}

export class LightBox extends Base {
    constructor() {
        super({version: "0.1.0"});
        this._eventController = null;
        this._formBoxes = new WeakSet();
        this._idCounter = 0;
        this._onFormSubmit = this.onFormSubmit.bind(this);
        this._onBoxClick = this.onBoxClick.bind(this);
        this._onKeyDown = this.onKeyDown.bind(this);
    }

    init() {
        this.initLinks();
        this.initForms(this.getBox());
        document.removeEventListener("keydown", this._onKeyDown);
        document.addEventListener("keydown", this._onKeyDown);
    }

    initLinks() {
        try {
            this._eventController?.abort();
            this._eventController = new AbortController();
            document.body.addEventListener("click", (event) => {
                if (event.target.matches?.(".reveal-modal-bg")) {
                    this.close();
                    return;
                }
                const link = event.target.closest?.("a.modal-ajax:not(.no-ajax)");
                if (!link) {
                    return;
                }
                event.preventDefault();
                this.open(link);
            }, {signal: this._eventController.signal});
        } catch (error) {
            console.error("DMK.LightBox: unable to initialize links.", error);
        }
    }

    initForms(box) {
        try {
            box = box || this.getBox();
            if (!box || this._formBoxes.has(box)) {
                return;
            }
            box.addEventListener("submit", this._onFormSubmit);
            box.addEventListener("click", this._onBoxClick);
            this._formBoxes.add(box);
        } catch (error) {
            console.error("DMK.LightBox: unable to initialize modal forms.", error);
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
            console.error("DMK.LightBox: unable to submit modal form.", error);
        }
    }

    onBoxClick(event) {
        if (event.target.closest?.(".close-reveal-modal")) {
            event.preventDefault();
            this.close(event.currentTarget);
        }
        if (event.target.classList.contains("reveal-modal-bg")) {
            this.close(event.currentTarget);
        }
    }

    onKeyDown(event) {
        if (event.key === "Escape") {
            this.close();
        }
    }

    async open(element, submitter = null) {
        try {
            const box = this.getBox();
            const request = DMK.Objects.getInstance("LightBoxAjaxRequest");
            if (!request) {
                throw new Error("LightBoxAjaxRequest is not registered.");
            }
            request.submitter = submitter;

            const parameters = {
                type: 400,
                requestType: element.matches("a") ? "GET" : undefined
            };
            if (element.classList.contains("external-modal-ajax")) {
                parameters.external = "true";
            }

            const baseOnSuccess = request.onSuccess.bind(request);
            request.onSuccess = async (data, requestParameters, textStatus, response) => {
                box.innerHTML = '<a class="close-reveal-modal">&times;</a>';
                const body = document.createRange().createContextualFragment(data);
                box.append(body);
                this.show(box);
                await baseOnSuccess(data, requestParameters, textStatus, response);
            };

            return await request.doCall(element, parameters);
        } catch (error) {
            console.error("DMK.LightBox: unable to load modal content.", error);
            return null;
        }
    }

    close(box) {
        try {
            box = box instanceof Element ? box : document.querySelector(".reveal-modal.open");
            if (!box) {
                return;
            }
            box.classList.remove("open");
            box.removeAttribute("aria-modal");
            box.setAttribute("aria-hidden", "true");
            document.querySelectorAll(".reveal-modal-bg").forEach((background) => background.remove());
            document.body.classList.remove("reveal-modal-open");
        } catch (error) {
            console.error("DMK.LightBox: unable to close modal.", error);
        }
    }

    show(box) {
        box.classList.add("open");
        box.setAttribute("role", "dialog");
        box.setAttribute("aria-modal", "true");
        box.setAttribute("aria-hidden", "false");
        document.body.classList.add("reveal-modal-open");

        let background = document.querySelector(".reveal-modal-bg");
        if (!background) {
            background = document.createElement("div");
            background.className = "reveal-modal-bg";
            document.body.append(background);
        }
        // Keep the backdrop behind the dialog in DOM stacking order.
        document.body.append(background, box);
        box.focus({preventScroll: true});
    }

    getBox() {
        try {
            let id = "reveal-ajax";
            let box = document.getElementById(id);
            if (box?.classList.contains("open")) {
                id = `${id}-${Date.now()}-${++this._idCounter}`;
                box = null;
            }

            if (!box) {
                box = document.createElement("div");
                box.id = id;
                box.className = "reveal-modal medium";
                box.tabIndex = -1;
                box.setAttribute("aria-hidden", "true");
                box.innerHTML = '<a class="close-reveal-modal">&times;</a>';
                document.body.append(box);
                this.initForms(box);
            }
            return box;
        } catch (error) {
            console.error("DMK.LightBox: unable to create modal.", error);
            throw error;
        }
    }
}

DMK.Objects.add(LightBoxAjaxRequest, "LightBoxAjaxRequest");
DMK.Libraries.add(LightBox);

export default LightBox;
