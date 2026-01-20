document.addEventListener( 'submit', function(evt) {
  window.koko_analytics.trackEvent('Form submit', 'Page: ' + location.pathname + '; Form: ' + evt.target.id);
});
