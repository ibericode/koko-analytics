<?php

use KokoAnalytics\Endpoint_Installer;

$endpoint_installer = new Endpoint_Installer();
?>

<h2 class="mt-0 mb-3"><?= esc_html__('Performance', 'koko-analytics') ?></h2>

<div class="mb-5">
    <?php if ($using_custom_endpoint) { ?>
        <p><span style="color: green;">✓</span> <?php esc_html_e('The plugin is currently using an optimized tracking endpoint. Great!', 'koko-analytics'); ?></p>
    <?php } else { ?>
        <p><?php esc_html_e('The plugin is currently not using an optimized tracking endpoint.', 'koko-analytics'); ?></p>
        <form method="POST" action="">
            <?php wp_nonce_field('koko_analytics_install_optimized_endpoint'); ?>
            <?php wp_referer_field(); ?>
            <input type="hidden" name="koko_analytics_action" value="install_optimized_endpoint">
            <input type="submit" value="<?php esc_attr_e('Create optimized endpoint file', 'koko-analytics'); ?>" class="btn btn-secondary btn-sm">
        </form>
        <p><?php printf(esc_html__('To use one, create the file %s with the following file contents: ', 'koko-analytics'), '<code>' . $endpoint_installer->get_file_name() . '</code>'); ?></p>
        <textarea readonly="readonly" class="ka-input font-monospace" rows="18" onfocus="this.select();" spellcheck="false"><?php echo esc_html($endpoint_installer->get_file_contents()); ?></textarea>
        <p><?php esc_html_e('Please note that this is entirely optional and only recommended for high-traffic websites.', 'koko-analytics'); ?></p>
    <?php } ?>
</div>
