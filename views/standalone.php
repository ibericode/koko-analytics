<?php
/**
* @var KokoAnalytics\Dashboard $this
 */
defined('ABSPATH') or exit; ?><!DOCTYPE html>
<html lang="<?php bloginfo('language'); ?>">
<head>
    <?php wp_print_scripts('koko-analytics-dashboard'); ?>
    <?php do_action('koko_analytics_dashboard_head'); ?>
    <link rel="stylesheet" href="<?php echo esc_attr(plugins_url('assets/dist/css/dashboard.css', KOKO_ANALYTICS_PLUGIN_FILE)); ?>">
    <meta name="charset" content="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer-when-downgrade">
    <meta name="robots" content="noindex, nofollow">
    <title>Koko Analytics</title>
</head>
<body class="ka-dashboard">
    <?php $this->show(); ?>
    <?php do_action('koko_analytics_dashboard_footer'); ?>
</body>
</html>
