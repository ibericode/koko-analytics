<?php

add_filter( 'koko_analytics_ignore_referrer_url', function( $url ) {
	// ignore all referrer urls containing "/out"
	if ( stripos( $url, '/out' ) !== false ) {
		return true;
	}

	return false;
});
