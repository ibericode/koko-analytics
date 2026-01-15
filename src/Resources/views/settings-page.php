<?php

use KokoAnalytics\Endpoint_Installer;

$tabs = apply_filters('koko_analytics_settings_tabs', [
    ['id' => 'tracking', 'url' => admin_url('options-general.php?page=koko-analytics-settings'), 'title' => __('Tracking', 'koko-analytics')],
    ['id' => 'dashboard', 'url' => admin_url('options-general.php?page=koko-analytics-settings&tab=dashboard'), 'title' => __('Dashboard', 'koko-analytics')],
    ['id' => 'events', 'url' => admin_url('options-general.php?page=koko-analytics-settings&tab=events'), 'title' => __('Events', 'koko-analytics')],
    ['id' => 'email-reports', 'url' => admin_url('options-general.php?page=koko-analytics-settings&tab=email-reports'), 'title' => __('Email reports', 'koko-analytics')],
    ['id' => 'data', 'url' => admin_url('options-general.php?page=koko-analytics-settings&tab=email-reports'), 'title' => __('Data', 'koko-analytics')],
    ['id' => 'performance', 'url' => admin_url('options-general.php?page=koko-analytics-settings&tab=performance'), 'title' => __('Performance', 'koko-analytics')],
    ['id' => 'help', 'url' => admin_url('options-general.php?page=koko-analytics-settings&tab=help'), 'title' => __('Help', 'koko-analytics')],
]);
?>

<div class="wrap koko-analytics" id="koko-analytics-admin">
    <a href="<?= esc_attr(admin_url('index.php?page=koko-analytics')) ?>">← <?= esc_html__('Back to stats', 'koko-analytics') ?></a>
    <h1 class="my-3"><img src="<?= plugins_url('assets/dist/img/icon.svg', KOKO_ANALYTICS_PLUGIN_FILE); ?>" height="32" width="32" alt="Koko Analytics logo" class="align-middle me-2" style="margin-top: -4px;"> <?php esc_html_e('Koko Analytics Settings', 'koko-analytics'); ?></h1>

    <hr class="my-3">

    <div class="ka-row">
        <div class="ka-col ka-col-3" style="max-width: 200px;">
            <ul class="ka-settings-nav">
                <?php foreach ($tabs as $t) : ?>
                    <li><a href="<?= esc_attr($t['url']) ?>" class="<?= $active_tab == $t['id'] ? 'active' : '' ?>"><?= esc_html($t['title']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="ka-col ka-col-9" style="max-width: 100ch;">
            <div class="ka-settings-main">

                <?php /* error messages: query key error */ ?>
                <?php if (!empty($_GET['error'])) { ?>
                    <div class="ka-alert ka-alert-warning ka-alert-dismissible" role="alert">
                        <?= esc_html($_GET['error']); ?>
                         <button type="button" class="btn-close" aria-label="<?= esc_attr('Close', 'koko-analytics') ?>" onclick="this.parentElement.remove()"></button>
                    </div>
                <?php } ?>

                <?php /* error messages: query key message */ ?>
                <?php if (!empty($_GET['message'])) { ?>
                    <div class="ka-alert ka-alert-success ka-alert-dismissible" role="alert">
                        <?= esc_html($_GET['message']); ?>
                         <button type="button" class="btn-close" aria-label="<?= esc_attr('Close', 'koko-analytics') ?>" onclick="this.parentElement.remove()"></button>
                    </div>
                <?php } ?>

                <?php /* settings saved messages: query key settings-updated */ ?>
               <?php if (isset($_GET['settings-updated'])) { ?>
                <div class="ka-alert ka-alert-success ka-alert-dismissible" role="alert">
                    <?php esc_html_e('Settings saved.', 'koko-analytics'); ?>
                    <button type="button" class="btn-close" aria-label="<?= esc_attr('Close', 'koko-analytics') ?>" onclick="this.parentElement.remove()"></button>
                </div>
               <?php } ?>

               <div>
                    <?php
                    // if this is a core settings tab, simply include the view file
                    if (file_exists(__DIR__ . "/settings/{$active_tab}.php")) {
                        include __DIR__ . "/settings/{$active_tab}.php";
                    } else {
                        // otherwise, fire an action hook to give plugins a chance to output their own stuff
                        do_action("koko_analytics_output_settings_tab_{$active_tab}");
                    }
                    ?>
               </div>
            </div><?php /* .ka-settings-main */ ?>
        </div><?php /* .ka-col-9 */ ?>
    </div><?php /* .ka-row */ ?>
</div><?php /* .wrap */ ?>

