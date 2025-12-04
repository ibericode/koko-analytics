<?php

use KokoAnalytics\Endpoint_Installer;
use KokoAnalytics\Router;

 defined('ABSPATH') or exit;
/**
 * @var \KokoAnalytics\Admin $this
 * @var array $settings
 * @var int $database_size
 * @var array $using_custom_endpoint
 * @var \KokoAnalytics\Endpoint_Installer $endpoint_installer
 * @var array $user_roles
 * @var array $date_presets
 */
$tab          = 'settings';
$public_dashboard_url = Router::url('dashboard-standalone');
?>
<div class="wrap koko-analytics" id="koko-analytics-admin">
    <div class="ka-dashboard-nav">
    <?php require __DIR__ . '/nav.php'; ?>
    </div>

    <div class="ka-row">
        <div class="ka-col ka-col-12 ka-col-lg-8">

            <?php /* general notices: can be of type info, warning or success */ ?>
            <?php if (isset($_GET['notice'])) { ?>
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

            <h1 class="mb-4" style="line-height: 28px;"><img src="<?= plugins_url('assets/dist/img/icon.svg', KOKO_ANALYTICS_PLUGIN_FILE); ?>" height="28" width="28" alt="Koko Analytics logo" class="align-middle me-2"> <?php esc_html_e('Koko Analytics Settings', 'koko-analytics'); ?></h1>

            <form method="POST" action="<?php echo esc_attr(add_query_arg(['koko_analytics_action' => 'save_settings'])); ?>">
                <?php wp_nonce_field('koko_analytics_save_settings'); ?>
                <?php wp_referer_field(); ?>



                <div class="mb-4">
                    <label for="ka-prune-after-input" class="ka-label"><?php esc_html_e('Automatically delete data older than how many months?', 'koko-analytics'); ?></label>
                    <input class="ka-input mb-2" id="ka-prune-after-input" name="koko_analytics_settings[prune_data_after_months]" type="number" step="1" min="0" max="600" value="<?php echo esc_attr($settings['prune_data_after_months']); ?>">
                    <p class="description"><?php esc_html_e('Statistics older than the number of months configured here will automatically be deleted. Set to 0 to disable.', 'koko-analytics'); ?></p>
                </div>

                <?php do_action('koko_analytics_extra_settings_rows_before_submit', $settings); ?>

                <div class="mb-5">
                    <input type="submit" class="btn btn-primary" value="<?= esc_attr__('Save Changes') ?>">
                </div>

                <?php do_action('koko_analytics_extra_settings_rows', $settings); ?>
            </form>

            <?php do_action('koko_analytics_show_settings_sections'); ?>

            <?php if (Endpoint_Installer::is_eligibile()) { ?>
                <div class="mb-5">
                    <h2 class="mb-2"><?php esc_html_e('Performance', 'koko-analytics'); ?></h2>
                    <?php if ($using_custom_endpoint) { ?>
                        <p><span style="color: green;">✓</span> <?php esc_html_e('The plugin is currently using an optimized tracking endpoint. Great!', 'koko-analytics'); ?></p>
                    <?php } else { ?>
                        <p><?php esc_html_e('The plugin is currently not using an optimized tracking endpoint.', 'koko-analytics'); ?></p>
                        <form method="POST" action="">
                            <?php wp_nonce_field('koko_analytics_install_optimized_endpoint'); ?>
                            <input type="hidden" name="koko_analytics_action" value="install_optimized_endpoint">
                            <input type="submit" value="<?php esc_attr_e('Create optimized endpoint file', 'koko-analytics'); ?>" class="btn btn-secondary btn-sm">
                        </form>
                        <p><?php printf(esc_html__('To use one, create the file %s with the following file contents: ', 'koko-analytics'), '<code>' . Endpoint_Installer::get_file_name() . '</code>'); ?></p>
                        <textarea readonly="readonly" class="ka-input font-monospace" rows="18" onfocus="this.select();" spellcheck="false"><?php echo esc_html(Endpoint_Installer::get_file_contents()); ?></textarea>
                        <p><?php esc_html_e('Please note that this is entirely optional and only recommended for high-traffic websites.', 'koko-analytics'); ?></p>
                    <?php } ?>
                </div>
            <?php } ?>


        </div><?php // end container ?>

        <div


        </div>
    </div><?php // end flex wrap ?>
</div>

<?php if (isset($_GET['notice'])) { ?>
<script>history.replaceState({}, null, "<?= Router::url('settings-page'); ?>");</script>
<?php } ?>
