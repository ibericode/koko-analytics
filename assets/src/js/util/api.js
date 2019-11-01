'use strict';

import m from "mithril";
const vars = window.koko_analytics;

function request(path, opts = {}) {
    opts.headers = {
        "X-WP-Nonce": vars.nonce
    };
    return m.request(vars.root + "koko-analytics/v1" + path, opts);
}

export default {request};