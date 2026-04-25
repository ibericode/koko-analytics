// If mail or tel links clicked

document.addEventListener('click', function(evt) {
    if (!evt.target || evt.target.tagName !== 'A' || ! evt.target.getAttribute('href')) {
        return;
    }

    let href = evt.target.getAttribute('href');
    if (href.startsWith("tel:")) {
        let phoneNumber = href.replace("tel:", "");
        window.koko_analytics.trackEvent('Phone Clicked', 'From page: ' + window.location.pathname + ' Ph:' + phoneNumber);
    } else if (href.startsWith("mailto:")) {
        let emailAddress = href.replace("mailto:", "");
        window.koko_analytics.trackEvent('Email Clicked',  'From page: ' + window.location.pathname + ' Email:' + emailAddress);
    }
});
