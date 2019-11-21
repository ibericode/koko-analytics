<?php

namespace KokoAnalytics;

class Admin {

	public function init() {
		global $pagenow;

		add_action( 'init', array( $this, 'maybe_run_migrations' ) );
		add_action( 'init', array( $this, 'maybe_seed' ) );
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'register_dashboard_widget' ) );

		//add_filter('admin_footer_text', array( $this, 'footer_text' ));
		// Hooks for Plugins overview page
		if ( $pagenow === 'plugins.php' ) {
			add_filter( 'plugin_action_links_' . plugin_basename( KOKO_ANALYTICS_PLUGIN_FILE ), array( $this, 'add_plugin_settings_link' ), 10, 2 );
			add_filter( 'plugin_row_meta', array( $this, 'add_plugin_meta_links' ), 10, 2 );
		}
	}

	public function register_menu() {
		add_submenu_page( 'index.php', __( 'Koko Analytics', 'koko-analytics' ), __( 'Analytics', 'koko-analytics' ), 'view_koko_analytics', 'koko-analytics', array( $this, 'show_page' ) );
	}

	public function show_page() {
		// aggregate stats whenever this page is requested
		do_action( 'koko_analytics_aggregate_stats' );

		$user_roles = array();
		foreach ( wp_roles()->roles as $key => $role ) {
			$user_roles[ $key ] = $role['name'];
		}

		$start_of_week = (int) get_option( 'start_of_week' );
		$settings      = get_settings();

		require KOKO_ANALYTICS_PLUGIN_DIR . '/views/admin-page.php';

		add_action( 'admin_footer_text', array( $this, 'footer_text' ) );
	}

	public function footer_text() {
		return sprintf( __( 'If you enjoy using Koko Analytics, please <a href="%s">review the plugin on WordPress.org</a> to help out.', 'koko-analytics' ), 'https://wordpress.org/support/view/plugin-reviews/koko-analytics?rate=5#postform' );
	}

	public function maybe_run_migrations() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$from_version = isset( $_GET['koko_analytics_migrate_from_version'] ) ? $_GET['koko_analytics_migrate_from_version'] : get_option( 'koko_analytics_version', '0.0.1' );
		$to_version   = KOKO_ANALYTICS_VERSION;
		if ( version_compare( $from_version, $to_version, '>=' ) ) {
			return;
		}

		$migrations_dir = KOKO_ANALYTICS_PLUGIN_DIR . '/migrations/';
		$migrations     = new Migrations( $from_version, $to_version, $migrations_dir );
		$migrations->run();
		update_option( 'koko_analytics_version', $to_version );
	}

	public function register_dashboard_widget() {
		// only show if user can view stats
		if (! current_user_can( 'view_koko_analytics') ) {
			return;
		}

		add_meta_box( 'koko-analytics-dashboard-widget', 'Koko Analytics', array( $this, 'dashboard_widget' ), 'dashboard', 'side', 'high' );
	}

	public function dashboard_widget() {
		wp_enqueue_script( 'koko-analytics-dashboard-widget', plugins_url( '/assets/dist/js/dashboard-widget.js', KOKO_ANALYTICS_PLUGIN_FILE ), array(), KOKO_ANALYTICS_VERSION, true );
		wp_localize_script(
			'koko-analytics-dashboard-widget',
			'koko_analytics',
			array(
			'root'          => rest_url(),
			'nonce'         => wp_create_nonce( 'wp_rest' ),
			)
		);
		echo '<div id="koko-analytics-dashboard-widget-mount"></div>';
		echo sprintf( '<p class="help" style="text-align: center;">%s &mdash; <a href="%s">%s</a></p>', __( 'Showing site visits over last 14 days', 'koko-analytics' ), esc_attr( admin_url( 'index.php?page=koko-analytics' ) ), __( 'View all statistics', 'koko-analytics' ) );
	}

	/**
	 * Add the settings link to the Plugins overview
	 *
	 * @param array $links
	 * @param       $file
	 *
	 * @return array
	 */
	public function add_plugin_settings_link( $links, $file ) {
		$settings_link = '<a href="' . admin_url( 'index.php?page=koko-analytics#!/settings' ) . '">' . __( 'Settings', 'koko-analytics' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Adds meta links to the plugin in the WP Admin > Plugins screen
	 *
	 * @param array $links
	 * @param string $file
	 *
	 * @return array
	 */
	public function add_plugin_meta_links( $links, $file ) {
		if ( $file !== plugin_basename( KOKO_ANALYTICS_PLUGIN_FILE ) ) {
			return $links;
		}

		$links[] = '<a href="https://www.kokoanalytics.com/#utm_source=wp-plugin&utm_medium=koko-analytics&utm_campaign=plugins-page">' . __( 'Visit plugin site', 'koko-analytics' ) . '</a>';
		return $links;
	}

	public function maybe_seed() {
		global $wpdb;

		if ( ! isset( $_GET['koko_analytics_seed'] ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$wpdb->suppress_errors( true );

		$query         = new \WP_Query();
		$posts         = $query->query(
			array(
				'posts_per_page' => 12,
				'post_type'      => 'any',
				'post_status'    => 'publish',
			)
		);
		$post_count    = count( $posts );
		$referrer_urls = array();
		foreach ( array(
					  'https://www.wordpress.org/',
					  'https://www.wordpress.org/plugins/koko-analytics',
					  'https://www.ibericode.com/',
					  'https://duckduckgo.com/',
					  'https://www.mozilla.org/',
					  'https://www.eff.org/',
					  'https://letsencrypt.org/',
					  'https://dannyvankooten.com/',
					  'https://github.com/ibericode/koko-analytics',
					  'https://lobste.rs/',
					  'https://joinmastodon.org/',
					  'https://www.php.net/',
					  'https://mariadb.org/',
		) as $url ) {
			$wpdb->insert(
				$wpdb->prefix . 'koko_analytics_referrer_urls',
				array(
					'url' => $url,
				)
			);
			$referrer_urls[ $wpdb->insert_id ] = $url;
		}
		$referrer_count = count( $referrer_urls );

		$n = 3 * 365;
		for ( $i = 0; $i < $n; $i++ ) {
			$progress = ( $n - $i ) / $n;
			$date      = gmdate( 'Y-m-d', strtotime( sprintf( '-%d days', $i ) ) );
			$pageviews = rand( 500, 1000 ) * $progress ^ 2;
			$visitors  = $pageviews * rand( 3, 6 ) / 10;

			$wpdb->insert(
				$wpdb->prefix . 'koko_analytics_site_stats',
				array(
					'date'      => $date,
					'pageviews' => $pageviews,
					'visitors'  => $visitors,
				)
			);

			$values = array();
			foreach ( $posts as $post ) {
				array_push( $values, $date, $post->ID, round( $pageviews / $post_count * rand( 5, 15 ) / 10 ), round( $visitors / $post_count * rand( 5, 15 ) / 10 ) );
			}
			$placeholders = rtrim( str_repeat( '(%s,%d,%d,%d),', count( $posts ) ), ',' );
			$wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}koko_analytics_post_stats(date, id, pageviews, visitors) VALUES {$placeholders}", $values ) );

			$values = array();
			foreach ( $referrer_urls as $id => $referrer_url ) {
				array_push( $values, $date, $id, round( $pageviews / $referrer_count * rand( 5, 15 ) / 10 ), round( $visitors / $referrer_count * rand( 5, 15 ) / 10 ) );
			}
			$placeholders = rtrim( str_repeat( '(%s,%d,%d,%d),', count( $referrer_urls ) ), ',' );
			$wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}koko_analytics_referrer_stats(date, id, pageviews, visitors) VALUES {$placeholders}", $values ) );
		}
	}
}
