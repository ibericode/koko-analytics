<?php
defined('ABSPATH') || exit;

/**
 * @var array $settings
 */

$database_stats = \KokoAnalytics\get_database_stats()->get();
?>

<h2 class="mt-0 mb-3"><?= esc_html__('Data settings', 'koko-analytics'); ?></h2>

<div class="mb-5">
    <h3 class="mb-2"><?php esc_html_e('Database usage', 'koko-analytics'); ?></h3>
    <p>
        <?php
        printf(
            esc_html__('Koko Analytics is using %1$s across approximately %2$s rows.', 'koko-analytics'),
            esc_html(size_format($database_stats['total_size'])),
            esc_html(number_format_i18n($database_stats['total_rows']))
        );
        ?>
    </p>
    <p class="description"><?php esc_html_e('Keeping less historical data can make your dashboard load faster and helps database migrations finish in constrained hosting environments. Use the setting below to automatically delete older statistics.', 'koko-analytics'); ?></p>
    <?php if (count($database_stats['tables']) > 0) : ?>
        <details>
            <summary><?php esc_html_e('Show database table details', 'koko-analytics'); ?></summary>
            <table class="widefat striped mt-2">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Table', 'koko-analytics'); ?></th>
                        <th><?php esc_html_e('Rows', 'koko-analytics'); ?></th>
                        <th><?php esc_html_e('Data size', 'koko-analytics'); ?></th>
                        <th><?php esc_html_e('Index size', 'koko-analytics'); ?></th>
                        <th><?php esc_html_e('Total size', 'koko-analytics'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($database_stats['tables'] as $table) : ?>
                        <tr>
                            <td><code><?= esc_html($table['name']); ?></code></td>
                            <td><?= esc_html('~' . number_format_i18n($table['rows'])); ?></td>
                            <td><?= esc_html(size_format($table['data_size'])); ?></td>
                            <td><?= esc_html(size_format($table['index_size'])); ?></td>
                            <td><?= esc_html(size_format($table['total_size'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </details>
    <?php else : ?>
        <p><?php esc_html_e('No Koko Analytics database tables found.', 'koko-analytics'); ?></p>
    <?php endif; ?>
</div>

<form method="POST" action="">
    <input type="hidden" name="koko_analytics_action" value="save_settings">
    <?php wp_nonce_field('koko_analytics_save_settings'); ?>
    <?php wp_referer_field(); ?>
    <div class="mb-2">
        <label for="ka-prune-after-input" class="ka-label"><?php esc_html_e('Automatically delete data older than how many months?', 'koko-analytics'); ?></label>
        <input class="ka-input mb-2" id="ka-prune-after-input" name="koko_analytics_settings[prune_data_after_months]" type="number" step="1" min="0" max="600" value="<?php echo esc_attr($settings['prune_data_after_months']); ?>">
        <p class="description"><?php esc_html_e('Statistics older than the number of months configured here will automatically be deleted. Set to 0 to disable.', 'koko-analytics'); ?></p>
    </div>

    <div class="mb-5">
        <input type="submit" class="btn btn-primary" value="<?= esc_attr__('Save Changes'); ?>">
    </div>
</form>

<div class="mb-5">
    <h3 id="import-data" class="mb-2"><?php esc_html_e('Import data', 'koko-analytics'); ?></h3>
    <h4><?php esc_html_e('Import from an export file', 'koko-analytics'); ?></h4>
    <p><?php esc_html_e('You can import a dataset from an earlier export into Koko Analytics using the form below.', 'koko-analytics'); ?></p>
    <form method="POST" action="" enctype="multipart/form-data" onsubmit="return confirm('<?php esc_attr_e('Are you sure you want to import the given dataset? This will replace your current data.', 'koko-analytics'); ?>')">
        <?php wp_nonce_field('koko_analytics_import_data'); ?>
        <?php wp_referer_field(); ?>
        <input type="hidden" name="koko_analytics_action" value="import_data" />
        <div class="mb-2">
            <input class="ka-input" type="file" name="import-file" id="import-file" accept=".ndjson,application/x-ndjson,application/json,text/plain" required>
        </div>
        <div class="mb-2">
            <input type="submit" value="<?php esc_attr_e('Import', 'koko-analytics'); ?>" class="btn btn-secondary btn-sm" />
        </div>
    </form>

    <h4 class="mt-4"><?php esc_html_e('Import from another plugin', 'koko-analytics'); ?></h4>
    <p><?= esc_html__('If you\'re coming from another statistics plugin, you may be able to import your historical data using one of our importers listed below.', 'koko-analytics'); ?></p>

    <ul class="ul-square">
        <li><a href="<?= esc_attr(admin_url('options-general.php?page=koko-analytics-settings&tab=jetpack_importer')); ?>"><?php esc_html_e('Import from Jetpack Stats', 'koko-analytics'); ?></a></li>
        <li><a href="<?= esc_attr(admin_url('options-general.php?page=koko-analytics-settings&tab=plausible_importer')); ?>"><?php esc_html_e('Import from Plausible', 'koko-analytics'); ?></a></li>
    </ul>
</div>


<div class="mb-5">
    <h3  class="mb-2"><?php esc_html_e('Export data', 'koko-analytics'); ?></h3>
    <p><?php esc_html_e('Export your current dataset to NDJSON using the form below.', 'koko-analytics'); ?></p>
    <form method="POST" action="">
        <?php wp_nonce_field('koko_analytics_export_data'); ?>
        <?php wp_referer_field(); ?>
        <input type="hidden" name="koko_analytics_action" value="export_data" />
        <input type="submit" value="<?php esc_attr_e('Export', 'koko-analytics'); ?>" class="btn btn-secondary btn-sm" />
    </form>
</div>

<div class="mb-5">
    <h3 class="mb-2"><?php esc_html_e('Reset data', 'koko-analytics'); ?></h3>
    <p><?php esc_html_e('Use the button below to erase all of your current analytics data.', 'koko-analytics'); ?></p>
    <form method="POST" action="" onsubmit="return confirm('<?php esc_attr_e('Are you sure you want to reset all of your statistics? This can not be undone.', 'koko-analytics'); ?>')">
        <?php wp_nonce_field('koko_analytics_reset_statistics'); ?>
        <?php wp_referer_field(); ?>
        <input type="hidden" name="koko_analytics_action" value="reset_statistics" />
        <input type="submit" value="<?php esc_attr_e('Reset Statistics', 'koko-analytics'); ?>" class="btn btn-danger btn-sm" />
    </form>
</div>
