/**
 * Location
 * 
 * Numme eine URL in all seine Bestandteile auseinander.
 * 
 * @copyright Copyright (c) 2014 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * @license http://www.gnu.org/licenses/lgpl.html
 * 			GNU Lesser General Public License, version 3 or later
 * @version 0.1.0
 * @requires jQuery, Base
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
(function(DMK, w, $){
	"use strict";
	var Location, VERSION = "0.1.0";
	
	Location = function Location(url, strictMode) {
		this.setData("version", VERSION);
		
		var
			Parsed = {}, Query = {}, _getData,
			parts = ["source","protocol","authority","userInfo","user","password","host","port","relative","path","directory","file","query","anchor"],
			parser = {
				strict : /^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@]*)(?::([^:@]*))?)?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
				loose  : /^(?:(?![^:@]+:[^:@\/]*@)([^:\/?#.]+):)?(?:\/\/)?((?:(([^:@]*)(?::([^:@]*))?)?@)?([^:\/?#]*)(?::(\d*))?)(((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[?#]|$)))*\/?)?([^?#\/]*))(?:\?([^#]*))?(?:#(.*))?)/
			},
			match = parser[strictMode ? "strict" : "loose"].exec(url),
			i = 14
		;
		while (i--) {
			Parsed[parts[i]] = (match[i] || "");
		}
		if (Parsed["query"].length > 0) {
			Parsed["query"].replace(
				/(?:^|&)([^&=]*)=?([^&]*)/g,
				function (part, name, value) {
					if (name && value) {
						Query[name] = value;
					}
				}
			);
		}
		
		_getData = function getData(Object, data) {
			return typeof Object[data] === "undefined"
				? null : Object[data]
		}

		// getter
		this.getProtocoll = function() {
			return _getData(Parsed, "protocol");
		};
		this.getHost = function() {
			return _getData(Parsed, "host");
		};
		this.getPort = function() {
			return _getData(Parsed, "port");
		};
		this.getPath = function() {
			return _getData(Parsed, "path");
		};
		this.getQuery = function() {
			return _getData(Parsed, "query");
		};
		this.getQueryData = function() {
			return Query;
		};
		this.getAnchor = function() {
			return _getData(Parsed, "anchor");
		};
		this.isSameOrigin = function() {
			return (
				(
					win.location.protocol == this.getProtocoll() + ":"
					|| win.location.protocol == this.getProtocoll()
				)
				&& win.location.host == this.getHost()
			);
		};
	};

	// wir erben von dem basis objekt
	Location = DMK.Base.extend(Location);
	
	// add lib to basic library
	DMK.Objects.add(Location);
})(DMK, window);
