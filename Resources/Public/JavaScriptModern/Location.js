/**
 * Location
 *
 * Splits a URL into its components.
 *
 * @copyright Copyright (c) 2014 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 * @version 0.1.0
 * @requires Base
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */

import {Base, DMK} from "./Base.js";

export class Location extends Base {
    constructor(url, strictMode = false) {
        super({version: "0.1.0"});
        this.url = this.parseUrl(url, strictMode);
    }

    parseUrl(url, strictMode) {
        try {
            const value = url || globalThis.location?.href || "http://localhost/";
            if (strictMode && !/^[a-z][a-z\d+.-]*:/i.test(value)) {
                throw new TypeError("Strict URL parsing requires an absolute URL.");
            }
            return new URL(value, globalThis.location?.href || "http://localhost/");
        } catch (error) {
            console.error("DMK.Location: unable to parse URL.", error);
            throw error;
        }
    }

    getProtocoll() {
        return this.url.protocol.replace(/:$/, "");
    }

    getHost() {
        return this.url.hostname;
    }

    getPort() {
        return this.url.port;
    }

    getPath() {
        return this.url.pathname;
    }

    getQuery() {
        return this.url.search.replace(/^\?/, "");
    }

    getQueryData() {
        return Object.fromEntries(this.url.searchParams.entries());
    }

    getAnchor() {
        return this.url.hash.replace(/^#/, "");
    }

    isSameOrigin() {
        try {
            return typeof location !== "undefined" && this.url.origin === location.origin;
        } catch (error) {
            console.error("DMK.Location: unable to compare URL origins.", error);
            return false;
        }
    }
}

DMK.Objects.add(Location);

export default Location;
