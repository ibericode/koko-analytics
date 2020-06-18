<?php

add_filter( 'koko_analytics_referrer_blocklist', function() {
	return array(
		'search.myway.com',
		'bad-website.com',
	);
});
