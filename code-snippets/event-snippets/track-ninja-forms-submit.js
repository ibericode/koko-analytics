// This code snippet is to track submit events coming from a Ninja Forms form
// Modified from https://developer.ninjaforms.com/codex/listening-to-submit-response/

var mySubmitController = Marionette.Object.extend( {
  initialize: function() {
    this.listenTo( Backbone.Radio.channel( 'forms' ), 'submit:response', this.actionSubmit );
  },
  actionSubmit: function( response ) {
      window.koko_analytics.trackEvent('Form submit', 'Page: ' + location.pathname + '; Form: ' + response.data.settings.title);
  },
});

jQuery( document ).ready(function() {
    new mySubmitController();
});
