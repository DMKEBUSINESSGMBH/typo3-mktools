/**
 * Basic DMK Scripts
 *
 * @copyright Copyright (c) 2014 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 * @version 0.1.0
 * @requires nothing (vanilla JavaScript)
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */

(function(w, d){
    "use strict";
    var _undefined, Base, DMK, VERSION = "0.1.0";

    // the base MVC Object
    Base = function Base (record) {
        this.data = typeof record === "object" ? record : {};
    };
    // add MVC Methods (getter, setter, ...)
    Base.prototype.hasData = function (key) {
        return typeof this.data[key] !== "undefined";
    };
    Base.prototype.setData = function (key, value) {
        if (typeof value === "undefined" && typeof key === "object") {
            this.data = key;
        } else {
            this.data[key] = value;
        }
        return this.getData(key);
    };
    Base.prototype.getData = function (key) {
        return typeof key === "undefined" ? this.data : this.data[key];
    };

    // add basic extend funcitons
    Base.extend = function(ParentInstance, Class, params) {
        Class.prototype = ParentInstance;
        Class.prototype.parent = function() { return ParentInstance; };
        Class.prototype.constructor = Class;
        Class.extend = function(Child, childParams) {
            return Base.extend(new Class(childParams), Child, childParams);
        };
        return Class;
    };
    Base.prototype.extend = function (Class, params) {
        return Base.extend(this.getInstance(this, true, params), Class, params);
    };
    Base.prototype.getInstance = function (Instance, forceNewObject, params) {
        Instance = this.isDefined(Instance) ? Instance : this;
        forceNewObject = this.isDefined(forceNewObject) ? forceNewObject : false;
        params = this.isDefined(params) ? params : {};
        if (this.isFunction(Instance)) {
            return new Instance(params);
        }
        if (this.isObject(Instance)) {
            return forceNewObject && this.isFunction(Instance.constructor) ? new Instance.constructor(params) : Instance;
        }
        return ;
    };
    Base.prototype.getClassName = function () {
        var matches = this.constructor.toString().match(/function (.{1,})\(/);
        return (matches && matches.length > 1) ? matches[1] : _undefined;
    };

    // add some basic functions
    Base.prototype.isDefined = function (val) {
        return typeof val !== "undefined";
    };
    Base.prototype.isObject = function (val) {
        return typeof val === "object";
    };
    /**
     * @deprecated jQuery is not required anymore. Only kept for BC.
     */
    Base.prototype.isObjectJQuery = function (val) {
        return typeof w.jQuery === "function" && val instanceof w.jQuery;
    };
    Base.prototype.isElement = function (val) {
        return val instanceof w.Element;
    };
    /**
     * Liefert das DOM-Element. jQuery-Objekte (BC) werden auf ihr erstes Element reduziert.
     */
    Base.prototype.toElement = function (val) {
        if (this.isObjectJQuery(val)) {
            return val.length > 0 ? val[0] : null;
        }
        return val;
    };
    Base.prototype.isFunction = function (val) {
        return typeof val === "function";
    };
    Base.prototype.isNumeric = function (val) {
        return !isNaN(parseFloat(val, 10)) && isFinite(val);
    };
    Base.prototype.isString = function (val) {
        return typeof val === "string";
    };

    // DOM helpers
    /**
     * Fuehrt callback aus, sobald das DOM geladen ist.
     */
    Base.prototype.ready = function (callback) {
        if (d.readyState === "loading") {
            d.addEventListener("DOMContentLoaded", function () { callback(); });
        } else {
            w.setTimeout(callback, 0);
        }
    };
    /**
     * Event delegation. Ein bereits unter dem gleichen key registrierter
     * Handler (Ersatz fuer jQuery Namespaces) wird vorher entfernt.
     * handler(event, matchedElement)
     */
    Base.prototype.delegate = function (root, type, selector, handler, key) {
        var id = "_dmkDelegates", name = type + "." + key, listener;
        root[id] = root[id] || {};
        if (root[id][name]) {
            root.removeEventListener(type, root[id][name]);
        }
        listener = function (event) {
            var target = event.target, match;
            if (!(target instanceof w.Element)) {
                target = target && target.parentElement;
            }
            match = target ? target.closest(selector) : null;
            if (match && root.contains(match)) {
                return handler.call(match, event, match);
            }
        };
        root[id][name] = listener;
        root.addEventListener(type, listener);
    };
    /**
     * Wandelt einen HTML String in ein DocumentFragment um.
     * Scripts werden neu erzeugt, damit sie nach dem Einfuegen ausgefuehrt werden.
     */
    Base.prototype.parseHtml = function (html) {
        var tpl = d.createElement("template");
        tpl.innerHTML = html;
        tpl.content.querySelectorAll("script").forEach(function (old) {
            var script = d.createElement("script");
            Array.prototype.forEach.call(old.attributes, function (attr) {
                script.setAttribute(attr.name, attr.value);
            });
            script.text = old.text;
            old.parentNode.replaceChild(script, old);
        });
        return tpl.content;
    };
    Base.prototype.show = function (el) {
        if (el) {
            this.stopAnimation(el);
            el.style.display = "block";
            el.style.opacity = "";
        }
    };
    Base.prototype.hide = function (el) {
        if (el) {
            this.stopAnimation(el);
            el.style.display = "none";
        }
    };
    Base.prototype.stopAnimation = function (el) {
        if (el._dmkAnimation) {
            el._dmkAnimation.cancel();
            el._dmkAnimation = null;
        }
    };
    /**
     * Animiert el von keyframes[0] nach keyframes[1], danach wird onfinish aufgerufen.
     */
    Base.prototype.animate = function (el, keyframes, duration, onfinish) {
        var anim;
        this.stopAnimation(el);
        if (typeof el.animate !== "function") {
            if (onfinish) { onfinish(); }
            return null;
        }
        anim = el.animate(keyframes, {duration: duration, easing: "ease-in-out"});
        el._dmkAnimation = anim;
        anim.onfinish = function () {
            el._dmkAnimation = null;
            if (onfinish) { onfinish(); }
        };
        return anim;
    };
    Base.prototype.fadeIn = function (el, duration) {
        if (!el) { return; }
        this.stopAnimation(el);
        el.style.display = "block";
        this.animate(el, [{opacity: 0}, {opacity: 1}], duration || 400);
    };
    Base.prototype.fadeOut = function (el, duration) {
        if (!el) { return; }
        this.animate(el, [{opacity: 1}, {opacity: 0}], duration || 400, function () {
            el.style.display = "none";
        });
    };

    // The global basic object
    DMK = function DMK () {
        var _DMK = this, _Libraries = [];
        this.Version = VERSION;
        this.Base = new Base({"name" : "Base", "description" : "Required base functionalities of DMK", "version" : VERSION});
        this.Objects = {
            add : function (Object, Name) {
                if (!_DMK.Base.isDefined(Name)) {
                    var Instance = new Object();
                    Name = Instance.getClassName();
                }
                this[Name] = Object;
            },
            extend : function (Name, Class, params) {
                if (!this[Name]) {
                    return null;
                }
                this[Name] = this[Name].extend(Class, params);
                return this[Name];
            },
            getInstance : function(Name, params) {
                return this[Name] ? new this[Name](params) : null;
            }
        };
        this.Libraries = {
            add : function (Object) {
                var Instance = _DMK.Base.getInstance(Object),
                    Name = Instance.getClassName(),
                    Class = Instance.constructor
                ;
                _DMK.Objects.add(Class, Name);
                _Libraries.push(Name);
                _DMK[Name] = Instance; // lib mappen
                return Instance;
            },
            init : function (name) {
                var object = _DMK[name],
                    init = function(){};
                if (!_DMK.Base.isDefined(object) || object.initialized === true) {
                    return ; // continue
                }
                init = object.initialize || object.init || init;
                if (_DMK.Base.isFunction(init)) {
                    init.call(object); // initialisieren
                }
                object.initialized = true; // status merken
                return object;
            }
        };
        this.init = function() {
            // automatisch alle Libraries initialisieren.
            _Libraries.forEach(function (name) {
                _DMK.Libraries.init(name);
            });
        };
        // auto init on document ready
        this.Base.ready(this.init);
    };// end DMK

    // singelton erstellen und global unter DMK bereitstellen
    w.DMK = new DMK();
})(window, document);

// A smal storage addon
(function(DMK){
    var Registry = function Registry() {
        this.setData("version", "0.1.0");
        this.buildCacheId = function(params) {
            return JSON.stringify(params);
        };
    };
    DMK.Libraries.add(DMK.Base.extend(Registry));
})(DMK);
