<?php
/**
* @var KokoAnalytics\Admin $this
 */
defined('ABSPATH') or exit; ?><!DOCTYPE html>
<html lang="<?php bloginfo('language'); ?>">
<head>
    <link rel="stylesheet" href="<?php echo esc_attr(plugins_url('assets/dist/css/standalone.css', KOKO_ANALYTICS_PLUGIN_FILE)); ?>">
    <link rel="stylesheet" href="<?php echo esc_attr(plugins_url('assets/dist/css/admin.css', KOKO_ANALYTICS_PLUGIN_FILE)); ?>">
    <meta name="charset" content="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="referrer" content="no-referrer-when-downgrade">
    <meta name="robots" content="noindex" />
    <title>Koko Analytics</title>
</head>
<body>
    <?php $this->show_dashboard_page(); ?>
    <?php wp_print_scripts('koko-analytics-admin'); ?>
    <?php do_action('koko_analytics_standalone_footer'); ?>
</body>
</html>
