// Map variables to global identifiers so that minifier can mangle them to even shorter names
var win = window;
var ka = "koko_analytics";

function request(data) {
  data['m'] = win[ka].use_cookie ? 'c' : win[ka].method[0];
  navigator.sendBeacon(win[ka].url, new URLSearchParams(data));
}

function trackPageview() {
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

win[ka].request = request;
win[ka].trackPageview = trackPageview;

win.addEventListener('load', function() { win[ka].trackPageview() });
