<?php

/**
* Plugin Name: Koko Analytics: Track 404 errors as Custom Event
*
* Requires an event named "Page Not Found"
*/

add_action('wp_footer', function () {
    if (! is_404()) {
        return;
    }

    ?><script>
window.addEventListener('load', function() {
    window.koko_analytics.trackEvent("Page Not Found", window.location.pathname);
});
</script><?php
});
