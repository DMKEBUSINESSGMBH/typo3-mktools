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
(function(DMK, w, d){
    "use strict";
    var AjaxRequest, AjaxContent, VERSION = "0.1.0",
        // complete list of available schemes:
        // http://www.iana.org/assignments/uri-schemes/uri-schemes.xhtml
        IGNORED_SCHEMES = ["javascript", "mailto", "tel", "fax", "about", "data"];


    AjaxContent = function AjaxContent() {
        this.setData("version", VERSION);
        this.setData("pageType", 9267);
    };

    // Ajax Request definieren
    AjaxRequest = DMK.Request.extend(function AjaxRequest() {});
    AjaxRequest.prototype.getLoader = function() { return null; };

    // wir erben von dem basis objekt
    AjaxContent = DMK.Base.extend(AjaxContent);


    AjaxContent.prototype.init = function() {
        var _self = this,
            body = d.body,
            _event = function(event, element) {
                return _self.handleAjaxClick(event, element);
            }
        ;
        // click events
        this.delegate(body, "click", ".ajax-links a, a.ajax-link", _event, "ajaxcontentlinks");
        // submit events
        this.delegate(body, "submit", "form.ajax-form", _event, "ajaxcontentform");
        // autotriger for forms
        this.delegate(
            body, "click",
            "form.ajax-autotrigger input[type]:not([type='text' i]):not([type='search' i])",
            _event, "ajaxcontentform"
        );
        this.delegate(body, "change", "form.ajax-autotrigger select", _event, "ajaxcontentform");

        d.querySelectorAll('.ajax-links-autoload').forEach(function(element) {
            _self.handleAjaxClick(null, element);
        });
    };

    AjaxContent.prototype.handleAjaxClick = function(event, element) {
        var _self = this, _request = DMK.Objects.getInstance("AjaxContentAjaxRequest"),
            parameters = {type : this.getData("pageType")},
            content, linkwrap, replaceId,
            xhr
        ;

        element = _self.toElement(_self.isDefined(element) ? element : event.currentTarget);

        // do request only if there is no target attribute
        if (element.tagName.toLowerCase() === "a") {
            if (
                element.target.length > 0
                || (
                    element.href.search(':') >= 0
                    && IGNORED_SCHEMES.indexOf(element.href.split(':').shift().toLowerCase()) >= 0
                )
            ) {
                return;
            }
        }

        if (element.classList.contains("ajax-autotrigger-ignore")) {
            return;
        }

        // wir suchen die contentid! (e.g. id="c516")
        replaceId = element.dataset.ajaxreplaceid;
        if (replaceId && _self.isNumeric(replaceId.slice(1))) {
            // Abbruch bei nicht vorhandenem Element
            content = d.getElementById(replaceId);
            if (!content) {
                return false;
            }
        }
        else {
            content = element.parentElement;
            while (content) {
                if (
                    content.matches("div[id^='c'], section[id^='c'], article[id^='c']")
                    && _self.isNumeric(content.id.slice(1))
                ) {
                    break;
                }
                content = content.parentElement;
            }
        }
        // kein content element gefunden, wir ersetzen nichts!
        if (!content) {
            return ;
        }

        content.classList.add("ajax-content");
        if (content.querySelector(".waiting") === null) {
            var waiting = d.createElement("div");
            waiting.className = "waiting";
            waiting.style.display = "none";
            content.appendChild(waiting);
        }
        var getWaiting = function() { return content.querySelectorAll(".waiting"); };

        // ajax parameter sammeln
        parameters.contentid = (content.dataset.ajaxreplaceid || content.id).slice(1);

        // so we know in DMK\Mktools\ContentObject\UserInternalContentObject
        // that the content should be rendered for real
        parameters.mktoolsAjaxRequest = true;

        linkwrap = element.parentElement;
        if (linkwrap && (linkwrap.classList.contains("ajax-link-next") || linkwrap.classList.contains("browse_next"))) {
            parameters.page = "next";
        }
        else if (linkwrap && (linkwrap.classList.contains("ajax-link-prev") || linkwrap.classList.contains("browse_prev"))) {
            parameters.page = "prev";
        }
        else {
            // try to fetch page
            parameters.page = "";
        }

        // decide whether action url is written in history or not
        if (!element.classList.contains("ajax-no-history")) {
            parameters.useHistory = true;
        }

        // die events anlegen
        _request.onStart = function(data, parameters){
            this.parent().onStart.call(this, data, parameters);
            getWaiting().forEach(function(el) { _self.fadeIn(el); });
        };
        _request.onComplete = function(data, parameters, response, textStatus){
            this.parent().onComplete.call(this, data, parameters, response, textStatus);
            getWaiting().forEach(function(el) { _self.fadeOut(el); });
        };
        _request.onSuccess = function(data, parameters, textStatus, response) {
            this.parent().onSuccess.call(this, data, parameters, textStatus, response);
            var from = 0, to = 0;
            if (parameters.page === "next") {
                to = 1;
            }
            else if (parameters.page === "prev") {
                from = 1;
            }
            if (element.dataset.ajaxCustomReplaceMethod) {
                _self[element.dataset.ajaxCustomReplaceMethod](parameters.contentid, data, from, to);
            } else {
                _self.replaceContent(parameters.contentid, data, from, to);
            }
        };

        if (element.classList.contains("notcachable")) {
            _request.getCache = function() {
                return false;
            };
        }

        if (
            (xhr = _request.doCall(element, parameters))
            && event
            && !element.classList.contains("test")
        ) {
            if (!element.classList.contains("ajax-dont-prevent-default")) {
                event.preventDefault();
            }
        }
        return xhr;
    };

    AjaxContent.prototype.replaceContent = function(contentId, html, from, to) {
        var _self = this, cOld = d.getElementById("c" + contentId), cNew = [],
            animateTime = 1000, fragment = this.parseHtml(html);
        if (!cOld) {
            return;
        }
        // wir sliden
        if (from != to) {
            var slidebox = cOld.parentElement,
                left = from < to, // true > slide to left, false > slide to right
                style = w.getComputedStyle(cOld),
                old = { width : parseFloat(style.width), height : parseFloat(style.height) };
            // slidebox wrap erzeugen, falls nicht existent.
            if (!slidebox.classList.contains("ajax-wrap")) {
                slidebox = d.createElement("div");
                slidebox.className = "ajax-wrap";
                cOld.parentElement.insertBefore(slidebox, cOld);
                slidebox.appendChild(cOld);
                slidebox.style.position = "relative";
                slidebox.style.overflow = "hidden";
            }
            slidebox.style.width = old.width + "px";
            slidebox.style.height = old.height + "px";
            cOld.style.position = "absolute";
            cOld.style.top = "0";
            cOld.style.left = "0";
            cOld.style.width = old.width + "px";
            // das alte element
            cOld.id = contentId + '-old';
            cOld.classList.add("ajax-content-old");
            cNew = Array.prototype.slice.call(fragment.children);
            if (left) {
                cOld.after(fragment);
            } else {
                cOld.before(fragment);
            }
            if (cNew.length === 0) {
                from = to = 0;
            }
            else {
                cNew.forEach(function(el) {
                    el.style.position = "absolute";
                    el.style.top = "0";
                    el.style.left = (old.width * (left ? +1 : -1)) + "px";
                    el.style.width = old.width + "px";
                });
                slidebox.querySelectorAll(".waiting").forEach(function(el) { _self.fadeOut(el); });
                var newHeight = parseFloat(w.getComputedStyle(cNew[0]).height);
                slidebox.style.height = newHeight + "px";
                this.animate(slidebox, [{height : old.height + "px"}, {height : newHeight + "px"}], animateTime);
                var oldLeft = (old.width * (left ? -1 : +1)) + "px";
                cOld.style.left = oldLeft;
                this.animate(cOld, [{left : "0px"}, {left : oldLeft}], animateTime);
                cNew.forEach(function(el, index) {
                    var startLeft = el.style.left;
                    el.style.left = "0px";
                    _self.animate(el, [{left : startLeft}, {left : "0px"}], animateTime, index > 0 ? null : function () {
                        w.setTimeout(function() {
                            cOld.remove();
                        }, 250);
                    });
                });
            }

        }

        // normales ersetzen
        if (from == to) {
            cOld.replaceWith(fragment);
        }

        this.onAfterReplaceContent(contentId);
    };

    /**
     * Overwrite this method if you want to do something after the content is replaced
     */
    AjaxContent.prototype.onAfterReplaceContent = function(contentId) {
    };

    // add lib to basic library
    DMK.Objects.add(AjaxRequest, "AjaxContentAjaxRequest");
    DMK.Libraries.add(AjaxContent);
})(DMK, window, document);
