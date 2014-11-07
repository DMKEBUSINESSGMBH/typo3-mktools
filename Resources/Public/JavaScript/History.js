/**
 * History
 * 
 * Manipuliert die URL bei Ajax-Calls, um eine History zu realisieren.
 * 
 * @copyright Copyright (c) 2014 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 * @version 0.1.0
 * @requires jQuery, Base, Location
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
(function(DMK, w, d, $){
	"use strict";
	var History, _loc = d.location, VERSION = "0.1.0";
	
	if (!DMK.Base.isDefined(DMK.Objects.Location)) {
		throw "DMK.History requires DMK.Location";
	}
	
	History = function History() {
		this.setData("version", VERSION);
		this.setData(
			"last_path",
			_loc.pathname + _loc.search + _loc.hash
		);
		this.setData(
			"support_push_state",
			("pushState" in w.history) && w.history.pushState !== null
		);
	};
	
	// wir erben von dem basis objekt
	History = DMK.Base.extend(History);
	
	History.prototype.init = function() {
		if (this.getData("support_push_state")) {
			w.addEventListener("popstate", this.onHistoryChanged, false);
		} else {
			w.setInterval(this.onHistoryChanged, 500);
		}
	};
	
	History.prototype.onHistoryChanged = function(event) {
		var _self = DMK.History,
			currentPath = _loc.pathname + _loc.search + _loc.hash
		;
		if (!_self.getData("support_push_state")) {
			if (_loc.hash.length > 0) {
				currentPath = _loc.hash.slice(2);
			}
		}
		if (currentPath !== _self.getData("last_path")) {
			_self.setData("last_path", currentPath);
			if (!_self.doRequestOnHistoryChanged(currentPath)) {
				return; //stopImmediatePropagation vermeiden, wenn der request fehlerhaft war
			}
		}
		if (_self.getData("support_push_state")) {
			event.stopImmediatePropagation();
		}
	};
	
	History.prototype.doRequestOnHistoryChanged = function(url) {
		// @TODO: how to manage the ajax calls on history change?
		_loc.href = url;
		return false;
	};
	
	History.prototype.setHistoryUrl = function(url) {
		var
			href = _loc.href,
			ParsedUrl = DMK.Objects.getInstance("Location", url),
			currentPath = ParsedUrl.getPath()
					+ (
						ParsedUrl.getQuery()
							? "?" + ParsedUrl.getQuery()
							: ""
					)
					+ (
						ParsedUrl.getAnchor()
							? "#" + ParsedUrl.getAnchor()
							: ""
					)
		;
		this.setData("last_path", currentPath);
		if (this.getData("support_push_state")) {
			w.history.pushState("", "", currentPath);
		}
		else {
			w.location.href = (
				href.indexOf("#") >= 0
					? href.slice(0, href.indexOf("#"))
					: href
				)  + "#!" + currentPath
			;
		}
	};
	
	// add lib to basic library
	DMK.Libraries.add(History);
	
})(DMK, window, document, jQuery);
