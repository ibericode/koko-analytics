<form method="POST" action="">
    <?php wp_nonce_field('koko_analytics_save_settings'); ?>
    <?php wp_referer_field(); ?>
    <input type="hidden" name="koko_analytics_action" value="save_settings">

    <?php do_action('koko_analytics_output_after_email_reports_settings', $settings); ?>
</form>

<?php if (! has_action('koko_analytics_output_after_email_reports_settings')) : ?>
    <h2 class="mt-0 mb-3"><?= esc_html__('Email reports', 'koko-analytics') ?></h2>

    <p>Email reports is a feature from Koko Analytics Pro that allows you to configure periodic email reports, delivering a summary of your most important metrics to your email inbox.</p>
    <p><a href="https://www.kokoanalytics.com/pricing/">Upgrade to Koko Analytics Pro</a></p>
<?php endif; ?>
