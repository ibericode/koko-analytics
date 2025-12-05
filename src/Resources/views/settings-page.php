<?php use KokoAnalytics\Endpoint_Installer; ?>

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
                <?php if (Endpoint_Installer::is_eligibile()) { ?>
                <li><a href="<?= esc_attr(admin_url('options-general.php?page=koko-analytics-settings&tab=performance')) ?>" class="<?= $active_tab == 'performance' ? 'active' : '' ?>"><?= esc_html__('Performance', 'koko-analytics') ?></a></li>
                <?php } ?>
                <li><a href="<?= esc_attr(admin_url('options-general.php?page=koko-analytics-settings&tab=help')) ?>" class="<?= $active_tab == 'help' ? 'active' : '' ?>"><?= esc_html__('Help', 'koko-analytics') ?></a></li>
            </ul>
        </div>
        <div class="ka-col ka-col-9" style="max-width: 100ch;">
            <div class="ka-settings-main">

                <?php /* general notices: can be of type info, warning or success */ ?>
                <?php if (isset($_GET['notice']) && is_array($_GET['notice'])) { ?>
                    <div class="ka-alert ka-alert-<?= esc_attr($_GET['notice']['type']); ?> ka-alert-dismissible" role="alert">
                        <?php if (isset($_GET['notice']['title'])) : ?>
                            <strong><?= esc_html($_GET['notice']['title']); ?></strong><br>
                        <?php endif; ?>
                        <?= esc_html($_GET['notice']['message']); ?>
                         <button type="button" class="btn-close" aria-label="<?= esc_attr('Close', 'koko-analytics') ?>" onclick="this.parentElement.remove()"></button>
                    </div>
                <?php } ?>
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

<?php if (isset($_GET['notice'])) { ?>
<script>history.replaceState({}, null, "<?= remove_query_arg('notice') ?>");</script>
<?php } ?>
