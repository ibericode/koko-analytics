document.addEventListener( 'submit', function(evt) {
  koko_analytics.trackEvent('Form submit', 'Page: ' + location.pathname + '; Form: ' + evt.target.id);
});
