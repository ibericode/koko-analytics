<?php
/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */
namespace KokoAnalytics;

class Admin
{
	public function init()
	{
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

	public function register_menu()
	{
		add_submenu_page( 'index.php', esc_html__( 'Koko Analytics', 'koko-analytics' ), esc_html__( 'Analytics', 'koko-analytics' ), 'view_koko_analytics', 'koko-analytics', array( $this, 'show_page' ) );
	}

	private function get_available_roles() {
		$roles = array();
		foreach ( wp_roles()->roles as $key => $role ) {
			$roles[ $key ] = $role['name'];
		}
		return $roles;
	}

	private function is_cron_event_working() {
		// detect issues with WP Cron event not running
		// it should run every minute, so if it didn't run in 10 minutes there is most likely something wrong
		$next_scheduled = wp_next_scheduled( 'koko_analytics_aggregate_stats' );
		return $next_scheduled !== false && $next_scheduled > ( time() - HOUR_IN_SECONDS );
	}

	public function show_page()
	{
		// aggregate stats whenever this page is requested
		do_action( 'koko_analytics_aggregate_stats' );

		$buffer_filename = get_buffer_filename();
		$buffer_dirname = dirname( $buffer_filename );
		$is_buffer_dir_writable = is_writable( $buffer_dirname );
		$is_cron_event_working = $this->is_cron_event_working();
		$user_roles = $this->get_available_roles();
		$start_of_week = (int) get_option( 'start_of_week' );
		$settings = get_settings();
		$colors = $this->get_colors();

		require KOKO_ANALYTICS_PLUGIN_DIR . '/views/admin-page.php';
		add_action( 'admin_footer_text', array( $this, 'footer_text' ) );
		add_action( 'shutdown', array( $this, 'install_optimized_endpoint' ) );
	}

	public function footer_text()
	{
		/* translators: %1$s links to the WordPress.org plugin review page, %2$s links to the admin page for creating a new post */
		return sprintf( wp_kses( __( 'If you enjoy using Koko Analytics, please <a href="%1$s">review the plugin on WordPress.org</a> or <a href="%2$s">write about it on your blog</a> to help out.', 'koko-analytics' ), array( 'a' => array( 'href' => array() ) ) ), 'https://wordpress.org/support/view/plugin-reviews/koko-analytics?rate=5#postform', admin_url( 'post-new.php' ) );
	}

	public function maybe_run_migrations()
	{
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$from_version = isset( $_GET['koko_analytics_migrate_from_version'] ) ? $_GET['koko_analytics_migrate_from_version'] : get_option( 'koko_analytics_version', '0.0.1' );
		$to_version = KOKO_ANALYTICS_VERSION;
		if ( version_compare( $from_version, $to_version, '>=' ) ) {
			return;
		}

		// run upgrade migrations (if any)
		$migrations_dir = KOKO_ANALYTICS_PLUGIN_DIR . '/migrations/';
		$migrations = new Migrations( $from_version, $to_version, $migrations_dir );
		$migrations->run();
		update_option( 'koko_analytics_version', $to_version );

		// make sure scheduled event is set-up correctly
		$aggregator = new Aggregator();
		$aggregator->setup_scheduled_event();
	}

	private function get_colors()
	{
		$color_scheme_name = get_user_option( 'admin_color' );
		global $_wp_admin_css_colors;
		if ( empty( $_wp_admin_css_colors[ $color_scheme_name ] ) ) {
			$color_scheme_name = 'fresh';
		}

		return $_wp_admin_css_colors[ $color_scheme_name ]->colors;
	}

	public function register_dashboard_widget()
	{
		// only show if user can view stats
		if ( ! current_user_can( 'view_koko_analytics' ) ) {
			return;
		}

		add_meta_box( 'koko-analytics-dashboard-widget', 'Koko Analytics', array( $this, 'dashboard_widget' ), 'dashboard', 'side', 'high' );
	}

	public function dashboard_widget()
	{
		wp_enqueue_script( 'koko-analytics-dashboard-widget', plugins_url( '/assets/dist/js/dashboard-widget.js', KOKO_ANALYTICS_PLUGIN_FILE ), array(), KOKO_ANALYTICS_VERSION, true );
		wp_localize_script(
			'koko-analytics-dashboard-widget',
			'koko_analytics',
			array(
				'root' => rest_url(),
				'nonce' => wp_create_nonce( 'wp_rest' ),
				'i18n' => array(
					'Visitors' => __( 'Visitors', 'koko-analytics' ),
					'Pageviews' => __( 'Pageviews', 'koko-analytics' ),
				),
				'colors' => $this->get_colors(),
			)
		);

		echo '<div id="koko-analytics-dashboard-widget-mount"></div>';
		echo sprintf( '<p class="help" style="text-align: center;">%s &mdash; <a href="%s">%s</a></p>', esc_html__( 'Showing site visits over last 14 days', 'koko-analytics' ), esc_attr( admin_url( 'index.php?page=koko-analytics' ) ), esc_html__( 'View all statistics', 'koko-analytics' ) );
	}

	/**
	 * Add the settings link to the Plugins overview
	 *
	 * @param array $links
	 * @param       $file
	 *
	 * @return array
	 */
	public function add_plugin_settings_link( $links, $file )
	{
		$settings_link = sprintf( '<a href="%s">%s</a>', admin_url( 'index.php?page=koko-analytics#/settings' ), esc_html__( 'Settings', 'koko-analytics' ) );
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
	public function add_plugin_meta_links( $links, $file )
	{
		if ( $file !== plugin_basename( KOKO_ANALYTICS_PLUGIN_FILE ) ) {
			return $links;
		}

		$links[] = '<a href="https://www.kokoanalytics.com/">' . esc_html__( 'Visit plugin site', 'koko-analytics' ) . '</a>';
		return $links;
	}

	public function get_database_size()
	{
		global $wpdb;
		$sql = $wpdb->prepare(
			'
			SELECT ROUND(SUM((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2)
			FROM information_schema.TABLES
			WHERE TABLE_SCHEMA = %s AND TABLE_NAME LIKE %s',
			DB_NAME,
			$wpdb->prefix . 'koko_analytics_%'
		);

		return $wpdb->get_var( $sql );
	}

	public function maybe_seed()
	{
		global $wpdb;

		if ( ! isset( $_GET['koko_analytics_seed'] ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$query = new \WP_Query();
		$posts = $query->query(
			array(
				'posts_per_page' => 32,
				'post_type' => 'any',
				'post_status' => 'publish',
			)
		);
		$post_count = count( $posts );
		$referrer_urls = array();
		$sample_referrers = array(
			'https://www.wordpress.org',
			'https://www.wordpress.org/plugins/koko-analytics',
			'https://www.ibericode.com',
			'https://duckduckgo.com',
			'https://www.mozilla.org',
			'https://www.eff.org',
			'https://letsencrypt.org',
			'https://dannyvankooten.com',
			'https://github.com/ibericode/koko-analytics',
			'https://lobste.rs',
			'https://joinmastodon.org',
			'https://www.php.net',
			'https://mariadb.org',
			'https://referrer-1.com',
			'https://referrer-2.com',
			'https://referrer-3.com',
			'https://referrer-4.com',
			'https://referrer-5.com',
			'https://referrer-6.com',
			'https://referrer-7.com',
			'https://referrer-8.com',
			'https://referrer-9.com',
			'https://referrer-10.com',
			'https://referrer-11.com',
			'https://referrer-12.com',
			'https://referrer-13.com',
			'https://referrer-14.com',
			'https://referrer-15.com',
			'https://referrer-16.com',
			'https://referrer-17.com',
			'https://referrer-18.com',
			'https://referrer-19.com',
			'https://referrer-20.com',
			'https://t.co/IiADWZC13f',
			'https://www.reddit.com/r/Wordpress/comments/e6ycsm/privacy_friendly_analytics_plugin_that_does_not/',
			'android-app://com.stefandekanski.hackernews.free',
		);
		foreach ( $sample_referrers as $url ) {
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
			$date = gmdate( 'Y-m-d', strtotime( sprintf( '-%d days', $i ) ) );
			$pageviews = max( 1, rand( 500, 1000 ) * $progress ^ 2 );
			$visitors = max( 1, $pageviews * rand( 3, 6 ) / 10 );

			// simulate a huge peak in traffic every 180 days
			if ( rand( 1, 180 ) === 1 ) {
				$pageviews = $pageviews * 10;
				$visitors = $visitors * 10;
			}

			$wpdb->insert(
				$wpdb->prefix . 'koko_analytics_site_stats',
				array(
					'date' => $date,
					'pageviews' => $pageviews,
					'visitors' => $visitors,
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

	public function install_optimized_endpoint() {
		update_option( 'koko_analytics_use_custom_endpoint', $this->install_optimized_endpoint_file(), true );
	}

	public function install_optimized_endpoint_file() {
		/* Do nothing if a custom endpoint was manually installed */
		if ( defined( 'KOKO_ANALYTICS_USE_CUSTOM_ENDPOINT' ) && KOKO_ANALYTICS_USE_CUSTOM_ENDPOINT ) {
			return true;
		}

		/* Do nothing if custom endpoint file already in place */
		if ( file_exists( ABSPATH . '/koko-analytics-collect.php' ) ) {
			return true;
		}

		/** @link https://www.php.net/manual/en/function.symlink.php */
		if ( ! function_exists( 'symlink' ) ) {
			return false;
		}

		/* Check for required directory structure (standard WordPress installation) */
		$required_files = array(
			KOKO_ANALYTICS_PLUGIN_DIR . '/koko-analytics-collect.php',
			KOKO_ANALYTICS_PLUGIN_DIR . '/src/functions.php',
			KOKO_ANALYTICS_PLUGIN_DIR . '/../../uploads/',
		);
		foreach ( $required_files as $f ) {
			if ( ! file_exists( $f ) ) {
				return false;
			}
		}

		/* Symlink the file into place */
		$success = symlink( KOKO_ANALYTICS_PLUGIN_DIR . '/koko-analytics-collect.php', ABSPATH . '/koko-analytics-collect.php' );
		if ( ! $success ) {
			return false;
		}

		/* Send an HTTP request to the custom endpoint to see if it's working properly */
		$tracker_url = site_url( '/koko-analytics-collect.php' );
		$response = wp_remote_get( $tracker_url );
		$status = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );
		if ( $status !== 200 || strstr( $body, 'require' ) !== false ) {
			/*
				If endpoint does not return the proper HTTP response,
				attempt to remove it again to prevent returning true on the next function run
			*/
			unlink( ABSPATH . '/koko-analytics-collect.php' );
			return false;
		}

		/* All looks good! Custom endpoint file is in place and returns 200 OK response */
		return true;
	}

}
