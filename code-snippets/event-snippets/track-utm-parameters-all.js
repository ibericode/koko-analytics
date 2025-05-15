// This code snippets monitors the URL for UTM parameters like "utm_source=WordPress.org" and sends it to a Koko Analytics custom event
// In order for this to work, you need to have the custom event created in your Koko Analytics dashboard settings.

window.addEventListener('load', function() {
  let map = {
    'utm_source': 'UTM Source',
    'utm_medium': 'UTM Medium',
    'utm_campaign': 'UTM Campaign',
  };

  let queryParams = new URLSearchParams(window.location.search);
  let hashParams = new URLSearchParams(window.location.hash.substring(1));
  for (let [p, eventName] of Object.entries(map)) {
    let value = queryParams.get(p) ?? hashParams.get(p);
    if (value) {
      window.koko_analytics.trackEvent(eventName, value);
    }
  }
});
