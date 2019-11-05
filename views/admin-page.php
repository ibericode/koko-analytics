<?php defined('ABSPATH') or exit; ?>
<div class="wrap" id="koko-analytics-admin">
    <h1>Koko Analytics</h1>

    <noscript>
        <?php echo __( 'Please enable JavaScript for this page to work.', 'koko-analytics'); ?>
    </noscript>

    <div id="koko-analytics-mount"></div>
</div>
<script>
    <?php echo 'var koko_analytics = ' . json_encode(array(
            'root' => rest_url(),
            'nonce' => wp_create_nonce('wp_rest'),
			'i18n' => array(
				'Date range' => __('Date range', 'koko-analytics'),
				'Exclude pageviews from these user roles' => __('Exclude pageviews from these user roles', 'koko-analytics'),
				'Last week' => __('Last week', 'koko-analytics'),
				'Last month' => __('Last month', 'koko-analytics'),
				'Last year' => __('Last year', 'koko-analytics'),
				'Pages' => __('Pages', 'koko-analytics'),
				'Pageviews' => __('Pageviews', 'koko-analytics'),
				'Referrers' => __('Referrers', 'koko-analytics'),
				'Save Changes' => __('Save Changes', 'koko-analytics'),
				'Saving - please wait' => __('Saving - please wait', 'koko-analytics'),
                'Saved!' => __('Saved!', 'koko-analytics'),
				'There\'s nothing here, yet!' => __('There\'s nothing here, yet!', 'koko-analytics'),
				'This week' => __('This week', 'koko-analytics'),
				'This month' => __('This month', 'koko-analytics'),
				'This year' => __('This year', 'koko-analytics'),
				'Total visitors' => __('Total visitors', 'koko-analytics'),
				'Total pageviews' => __('Total pageviews', 'koko-analytics'),
				'Visitors' => __('Visitors', 'koko-analytics'),
			),
            'start_of_week' => $start_of_week,
            'user_roles' => $user_roles,
            'settings' => $settings,
        )); ?>
</script>
<script src="<?php echo sprintf('%s?ver=%s', plugins_url('assets/dist/js/admin.js', KOKO_ANALYTICS_PLUGIN_FILE), KOKO_ANALYTICS_VERSION); ?>"></script>
