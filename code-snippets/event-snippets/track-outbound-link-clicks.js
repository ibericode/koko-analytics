document.addEventListener('click', function (evt) {
    if (evt.target.tagName !== 'A') {
        return;
    }

    var href = evt.target.href;
    if (typeof href !== 'string' || href.indexOf('http') === -1 || href.indexOf(window.location.host) > -1) {
        return;
    }

    window.koko_analytics.trackEvent('Outbound click', href);
});
