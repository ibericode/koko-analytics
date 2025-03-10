// This code snippets allows you to send URL parameters containing "utm_source" to an event named "UTM Source" in Koko Analytics

var source = window.location.hash.match(/[#&]utm_source=([^&]*)/);

if (source) {
  window.koko_analytics.trackEvent('UTM Source', source);
}
