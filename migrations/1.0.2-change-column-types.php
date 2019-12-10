<?php

defined( 'ABSPATH' ) or exit;

global $wpdb;

$wpdb->query( "ALTER TABLE {$wpdb->prefix}koko_analytics_site_stats MODIFY visitors MEDIUMINT UNSIGNED NOT NULL" );
$wpdb->query( "ALTER TABLE {$wpdb->prefix}koko_analytics_site_stats MODIFY pageviews MEDIUMINT UNSIGNED NOT NULL" );

$wpdb->query( "ALTER TABLE {$wpdb->prefix}koko_analytics_post_stats MODIFY visitors MEDIUMINT UNSIGNED NOT NULL" );
$wpdb->query( "ALTER TABLE {$wpdb->prefix}koko_analytics_post_stats MODIFY pageviews MEDIUMINT UNSIGNED NOT NULL" );

$wpdb->query( "ALTER TABLE {$wpdb->prefix}koko_analytics_referrer_stats MODIFY id MEDIUMINT UNSIGNED NOT NULL" );
$wpdb->query( "ALTER TABLE {$wpdb->prefix}koko_analytics_referrer_stats MODIFY visitors MEDIUMINT UNSIGNED NOT NULL" );
$wpdb->query( "ALTER TABLE {$wpdb->prefix}koko_analytics_referrer_stats MODIFY pageviews MEDIUMINT UNSIGNED NOT NULL" );

$wpdb->query( "ALTER TABLE {$wpdb->prefix}koko_analytics_referrer_urls MODIFY id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT" );
