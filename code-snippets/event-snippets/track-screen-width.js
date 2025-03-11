window.addEventListener('load', function() {
    function bucket(n) {
        if (n < 500) {
            return '0 - 500';
        } else if (n < 1000) {
            return '500 - 1000';
        } else if (n < 1500) {
            return '1000 - 1500';
        } else if (n < 2000) {
            return '1500 - 2000';
        } else {
            return '2000+';
        }
    }

    window.koko_analytics.trackEvent('Screen width', bucket(screen.width));
});
