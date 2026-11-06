/**
 * Basic DMK Scripts
 *
 * @copyright Copyright (c) 2014 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 * @version 0.1.0
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */

(function(w){
    "use strict";
    var _undefined, Base, DMK, VERSION = "0.1.0", listeners = new WeakMap(),
        modalDialogs = new Set(), modalBodyOverflow;

    // the base MVC Object
    Base = function Base (record) {
        this.data = record && typeof record === "object" ? record : {};
    };
    // add MVC Methods (getter, setter, ...)
    Base.prototype.hasData = function (key) {
        return typeof this.data[key] !== "undefined";
    };
    Base.prototype.setData = function (key, value) {
        if (!Object.prototype.hasOwnProperty.call(this, "data")) {
            this.data = Object.assign({}, this.data);
        }
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
    Base.prototype.isElement = function (val) {
        return val instanceof w.Element;
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

    // Replace a delegated handler when a library is initialized again.
    Base.prototype.delegate = function(root, type, name, selector, callback) {
        var handlers = listeners.get(root) || {}, key = type + "." + name;
        root.removeEventListener(type, handlers[key]);
        handlers[key] = function(event) {
            var target = event.target instanceof w.Element ? event.target : event.target.parentElement,
                element = target && target.closest(selector);
            if (element && root.contains(element)) {
                callback(event, element);
            }
        };
        listeners.set(root, handlers);
        root.addEventListener(type, handlers[key]);
    };

    // Native dialogs provide focus trapping, Escape handling and a backdrop.
    Base.prototype.showModal = function(box) {
        var dialog = box.parentElement;
        if (!dialog || !dialog.classList.contains("dmk-modal-dialog")) {
            dialog = w.document.createElement("dialog");
            dialog.className = "dmk-modal-dialog";
            dialog.style.cssText = "border:0;padding:0;margin:0;max-width:none;max-height:none;width:100%;height:100%;background:transparent;overflow:auto";
            box.before(dialog);
            dialog.append(box);
            dialog.addEventListener("click", function(event) {
                if (event.target === dialog || (event.target === box && box.classList.contains("modal"))) {
                    dialog.close();
                }
            });
            dialog.addEventListener("close", function() {
                if (dialog.open) {
                    return;
                }
                box.style.display = "none";
                box.classList.remove("show", "in", "open");
                box.setAttribute("aria-hidden", "true");
                box.removeAttribute("aria-modal");
                modalDialogs.delete(dialog);
                if (modalDialogs.size === 0) {
                    w.document.body.style.overflow = modalBodyOverflow;
                }
            });
            if (!w.document.getElementById("dmk-modal-style")) {
                var style = w.document.createElement("style");
                style.id = "dmk-modal-style";
                style.textContent = ".dmk-modal-dialog::backdrop{background:rgba(0,0,0,.5)}";
                w.document.head.append(style);
            }
        }
        if (modalDialogs.size === 0) {
            modalBodyOverflow = w.document.body.style.overflow;
        }
        box.style.display = "block";
        box.style.visibility = "visible";
        box.classList.add("show", "in", "open");
        box.setAttribute("aria-hidden", "false");
        box.setAttribute("aria-modal", "true");
        if (!dialog.open) {
            dialog.showModal();
        }
        modalDialogs.add(dialog);
        w.document.body.style.overflow = "hidden";
    };
    Base.prototype.hideModal = function(box) {
        if (box && box.parentElement && box.parentElement.open) {
            box.parentElement.close();
        }
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
            _Libraries.forEach(function(name) {
                _DMK.Libraries.init(name);
            });
        };
        // auto init on document ready
        if (w.document.readyState === "loading") {
            w.document.addEventListener("DOMContentLoaded", this.init, {once: true});
        } else {
            w.setTimeout(this.init, 0);
        }
    };// end DMK

    // singelton erstellen und global unter DMK bereitstellen
    w.DMK = new DMK();
})(window);

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
