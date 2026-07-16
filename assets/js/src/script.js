const ka = window.koko_analytics;
const utmParams = ['utm_source', 'utm_medium', 'utm_campaign'];

// keep in sync with BOT_USER_AGENT_PATTERN in src/Resources/functions/collect.php
const botPattern = /bot|crawl|spider|seo|lighthouse|facebookexternalhit|preview|prerender|headless|phantom|scrapy|python|curl|wget|go-http|okhttp|node-fetch|axios|java\/|libwww|http[-_]?client|monitor|uptime|pingdom|statuscake|validator|scanner/i;

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

function sendData(url, params) {
  // not all browsers support navigator.sendBeacon, fall back to fetch()
  if (typeof navigator.sendBeacon === 'function') {
    navigator.sendBeacon(url, params);
    return;
  }

  fetch(url, {
    method: 'POST',
    body: params,
    keepalive: true,
    credentials: 'same-origin'
  }).catch(() => {});
}

ka.trackPageview = function(path, post_id) {
  if (
    // do not track if user agent looks like bot
    botPattern.test(navigator.userAgent)

    // do not track if this is a headless browser (e.g. for testing)
    || (window._phantom || window.__nightmare || window.navigator.webdriver || window.Cypress)
  ) {
    console.debug('Koko Analytics: Ignoring call to trackPageview because user agent is a bot or this is a headless browser.');
    return;
  }

  sendData(ka.url, new URLSearchParams({
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

// track right away, on activation of a prerendered page, or as soon as the page becomes visible
if (document.prerendering) {
  document.addEventListener('prerenderingchange', trackInitialPageview, { once: true });
} else if (
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
