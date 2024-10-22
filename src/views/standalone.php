<?php
/**
* @var KokoAnalytics\Dashboard $this
 */
defined('ABSPATH') or exit; ?><!DOCTYPE html>
<html lang="<?php bloginfo('language'); ?>">
<head>
    <link rel="stylesheet" href="<?php echo plugins_url('assets/dist/css/dashboard.css', KOKO_ANALYTICS_PLUGIN_FILE); ?>?v=<?php echo KOKO_ANALYTICS_VERSION; ?>">
    <script src="<?php echo plugins_url('assets/dist/js/dashboard.js', KOKO_ANALYTICS_PLUGIN_FILE); ?>?v=<?php echo KOKO_ANALYTICS_VERSION; ?>" defer></script>
    <meta name="charset" content="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer-when-downgrade">
    <meta name="robots" content="noindex,nofollow">
    <title>Koko Analytics</title>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Koko Analytics">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <link rel="apple-touch-icon" href="<?php echo plugins_url('assets/dist/img/apple-touch-icon.png', KOKO_ANALYTICS_PLUGIN_FILE); ?>">
    <link rel="manifest" href="<?php echo plugins_url('assets/dist/manifest.json', KOKO_ANALYTICS_PLUGIN_FILE); ?>">
    <meta name="theme-color" content="#B60205">
</head>
<body class="ka-dashboard">
    <?php $this->show(); ?>
    <script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register(
            '<?php echo plugins_url('assets/dist/js/sw.js', KOKO_ANALYTICS_PLUGIN_FILE); ?>'
        );
    }
    </script>
</body>
</html>
