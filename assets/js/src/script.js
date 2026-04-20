const ka = window.koko_analytics;
let tracked = false;

ka.trackPageview = function(path, post_id) {
  if (
    // do not track if user agent looks like bot
    ((/bot|crawl|spider|seo|lighthouse|facebookexternalhit|preview/i).test(navigator.userAgent))

    // do not track if this is a headless browser (e.g. for testing)
    || (window._phantom || window.__nightmare || window.navigator.webdriver || window.Cypress)
  ) {
    console.debug('Koko Analytics: Ignoring call to trackPageview because user agent is a bot or this is a headless browser.');
    return;
  }

  navigator.sendBeacon(ka.url, new URLSearchParams({
    action: 'koko_analytics_collect',
    pa: path,
    po: post_id,

    // don't store referrer if from same-site
    r: document.referrer.indexOf(ka.site_url) == 0 ? '' : document.referrer,

    // use cookie if allowed, otherwise tracking method from settings
    m: ka.use_cookie ? 'c' : ka.method[0]
  }));
};

function trackCurrentPageview() {
  tracked = true;
  ka.trackPageview(ka.path, ka.post_id);
}

if (
  document.visibilityState === 'hidden' ||
  document.visibilityState === 'prerender'
) {
  // Track page as soon as it becomes visible
  document.addEventListener('visibilitychange', () => {
    if (!tracked && document.visibilityState === 'visible') {
      trackCurrentPageview();
    }
  });
} else {
  // Otherwise, track page right away
  trackCurrentPageview();
}

// Track pageviews for pages restored from bfcache
window.addEventListener('pageshow', (evt) => {
    if (evt.persisted) {
      trackCurrentPageview();
    }
});