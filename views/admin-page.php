<?php defined( 'ABSPATH' ) or exit; ?>
<div class="wrap" id="koko-analytics-admin">
	<noscript>
		<?php echo esc_html__( 'Please enable JavaScript for this page to work.', 'koko-analytics' ); ?>
	</noscript>

	<div id="koko-analytics-mount"></div>
</div>
<script>
	<?php
	echo 'var koko_analytics = ' . json_encode(
		array(
			'root'          => rest_url(),
			'nonce'         => wp_create_nonce( 'wp_rest' ),
			'i18n'          => array(
				'Automatically delete data older than how many months?' => __( 'Automatically delete data older than how many months?', 'koko-analytics' ),
				'Database size:'                          => esc_html__( 'Database size:', 'koko-analytics' ),
				'Date range'                              => esc_html__( 'Date range', 'koko-analytics' ),
				'Exclude pageviews from these user roles' => esc_html__( 'Exclude pageviews from these user roles', 'koko-analytics' ),
				'Last week'                               => esc_html__( 'Last week', 'koko-analytics' ),
				'Last month'                              => esc_html__( 'Last month', 'koko-analytics' ),
				'Last year'                               => esc_html__( 'Last year', 'koko-analytics' ),
				'months'                                  => esc_html__( 'months', 'koko-analytics' ),
				'Next'                                    => esc_html__( 'Next', 'koko-analytics' ),
				'No'                                      => esc_html__( 'No', 'koko-analytics' ),
				'Pages'                                   => esc_html__( 'Pages', 'koko-analytics' ),
				'Pageviews'                               => esc_html__( 'Pageviews', 'koko-analytics' ),
				'Previous'                                => esc_html__( 'Previous', 'koko-analytics' ),
				'Referrers'                               => esc_html__( 'Referrers', 'koko-analytics' ),
				'Save Changes'                            => esc_html__( 'Save Changes', 'koko-analytics' ),
				'Saving - please wait'                    => esc_html__( 'Saving - please wait', 'koko-analytics' ),
				'Saved!'                                  => esc_html__( 'Saved!', 'koko-analytics' ),
				'Settings'                                => esc_html__( 'Settings', 'koko-analytics' ),
				'Stats'                                   => esc_html__( 'Stats', 'koko-analytics' ),
				'Statistics older than the number of months configured here will automatically be deleted. Set to 0 to disable.' => __( 'Statistics older than the number of months configured here will automatically be deleted. Set to 0 to disable.', 'koko-analytics' ),
				'There\'s nothing here, yet!'             => esc_html__( 'There\'s nothing here, yet!', 'koko-analytics' ),
				'This week'                               => esc_html__( 'This week', 'koko-analytics' ),
				'This month'                              => esc_html__( 'This month', 'koko-analytics' ),
				'This year'                               => esc_html__( 'This year', 'koko-analytics' ),
				'Total visitors'                          => esc_html__( 'Total visitors', 'koko-analytics' ),
				'Total pageviews'                         => esc_html__( 'Total pageviews', 'koko-analytics' ),
				'Use cookie to determine unique visitors and pageviews?' => esc_html__( 'Use cookie to determine unique visitors and pageviews?', 'koko-analytics' ),
				'Visitors'                                => esc_html__( 'Visitors', 'koko-analytics' ),
				'Visits and pageviews from users with any of the selected roles will be ignored.' => __( 'Visits and pageviews from users with any of the selected roles will be ignored.', 'koko-analytics' ),
				'Yes'                                     => esc_html__( 'Yes', 'koko-analytics' ),
				'Set to "no" if you do not want to use a cookie. Without the use of a cookie, Koko Analytics can not reliably detect returning visitors.' => __( 'Set to "no" if you do not want to use a cookie. Without the use of a cookie, Koko Analytics can not reliably detect returning visitors.', 'koko-analytics' ),
			),
			'start_of_week' => $start_of_week,
			'user_roles'    => $user_roles,
			'settings'      => $settings,
			'showSettings'  => current_user_can( 'manage_koko_analytics' ),
			'dbSize'        => $this->get_database_size(),
		)
	);
	?>
</script>
<script src="<?php echo esc_url( plugins_url( 'assets/dist/js/admin.js', KOKO_ANALYTICS_PLUGIN_FILE ) .'?ver='. KOKO_ANALYTICS_VERSION ); ?>"></script>
