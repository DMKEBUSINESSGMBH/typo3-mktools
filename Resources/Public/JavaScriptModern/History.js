/**
 * History
 *
 * Updates the browser URL after Ajax calls.
 *
 * @copyright Copyright (c) 2014 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 * @version 0.1.0
 * @requires Base, Location
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */

import {Base, DMK} from "./Base.js";
import {Location} from "./Location.js";

export class History extends Base {
    constructor() {
        const currentPath = typeof location === "undefined"
            ? ""
            : `${location.pathname}${location.search}${location.hash}`;
        super({
            version: "0.1.0",
            last_path: currentPath,
            support_push_state: typeof history !== "undefined"
                && typeof history.pushState === "function"
        });
        this._onHistoryChanged = this.onHistoryChanged.bind(this);
    }

    init() {
        if (typeof window === "undefined") {
            return;
        }
        window.addEventListener("popstate", this._onHistoryChanged);
        if (!this.getData("support_push_state")) {
            window.addEventListener("hashchange", this._onHistoryChanged);
        }
    }

    onHistoryChanged(event) {
        try {
            let currentPath = `${location.pathname}${location.search}${location.hash}`;
            if (!this.getData("support_push_state") && location.hash.length > 0) {
                currentPath = location.hash.slice(2);
            }

            if (currentPath !== this.getData("last_path")) {
                this.setData("last_path", currentPath);
                if (!this.doRequestOnHistoryChanged(currentPath)) {
                    return;
                }
            }
            if (this.getData("support_push_state")) {
                event?.stopImmediatePropagation?.();
            }
        } catch (error) {
            console.error("DMK.History: unable to handle browser history change.", error);
        }
    }

    doRequestOnHistoryChanged(url) {
        // The current implementation reloads the URL; Ajax history restoration is not implemented.
        try {
            location.href = url;
            return false;
        } catch (error) {
            console.error("DMK.History: unable to open history URL.", error);
            return false;
        }
    }

    setHistoryUrl(url) {
        try {
            const parsedUrl = new Location(url);
            const currentPath = parsedUrl.getPath()
                + (parsedUrl.getQuery() ? `?${parsedUrl.getQuery()}` : "")
                + (parsedUrl.getAnchor() ? `#${parsedUrl.getAnchor()}` : "");
            this.setData("last_path", currentPath);

            if (this.getData("support_push_state")) {
                window.history.pushState("", "", currentPath);
            } else {
                const href = window.location.href.split("#", 1)[0];
                window.location.href = `${href}#!${currentPath}`;
            }
        } catch (error) {
            console.error("DMK.History: unable to update browser URL.", error);
        }
    }
}

DMK.Libraries.add(History);

export default History;
