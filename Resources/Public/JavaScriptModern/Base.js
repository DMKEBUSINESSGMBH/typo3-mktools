/**
 * Basic DMK scripts
 *
 * Shared data, class and library helpers for the modern JavaScript modules.
 *
 * @copyright Copyright (c) 2014 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 * @version 0.1.0
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */

const VERSION = "0.1.0";

export class Base {
    constructor(record = {}) {
        this.data = record && typeof record === "object" ? record : {};
    }

    hasData(key) {
        return typeof this.data[key] !== "undefined";
    }

    setData(key, value) {
        if (typeof value === "undefined" && key && typeof key === "object") {
            this.data = key;
            return this.getData();
        }

        this.data[key] = value;
        return this.getData(key);
    }

    getData(key) {
        return typeof key === "undefined" ? this.data : this.data[key];
    }

    getInstance(instance = this, forceNewObject = false, params = {}) {
        if (typeof instance === "function") {
            return new instance(params);
        }
        if (instance && typeof instance === "object") {
            return forceNewObject && typeof instance.constructor === "function"
                ? new instance.constructor(params)
                : instance;
        }
        return null;
    }

    extend(ChildClass, params) {
        return Base.extend(this.constructor, ChildClass, params);
    }

    getClassName() {
        return this.constructor?.name || null;
    }

    isDefined(value) {
        return typeof value !== "undefined";
    }

    isObject(value) {
        return value !== null && typeof value === "object";
    }

    isElement(value) {
        return typeof Element !== "undefined" && value instanceof Element;
    }

    isFunction(value) {
        return typeof value === "function";
    }

    isNumeric(value) {
        return value !== "" && value !== null && Number.isFinite(Number(value));
    }

    isString(value) {
        return typeof value === "string";
    }

    /**
     * Keeps a small compatibility helper for callers that used Base.extend.
     * New modules should use native `extends` declarations directly.
     */
    static extend(ParentClass, ChildClass, params) {
        const Parent = typeof ParentClass === "function" ? ParentClass : Base;

        if (typeof ChildClass !== "function") {
            throw new TypeError("Base.extend expects a child constructor.");
        }
        const childIsClass = /^class\s/.test(Function.prototype.toString.call(ChildClass));

        class Extended extends Parent {
            constructor(...args) {
                super(params ?? args[0]);
                if (childIsClass) {
                    Object.assign(this, new ChildClass(...args));
                } else {
                    ChildClass.apply(this, args);
                }
            }
        }

        for (const key of Reflect.ownKeys(ChildClass.prototype)) {
            if (key === "constructor") {
                continue;
            }
            Object.defineProperty(
                Extended.prototype,
                key,
                Object.getOwnPropertyDescriptor(ChildClass.prototype, key)
            );
        }

        return Extended;
    }
}

class Registry extends Base {
    constructor() {
        super({version: VERSION});
    }

    buildCacheId(params) {
        return JSON.stringify(params);
    }
}

class DMKManager {
    constructor() {
        this.Version = VERSION;
        this.Base = new Base({
            name: "Base",
            description: "Required base functionalities of DMK",
            version: VERSION
        });
        this.Objects = this.createObjectRegistry();
        this.Libraries = this.createLibraryRegistry();
        this._libraries = new Set();
        this._initialized = false;

        this.Libraries.add(Registry);
        this.scheduleInit();
    }

    createObjectRegistry() {
        return {
            add(ObjectClass, name) {
                try {
                    const instance = typeof ObjectClass === "function" ? new ObjectClass() : ObjectClass;
                    const objectName = name || instance?.getClassName?.();
                    if (!objectName) {
                        throw new TypeError("Cannot register an object without a class name.");
                    }
                    this[objectName] = ObjectClass;
                    return ObjectClass;
                } catch (error) {
                    console.error("DMK: unable to register object.", error);
                    return null;
                }
            },

            extend(name, ChildClass, params) {
                const ParentClass = this[name];
                if (!ParentClass) {
                    return null;
                }
                const Extended = Base.extend(ParentClass, ChildClass, params);
                this[name] = Extended;
                return Extended;
            },

            getInstance(name, params) {
                const ObjectClass = this[name];
                if (typeof ObjectClass !== "function") {
                    return null;
                }
                try {
                    return new ObjectClass(params);
                } catch (error) {
                    console.error(`DMK: unable to create ${name}.`, error);
                    return null;
                }
            }
        };
    }

    createLibraryRegistry() {
        const manager = this;
        return {
            add(ObjectClass, name) {
                try {
                    const instance = typeof ObjectClass === "function" ? new ObjectClass() : ObjectClass;
                    const libraryName = name || instance?.getClassName?.();
                    if (!libraryName) {
                        throw new TypeError("Cannot register a library without a class name.");
                    }
                    manager.Objects.add(ObjectClass, libraryName);
                    manager._libraries.add(libraryName);
                    manager[libraryName] = instance;
                    if (manager._initialized) {
                        manager.Libraries.init(libraryName);
                    }
                    return instance;
                } catch (error) {
                    console.error("DMK: unable to register library.", error);
                    return null;
                }
            },

            init(name) {
                const library = manager[name];
                if (!library || library.initialized === true) {
                    return library ?? null;
                }

                try {
                    const initialize = library.initialize || library.init;
                    if (typeof initialize === "function") {
                        initialize.call(library);
                    }
                    library.initialized = true;
                    return library;
                } catch (error) {
                    console.error(`DMK: unable to initialize ${name}.`, error);
                    return null;
                }
            }
        };
    }

    scheduleInit() {
        if (typeof document === "undefined") {
            return;
        }
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", () => this.init(), {once: true});
        } else {
            queueMicrotask(() => this.init());
        }
    }

    init() {
        if (this._initialized) {
            return;
        }
        this._initialized = true;
        for (const name of this._libraries) {
            this.Libraries.init(name);
        }
    }
}

export const DMK = new DMKManager();

// Keep the familiar global entry point for integrations that customize DMK.
if (typeof window !== "undefined") {
    window.DMK = DMK;
}
