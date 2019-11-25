'use strict';

const vars = window.koko_analytics;
import 'whatwg-fetch';
import 'promise-polyfill/src/polyfill';

function request(path, opts = {}) {
    opts.headers = {
        "X-WP-Nonce": vars.nonce,
		"Accepts": "application/json",
    };

    let url = vars.root + "koko-analytics/v1" + path;

    if (opts.body) {
		// allow passing "body" option for GET requests, convert it to query params
		if (!opts.method || opts.method === 'GET') {
			if (url.indexOf('?') < 0) {
				url += '?';
			} else {
				url += '&';
			}
			for(let key in opts.body) {
				url += `${window.encodeURIComponent(key)}=${window.encodeURIComponent(opts.body[key])}&`;
			}
			url = url.substring(0, url.length - 1);
			delete opts.body;
		}

		if (opts.method === "POST") {
			opts.headers['Content-Type'] = 'application/json';

			if (typeof(opts.body) !== "string") {
				opts.body = JSON.stringify(opts.body);
			}
		}
	}

    return window.fetch(url, opts).then(r => r.json());
}

export default {request};
