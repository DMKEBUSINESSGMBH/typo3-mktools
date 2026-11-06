/**
 * AjaxContent
 *
 * Typo3 Ajax-Content Lib.
 *
 * Fuehrt automatisch einen Ajax call fuer bestimmte Links oder Formulare durch.
 * Dabei wird automatisch die ContentId ermittelt und genau dieses Element
 * neu gerendert und ersetzt.
 *
 * @copyright Copyright (c) 2014 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * @license http://www.gnu.org/licenses/lgpl.html
 *             GNU Lesser General Public License, version 3 or later
 * @version 0.1.0
 * @requires Base, Request
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
/*
 * Sample to set the PageType:
 * DMK.AjaxContent.setData("pageType", 99);
 */
/*
 * Sample to override the RequestCall:
 *     DMK.Objects.AjaxContentAjaxRequest.prototype.onSuccess = function(data, parameters) {
 *         // do some thinks
 *     }
 */
(function(DMK, w){
    "use strict";
    var AjaxRequest = DMK.Request.extend(function AjaxRequest() {}),
        AjaxContent = DMK.Base.extend(function AjaxContent() {
            this.setData("version", "0.1.0");
            this.setData("pageType", 9267);
        });

    AjaxRequest.prototype.getLoader = function() { return null; };

    AjaxContent.prototype.init = function() {
        var self = this, body = w.document.body,
            handler = function(event, element) {
                self.handleAjaxClick(event, element);
            };
        this.delegate(body, "click", "ajaxcontentlinks", ".ajax-links a, a.ajax-link", handler);
        this.delegate(body, "submit", "ajaxcontentform", "form.ajax-form", handler);
        this.delegate(body, "click", "ajaxcontentform", "form.ajax-autotrigger input[type]:not([type='text']):not([type='search'])", handler);
        this.delegate(body, "change", "ajaxcontentform", "form.ajax-autotrigger select", handler);
        body.querySelectorAll(".ajax-links-autoload").forEach(function(element) {
            self.handleAjaxClick(null, element);
        });
    };

    AjaxContent.prototype.handleAjaxClick = function(event, element) {
        var self = this, request = DMK.Objects.getInstance("AjaxContentAjaxRequest"),
            parameters = {type: this.getData("pageType")}, content, replaceId, linkwrap, xhr;
        element = element || (event && event.currentTarget);
        if (!this.isElement(element)) {
            return;
        }
        if (element.matches("a") && (element.target.length > 0 || /^(javascript|mailto|tel|fax|about|data):/i.test(element.href))) {
            return;
        }
        if (element.classList.contains("ajax-autotrigger-ignore")) {
            return;
        }
        replaceId = element.dataset.ajaxreplaceid;
        if (replaceId && this.isNumeric(replaceId.slice(1))) {
            content = w.document.getElementById(replaceId);
        } else {
            content = element.parentElement;
            while (content && !(content.matches("div[id^='c'], section[id^='c'], article[id^='c']") && this.isNumeric(content.id.slice(1)))) {
                content = content.parentElement;
            }
        }
        if (!content) {
            return;
        }
        content.classList.add("ajax-content");
        if (!content.querySelector(".waiting")) {
            var loader = w.document.createElement("div");
            loader.className = "waiting";
            loader.style.display = "none";
            content.append(loader);
        }
        parameters.contentid = (content.dataset.ajaxreplaceid || content.id).slice(1);
        parameters.mktoolsAjaxRequest = true;
        linkwrap = element.parentElement;
        if (linkwrap && linkwrap.matches(".ajax-link-next, .browse_next")) {
            parameters.page = "next";
        } else if (linkwrap && linkwrap.matches(".ajax-link-prev, .browse_prev")) {
            parameters.page = "prev";
        } else {
            parameters.page = "";
        }
        if (!element.classList.contains("ajax-no-history")) {
            parameters.useHistory = true;
        }
        request.onStart = function(data, parameters) {
            this.parent().onStart.call(this, data, parameters);
            content.querySelectorAll(".waiting").forEach(function(loader) {
                loader.style.display = "block";
            });
        };
        request.onComplete = function(data, parameters, xhr, textStatus) {
            this.parent().onComplete.call(this, data, parameters, xhr, textStatus);
            content.querySelectorAll(".waiting").forEach(function(loader) {
                loader.style.display = "none";
            });
        };
        request.onSuccess = function(data, parameters, textStatus, xhr) {
            this.parent().onSuccess.call(this, data, parameters, textStatus, xhr);
            var from = parameters.page === "prev" ? 1 : 0,
                to = parameters.page === "next" ? 1 : 0,
                method = element.dataset.ajaxCustomReplaceMethod || "replaceContent";
            self[method](parameters.contentid, data, from, to);
        };
        if (element.classList.contains("notcachable")) {
            request.getCache = function() { return false; };
        }
        xhr = request.doCall(element, parameters, event && event.submitter);
        if (xhr && event && !element.classList.contains("test") && !element.classList.contains("ajax-dont-prevent-default")) {
            event.preventDefault();
        }
        return xhr;
    };

    AjaxContent.prototype.replaceContent = function(contentId, html, from, to) {
        var oldContent = w.document.getElementById("c" + contentId);
        if (!oldContent) {
            return;
        }
        var range = w.document.createRange();
        range.selectNode(oldContent);
        var fragment = range.createContextualFragment(html),
            newContent = fragment.querySelector("[id='c" + contentId + "']") || fragment.firstElementChild;
        if (from != to && newContent && typeof newContent.animate === "function") {
            var slidebox = oldContent.parentElement,
                left = from < to,
                old = oldContent.getBoundingClientRect(),
                originalStyle = newContent.style.cssText;
            if (!slidebox.classList.contains("ajax-wrap")) {
                slidebox = w.document.createElement("div");
                slidebox.className = "ajax-wrap";
                oldContent.before(slidebox);
                slidebox.append(oldContent);
            }
            Object.assign(slidebox.style, {position: "relative", overflow: "hidden", width: old.width + "px", height: old.height + "px"});
            Object.assign(oldContent.style, {position: "absolute", top: "0", left: "0", width: old.width + "px"});
            oldContent.id = contentId + "-old";
            oldContent.classList.add("ajax-content-old");
            if (left) {
                oldContent.after(fragment);
            } else {
                oldContent.before(fragment);
            }
            Object.assign(newContent.style, {position: "absolute", top: "0", left: "0", width: old.width + "px"});
            slidebox.querySelectorAll(".waiting").forEach(function(loader) {
                loader.style.display = "none";
            });
            var options = {duration: 1000, easing: "ease"};
            slidebox.animate([{height: old.height + "px"}, {height: newContent.getBoundingClientRect().height + "px"}], options);
            oldContent.animate([{transform: "translateX(0)"}, {transform: "translateX(" + old.width * (left ? -1 : 1) + "px)"}], options);
            var animation = newContent.animate([{transform: "translateX(" + old.width * (left ? 1 : -1) + "px)"}, {transform: "translateX(0)"}], options);
            function finish() {
                oldContent.remove();
                newContent.style.cssText = originalStyle;
                slidebox.style.width = "";
                slidebox.style.height = "";
            }
            animation.finished.then(finish, finish);
        } else {
            oldContent.replaceWith(fragment);
        }
        this.onAfterReplaceContent(contentId);
    };

    /** Override to initialize content after it has been replaced. */
    AjaxContent.prototype.onAfterReplaceContent = function(contentId) {};

    DMK.Objects.add(AjaxRequest, "AjaxContentAjaxRequest");
    DMK.Libraries.add(AjaxContent);
})(DMK, window);
