<?php
defined( 'ABSPATH' ) or exit;

// if the table contains entries with id colum being 0 the auto increment would fail to be created
// unless we set it to a higher value than the max value in the table
global $wpdb;
$max_id = (int) $wpdb->get_var( "SELECT MAX(id) FROM  {$wpdb->prefix}koko_analytics_referrer_urls" );
$max_id++;
$wpdb->query( $wpdb->prepare( "ALTER TABLE {$wpdb->prefix}koko_analytics_referrer_urls AUTO_INCREMENT = %d, MODIFY id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT", $max_id ) );
