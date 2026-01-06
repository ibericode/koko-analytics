// Map variables to global identifiers so that minifier can mangle them to even shorter names
var win = window;
var ka = "koko_analytics";

win[ka].trackPageview = function(path, post_id) {

  if (
  // do not track if this is a prerender request
  (document.visibilityState == 'prerender') ||

  // do not track if user agent looks like a bot
  ((/bot|crawl|spider|seo|lighthouse|facebookexternalhit|preview/i).test(navigator.userAgent))
  ) {
  return;
  }

  navigator.sendBeacon(win[ka].url, new URLSearchParams({
    pa: path,
    po: post_id,

    // don't store referrer if from same-site
    r: document.referrer.indexOf(win[ka].site_url) == 0 ? '' : document.referrer,

    // use cookie if allowed, otherwise tracking method from settings
    m: win[ka].use_cookie ? 'c' : win[ka].method[0]
  }));
};

win.addEventListener('load', function() {
  win[ka].trackPageview(win[ka].path, win[ka].post_id)
});
