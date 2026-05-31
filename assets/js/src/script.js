const ka = window.koko_analytics;
const utmParams = ['utm_source', 'utm_medium', 'utm_campaign'];

function getUtmData() {
  const data = {};
  const queryParams = new URLSearchParams(window.location.search);
  const hashParams = new URLSearchParams(window.location.hash.substring(1));

  utmParams.forEach((param) => {
    const value = queryParams.get(param) || hashParams.get(param);
    if (value) {
      data[param] = value;
    }
  });

  return data;
}

ka.trackPageview = function(path, post_id) {
  if (
    // do not track if user agent looks like bot
    ((/bot|crawl|spider|seo|lighthouse|facebookexternalhit|preview|prerender/i).test(navigator.userAgent))

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
    m: ka.use_cookie ? 'c' : ka.method[0],

    ...getUtmData()
  }));
};

function trackCurrentPage() {
  ka.trackPageview(ka.path, ka.post_id);
}

function trackInitialPageview() {
  if (ka.autotracked) {
    return;
  }

  trackCurrentPage();
  ka.autotracked = true;
}

// track right away or as soon as page becomes visible
if (
  document.visibilityState === 'hidden' ||
  document.visibilityState === 'prerender'
) {
  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') {
      trackInitialPageview();
    }
  });
} else {
  trackInitialPageview()
}

// track pageviews for pages restored from bfcache
window.addEventListener('pageshow', (evt) => {
  if (evt.persisted) {
    trackCurrentPage();
  }
});
