// If CF7 form submitted successfully
document.addEventListener( 'wpcf7submit', function( event ) {
  if ( '244' == event.detail.contactFormId ) {
    koko_analytics.trackEvent('CF7 Form Submitted', 'Main Contact Form ID: ' + event.detail.contactFormId);
  } else {
   koko_analytics.trackEvent('CF7 Form Submitted', 'Other Contact Form ID: ' +  event.detail.contactFormId);
  }
});
