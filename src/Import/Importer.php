<?php

namespace KokoAnalytics\Import;

abstract class Importer
{
    abstract protected static function show_page_content();

    public static function show_page()
    {
        if (!current_user_can('manage_koko_analytics')) {
            return;
        }

        ?>
        <div class="wrap" style="max-width: 760px;">
            <?php if (isset($_GET['error'])) { ?>
                    <div class="notice notice-error is-dismissible">
                        <p>
                            <?php esc_html_e('An error occurred trying to import your statistics.', 'koko-analytics'); ?>
                            <?php echo ' '; ?>
                            <?php echo wp_kses(stripslashes(trim($_GET['error'])), [ 'br' => []]); ?>
                        </p>
                    </div>
            <?php } ?>
            <?php if (isset($_GET['success']) && $_GET['success'] == 1) { ?>
                    <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Big success! Your stats are now imported into Koko Analytics.', 'koko-analytics'); ?></p></div>
            <?php } ?>

            <?php static::show_page_content(); ?>
        </div>
        <?php
    }

    protected static function redirect_with_error(string $redirect_url, string $error_message): void
    {
        $redirect_url = add_query_arg([ 'error' => urlencode($error_message)], $redirect_url);
        wp_safe_redirect($redirect_url);
        exit;
    }
}
