<?php
/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */
namespace KokoAnalytics;

class Aggregator {

	public function init() {
		add_action( 'koko_analytics_aggregate_stats', array( $this, 'aggregate' ) );
		add_filter( 'cron_schedules', array( $this, 'add_interval' ) );
		add_action( 'init', array( $this, 'maybe_setup_scheduled_event' ) );
	}

	public function add_interval( $intervals ) {
		$intervals['koko_analytics_stats_aggregate_interval'] = array(
			'interval' => 1 * 60, // 1 minute
			'display'  => esc_html__( 'Every minute', 'koko-analytics' ),
		);
		return $intervals;
	}

	public function setup_scheduled_event() {
		if ( ! wp_next_scheduled( 'koko_analytics_aggregate_stats' ) ) {
			wp_schedule_event( time() + 60, 'koko_analytics_stats_aggregate_interval', 'koko_analytics_aggregate_stats' );
		}
	}

	public function maybe_setup_scheduled_event() {
		if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || $_SERVER['REQUEST_METHOD'] !== 'POST' || ! is_admin() ) {
			return;
		}

		$this->setup_scheduled_event();
	}

	public function aggregate() {
		global $wpdb;

		// read pageviews buffer file into array
		$wp_upload_dir = wp_get_upload_dir();
		$filename      = $wp_upload_dir['basedir'] . '/pageviews.php';
		if ( ! file_exists( $filename ) ) {
			return;
		}

		// rename file to temporary location so nothing new is written to it while we process it
		$tmp_filename = $wp_upload_dir['basedir'] . '/pageviews-busy.php';
		$renamed = rename( $filename, $tmp_filename );
		if ( $renamed !== true ) {
			// TODO: Write to some kind of log
			return;
		}

		// open file for reading
		$file_handle = fopen( $tmp_filename, 'rb' );
		if ( ! is_resource( $file_handle ) ) {
			// TODO: Write to some kind of log
			return;
		}

		// read and ignore first line (the PHP header that prevents direct file access)
		fgets( $file_handle, 1024 );

		// combine stats for each table
		$site_stats     = array(
			'visitors'  => 0,
			'pageviews' => 0,
		);
		$post_stats     = array();
		$referrer_stats = array();

		// read blocklist into array
		$blocklist = file( KOKO_ANALYTICS_PLUGIN_DIR . '/data/referrer-blocklist', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );

		while ( ( $line = fgets( $file_handle, 1024 ) ) !== false ) {
			$line            = rtrim( $line );
			$p               = explode( ',', $line );
			$post_id         = (int) $p[0];
			$new_visitor     = (int) $p[1];
			$unique_pageview = (int) $p[2];
			$referrer_url    = trim( $p[3] );

			// update site stats
			$site_stats['pageviews'] += 1;
			if ( $new_visitor ) {
				$site_stats['visitors'] += 1;
			}

			// update page stats (if received)
			if ( $post_id > 0 ) {
				if ( ! isset( $post_stats[ $post_id ] ) ) {
					$post_stats[ $post_id ] = array(
						'visitors'  => 0,
						'pageviews' => 0,
					);
				}

				$post_stats[ $post_id ]['pageviews'] += 1;

				if ( $unique_pageview ) {
					$post_stats[ $post_id ]['visitors'] += 1;
				}
			}

			// increment referrals
			if ( $referrer_url !== '' && ! $this->in_blocklist( $referrer_url, $blocklist ) ) {

				$referrer_url = $this->clean_url( $referrer_url );
				$referrer_url = $this->normalize_url( $referrer_url );

				if ( ! isset( $referrer_stats[ $referrer_url ] ) ) {
					$referrer_stats[ $referrer_url ] = array(
						'pageviews' => 0,
						'visitors'  => 0,
					);
				}

				$referrer_stats[ $referrer_url ]['pageviews'] += 1;
				if ( $new_visitor ) {
					$referrer_stats[ $referrer_url ]['visitors'] += 1;
				}
			}
		}

		// close file & remove it from filesystem
		fclose( $file_handle );
		unlink( $tmp_filename );

		// bail if nothing happened
		if ( $site_stats['pageviews'] === 0 ) {
			return;
		}

		// store as local date using the timezone specified in WP settings
		$date = gmdate( 'Y-m-d', time() + get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );

		// insert site stats
		$sql = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}koko_analytics_site_stats(date, visitors, pageviews) VALUES(%s, %d, %d) ON DUPLICATE KEY UPDATE visitors = visitors + VALUES(visitors), pageviews = pageviews + VALUES(pageviews)", array( $date, $site_stats['visitors'], $site_stats['pageviews'] ) );
		$wpdb->query( $sql );

		// insert post stats
		if ( count( $post_stats ) > 0 ) {
			$values = array();
			foreach ( $post_stats as $post_id => $s ) {
				array_push( $values, $date, $post_id, $s['visitors'], $s['pageviews'] );
			}
			$placeholders = rtrim( str_repeat( '(%s,%d,%d,%d),', count( $post_stats ) ), ',' );
			$sql          = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}koko_analytics_post_stats(date, id, visitors, pageviews) VALUES {$placeholders} ON DUPLICATE KEY UPDATE visitors = visitors + VALUES(visitors), pageviews = pageviews + VALUES(pageviews)", $values );
			$wpdb->query( $sql );
		}

		if ( count( $referrer_stats ) > 0 ) {
			// retrieve ID's for known referrer urls
			$referrer_urls = array_keys( $referrer_stats );
			$placeholders  = rtrim( str_repeat( '%s,', count( $referrer_urls ) ), ',' );
			$sql           = $wpdb->prepare( "SELECT id, url FROM {$wpdb->prefix}koko_analytics_referrer_urls r WHERE r.url IN({$placeholders})", $referrer_urls );
			$results       = $wpdb->get_results( $sql );
			foreach ( $results as $r ) {
				$referrer_stats[ $r->url ]['id'] = $r->id;
			}

			// build query for new referrer urls
			$new_referrer_urls = array();
			foreach ( $referrer_stats as $url => $r ) {
				if ( ! isset( $r['id'] ) ) {
					$new_referrer_urls[] = $url;
				}
			}

			// insert new referrer urls and set ID in map
			if ( count( $new_referrer_urls ) > 0 ) {
				$values       = $new_referrer_urls;
				$placeholders = rtrim( str_repeat( '(%s),', count( $values ) ), ',' );
				$sql          = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}koko_analytics_referrer_urls(url) VALUES {$placeholders}", $values );
				$wpdb->query( $sql );
				$last_insert_id = $wpdb->insert_id;
				foreach ( array_reverse( $values ) as $url ) {
					$referrer_stats[ $url ]['id'] = $last_insert_id--;
				}
			}

			// insert referrer stats
			$values = array();
			foreach ( $referrer_stats as $referrer_url => $r ) {
				array_push( $values, $date, $r['id'], $r['visitors'], $r['pageviews'] );
			}
			$placeholders = rtrim( str_repeat( '(%s,%d,%d,%d),', count( $referrer_stats ) ), ',' );
			$sql          = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}koko_analytics_referrer_stats(date, id, visitors, pageviews) VALUES {$placeholders} ON DUPLICATE KEY UPDATE visitors = visitors + VALUES(visitors), pageviews = pageviews + VALUES(pageviews)", $values );
			$wpdb->query( $sql );
		}

		$this->update_realtime_pageview_count( $site_stats['pageviews'] );
	}

	private function update_realtime_pageview_count( $pageviews ) {
		$counts = (array) get_option( 'koko_analytics_realtime_pageview_count', array() );
		$one_hour_ago = strtotime( '-60 minutes' );

		foreach ( $counts as $timestamp => $count ) {
			// delete all data older than one hour
			if ( $timestamp < $one_hour_ago ) {
				unset( $counts[ $timestamp ] );
			}
		}

		// add pageviews for this minute
		$counts[ (string) time() ] = $pageviews;
		update_option( 'koko_analytics_realtime_pageview_count', $counts, false );
	}

	private function in_blocklist( $url, array $blocklist ) {
		foreach ( $blocklist as $blocklisted_domain ) {
			if ( false !== stripos( $url, $blocklisted_domain ) ) {
				return true;
			}
		}

		return false;
	}

	public function clean_url( $url ) {
		// remove # from URL
		$pos = strpos( $url, '#' );
		if ( $pos !== false ) {
			$url = substr( $url, 0, $pos );
		}

		// if URL contains query string, parse it and only keep certain parameters
		$pos = strpos( $url, '?' );
		if ( $pos !== false ) {
			$query_str = substr( $url, $pos + 1 );

			$params = array();
			parse_str( $query_str, $params );

			// strip all non-allowed params from url
			$allowed_params = array( 'page_id', 'p', 'cat', 'product' );
			$new_params    = array_intersect_key( $params, array_flip( $allowed_params ) );
			$new_query_str = http_build_query( $new_params );
			$new_url       = substr( $url, 0, $pos + 1 ) . $new_query_str;

			// trim trailing question mark & replace url with new sanitized url
			$url = rtrim( $new_url, '?' );
		}

		// trim trailing slash
		$url = rtrim( $url, '/' );

		return $url;
	}

	public function normalize_url( $url ) {
		// if URL has no protocol, assume HTTP
		// we change this to HTTPS for sites that are known to support it (hopefully, all)
		if ( strpos( $url, '://' ) === false ) {
			$url = 'http://' . $url;
		}

		$aggregations = array(
			'/^android-app:\/\/com\.(www\.)?google\.android\.googlequicksearchbox(\/.+)?$/' => 'https://www.google.com',
			'/^android-app:\/\/com\.www\.google\.android\.gm$/' => 'https://www.google.com',
			'/^https?:\/\/(?:www\.)?(google|bing|ecosia)\.([a-z]{2,3}(?:\.[a-z]{2,3})?)(?:\/search|\/url)?/' => 'https://www.$1.$2',
			'/^android-app:\/\/com\.facebook\.(.+)/' => 'https://facebook.com',
			'/^https?:\/\/(?:[a-z-]+)?\.?l?facebook\.com(?:\/l\.php)?/' => 'https://facebook.com',
			'/^https?:\/\/(?:[a-z-]+)?\.?l?instagram\.com(?:\/l\.php)?/' => 'https://www.instagram.com',
			'/^https?:\/\/(?:www\.)?linkedin\.com\/feed.*/' => 'https://www.linkedin.com',
			'/(?:www|m)\.baidu\.com.*/' => 'www.baidu.com',
			'/yandex\.ru\/clck.*/' => 'yandex.ru',
		);

		return preg_replace( array_keys( $aggregations ), array_values( $aggregations ), $url, 1 );
	}

}
