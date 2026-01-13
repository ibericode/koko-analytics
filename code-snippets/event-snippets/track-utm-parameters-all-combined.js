// Event name: UTM Parameters
// This snippet tracks any UTM parameter into a single "UTM Parameters" event using the following format: Source / Medium / Campaign
window.addEventListener('load', function() {
  let queryParams = new URLSearchParams(window.location.search);
  let hashParams = new URLSearchParams(window.location.hash.substring(1));

  let source = queryParams.get('utm_source') ?? hashParams.get('utm_source');
  let medium = queryParams.get('utm_medium') ?? hashParams.get('utm_medium');
  let campaign = queryParams.get('utm_campaign') ?? hashParams.get('utm_campaign');

  if (source || medium || campaign) {
      let eventName = 'UTM Parameters';
      let eventValue = [source, medium, campaign].filter(v => !!v).join(' / ')

      window.koko_analytics.trackEvent(eventName, eventValue);
    }
});
