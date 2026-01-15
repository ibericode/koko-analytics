<?php

do_action("koko_analytics_output_settings_tab_modules", $settings);

if (! has_action('koko_analytics_output_settings_tab_modules')) {
    $available_modules = [
        [ 'id' => 'Geolocation', 'title' => __('Geolocation', 'koko-analytics'), 'description' => 'See exactly what countries your site is visited from.'],
        [ 'id' => 'Events', 'title' => __('Events', 'koko-analytics'), 'description' => 'Track outbound links, form submissions or any type of custom event.'],
        [ 'id' => 'Devices', 'title' => __('Devices', 'koko-analytics'), 'description' => 'See exactly what browsers, operating systems and devices your site is visited from.'],
        [ 'id' => 'Emails', 'title' => __('Email Reports', 'koko-analytics'), 'description' => 'Daily, weekly or monthly email reports.'],
        [ 'id' => 'CSV', 'title' => __('CSV Export', 'koko-analytics'), 'description' => 'Export dashboard to CSV.'],
        [ 'id' => 'Column', 'title' => __('Post Table Column', 'koko-analytics'), 'description' => 'Shows pageviews counts in your posts table.'],
        [ 'id' => 'Toolbar', 'title' => __('WP Toolbar', 'koko-analytics'), 'description' => 'Shows a pageview chart in your toolbar.'],
    ];

    ?>
    <h2 class="mt-0 mb-3">Modules</h2>
    <p><a href="https://www.kokoanalytics.com/pricing/">Purchase Koko Analytics Pro</a> to use the following modules.</p>
    <div class="mb-5">
        <?php foreach ($available_modules as $m) : ?>
        <div class="form-check form-switch mb-3">
          <input disabled name="koko_analytics_settings[enabled_modules][]" class="form-check-input" type="checkbox" role="switch" id="input-module-<?= $m['id'] ?>">
          <label class="form-check-label" for="input-module-<?= $m['id'] ?>">
              <strong><?= $m['title'] ?></strong><br />
              <?= esc_html($m['description']); ?>
          </label>
        </div>
        <?php endforeach; ?>
    </div>
    <?php
}
