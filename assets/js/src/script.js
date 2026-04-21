const ka = window.koko_analytics;

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

// Track page immediately, including when opened in a background tab.
ka.trackPageview(ka.path, ka.post_id);

// Track pageviews for pages restored from bfcache
window.addEventListener('pageshow', (evt) => {
  if (evt.persisted) {
    ka.trackPageview(ka.path, ka.post_id);
  }
});
