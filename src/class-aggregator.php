<?php
/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */
namespace KokoAnalytics;

use Exception;

class Aggregator {

	public function init() {
		add_action( 'koko_analytics_aggregate_stats', array( $this, 'aggregate' ) );
		add_filter( 'cron_schedules', array( $this, 'add_interval' ) );
		add_action( 'init', array( $this, 'maybe_setup_scheduled_event' ) );

	}

	public function add_interval( $intervals ) {
		$intervals['koko_analytics_stats_aggregate_interval'] = array(
			'interval' => 60, // 60 seconds
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

	/**
	 * Reads the buffer file into memory and moves data into the MySQL database (in bulk)
	 *
	 * @throws Exception
	 */
	public function aggregate($force = false) {
		// init pageview aggregator
		$pageview_aggregator = new Pageview_Aggregator();
		$pageview_aggregator->init();

		// read pageviews buffer file into array
		$filename = get_buffer_filename();
		if ( ! file_exists( $filename ) ) {
			// no pageviews were collected since last run, so we have nothing to do
			return;
		}

		// rename file to temporary location so nothing new is written to it while we process it
		$tmp_filename = dirname( $filename ) . '/pageviews-busy.php';

		// if file exists, previous aggregation job is still running or never finished
		if ( !$force && file_exists( $tmp_filename ) ) {
			// if file is less than 3 minutes old, wait for it to eventually finish
			if ( filemtime( $tmp_filename ) > ( time() - 3 * 60 ) ) {
				return;
			} else {
				// try to delete file to signal other process to finish
				unlink( $tmp_filename );
				sleep( 2 );
			}
		}

		$renamed = rename( $filename, $tmp_filename );
		if ( $renamed !== true ) {
			if ( WP_DEBUG ) {
				throw new Exception( 'Error renaming buffer file.' );
			}
			return;
		}

		// open file for reading
		$file_handle = fopen( $tmp_filename, 'rb' );
		if ( ! is_resource( $file_handle ) ) {
			if ( WP_DEBUG ) {
				throw new Exception( 'Error opening buffer file for reading.' );
			}
			return;
		}

		// read and ignore first line (the PHP header that prevents direct file access)
		fgets( $file_handle, 1024 );

		while ( ( $line = fgets( $file_handle, 1024 ) ) !== false ) {
			$line = rtrim( $line );
			if ( empty( $line ) ) {
				continue;
			}

			$params = explode( ',', $line );
			$type = array_shift( $params );
			do_action( 'koko_analytics_aggregate_line', $type, $params );
		}

		// close file & remove it from filesystem
		fclose( $file_handle );
		unlink( $tmp_filename );

		do_action( 'koko_analytics_aggregate_finish' );
	}

}
