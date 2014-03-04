// Basic DMK Scripts
(function(w){
	"use strict";
	var
		Storage = function Storage (record) {
			var data = typeof record === "object" ? record : {};
			this.hasData = function (key) {
				return typeof data[key] !== "undefined";
			};
			this.setData = function (key, value) {
				if (typeof value === "undefined" && typeof value === "object") {
					data = key;
				} else {
					data[key] = value;
				}
				return this.getData(key);
			};
			this.getData = function (key) {
				return typeof key === "undefined" ? data : data[key];
			};
		},
		Basics = function Basics (record) {
			// @TODO: make this private !?
			this.Storage = new Storage(record);
			this.getInstance = function (Instance, forceNewObject) {
				forceNewObject = this.isDefined(forceNewObject) ? forceNewObject : false;
				if (this.isFunction(Instance)) {
					return new Instance();
				}
				if (this.isObject(Instance)) {
					return forceNewObject && this.isFunction(Instance.constructor) ? new Instance.constructor() : Instance;
				}
				return ;
			};
			this.isDefined = function (val) {
				return typeof val !== "undefined";
			};
			this.isObject = function (val) {
				return typeof val === "object";
			};
			this.isFunction = function (val) {
				return typeof val === "function";
			};
			this.extend = function (Class) {
				Class.prototype = this.getInstance(this, true);
				Class.prototype.constructor = Class;
				return Class;
			};
		}, // end Basics
		Libraries = function Libraries (DMK) {
			var _libs = []; // @TODO: auf storage umstellen
			this.add = function (object, name) {
				name = name || object.name || object.constructor.name;
				_libs.push(name); // lib merken
				DMK[name] = DMK.Base.getInstance(object); // lib mappen
				return this.get(name);
			};
			this.has = function (name) {
				return _libs.indexOf(name) >= 0;
			};
			this.get = function (name) {
				if (this.has(name)) {
					return DMK[name];
				}
			};
			this.names = function () {
				return _libs;
			};
			this.init = function (name) {
				var object = this.get(name),
					init = function(){};
				if (!this.has(name) || object.initialized === true) {
					return ; // continue
				}
				init = object.initialize || object.init || init;
				if (typeof init === "function") {
					init.call(object); // initialisieren
				}
				object.initialized = true; // status merken
				return object;
			};
		}, // end Libraries
		// the global basic object
		DMK = function DMK () {
			var _DMK = this;
			this.Base = new Basics({"name" : "Basics", "description" : "Required basic functionalities of DMK"});
			this.Libraries = new Libraries(_DMK);
			this.init = function() {
				// automatisch alle Libraries initialisieren.
				$.each(
					_DMK.Libraries.names(),
					function (index, name) {
						_DMK.Libraries.init(name);
					}
				);
			};
			// auto init on document ready
			$(this.init);
		}// end DMK
	; // end var
	// singelton erstellen und global unter DMK bereitstellen
	w.DMK = new DMK();
})(window);
