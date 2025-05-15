// This code snippets monitors the URL for a single UTM parameter and sends it to a Koko Analytics custom event
// In order for this to work, you need to have the custom event created in your Koko Analytics dashboard settings.

window.addEventListener('load', function() {
  // URL parameter to track
  const PARAMETER = 'utm_source';

  // Name of your event in the Koko Analytics dashboard settings
  const EVENT_NAME = 'UTM Source';

  let queryParams = new URLSearchParams(window.location.search);
  let hashParams = new URLSearchParams(window.location.hash.substring(1));
  let value = queryParams.get(PARAMETER) ?? hashParams.get(PARAMETER);
  if (value) {
    window.koko_analytics.trackEvent(EVENT_NAME, value);
  }
});

