'use strict';

const md5 = require('blueimp-md5');

function stringifyObject(obj) {
    return Object.keys(obj).map(function(k) {
            return encodeURIComponent(k) + '=' + encodeURIComponent(obj[k]);
        }).join('&');
}

function trackPageview(vars) {
    vars = vars || {};

    // Respect "Do Not Track" requests
    if ('doNotTrack' in navigator && navigator.doNotTrack === "1") {
        return;
    }

    // ignore prerendered pages
    if ('visibilityState' in document && document.visibilityState === 'prerender') {
        return;
    }

    // if <body> did not load yet, try again at dom ready event
    if (document.body === null) {
        document.addEventListener("DOMContentLoaded", () => trackPageview(vars));
        return;
    }

    const visitorHash = md5(aaa.ip + navigator.userAgent + window.screen.width + window.screen.height + navigator.language + navigator.doNotTrack + [].map.call(navigator.plugins, (p) => p.description).join(','));
    const pageviewHash = md5(visitorHash + aaa.post_id);

    const d = {
        p: aaa.post_id,
        vh: visitorHash,
        ph: pageviewHash,
    };

    let img = document.createElement('img');
    img.setAttribute('alt', '');
    img.setAttribute('aria-hidden', 'true');
    img.src = aaa.tracker_url + '?action=aaa_collect&' + stringifyObject(d);

    img.addEventListener('load', function() {
        document.body.removeChild(img)
    });

    // in case img.onload never fires, remove img after 1s & reset src attribute to cancel request
    window.setTimeout(() => {
        if (!img.parentNode) {
            return;
        }

        img.src = '';
        document.body.removeChild(img)
    }, 1000);

    // add to DOM to fire request
    document.body.appendChild(img);
}

trackPageview();
