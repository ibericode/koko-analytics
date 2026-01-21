<h2 class="mt-0 mb-3"><?= esc_html__('Dashboard settings', 'koko-analytics') ?></h2>
<form method="POST" action="">

    <input type="hidden" name="koko_analytics_action" value="save_settings">
    <?php wp_nonce_field('koko_analytics_save_settings'); ?>
    <?php wp_referer_field(); ?>

    <div class="mb-4">
        <fieldset class="mb-2">
            <legend class="ka-label"><?php esc_html_e('Should your dashboard be publicly accessible?', 'koko-analytics'); ?></legend>
            <label class="me-1"><input type="radio" name="koko_analytics_settings[is_dashboard_public]" value="1" <?php checked($settings['is_dashboard_public'], 1); ?>><?php esc_html_e('Yes', 'koko-analytics'); ?></label>
            <label class=""><input type="radio" name="koko_analytics_settings[is_dashboard_public]" value="0" <?php checked($settings['is_dashboard_public'], 0); ?>> <?php esc_html_e('No', 'koko-analytics'); ?></label>
        </fieldset>
        <p class="description">
            <?php echo wp_kses(sprintf(__('Set to "yes" if you want your dashboard to be publicly accessible. With this setting enabled, you can <a href="%s">find your public dashboard here</a>.', 'koko-analytics'), esc_attr($public_dashboard_url)), ['a' => ['href' => []]]); ?>
        </p>
    </div>

    <div class="mb-4">
        <label for="ka-default-date-period" class="ka-label"><?php esc_html_e('Default date period', 'koko-analytics'); ?></label>
        <select class="ka-select mb-2" id="ka-default-date-period" name="koko_analytics_settings[default_view]">
            <?php
            foreach ($date_presets as $key => $label) {
                $selected = ($key === $settings['default_view'] ? 'selected' : '');
                echo "<option value=\"{$key}\" {$selected}>{$label}</option>";
            }
            ?>
        </select>
        <p class="description"><?php esc_html_e('The default date period to show when opening the analytics dashboard.', 'koko-analytics'); ?></p>
    </div>

    <?php do_action('koko_analytics_output_after_dashboard_settings', $settings); ?>

    <div>
        <input type="submit" class="btn btn-primary" value="<?= esc_attr__('Save Changes') ?>">
    </div>
</form>
