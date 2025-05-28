/**
 * @package koko-analytics
 * @author Danny van Kooten
 * @license GPL-3.0+
 */

// Map variables to global identifiers so that minifier can mangle them to even shorter names
var doc = document;
var win = window;
var nav = navigator;
var enc = encodeURIComponent;
var loc = win.location;
var ka = "koko_analytics";

function getPagesViewed() {
  var m = doc.cookie.match(/_koko_analytics_pages_viewed=([^;]+)/);
  return m ? m.pop().split('a') : [];
}

function request(params, u) {
  u = u !== undefined ? u : 0;
  var url = win[ka].urls[u];
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

  var pagesViewed = getPagesViewed();
  postId += ""; // convert to string
  var isNewVisitor = pagesViewed.length ? 0 : 1;
  var isUniquePageview = pagesViewed.indexOf(postId) == -1 ? 1 : 0;
  var referrer = doc.referrer;

  if (doc.referrer.indexOf(win[ka].site_url) == 0) {
    // don't store referrer if from same-site
    referrer = ''

    // not new visitor if coming from same-site
    if (!win[ka].use_cookie) {
      isNewVisitor = 0

      // check if referred by same page (so not a unique pageview)
      if (doc.referrer == loc.href) isUniquePageview = 0
    }
  }

  request("p="+postId+"&nv="+isNewVisitor+"&up="+isUniquePageview+"&r="+enc(referrer));
  if (isUniquePageview) pagesViewed.push(postId);
  if (win[ka].use_cookie) doc.cookie = "_"+ka+"_pages_viewed="+pagesViewed.join('a')+";SameSite=lax;path="+win[ka].cookie_path+";max-age=21600";

}

win.addEventListener('load', function() {
  win[ka].trackPageview(win[ka].post_id);
});
