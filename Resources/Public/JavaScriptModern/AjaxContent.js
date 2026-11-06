/**
 * AjaxContent
 *
 * TYPO3 Ajax content library. It automatically requests and replaces the
 * content element associated with selected links and forms.
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

/**
 * Sample to set the page type:
 * DMK.AjaxContent.setData("pageType", 99);
 *
 * To customize requests, extend DMK.Objects.AjaxContentAjaxRequest.
 */

class AjaxContentAjaxRequest extends Request {
    getLoader() {
        return null;
    }
}

export class AjaxContent extends Base {
    constructor() {
        super({version: "0.1.0", pageType: 9267});
        this._eventController = null;
    }

    init() {
        try {
            this._eventController?.abort();
            this._eventController = new AbortController();
            const options = {signal: this._eventController.signal};

            // Click events
            document.body.addEventListener("click", (event) => {
                const element = event.target.closest?.(".ajax-links a, a.ajax-link");
                if (element) {
                    this.handleAjaxClick(event, element);
                }

                const autoTrigger = event.target.closest?.(
                    "form.ajax-autotrigger input:not([type='text']):not([type='search'])"
                );
                if (autoTrigger) {
                    this.handleAjaxClick(event, autoTrigger);
                }
            }, options);

            // Submit events
            document.body.addEventListener("submit", (event) => {
                const form = event.target.closest?.("form.ajax-form");
                if (form) {
                    this.handleAjaxClick(event, form);
                }
            }, options);

            // Select changes trigger the containing form.
            document.body.addEventListener("change", (event) => {
                const select = event.target.closest?.("form.ajax-autotrigger select");
                if (select) {
                    this.handleAjaxClick(event, select);
                }
            }, options);

            document.querySelectorAll(".ajax-links-autoload").forEach((element) => {
                this.handleAjaxClick(null, element);
            });
        } catch (error) {
            console.error("DMK.AjaxContent: unable to initialize event handlers.", error);
        }
    }

    handleAjaxClick(event, element) {
        try {
            element = element || event?.currentTarget;
            if (!(element instanceof Element)) {
                return null;
            }

            // Do not request links with a target or a non-HTTP URL scheme.
            if (element.matches("a")) {
                const scheme = new URL(element.href, document.baseURI).protocol.toLowerCase();
                if (element.target.length > 0 || [
                    "javascript:", "mailto:", "tel:", "fax:", "about:", "data:"
                ].includes(scheme)) {
                    return null;
                }
            }

            if (element.classList.contains("ajax-autotrigger-ignore")) {
                return null;
            }

            let content = null;
            const replaceId = element.getAttribute("data-ajaxreplaceid")
                || element.dataset.ajaxreplaceid
                || element.dataset.ajaxReplaceId;

            // Find the content element (e.g. id="c516").
            if (replaceId && this.isNumeric(replaceId.slice(1))) {
                content = document.getElementById(replaceId);
            } else {
                let parent = element.parentElement;
                while (parent) {
                    if (parent.matches("div[id^='c'], section[id^='c'], article[id^='c']")
                        && this.isNumeric(parent.id.slice(1))) {
                        content = parent;
                        break;
                    }
                    parent = parent.parentElement;
                }
            }

            // No content element was found, so there is nothing to replace.
            if (!content) {
                return null;
            }

            content.classList.add("ajax-content");
            let waiting = content.querySelector(".waiting");
            if (!waiting) {
                waiting = document.createElement("div");
                waiting.classList.add("waiting");
                waiting.style.display = "none";
                content.append(waiting);
            }

            const contentId = replaceId ? replaceId.slice(1) : content.id.slice(1);
            const parameters = {
                type: this.getData("pageType"),
                contentid: contentId,
                mktoolsAjaxRequest: true
            };

            const linkWrap = element.parentElement;
            if (linkWrap?.classList.contains("ajax-link-next")
                || linkWrap?.classList.contains("browse_next")) {
                parameters.page = "next";
            } else if (linkWrap?.classList.contains("ajax-link-prev")
                || linkWrap?.classList.contains("browse_prev")) {
                parameters.page = "prev";
            } else {
                parameters.page = "";
            }

            // Decide whether the action URL is written to browser history.
            if (!element.classList.contains("ajax-no-history")) {
                parameters.useHistory = true;
            }

            const request = DMK.Objects.getInstance("AjaxContentAjaxRequest");
            if (!request) {
                throw new Error("AjaxContentAjaxRequest is not registered.");
            }
            if (event?.submitter instanceof HTMLElement) {
                request.submitter = event.submitter;
            }

            const baseOnStart = request.onStart.bind(request);
            const baseOnComplete = request.onComplete.bind(request);
            const baseOnSuccess = request.onSuccess.bind(request);

            request.onStart = async (data, requestParameters) => {
                await baseOnStart(data, requestParameters);
                this.showWaiting(content, true);
            };
            request.onComplete = async (data, requestParameters, response, textStatus) => {
                try {
                    await baseOnComplete(data, requestParameters, response, textStatus);
                } finally {
                    this.showWaiting(content, false);
                }
            };
            request.onSuccess = async (data, requestParameters, textStatus, response) => {
                await baseOnSuccess(data, requestParameters, textStatus, response);
                const from = requestParameters.page === "prev" ? 1 : 0;
                const to = requestParameters.page === "next" ? 1 : 0;
                const customMethod = element.dataset.ajaxCustomReplaceMethod;

                if (customMethod && typeof this[customMethod] === "function") {
                    this[customMethod](requestParameters.contentid, data, from, to);
                } else {
                    this.replaceContent(requestParameters.contentid, data, from, to);
                }
            };

            if (element.classList.contains("notcachable")) {
                request.getCache = () => null;
            }

            const result = request.doCall(element, parameters);
            if (result && event && !element.classList.contains("test")
                && !element.classList.contains("ajax-dont-prevent-default")) {
                event.preventDefault();
            }
            return result;
        } catch (error) {
            console.error("DMK.AjaxContent: unable to handle Ajax action.", error);
            return null;
        }
    }

    showWaiting(content, visible) {
        try {
            const waiting = content.querySelector(".waiting");
            if (!waiting) {
                return;
            }
            waiting.hidden = !visible;
            waiting.style.display = visible ? "" : "none";
            if (visible && getComputedStyle(waiting).display === "none") {
                waiting.style.display = "block";
            }
            waiting.setAttribute("aria-hidden", String(!visible));
            if (visible && typeof waiting.animate === "function") {
                waiting.animate([{opacity: 0}, {opacity: 1}], {duration: 150});
            }
        } catch (error) {
            console.error("DMK.AjaxContent: unable to update loading indicator.", error);
        }
    }

    replaceContent(contentId, html, from, to) {
        try {
            const oldContent = document.getElementById(`c${contentId}`);
            if (!oldContent) {
                return;
            }

            const parsed = new DOMParser().parseFromString(String(html), "text/html");
            const nodes = Array.from(parsed.body.childNodes);
            const newContent = nodes.find((node) => node instanceof Element
                && node.id === `c${contentId}`)
                || nodes.find((node) => node instanceof Element);

            // Slide next/previous content into the existing content wrapper.
            if (from !== to && newContent) {
                let slideBox = oldContent.parentElement;
                const oldSize = {
                    width: oldContent.getBoundingClientRect().width,
                    height: oldContent.getBoundingClientRect().height
                };

                if (!slideBox.classList.contains("ajax-wrap")) {
                    const wrapper = document.createElement("div");
                    wrapper.classList.add("ajax-wrap");
                    oldContent.before(wrapper);
                    wrapper.append(oldContent);
                    slideBox = wrapper;
                    slideBox.style.position = "relative";
                    slideBox.style.overflow = "hidden";
                }

                slideBox.style.width = `${oldSize.width}px`;
                slideBox.style.height = `${oldSize.height}px`;
                oldContent.style.position = "absolute";
                oldContent.style.top = "0";
                oldContent.style.left = "0";
                oldContent.style.width = `${oldSize.width}px`;
                oldContent.id = `${contentId}-old`;
                oldContent.classList.add("ajax-content-old");

                const movingLeft = from < to;
                const fragment = document.createDocumentFragment();
                for (const node of nodes) {
                    fragment.append(node);
                }
                if (movingLeft) {
                    oldContent.after(fragment);
                } else {
                    oldContent.before(fragment);
                }

                newContent.style.position = "absolute";
                newContent.style.top = "0";
                newContent.style.left = `${oldSize.width * (movingLeft ? 1 : -1)}px`;
                newContent.style.width = `${oldSize.width}px`;
                const newHeight = newContent.getBoundingClientRect().height;
                const direction = movingLeft ? -1 : 1;

                const animation = Promise.all([
                    this.animateElement(slideBox, {height: `${newHeight}px`}, 1000),
                    this.animateElement(oldContent, {left: `${oldSize.width * direction}px`}, 1000),
                    this.animateElement(newContent, {left: "0px"}, 1000)
                ]);
                this.showWaiting(slideBox, false);

                animation.catch((error) => {
                    console.error("DMK.AjaxContent: slide animation failed.", error);
                }).finally(() => {
                    window.setTimeout(() => oldContent.remove(), 250);
                });
            } else {
                const fragment = document.createDocumentFragment();
                for (const node of nodes) {
                    fragment.append(node);
                }
                oldContent.replaceWith(fragment);
            }

            this.onAfterReplaceContent(contentId);
        } catch (error) {
            console.error("DMK.AjaxContent: unable to replace content.", error);
        }
    }

    animateElement(element, styles, duration) {
        if (typeof element.animate !== "function") {
            Object.assign(element.style, styles);
            return Promise.resolve();
        }

        const from = {};
        const to = {};
        for (const [property, value] of Object.entries(styles)) {
            const cssProperty = property.replace(/[A-Z]/g, (letter) => `-${letter.toLowerCase()}`);
            from[property] = getComputedStyle(element).getPropertyValue(cssProperty);
            to[property] = value;
        }

        const animation = element.animate([from, to], {duration, fill: "forwards"});
        return animation.finished.then(() => {
            Object.assign(element.style, styles);
            animation.cancel();
        });
    }

    /** Overwrite this method to run code after the content is replaced. */
    onAfterReplaceContent(contentId) {}
}

DMK.Objects.add(AjaxContentAjaxRequest, "AjaxContentAjaxRequest");
DMK.Libraries.add(AjaxContent);

export default AjaxContent;
