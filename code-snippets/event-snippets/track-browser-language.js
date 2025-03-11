window.addEventListener('load', function() {
    if (location.pathname === '/') {
        window.koko_analytics.trackEvent('Browser language', navigator.language.substring(0, 2));
    }
})
