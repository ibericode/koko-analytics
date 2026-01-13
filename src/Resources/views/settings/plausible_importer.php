<?php if (isset($_GET['error'])) { ?>
       <div class="ka-alert ka-alert-warning ka-alert-dismissible" role="alert">
            <?php esc_html_e('An error occurred trying to import your statistics.', 'koko-analytics'); ?>
            <?php echo ' '; ?>
            <?php echo wp_kses(stripslashes(trim($_GET['error'])), [ 'br' => []]); ?>
            <button type="button" class="btn-close" aria-label="<?= esc_attr('Close', 'koko-analytics') ?>" onclick="this.parentElement.remove()"></button>
        </div>
<?php } ?>
<?php if (isset($_GET['success']) && $_GET['success'] == 1) { ?>
    <div class="ka-alert ka-alert-success ka-alert-dismissible" role="alert">
        <?php esc_html_e('Big success! Your stats are now imported into Koko Analytics.', 'koko-analytics'); ?>
        <button type="button" class="btn-close" aria-label="<?= esc_attr('Close', 'koko-analytics') ?>" onclick="this.parentElement.remove()"></button>
    </div>
<?php } ?>

<h1 class="mt-0"><?php esc_html_e('Import from Plausible', 'koko-analytics'); ?></h1>
<form method="post" onsubmit="return confirm('<?php esc_attr_e('Are you sure you want to import statistics between', 'koko-analytics'); ?> ' + this['date-start'].value + '<?php esc_attr_e(' and ', 'koko-analytics'); ?>' + this['date-end'].value + '<?php esc_attr_e('? This will add to any existing data in your Koko Analytics database tables.', 'koko-analytics'); ?>');" action="" enctype="multipart/form-data">

    <input type="hidden" name="koko_analytics_action" value="start_plausible_import">
    <?php wp_nonce_field('koko_analytics_start_plausible_import'); ?>
    <?php wp_referer_field(); ?>

    <table class="form-table">
        <tr>
            <th><label for="plausible-export-file"><?php esc_html_e('Plausible CSV export', 'koko-analytics'); ?></label></th>
            <td>
                <input id="plausible-export-file" type="file" class="form-control" name="plausible-export-file" accept=".csv" required>
                 <p class="description"><?php esc_html_e('Accepted files are "imported_visitors.csv", "imported_pages.csv" and "imported_sources.csv" from the Plausible export ZIP.', 'koko-analytics'); ?></p>
            </td>
        </tr>

        <tr>
            <th><label for="date-start"><?php esc_html_e('Start date', 'koko-analytics'); ?></label></th>
            <td>
                <input id="date-start" name="date-start" type="date" value="<?php echo esc_attr(date('Y-m-d', strtotime('-1 year'))); ?>" required>
                 <p class="description"><?php esc_html_e('The earliest date for which to import data.', 'koko-analytics'); ?></p>

            </td>
        </tr>

        <tr>
            <th><label for="date-end"><?php esc_html_e('End date', 'koko-analytics'); ?></label></th>
            <td>
                <input id="date-end" name="date-end" type="date" value="<?php echo esc_attr(date('Y-m-d')); ?>" required>
                <p class="description"><?php esc_html_e('The last date for which to import data.', 'koko-analytics'); ?></p>

            </td>
        </tr>
    </table>

    <p style="color: indianred;">
        <strong><?php esc_html_e('Warning: ', 'koko-analytics'); ?></strong>
        <?php esc_html_e('Importing data for a given date range will add to any existing data. The import process can not be reverted unless you reinstate a back-up of your database in its current state.', 'koko-analytics'); ?>
    </p>

    <p>
        <button type="submit" class="btn btn-primary"><?php esc_html_e('Import analytics data', 'koko-analytics'); ?></button>
    </p>
</form>
