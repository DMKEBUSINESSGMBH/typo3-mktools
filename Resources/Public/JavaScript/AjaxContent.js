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
 * @requires jQuery, Base, Request
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
(function(DMK, w, $){
    "use strict";
    var AjaxRequest, AjaxContent, VERSION = "0.1.0";


    AjaxContent = function AjaxContent() {
        this.setData("version", VERSION);
        this.setData("pageType", 9267);
    };

    // Ajax Request definieren
    AjaxRequest = DMK.Request.extend(function AjaxRequest() {});
    AjaxRequest.prototype.getLoader = function() { return $(); };

    // wir erben von dem basis objekt
    AjaxContent = DMK.Base.extend(AjaxContent);


    AjaxContent.prototype.init = function() {
        var _self = this,
            _event = function(event, element) {
                return _self.handleAjaxClick(event, element);
            }
        ;
        // click events
        $("body")
            .off("click.ajaxcontentlinks")
            .on(
                "click.ajaxcontentlinks",
                ".ajax-links a, a.ajax-link",
                _event
            );
        // submit events
        $("body")
            .off("submit.ajaxcontentform")
            .on(
                "submit.ajaxcontentform",
                "form.ajax-form",
                _event
            );
        // autotriger for forms
        $("body")
            .off("click.ajaxcontentform")
            .on(
                "click.ajaxcontentform",
                "form.ajax-autotrigger input:not(:text)",
                _event
            )
            .off("change.ajaxcontentform")
            .on(
                "change.ajaxcontentform",
                "form.ajax-autotrigger select",
                _event
            );

        $('.ajax-links-autoload').each(function() {
            _self.handleAjaxClick(null, $(this)[0]);
        });
    };

    AjaxContent.prototype.handleAjaxClick = function(event, element) {
        var _self = this, _request = DMK.Objects.getInstance("AjaxContentAjaxRequest"),
            parameters = {type : this.getData("pageType")},
            $el, $linkwrap, $content,
            xhr
        ;

        element = _self.isDefined(element) ? element : event.target;

        // do request only if there is no target attribute
        if (element.tagName.toLowerCase() === "a") {
            if (
                element.target.length > 0
                || (
                    element.href.search(':') >= 0
                    && $.inArray(
                        element.href.split(':').shift().toLowerCase(),
                        // complete list of available schemes:
                        // http://www.iana.org/assignments/uri-schemes/uri-schemes.xhtml
                        ["javascript", "mailto", "tel", "fax", "about", "data"]
                    ) >= 0
                )
            ) {
                return;
            }
        }

        $el = $(element);

        if ($el.hasClass("ajax-autotrigger-ignore")) {
            return;
        }

        // wir suchen die contentid! (e.g. id="c516")
        if ($el.data("ajaxreplaceid") && _self.isNumeric($el.data("ajaxreplaceid").slice(1))) {
            $content = $el;
        }

        if (_self.isDefined($content)) {
            // Abbruch bei nicht vorhandenem Element
            $content = $("#" + $content.data("ajaxreplaceid"));
            if (typeof $content === "undefined") {
                return false;
            }
        }
        else {
            $el.parents("div[id^='c'], section[id^='c'], article[id^='c']").each(
                function(index, element) {
                    $content = $(element);
                    if (_self.isNumeric($content.attr("id").slice(1))) {
                        return false;
                    }
                    return true;
                }
            );
        }
        // kein content element gefunden, wir ersetzen nichts!
        if (!_self.isObjectJQuery($content) || $content.length === 0) {
            return ;
        }

        $content.addClass("ajax-content");
        if ($content.find(".waiting").length === 0) {
            $content.append($("<div>").addClass("waiting").hide());
        }

        // ajax parameter sammeln
        if ($content.data("ajaxreplaceid")) {
            parameters.contentid = $content.data("ajaxreplaceid").slice(1);
        }
        else {
            parameters.contentid = $content.attr("id").slice(1);
        }

        // so we know in DMK\Mktools\ContentObject\UserInternalContentObject
        // that the content should be rendered for real
        parameters.mktoolsAjaxRequest = true;

        $linkwrap = $el.parent();
        if ($linkwrap.hasClass("ajax-link-next") || $linkwrap.hasClass("browse_next")) {
            parameters.page = "next";
        }
        else if ($linkwrap.hasClass("ajax-link-prev") || $linkwrap.hasClass("browse_prev")) {
            parameters.page = "prev";
        }
        else {
            // try to fetch page
            parameters.page = "";
        }

        // decide whether action url is written in history or not
        if (!$el.hasClass("ajax-no-history")) {
            parameters.useHistory = true;
        }

        // die events anlegen
        _request.onStart = function(data, parameters){
            this.parent().onStart.call(this, data, parameters);
            $content.find(".waiting").clearQueue().fadeIn();
        };
        _request.onComplete = function(data, parameters){
            this.parent().onComplete.call(this, data, parameters);
            $content.find(".waiting").clearQueue().fadeOut();
        };
        _request.onSuccess = function(data, parameters) {
            this.parent().onSuccess.call(this, data, parameters);
            var from = 0, to = 0;
            if (parameters.page === "next") {
                to = 1;
            }
            else if (parameters.page === "prev") {
                from = 1;
            }
            _self.replaceContent(parameters.contentid, data, from, to);
        };

        if ($el.hasClass("notcachable")) {
            _request.getCache = function() {
                return false;
            };
        }

        if (xhr = _request.doCall($el, parameters) && event) {
            event.preventDefault();
        }
        return xhr;
    };

    AjaxContent.prototype.replaceContent = function(contentId, html, from, to) {
        var $cOld = $("#c" + contentId), $cNew,
            animateTime = 1000;
        if ($cOld.length === 0) {
            return;
        }
        // wir sliden
        if (from != to) {
            var $slidebox = $cOld.parent(),
                left = from < to, // true > slide to left, false > slide to right
                old = { width : $cOld.width(), height : $cOld.height() };
            // slidebox wrap erzeugen, falls nicht existent.
            if (!$slidebox.hasClass("ajax-wrap")) {
                $cOld.wrap($("<div>").addClass("ajax-wrap"));
                $slidebox = $cOld.parent();
                $slidebox.css({"position" : "relative", "overflow" : "hidden"});
            }
            $slidebox.width(old.width).height(old.height);
            $cOld.css({"position" : "absolute", "top" : 0, "left" : 0, "width" : old.width});
            // das alte element
            $cOld.attr('id', contentId + '-old').addClass("ajax-content-old");
            $cNew = left ? $(html).insertAfter($cOld) : $(html).insertBefore($cOld);
            if ($cNew.length === 0) {
                from = to = 0;
            }
            else {
                $cNew.css({"position" : "absolute", "top" : 0, "left" : old.width * (left ? +1 : -1), "width" : old.width});
                $slidebox.find(".waiting").clearQueue().fadeOut();
                $slidebox.animate({"height" : $cNew.height()}, animateTime);
                $cOld.animate({"left" : old.width * (left ? -1 : +1)}, animateTime);
                $cNew.animate({"left" : 0}, animateTime, function () {
                    w.setTimeout(function() {
                        $cOld.remove();
                    }, 250);
                });
            }

        }

        // normales ersetzen
        if (from == to) {
            $cOld.replaceWith(html);
        }

        this.onAfterReplaceContent();
    };

    /**
     * Overwrite this method if you want to do something after the content is replaced
     */
    AjaxContent.prototype.onAfterReplaceContent = function() {
    };

    // add lib to basic library
    DMK.Objects.add(AjaxRequest, "AjaxContentAjaxRequest");
    DMK.Libraries.add(AjaxContent);
})(DMK, window, jQuery);
