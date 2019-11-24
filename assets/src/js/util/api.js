'use strict';

const vars = window.koko_analytics;
import 'whatwg-fetch';
import 'promise-polyfill/src/polyfill';

function request(path, opts = {}) {
    opts.headers = {
        "X-WP-Nonce": vars.nonce,
		"Accepts": "application/json",
    };

    // allow passing "body" option for GET requests, convert it to query params
    if (opts.body && (!opts.method || opts.method === 'GET')) {
    	if (path.indexOf('?') === false ) {
    		path += '?';
		} else {
    		path += '&';
		}
    	for(let key in opts.body) {
    		path += `${window.encodeURIComponent(key)}=${window.encodeURIComponent(opts.body[key])}&`;
		}
    	path = path.substring(0, path.length - 1);
    	delete opts.body;
	}

    if (opts.body && typeof(opts.body) !== "string") {
    	opts.body = JSON.stringify(opts.body);
	}

    if (opts.body && opts.method === "POST") {
    	opts.headers['Content-Type'] = 'application/json';
	}

    return window.fetch(vars.root + "koko-analytics/v1" + path, opts).then(r => r.json());
}

export default {request};
