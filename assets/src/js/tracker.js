'use strict';

function stringifyObject(obj) {
    return Object.keys(obj).map(function(k) {
        return encodeURIComponent(k) + '=' + encodeURIComponent(obj[k]);
    }).join('&');
}

function getCookie(name) {
    const cookies = document.cookie ? document.cookie.split('; ') : [];

    for (let i = 0; i < cookies.length; i++) {
        const parts = cookies[i].split('=');
        if (decodeURIComponent(parts[0]) !== name) {
            continue;
        }

        const cookie = parts.slice(1).join('=');
        return decodeURIComponent(cookie);
    }

    return '';
}

function setCookie(name, data, args) {
    name = encodeURIComponent(name);
    data = encodeURIComponent(String(data));

    let str = name + '=' + data;

    if(args.path) {
        str += ';path=' + args.path;
    }
    if (args.expires) {
        str += ';expires='+args.expires.toUTCString();
    }

    document.cookie = str;
}

function trackPageview() {
    // respect "Do Not Track" requests
    if ('doNotTrack' in navigator && navigator.doNotTrack === "1") {
        return;
    }

    // ignore pre-rendering requests
    if ('visibilityState' in document && document.visibilityState === 'prerender') {
        return;
    }

    // if <body> did not load yet, try again at dom ready event
    if (document.body === null) {
        document.addEventListener("DOMContentLoaded", () => trackPageview());
        return;
    }

    const cookie = getCookie('_ap_pages_viewed');
    const postId = window.ap.post_id;
    const isNewVisitor = cookie.length === 0;
    const pagesViewed = cookie.split(',').filter(id => id !== '');
    const isUniquePageview = pagesViewed.indexOf(postId) === -1;
    const d = {
        p:  postId,
        nv: isNewVisitor ? 1 : 0,
        up: isUniquePageview ? 1 : 0,
    };

    const img = document.createElement('img');
    img.alt = '';
    img.style.display = 'none';
    img.setAttribute('aria-hidden', 'true');

    const finalize = () => {
        // clear src to cancel request (if called via timeout)
        img.src = '';

        // remove from dom
        if (img.parentNode) {
            document.body.removeChild(img);
        }

        // update tracking cookie
        if (isUniquePageview) {
            pagesViewed.push(postId)
        }
        let expires = new Date();
        expires.setHours(expires.getHours() + 6);
        setCookie('_ap_pages_viewed', pagesViewed.join(','), { expires, path: '/' })
    };

    // clean-up tracking pixel after 5s or onload
    img.onload = finalize;
    window.setTimeout(finalize, 5000);

    // add to DOM to fire request
    img.src = window.ap.tracker_url + '?action=ap_collect&' + stringifyObject(d);
    document.body.appendChild(img);
}

trackPageview();
