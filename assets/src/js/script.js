/**
 * @package koko-analytics
 * @author Danny van Kooten
 * @license GPL-3.0+
 */

// Map variables to global identifiers so that minifier can mangle them to even shorter names
var doc = document;
var win = window;
var nav = navigator;
var ka = "koko_analytics";

function request(params, u) {
  u = u !== undefined ? u : 0;
  var url = win[ka].urls ? win[ka].urls[u] : win[ka].url;
  url += (url.indexOf('?') > -1 ? '&' : '?') + params;

  win.fetch(url, { method: 'POST', cache: 'no-store', priority: 'low' })
    .then(function(response) {
      // verify response
      // if failed, try next url from config array
      if ((!response.ok || response.headers.get('Content-Type').indexOf('text/plain') === -1) && win[ka].urls.length > u+1) {
        request(params + "&disable-custom-endpoint=1", u+1);
      }
    });
}

win[ka].trackPageview = function(postId) {
  if (
    // do not track if this is a prerender request
    (doc.visibilityState == 'prerender') ||

    // do not track if user agent looks like a bot
    ((/bot|crawl|spider|seo|lighthouse|facebookexternalhit|preview/i).test(nav.userAgent))
  ) {
    return;
  }

  // if window.koko_analytics.use_cookie is set, use that (for cookie consent plugins)
  var m = win[ka].use_cookie ? 'c' : win[ka].method[0];

  // don't store referrer if from same-site
  var referrer = doc.referrer.indexOf(win[ka].site_url) == 0 ? '' : doc.referrer;

  request("m="+m+"&p="+postId+"&r="+encodeURIComponent(referrer));
}

win.addEventListener('load', function() {
  win[ka].trackPageview(win[ka].post_id);
});
