<?php
/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */
namespace KokoAnalytics;

class Plugin {

	/**
	 * @var Aggregator
	 */
	private $aggregator;

	/**
	 * @param Aggregator $aggregator
	 */
	public function __construct( Aggregator $aggregator ) {
		$this->aggregator = $aggregator;
	}

	public function init() {
		add_filter( 'pre_update_option_active_plugins', array( $this, 'filter_active_plugins' ) );
		register_activation_hook( KOKO_ANALYTICS_PLUGIN_FILE, array( $this, 'on_activation' ) );
	}

	public function filter_active_plugins( $plugins ) {
		if ( empty( $plugins ) ) {
			return $plugins;
		}

		$pattern = '/' . preg_quote( plugin_basename( KOKO_ANALYTICS_PLUGIN_FILE ), '/' ) . '$/';
		return array_merge(
			preg_grep( $pattern, $plugins ),
			preg_grep( $pattern, $plugins, PREG_GREP_INVERT )
		);
	}

	public function on_activation() {
		// make sure koko analytics loads first to prevent unnecessary work on stat collection requests
		update_option( 'activate_plugins', get_option( 'active_plugins' ) );

		// add capabilities to administrator role
		$role = get_role( 'administrator' );
		$role->add_cap( 'view_koko_analytics' );
		$role->add_cap( 'manage_koko_analytics' );

		// schedule action for aggregating stats
		$this->aggregator->setup_scheduled_event();
	}

	//
	//    public function create_symlink()
	//    {
	//        if (!file_exists( ABSPATH . '/koko-analytics-collect.php') && function_exists('symlink')) {
	//            @symlink( KOKO_ANALYTICS_PLUGIN_DIR . '/collect.php', ABSPATH . '/koko-analytics-collect.php'  );
	//        }
	//    }
	//
	//    public function remove_symlink()
	//    {
	//        if (file_exists( ABSPATH . '/koko-analytics-collect.php' )) {
	//            unlink(ABSPATH . '/koko-analytics-collect.php');
	//        }
	//    }
}
