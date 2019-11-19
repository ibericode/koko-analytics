'use strict';

const vars = window.koko_analytics;

function request(path, opts = {}) {
    opts.headers = {
        "X-WP-Nonce": vars.nonce
    };

    // allow passing "body" option for GET requests, convert it to query params
    if (opts.body && (!opts.method || opts.method === 'GET')) {
    	path += '?';
    	for(let key in opts.body) {
    		path += `${window.encodeURIComponent(key)}=${window.encodeURIComponent(opts.body[key])}&`;
		}
    	path = path.substring(0, path.length - 1);
    	delete opts.body;
	}


    return window.fetch(vars.root + "koko-analytics/v1" + path, opts).then(r => r.json());
}

export default {request};
