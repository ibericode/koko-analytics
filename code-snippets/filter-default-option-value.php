<?php
/**
 * Sets the default value of the "use cookie" setting to false
 */
add_filter( 'default_option_koko_analytics_settings', function( $options ) {
	$options['use_cookie'] = 0;
	return $options;
});