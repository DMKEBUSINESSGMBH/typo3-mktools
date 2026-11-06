/**
 * Native lightbox with Foundation Reveal markup.
 *
 * @requires Base, Request
 */
(function(DMK, w){
    "use strict";
    var nextId = 0, LightBox = DMK.Base.extend(function LightBox(options) {
        this.setData(Object.assign({pageType: 400}, options));
    });

    LightBox.prototype.init = function() {
        this.initLinks();
        this.initForms();
    };
    LightBox.prototype.initLinks = function() {
        var self = this;
        this.delegate(w.document.body, "click", "dmk.modal.foundation", "a.modal-ajax:not(.no-ajax)", function(event, element) {
            event.preventDefault();
            self.open(element);
        });
    };
    LightBox.prototype.initForms = function(box) {
        var self = this;
        box = box || this.getBox();
        this.delegate(box, "submit", "dmk.modal.foundation", "form:not(.no-ajax)", function(event, element) {
            event.preventDefault();
            self.open(element, event.submitter);
        });
        this.delegate(box, "click", "dmk.modal.foundation.close", ".close-reveal-modal, [data-close]", function(event) {
            event.preventDefault();
            self.close(box);
        });
    };
    LightBox.prototype.open = function(element, submitter) {
        var self = this, box = this.getBox(), request = DMK.Objects.getInstance("Request"),
            parameters = {type: this.getData("pageType")};
        if (element.classList.contains("external-modal-ajax")) {
            parameters.external = true;
        }
        request.onSuccess = function(data) {
            var range = w.document.createRange(), body = box.querySelector(".reveal-body");
            range.selectNodeContents(body);
            body.replaceChildren(range.createContextualFragment(data));
            self.showModal(box);
            box.dispatchEvent(new w.CustomEvent("opened", {bubbles: true}));
        };
        return request.doCall(element, parameters, submitter);
    };
    LightBox.prototype.close = function(box) {
        this.hideModal(this.isElement(box) ? box : w.document.querySelector(".reveal-modal.open"));
    };
    LightBox.prototype.getBox = function() {
        var id = "reveal-ajax", box = w.document.getElementById(id);
        if (box && box.classList.contains("open")) {
            id += "-" + (++nextId);
            box = null;
        }
        if (!box) {
            box = w.document.createElement("div");
            box.id = id;
            box.className = "reveal reveal-modal medium";
            box.setAttribute("role", "dialog");
            box.setAttribute("aria-label", "Dialog");
            box.setAttribute("aria-hidden", "true");
            box.tabIndex = -1;
            box.style.display = "none";
            box.innerHTML = '<div class="reveal-body"></div><button type="button" class="close-reveal-modal" data-close aria-label="schließen">&times;</button>';
            w.document.body.append(box);
            this.initForms(box);
        }
        return box;
    };

    DMK.Libraries.add(LightBox);
})(DMK, window);
