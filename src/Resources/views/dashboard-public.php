<?php
/**
 * @var KokoAnalytics\Dashboard $this
 */
defined('ABSPATH') or exit; ?>
<?php if (apply_filters('koko_analytics_print_html_comments', true)) : ?>
<!-- This dashboard is powered by Koko Analytics: privacy-friendly website analytics for WordPress sites. Find out more at https://www.kokoanalytics.com/ -->
<?php endif; ?>
<!DOCTYPE html>
<html lang="<?= bloginfo('language'); ?>">
<head>
    <meta name="charset" content="<?= bloginfo('charset'); ?>">
    <link rel="stylesheet" href="<?= plugins_url('assets/css/dashboard.css', KOKO_ANALYTICS_PLUGIN_FILE); ?>?v=<?= KOKO_ANALYTICS_VERSION; ?>">
    <script src="<?= plugins_url('assets/js/dashboard.js', KOKO_ANALYTICS_PLUGIN_FILE); ?>?v=<?= KOKO_ANALYTICS_VERSION; ?>" defer></script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer-when-downgrade">
    <title>Koko Analytics</title>
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Koko Analytics">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <link rel="apple-touch-icon" href="<?= plugins_url('assets/img/apple-touch-icon.png', KOKO_ANALYTICS_PLUGIN_FILE); ?>">
    <link rel="manifest" href="<?= plugins_url('assets/manifest.json', KOKO_ANALYTICS_PLUGIN_FILE); ?>">
    <link rel="shortcut icon" href="<?= plugins_url('assets/img/favicon.ico', KOKO_ANALYTICS_PLUGIN_FILE); ?>">
    <link rel="canonical" href="<?= site_url('/koko-analytics-dashboard/'); ?>">
    <meta name="robots" content="nofollow, noindex">
    <meta name="theme-color" content="#B60205">
</head>
<body class="koko-analytics">
    <?php parent::show(); // @phpstan-ignore-line ?>
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register(
                '<?= plugins_url('assets/js/sw.js', KOKO_ANALYTICS_PLUGIN_FILE); ?>'
            );
        }
    </script>
</body>
</html>
