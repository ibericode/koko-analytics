<?php
/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */
namespace KokoAnalytics;

class Rest {

	public function init() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	function register_routes() {
		register_rest_route(
			'koko-analytics/v1',
			'/stats',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_stats' ),
				'args'                => array(
					'start_date' => array(
						'validate_callback' => array( $this, 'validate_date_param' ),
					),
					'end_date'   => array(
						'validate_callback' => array( $this, 'validate_date_param' ),
					),
				),
				'permission_callback' => function () {
					return current_user_can( 'view_koko_analytics' );
				},
			)
		);

		register_rest_route(
			'koko-analytics/v1',
			'/posts',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_posts' ),
				'args'                => array(
					'start_date' => array(
						'validate_callback' => array( $this, 'validate_date_param' ),
					),
					'end_date'   => array(
						'validate_callback' => array( $this, 'validate_date_param' ),
					),
				),
				'permission_callback' => function () {
					return current_user_can( 'view_koko_analytics' );
				},
			)
		);

		register_rest_route(
			'koko-analytics/v1',
			'/referrers',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_referrers' ),
				'args'                => array(
					'start_date' => array(
						'validate_callback' => array( $this, 'validate_date_param' ),
					),
					'end_date'   => array(
						'validate_callback' => array( $this, 'validate_date_param' ),
					),
				),
				'permission_callback' => function () {
					return current_user_can( 'view_koko_analytics' );
				},
			)
		);

		register_rest_route(
			'koko-analytics/v1',
			'/settings',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'update_settings' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_koko_analytics' );
				},
			)
		);

		register_rest_route(
			'koko-analytics/v1',
			'/realtime',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_realtime_pageview_count' ),
				'args'                => array(
					'since' => array(
						'validate_callback' => array( $this, 'validate_date_param' ),
					),
				),
				'permission_callback' => function () {
					return current_user_can( 'view_koko_analytics' );
				},
			)
		);
	}

	public function validate_date_param( $param, $one, $two ) {
		return strtotime( $param ) !== false;
	}

	public function get_stats( \WP_REST_Request $request ) {
		global $wpdb;
		$params     = $request->get_query_params();
		$start_date = isset( $params['start_date'] ) ? $params['start_date'] : gmdate( 'Y-m-d', strtotime( '1st of this month' ) + get_option( 'gmt_offset', 0 ) * HOUR_IN_SECONDS );
		$end_date   = isset( $params['end_date'] ) ? $params['end_date'] : gmdate( 'Y-m-d', time() + get_option( 'gmt_offset', 0 ) * HOUR_IN_SECONDS );
		$sql        = $wpdb->prepare( "SELECT date, visitors, pageviews FROM {$wpdb->prefix}koko_analytics_site_stats s WHERE s.date >= %s AND s.date <= %s", array( $start_date, $end_date ) );
		$result     = $wpdb->get_results( $sql );
		return $result;
	}

	public function get_posts( \WP_REST_Request $request ) {
		global $wpdb;
		$params     = $request->get_query_params();
		$start_date = isset( $params['start_date'] ) ? $params['start_date'] : gmdate( 'Y-m-d', strtotime( '1st of this month' ) + get_option( 'gmt_offset', 0 ) * HOUR_IN_SECONDS );
		$end_date   = isset( $params['end_date'] ) ? $params['end_date'] : gmdate( 'Y-m-d', time() + get_option( 'gmt_offset', 0 ) * HOUR_IN_SECONDS );
		$offset = isset( $params['offset'] ) ? absint( $params['offset'] ) : 0;
		$limit = isset( $params['limit'] ) ? absint( $params['limit'] ) : 10;
		$sql        = $wpdb->prepare( "SELECT s.id, SUM(visitors) AS visitors, SUM(pageviews) AS pageviews, p.post_title FROM {$wpdb->prefix}koko_analytics_post_stats s LEFT JOIN {$wpdb->posts} p ON p.ID = s.id WHERE s.date >= %s AND s.date <= %s GROUP BY s.id ORDER BY pageviews DESC, s.id ASC LIMIT %d, %d", array( $start_date, $end_date, $offset, $limit ) );
		$results    = $wpdb->get_results( $sql );
		if ( empty( $results ) ) {
			return array();
		}

		// add permalink to each result
		foreach ( $results as $i => $row ) {
			$results[ $i ]->post_permalink = get_permalink( $row->id );
		}

		return $results;
	}

	public function get_referrers( \WP_REST_Request $request ) {
		global $wpdb;
		$params     = $request->get_query_params();
		$start_date = isset( $params['start_date'] ) ? $params['start_date'] : gmdate( 'Y-m-d', strtotime( '1st of this month' ) + get_option( 'gmt_offset', 0 ) * HOUR_IN_SECONDS );
		$end_date   = isset( $params['end_date'] ) ? $params['end_date'] : gmdate( 'Y-m-d', time() + get_option( 'gmt_offset', 0 ) * HOUR_IN_SECONDS );
		$offset = isset( $params['offset'] ) ? absint( $params['offset'] ) : 0;
		$limit = isset( $params['limit'] ) ? absint( $params['limit'] ) : 10;
		$sql        = $wpdb->prepare( "SELECT s.id, url, SUM(visitors) As visitors, SUM(pageviews) AS pageviews FROM {$wpdb->prefix}koko_analytics_referrer_stats s JOIN {$wpdb->prefix}koko_analytics_referrer_urls r ON r.id = s.id WHERE s.date >= %s AND s.date <= %s GROUP BY s.id ORDER BY pageviews DESC, r.id ASC LIMIT %d, %d", array( $start_date, $end_date, $offset, $limit ) );
		$results    = $wpdb->get_results( $sql );
		return $results;
	}

	public function update_settings( \WP_REST_Request $request ) {
		$settings     = get_settings();
		$new_settings = $request->get_json_params();

		if ( isset( $new_settings['prune_data_after_months'] ) ) {
			$new_settings['prune_data_after_months'] = abs( intval( $new_settings['prune_data_after_months'] ) );
		}

		if ( isset( $new_settings['use_cookie'] ) ) {
			$new_settings['use_cookie'] = intval( $new_settings['use_cookie'] );
		}

		// merge with old settings to allow posting partial settings
		$new_settings = array_merge( $settings, $new_settings );
		update_option( 'koko_analytics_settings', $new_settings, true );
		return true;
	}

	public function get_realtime_pageview_count( \WP_REST_Request $request ) {
		$params = $request->get_query_params();
		$since = isset( $params['since'] ) ? strtotime( $params['since'] ) : null;
		return get_realtime_pageview_count( $since );
	}

}
