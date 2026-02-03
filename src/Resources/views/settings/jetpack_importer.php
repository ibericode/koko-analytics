<?php if (isset($_GET['success']) && $_GET['success'] == 1) { ?>
    <div class="ka-alert ka-alert-success ka-alert-dismissible" role="alert">
        <?php esc_html_e('Big success! Your stats are now imported into Koko Analytics.', 'koko-analytics'); ?>
        <button type="button" class="btn-close" aria-label="<?= esc_attr('Close', 'koko-analytics') ?>" onclick="this.parentElement.remove()"></button>
    </div>
<?php } ?>

<h1 class="mt-0"><?php esc_html_e('Import from Jetpack Stats', 'koko-analytics'); ?></h1>
<p><?php esc_html_e('To import your historical analytics data from JetPack Stats into Koko Analytics, provide your WordPress.com API key and blog URL in the field below.', 'koko-analytics'); ?></p>

<form method="post" onsubmit="return confirm('<?php esc_attr_e('Are you sure you want to import statistics between', 'koko-analytics'); ?> ' + this['date-start'].value + '<?php esc_attr_e(' and ', 'koko-analytics'); ?>' + this['date-end'].value + '<?php esc_attr_e('? This will overwrite any existing data in your Koko Analytics database tables.', 'koko-analytics'); ?>');" action="">

    <input type="hidden" name="koko_analytics_action" value="start_jetpack_import">
    <?php wp_nonce_field('koko_analytics_start_jetpack_import'); ?>
    <?php wp_referer_field(); ?>

    <table class="form-table">
        <tr>
            <th><label for="wpcom-api-key"><?php esc_html_e('WordPress.com API key', 'koko-analytics'); ?></label></th>
            <td>
                <input id="wpcom-api-key" type="text" class="regular-text" name="wpcom-api-key" required>
                <p class="description"><?php printf(esc_html__('You can %1$sfind your WordPress.com API key here%2$s.', 'koko-analytics'), '<a href="https://apikey.wordpress.com/" target="_blank">', '</a>'); ?></p>
            </td>
        </tr>

        <tr>
            <th><label for="wpcom-blog-uri"><?php esc_html_e('Blog URL', 'koko-analytics'); ?></label></th>
            <td>
                <input id="wpcom-blog-uri" type="text" class="regular-text" name="wpcom-blog-uri" value="<?php echo esc_attr(get_site_url()); ?>" required>
                <p class="description"><?php esc_html_e('The full URL to the root directory of your blog. Including the full path.', 'koko-analytics'); ?></p>
            </td>
        </tr>

        <tr>
            <th><label for="date-start"><?php esc_html_e('Start date', 'koko-analytics'); ?></label></th>
            <td>
                <input id="date-start" name="date-start" type="date" value="<?php echo esc_attr(date('Y-m-d', strtotime('-1 year'))); ?>" required>
                <p class="description"><?php esc_html_e('The earliest date for which to import data. You should probably set this to the date that you installed and activated Jetpack Stats.', 'koko-analytics'); ?></p>

            </td>
        </tr>

        <tr>
            <th><label for="date-end"><?php esc_html_e('End date', 'koko-analytics'); ?></label></th>
            <td>
                <input id="date-end" name="date-end" type="date" value="<?php echo esc_attr(date('Y-m-d')); ?>" required>
                <p class="description"><?php esc_html_e('The last date for which to import data. You should probably set this to just before the date that you installed and activated Koko Analytics.', 'koko-analytics'); ?></p>

            </td>
        </tr>

        <tr>
            <th><label for="chunk-size"><?php esc_html_e('Chunk size', 'koko-analytics'); ?></label></th>
            <td>
                <input id="chunk-size" name="chunk-size" type="number" value="30" min="1" max="90" required>
                <p class="description"><?php esc_html_e('The number of days to pull in at once. If your website has a lot of different posts or pages, it may be worth setting this to a lower value.', 'koko-analytics'); ?></p>

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

<div class="ka-margin-m">
    <h3><?php esc_html_e('Things to know before running the import', 'koko-analytics'); ?></h3>
    <p><?php esc_html_e('JetPack doesn\'t provide data for the distinct number of visitors. The plugin can only import the total number of pageviews for each post.', 'koko-analytics'); ?></p>
</div>
