<?php do_action('koko_analytics_output_settings_tab_emails', $settings); ?>

<?php if (! has_action('koko_analytics_output_settings_tab_emails')) : ?>
    <h2 class="mt-0 mb-3"><?= esc_html__('Email reports', 'koko-analytics') ?></h2>

    <p>Email reports is a feature from Koko Analytics Pro that allows you to configure periodic email reports, delivering a summary of your most important metrics to your email inbox.</p>
    <p><a href="https://www.kokoanalytics.com/pricing/">Upgrade to Koko Analytics Pro</a></p>
<?php endif; ?>
