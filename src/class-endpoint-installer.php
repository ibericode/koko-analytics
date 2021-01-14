<?php

namespace KokoAnalytics;

class Endpoint_Installer {
	public function run() {
		update_option( 'koko_analytics_use_custom_endpoint', $this->install_optimized_endpoint_file(), true );
	}

	private function install_optimized_endpoint_file() {
		/* Do nothing if a custom endpoint was manually installed */
		if ( defined( 'KOKO_ANALYTICS_USE_CUSTOM_ENDPOINT' ) ) {
			return KOKO_ANALYTICS_USE_CUSTOM_ENDPOINT;
		}

		/* Do nothing if custom endpoint file already in place */
		if ( file_exists( ABSPATH . '/koko-analytics-collect.php' ) ) {
			return true;
		}

		/** @link https://www.php.net/manual/en/function.symlink.php */
		if ( ! function_exists( 'symlink' ) ) {
			return false;
		}

		/* Do nothing if site url differs from WP url */
		if ( get_option( 'home' ) !== get_option( 'siteurl' ) ) {
			return false;
		}

		/* Do nothing if running Multisite */
		if ( defined( 'MULTISITE' ) && MULTISITE ) {
			return false;
		}

		/* Do nothing if uploads directory is not one-up from plugins directory */
		$uploads = wp_upload_dir( null, false );
		if ( realpath( KOKO_ANALYTICS_PLUGIN_DIR . '/../../uploads/' ) !== realpath( $uploads['basedir'] ) ) {
			return false;
		}

		/* Check for required directory structure (standard WordPress installation) */
		$required_files = array(
			KOKO_ANALYTICS_PLUGIN_DIR . '/koko-analytics-collect.php',
			KOKO_ANALYTICS_PLUGIN_DIR . '/src/functions.php',
		);
		foreach ( $required_files as $f ) {
			if ( ! file_exists( $f ) ) {
				return false;
			}
		}

		/* Symlink the file into place */
		$success = @symlink( KOKO_ANALYTICS_PLUGIN_DIR . '/koko-analytics-collect.php', ABSPATH . '/koko-analytics-collect.php' );
		if ( ! $success ) {
			return false;
		}

		/* Send an HTTP request to the custom endpoint to see if it's working properly */
		$works = $this->test();
		if ( ! $works ) {
			/*
				If endpoint does not return the proper HTTP response,
				attempt to remove it to prevent returning true on the next function run
			*/
			unlink( ABSPATH . '/koko-analytics-collect.php' );
			return false;
		}

		/* All looks good! Custom endpoint file is in place and returns 200 OK response */
		return true;
	}

	/**
	 * Check for correct HTTP response from custom endpoint file.
	 *
	 * @see collect_request()
	 * @return bool
	 */
	private function test() {
		$tracker_url = site_url( '/koko-analytics-collect.php?nv=1&p=0&up=1&test=1' );
		$response = wp_remote_get( $tracker_url );
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$status = wp_remote_retrieve_response_code( $response );
		$headers = wp_remote_retrieve_headers( $response );
		$body = wp_remote_retrieve_body( $response );
		if ( $status !== 200 || $headers['Content-Type'] !== 'image/gif' || $body !== base64_decode( 'R0lGODlhAQABAIAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==' ) ) {
			return false;
		}

		return true;
	}
}
