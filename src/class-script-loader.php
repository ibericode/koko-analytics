<?php

namespace KokoAnalytics;

use WP_User;

class Script_Loader {

	public function init() {
		add_action( 'wp', array( $this, 'maybe_enqueue_script' ) );

	}

	public function maybe_enqueue_script() {
		$settings = get_settings();
		$user     = wp_get_current_user();

		// bail if user matches one of excluded roles
		if ( $user->exists() && $this->user_has_roles( $user, $settings['exclude_user_roles'] ) ) {
			return;
		}

		// TODO: Handle "term" requests so we track both terms and post types.
		$post_id             = is_singular() ? (int) get_queried_object_id() : 0;
		$use_custom_endpoint = ( defined( 'KOKO_ANALYTICS_USE_CUSTOM_ENDPOINT' ) && KOKO_ANALYTICS_USE_CUSTOM_ENDPOINT ) || file_exists( ABSPATH . '/koko-analytics-collect.php' );
		$tracker_url         = $use_custom_endpoint ? home_url( '/koko-analytics-collect.php' ) : admin_url( 'admin-ajax.php?action=koko_analytics_collect' );
		$script_data         = array(
			'use_cookie'    => $settings['use_cookie'],
			'post_id'       => $post_id,
			'tracker_url'   => $tracker_url,
		);

		add_filter( 'script_loader_tag', array( $this, 'add_async_attribute' ), 20, 2 );
		wp_enqueue_script( 'koko-analytics', plugins_url( 'assets/dist/js/script.js', KOKO_ANALYTICS_PLUGIN_FILE ), array(), KOKO_ANALYTICS_VERSION, true );
		wp_localize_script( 'koko-analytics', 'koko_analytics', $script_data );

		/*
		 * The following adds support for the official AMP plugin
		 * @see https://amp-wp.org/
		 */
		add_filter( 'amp_analytics_entries', function( $entries ) use ( $settings, $tracker_url, $post_id ) {
			$posts_viewed = isset( $_COOKIE['_koko_analytics_pages_viewed'] ) ? explode( ',', $_COOKIE['_koko_analytics_pages_viewed'] ) : array();
			$data = array(
				'sc' => $settings['use_cookie'], // inform tracker endpoint to set cookie server-side
				'nv' => $posts_viewed === array() ? 1 : 0,
				'up' => ! in_array( $post_id, $posts_viewed ) ? 1 : 0,
				'p' => $post_id,
			);
			$url = add_query_arg( $data, $tracker_url );
			$entries[] = array(
				'type' => 'koko-analytics',
				'attributes' => array(),
				'config' => json_encode(
					array(
						'requests' => array(
							'pageview' => $url,
						),
						'triggers' => array(
							'trackPageview' => array(
								'on' => 'visible',
								'request' => 'pageview',
							),
						),
					)
				),
			);
			return $entries;
		});

	}

	public function add_async_attribute( $tag, $handle ) {
		if ( $handle !== 'koko-analytics' ) {
			return $tag;
		}

		return str_replace( ' src', ' async="async" src', $tag );
	}

	public function user_has_roles( WP_User $user, array $roles ) {
		foreach ( $user->roles as $user_role ) {
			if ( in_array( $user_role, $roles, true ) ) {
				return true;
			}
		}

		return false;
	}
}
