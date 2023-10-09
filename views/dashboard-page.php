<?php defined('ABSPATH') or exit;
$tab = 'dashboard';
?>
<div class="wrap" id="koko-analytics-admin">

    <?php
    if (false === $is_cron_event_working) {
        echo '<div class="notice notice-warning inline koko-analytics-cron-warning"><p>';
        echo esc_html__('There seems to be an issue with your site\'s WP Cron configuration that prevents Koko Analytics from automatically processing your statistics.', 'koko-analytics');
        echo ' ';
        echo esc_html__('If you\'re not sure what this is about, please ask your webhost to look into this.', 'koko-analytics');
        echo '</p></div>';
    }

    if (false === $is_buffer_dir_writable) {
        echo  '<div class="notice notice-warning inline is-dismissible"><p>';
        echo wp_kses(sprintf(__('Koko Analytics is unable to write to the <code>%s</code> directory. Please update the file permissions so that your web server can write to it.', 'koko-analytics'), $buffer_dirname), array( 'code' => array() ));
        echo '</p></div>';
    }

    ?>

    <div class="notice notice-warning is-dismissible" id="koko-analytics-adblock-notice" style="display: none;">
        <p>
            <?php _e('You appear to be using an ad-blocker that has Koko Analytics on its blocklist. Please whitelist this domain in your ad-blocker setting if your dashboard does not seem to be working correctly.', 'koko-analytics'); ?>
        </p>
    </div>
    <script src="<?php echo plugins_url('/assets/dist/js/koko-analytics-script-test.js', KOKO_ANALYTICS_PLUGIN_FILE); ?>" defer onerror="document.getElementById('koko-analytics-adblock-notice').style.display = '';"></script>

    <noscript>
        <?php echo esc_html__('Please enable JavaScript for this page to work.', 'koko-analytics'); ?>
    </noscript>

    <?php require __DIR__ . '/nav.php'; ?>

    <div id="koko-analytics-mount">
        <p><?php echo __('Please wait, your Koko Analytics dashboard is booting up...', 'koko-analytics'); ?></p>
        <p><?php echo __('If your dashboard does not automatically appear in a few seconds, please check your browser console for any error messages.', 'koko-analytics'); ?></p>
    </div>
</div>
