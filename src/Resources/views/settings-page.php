<?php

use KokoAnalytics\Endpoint_Installer;

?>

<div class="wrap koko-analytics" id="koko-analytics-admin">
    <a href="<?= esc_attr(admin_url('index.php?page=koko-analytics')) ?>">← <?= esc_html__('Back to stats', 'koko-analytics') ?></a>
    <h1 class="my-3"><img src="<?= plugins_url('assets/dist/img/icon.svg', KOKO_ANALYTICS_PLUGIN_FILE); ?>" height="32" width="32" alt="Koko Analytics logo" class="align-middle me-2" style="margin-top: -4px;"> <?php esc_html_e('Koko Analytics Settings', 'koko-analytics'); ?></h1>

    <hr class="my-3">

    <div class="ka-row">
        <div class="ka-col ka-col-3" style="max-width: 280px;">
            <ul class="ka-settings-nav">
                <li><a href="<?= esc_attr(admin_url('options-general.php?page=koko-analytics-settings')) ?>" class="<?= $active_tab == 'tracking' ? 'active' : '' ?>"><?= esc_html__('Tracking', 'koko-analytics') ?></a></li>
                <li><a href="<?= esc_attr(admin_url('options-general.php?page=koko-analytics-settings&tab=dashboard')) ?>" class="<?= $active_tab == 'dashboard' ? 'active' : '' ?>"><?= esc_html__('Dashboard', 'koko-analytics') ?></a></li>
                <li><a href="<?= esc_attr(admin_url('options-general.php?page=koko-analytics-settings&tab=events')) ?>" class="<?= $active_tab == 'events' ? 'active' : '' ?>"><?= esc_html__('Events', 'koko-analytics') ?></a></li>
                <li><a href="<?= esc_attr(admin_url('options-general.php?page=koko-analytics-settings&tab=email-reports')) ?>" class="<?= $active_tab == 'email-reports' ? 'active' : '' ?>"><?= esc_html__('Email reports', 'koko-analytics') ?></a></li>
                <li><a href="<?= esc_attr(admin_url('options-general.php?page=koko-analytics-settings&tab=data')) ?>" class="<?= $active_tab == 'data' ? 'active' : '' ?>">Data</a></li>
                <?php if (!is_multisite()) { ?>
                <li><a href="<?= esc_attr(admin_url('options-general.php?page=koko-analytics-settings&tab=performance')) ?>" class="<?= $active_tab == 'performance' ? 'active' : '' ?>"><?= esc_html__('Performance', 'koko-analytics') ?></a></li>
                <?php } ?>
                <li><a href="<?= esc_attr(admin_url('options-general.php?page=koko-analytics-settings&tab=help')) ?>" class="<?= $active_tab == 'help' ? 'active' : '' ?>"><?= esc_html__('Help', 'koko-analytics') ?></a></li>
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
                    <?php include __DIR__ . "/settings/{$active_tab}.php"; ?>
                </div>
            </div><?php /* .ka-settings-main */ ?>
        </div><?php /* .ka-col-9 */ ?>
    </div><?php /* .ka-row */ ?>


</div><?php /* .wrap */ ?>

