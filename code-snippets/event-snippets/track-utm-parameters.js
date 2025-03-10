// This code snippets monitors the URL for UTM parameters like "utm_source=WordPress.org" and sends it to a Koko Analytics custom event
// In order for this to work, you need to have the custom event created in your Koko Analytics dashboard settings.

const PARAMETER = 'utm_source';
const EVENT_NAME = 'UTM Source';

let queryParams = new URLSearchParams(window.location.search);
let hashParams = new URLSearchParams(window.location.hash.substring(1));
let value = queryParams.get(PARAMETER) ?? hashParams.get(PARAMETER);
if (value) {
  window.koko_analytics.trackEvent(EVENT_NAME, value);
}
