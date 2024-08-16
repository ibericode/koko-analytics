window.addEventListener('load', function() {
    if (location.pathname === '/') {
        koko_analytics.trackEvent('Browser language', navigator.language.substring(0, 2));
    }
})
