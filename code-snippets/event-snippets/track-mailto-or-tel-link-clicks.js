// If mail or tel links clicked
jQuery('body').on('click', 'a', function(event) {
    var href = jQuery(this).attr('href');

    if (href.startsWith("tel:")) {
        var phoneNumber = href.replace("tel:", "");
        koko_analytics.trackEvent('Phone Clicked', 'From page: ' +
        window.location.pathname + ' Ph:' + phoneNumber);
    } else if (href.startsWith("mailto:")) {
        var emailAddress = href.replace("mailto:", "");
        koko_analytics.trackEvent('Email Clicked',  'From page: ' +
        window.location.pathname + ' Email:' + emailAddress);
    }
});
