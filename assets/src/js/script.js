/**
 * @package koko-analytics
 * @author Danny van Kooten
 * @license GPL-3.0+
 */

// Map variables to global identifiers so that minifier can mangle them to even shorter names
var win = window;
var ka = "koko_analytics";

function request(data) {
  // if window.koko_analytics.use_cookie is set, use that (for cookie consent plugins)
  if (win[ka].use_cookie) {
    data['m'] = 'c';
  // to work with full-page html caching refusing to update the configuration object....
  // TODO: Remove this ~Aug 2025
  } else if (win[ka].method) {
    data['m'] = win[ka].method[0];
  }

  navigator.sendBeacon(win[ka].url, new URLSearchParams(data));
}

win[ka].trackPageview = function() {
  if (
    // do not track if this is a prerender request
    (document.visibilityState == 'prerender') ||

    // do not track if user agent looks like a bot
    ((/bot|crawl|spider|seo|lighthouse|facebookexternalhit|preview/i).test(navigator.userAgent))
  ) {
    return;
  }

  // don't store referrer if from same-site
  var referrer = document.referrer.indexOf(win[ka].site_url) == 0 ? '' : document.referrer;
  request({ pa: win[ka].path, po: win[ka].post_id, r: referrer })
}

win.addEventListener('load', win[ka].trackPageview);
