<?php

// This instructs Koko Analytics to not load the tracking script
// If the User-Agent contains either "StatusCake_Pagespeed_Indev" or "GTmetrix"
add_filter( 'koko_analytics_load_tracking_script', function() {
	if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
		if ( preg_match( '/StatusCake_Pagespeed_Indev|GTmetrix/', $_SERVER['HTTP_USER_AGENT'] ) ) {
			return false;
		}
	}

	return true;
});
