<?php
/**
 * @var KokoAnalytics\Dashboard $this
 */
defined('ABSPATH') || exit; ?>
<?php if (apply_filters('koko_analytics_print_html_comments', true)) : ?>
<!-- This dashboard is powered by Koko Analytics: privacy-friendly website analytics for WordPress sites. Find out more at https://www.kokoanalytics.com/ -->
<?php endif; ?>
<!DOCTYPE html>
<html lang="<?= esc_attr(get_bloginfo('language')); ?>">
<head>
    <meta name="charset" content="<?= esc_attr(get_bloginfo('charset')); ?>">
    <link rel="stylesheet" href="<?= esc_url(plugins_url('assets/css/dashboard.css', KOKO_ANALYTICS_PLUGIN_FILE)); ?>?v=<?= esc_attr(KOKO_ANALYTICS_VERSION); ?>">
    <script src="<?= esc_url(plugins_url('assets/js/dashboard.js', KOKO_ANALYTICS_PLUGIN_FILE)); ?>?v=<?= esc_attr(KOKO_ANALYTICS_VERSION); ?>" defer></script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer-when-downgrade">
    <title>Koko Analytics</title>
    <meta name="theme-color" content="#B60205">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Koko Analytics">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <link rel="apple-touch-icon" href="<?= esc_url(plugins_url('assets/img/icon-180x180.png', KOKO_ANALYTICS_PLUGIN_FILE)); ?>">
    <link rel="manifest" href="<?= esc_url(plugins_url('assets/manifest.json', KOKO_ANALYTICS_PLUGIN_FILE)); ?>">
    <link rel="shortcut icon" href="<?= esc_url(plugins_url('assets/img/icon.svg', KOKO_ANALYTICS_PLUGIN_FILE)); ?>" type="image/svg+xml">
    <link rel="shortcut icon" href="<?= esc_url(plugins_url('assets/img/favicon.ico', KOKO_ANALYTICS_PLUGIN_FILE)); ?>">
    <meta name="robots" content="nofollow, noindex">
</head>
<body class="koko-analytics">
    <?php parent::show(); // @phpstan-ignore-line ?>
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register(
                '<?= esc_url(plugins_url('assets/js/sw.js', KOKO_ANALYTICS_PLUGIN_FILE)); ?>'
            );
        }
    </script>
</body>
</html>
