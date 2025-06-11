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

function request(params) {
  if (!win[ka].urls.length) return;

  var url = win[ka].urls[0];

  // if window.koko_analytics.use_cookie is set, use that (for cookie consent plugins)
  var m = win[ka].use_cookie ? 'c' : win[ka].method[0];

  url += (url.indexOf('?') > -1 ? '&' : '?');
  url += params;
  url += "&m=";
  url += m;

  win.fetch(url, { method: 'POST', cache: 'no-store', priority: 'low' })
    .then(function(response) {
      // verify response: if failed, try next url from config array
      if ((!response.ok || response.headers.get('Content-Type').indexOf('text/plain') === -1)) {
        win[ka].urls.shift();
        request(params + "&disable-custom-endpoint=1");
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

  // don't store referrer if from same-site
  var referrer = doc.referrer.indexOf(win[ka].site_url) == 0 ? '' : doc.referrer;

  request("p="+postId+"&r="+encodeURIComponent(referrer));
}

win.addEventListener('load', function() {
  win[ka].trackPageview(win[ka].post_id);
});
