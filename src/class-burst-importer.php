<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

use WP_Error;
use Exception;
use DateTimeImmutable;

class Burst_Importer
{
    public static function show_page(): void
    {
        if (!current_user_can('manage_koko_analytics')) {
            return;
        }

         // grab start date
        global $wpdb;
        $date_start = $wpdb->get_var("SELECT MIN(date) FROM {$wpdb->prefix}burst_summary;");
        $date_end = $wpdb->get_var("SELECT MAX(date) FROM {$wpdb->prefix}burst_summary");

        ?>
        <div class="wrap" style="max-width: 820px;">

        <?php if (isset($_GET['error'])) { ?>
                <div class="notice notice-error is-dismissible">
                    <p>
                        <?php esc_html_e('An error occurred trying to import your statistics.', 'koko-analytics'); ?>
                        <?php echo ' '; ?>
                        <?php echo esc_html($_GET['error']); ?>
                    </p>
                </div>
        <?php } ?>

        <?php if (isset($_GET['success']) && $_GET['success'] == 1) { ?>
                <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Big success! Your stats are now imported into Koko Analytics.', 'koko-analytics'); ?></p></div>
        <?php } ?>

            <h1><?php esc_html_e('Import from Burst Statistics', 'koko-analytics'); ?></h1>
            <p><?php esc_html_e('Use the button below to start importing your historical statistics data from Burst Statistics into Koko Analytics.', 'koko-analytics'); ?></p>

            <form method="post" onsubmit="return confirm('<?php esc_attr_e('Are you sure you want to import statistics between', 'koko-analytics'); ?> ' + this['date-start'].value + '<?php esc_attr_e(' and ', 'koko-analytics'); ?>' + this['date-end'].value + '<?php esc_attr_e('? This will overwrite any existing data in your Koko Analytics database tables.', 'koko-analytics'); ?>');" action="<?php echo esc_url(admin_url('index.php?page=koko-analytics&tab=burst_importer')); ?>">
                <input type="hidden" name="koko_analytics_action" value="start_burst_import">
                <?php wp_nonce_field('koko_analytics_start_burst_import'); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="date-start"><?php esc_html_e('Start date', 'koko-analytics'); ?></label></th>
                        <td>
                            <input id="date-start" name="date-start" type="date" value="<?php echo esc_attr($date_start); ?>" required>

                        </td>
                    </tr>
                    <tr>
                        <th><label for="date-end"><?php esc_html_e('End date', 'koko-analytics'); ?></label></th>
                        <td>
                            <input id="date-end" name="date-end" type="date" value="<?php echo esc_attr($date_end); ?>" required>

                        </td>
                    </tr>
                </table>

                <p style="color: indianred;">
                    <strong><?php esc_html_e('Warning: ', 'koko-analytics'); ?></strong>
                    <?php esc_html_e('Importing data for a given date range will add to any existing data. The import process can not be reverted unless you reinstate a back-up of your database in its current state.', 'koko-analytics'); ?>
                </p>

                <p>
                    <button type="submit" class="button button-primary"><?php esc_html_e('Import analytics data', 'koko-analytics'); ?></button>
                </p>
            </form>
        </div>
        <?php
    }

    private static function redirect_with_error(string $redirect_url, string $error_message): void
    {
        $redirect_url = add_query_arg([ 'error' => urlencode($error_message)], $redirect_url);
        wp_safe_redirect($redirect_url);
        exit;
    }

    public static function start_import(): void
    {
        // authorize user
        if (!current_user_can('manage_koko_analytics')) {
            return;
        }

        // verify nonce
        check_admin_referer('koko_analytics_start_burst_import');

        $date_start = trim($_POST['date-start']);
        $date_end = trim($_POST['date-end']);
        if ($date_start === '' || $date_end === '') {
            self::redirect_with_error(admin_url('/index.php?page=koko-analytics&tab=burst_importer'), __('A required field was missing', 'koko-analytics'));
            exit;
        }

        // first chunk is 30 days after date-start
        try {
            $date_start = new DateTimeImmutable($date_start);
            $date_end = new DateTimeImmutable($date_end);
            if ($date_end < $date_start) {
                throw new Exception("End date must be after start date");
            }
        } catch (Exception $e) {
            self::redirect_with_error(admin_url('/index.php?page=koko-analytics&tab=burst_importer'), __('Invalid date fields', 'koko-analytics'));
            exit;
        }

        // redirect to first chunk
        wp_safe_redirect(add_query_arg(['koko_analytics_action' => 'burst_import_chunk', 'date-start' => $date_start->format('Y-m-d'), 'date-end' => $date_end->format('Y-m-d'), '_wpnonce' => wp_create_nonce('koko_analytics_burst_import_chunk')]));
        exit;
    }

    public static function import_chunk(): void
    {
        // authorize
        if (!current_user_can('manage_koko_analytics')) {
            return;
        }

        // verify nonce
        check_admin_referer('koko_analytics_burst_import_chunk');

        $date_start = new \DateTimeImmutable(trim($_GET['date-start']));
        $date_end = new \DateTimeImmutable(trim($_GET['date-end']));
        $chunk_end = $date_start->modify('+30 days');
        if ($chunk_end > $date_end) {
            $chunk_end = $date_end;
        }

        // import this chunk
        try {
            self::perform_chunk_import($date_start, $chunk_end);
        } catch (Exception $e) {
            // redirect to form page
            self::redirect_with_error(admin_url('/index.php?page=koko-analytics&tab=burst_importer'), $e->getMessage());
            exit;
        }

        // If we're done, redirect to success page
        $next_date_start = $chunk_end->modify('+1 day');
        $done = $next_date_start > $date_end;
        if ($done) {
            wp_safe_redirect(get_admin_url(null, '/index.php?page=koko-analytics&tab=burst_importer&success=1'));
            exit;
        }

        $url = add_query_arg(['koko_analytics_action' => 'burst_import_chunk', 'date-start' => $next_date_start->format('Y-m-d'), 'date-end' => $date_end->format('Y-m-d'), '_wpnonce' => wp_create_nonce('koko_analytics_burst_import_chunk')]);

        $chunks_left = max(1, $date_end->diff($next_date_start)->days / 30);

        // we could do a wp_safe_redirect() here
        // but instead we send some HTML to the client and perform a client-side redirect just so the user knows we're still alive and working
        ?>
        <style>body { background: #f0f0f1; color: #3c434a; font-family: sans-serif; font-size: 16px; line-height: 1.5; padding: 32px; }</style>
        <meta http-equiv="refresh" content="1; url=<?php echo esc_attr($url); ?>">
        <h1><?php esc_html_e('Liberating your data... Please wait.', 'koko-analytics'); ?></h1>
        <p>
        <?php esc_html_e('Importing stats, please wait...', 'koko-analytics'); ?>
        </p>
        <p><?php esc_html_e('Please do not close this browser tab while the importer is running.', 'koko-analytics'); ?></p>
        <p><?php printf(__('Estimated time left: %s seconds.', 'koko-analytics'), round($chunks_left * 1.5)); ?></p>
            <?php
            exit;
    }

    public static function perform_chunk_import(\DateTimeImmutable $date_start, \DateTimeImmutable $date_end): void
    {
        @set_time_limit(90);

        /** @var wpdb $wpdb */
        global $wpdb;

        // TODO: Limit to slugs from query set?
        $urls_to_id = $wpdb->get_results("SELECT post_name, ID FROM {$wpdb->posts} WHERE post_status = 'publish'", OBJECT_K);

        $data = $wpdb->get_results($wpdb->prepare("SELECT date, visitors, pageviews, page_url, SUBSTRING_INDEX(SUBSTRING_INDEX(page_url, '/', -2), '/', 1) AS post_name FROM {$wpdb->prefix}burst_summary s WHERE s.date >= %s AND s.date <= %s", [
            $date_start->format('Y-m-d'),
            $date_end->format('Y-m-d')
        ]));

        $site_stats = [];
        $post_stats = [];

        foreach ($data as $item) {
            if ($item->page_url === 'burst_day_total') {
                $site_stats[] = [$item->date, $item->visitors, $item->pageviews];
            } else {
                $matches = [];
                if ($item->page_url === '/') {
                    $post_id = 0;
                } elseif (preg_match('/\?p=(\d+)/', $item->page_url, $matches)) {
                    // grab from /?p=<id> URL (when using simple permalink structure)
                    $post_id = $matches[1];
                } elseif (isset($urls_to_id[$item->post_name])) {
                    // grab from last URL part (when using named permalink structure)
                    $post_id = $urls_to_id[$item->post_name]->ID;
                } else {
                    // if not a page, post, custom post type or the homepage, skip
                    continue;
                }
                $post_stats[] = [$item->date, $post_id, $item->visitors, $item->pageviews];
            }
        }

        self::insert_site_stats($site_stats);
        self::insert_post_stats($post_stats);
    }

    public static function insert_site_stats($stats): void
    {
        global $wpdb;
        if (count($stats) === 0) {
            return;
        }

        // update site stats
        $values = [];
        $placeholders = rtrim(str_repeat('(%s,%d,%d),', count($stats)), ',');
        foreach ($stats as $s) {
            array_push($values, ...$s);
        }

        $query = $wpdb->prepare("INSERT INTO {$wpdb->prefix}koko_analytics_site_stats(date, visitors, pageviews) VALUES {$placeholders} ON DUPLICATE KEY UPDATE visitors = visitors + VALUES(visitors), pageviews = pageviews + VALUES(pageviews)", $values);
        $wpdb->query($query);
        if ($wpdb->last_error !== '') {
            throw new Exception(__("A database error occurred: ", 'koko-analytics') . " {$wpdb->last_error}");
        }
    }

    public static function insert_post_stats($stats): void
    {
        global $wpdb;
        if (count($stats) === 0) {
            return;
        }

        $placeholders = rtrim(str_repeat('(%s,%d,%d,%d),', count($stats)), ',');
        $values = [];
        foreach ($stats as $s) {
            array_push($values, ...$s);
        }

        $query = $wpdb->prepare("INSERT INTO {$wpdb->prefix}koko_analytics_post_stats(date, id, visitors, pageviews) VALUES {$placeholders} ON DUPLICATE KEY UPDATE visitors = visitors + VALUES(visitors), pageviews = pageviews + VALUES(pageviews)", $values);
        $wpdb->query($query);
        if ($wpdb->last_error !== '') {
            throw new Exception(__("A database error occurred: ", 'koko-analytics') . " {$wpdb->last_error}");
        }
    }
}
