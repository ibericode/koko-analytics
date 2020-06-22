<?php
add_filter('koko_analytics_load_tracking_script', function() {
	// do not load tracking script if URL starts with "/wp-admin/"
	if ( stripos( $_SERVER['REQUEST_URI'], '/wp-admin/' ) === 0 ) {
		return false;
	}

	return true;
});
