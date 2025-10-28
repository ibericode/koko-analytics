<?php

/**
* Plugin Name: Koko Analytics: Track UTM Parameters
*
* Requires 3 events named "UTM Source", "UTM Medium" and "UTM Campaign"
*/

add_action('wp_footer', function () {
    ?><script>
window.addEventListener('load', function() {
    let map = {
        'utm_source': 'UTM Source',
        'utm_medium': 'UTM Medium',
        'utm_campaign': 'UTM Campaign',
    };

    let queryParams = new URLSearchParams(window.location.search);
    let hashParams = new URLSearchParams(window.location.hash.substring(1));
    for (let [p, eventName] of Object.entries(map)) {
        let value = queryParams.get(p) ?? hashParams.get(p);
        if (value) {
          window.koko_analytics.trackEvent(eventName, value);
        }
    }
});
</script><?php
});
