<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics\Import;

use DateTimeImmutable;
use Exception;
use KokoAnalytics\Normalizers\Path;
use KokoAnalytics\Normalizers\Referrer;

class WP_Statistics_Importer extends Importer
{
    private const CHUNK_SIZE = 30;
    private const INSERT_BATCH_SIZE = 100;

    protected function get_admin_url(): string
    {
        return admin_url('options-general.php?page=koko-analytics-settings&tab=wp_statistics_importer');
    }

    /**
     * @return array{start: string, end: string}|null
     */
    public function get_available_date_range(): ?array
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        if (!$this->source_tables_exist()) {
            return null;
        }

        $range = $wpdb->get_row("SELECT MIN(date) AS start, MAX(date) AS end FROM (SELECT date FROM {$wpdb->prefix}statistics_summary_totals UNION ALL SELECT last_counter AS date FROM {$wpdb->prefix}statistics_visitor) statistics_dates");
        if (!$range || !$range->start || !$range->end) {
            return null;
        }

        return [
            'start' => $range->start,
            'end' => $range->end,
        ];
    }

    public function start_import(): void
    {
        if (!current_user_can('manage_koko_analytics') || !check_admin_referer('koko_analytics_start_wp_statistics_import')) {
            return;
        }

        if (!$this->source_tables_exist()) {
            $this->redirect_with_error($this->get_admin_url(), __('Could not find the WP Statistics database tables.', 'koko-analytics'));
            exit;
        }

        $date_start = trim(wp_unslash($_POST['date-start'] ?? ''));
        $date_end   = trim(wp_unslash($_POST['date-end'] ?? ''));
        if ($date_start === '' || $date_end === '') {
            $this->redirect_with_error($this->get_admin_url(), __('A required field was missing', 'koko-analytics'));
            exit;
        }

        try {
            $date_start = new DateTimeImmutable($date_start, wp_timezone());
            $date_end   = new DateTimeImmutable($date_end, wp_timezone());
            if ($date_end < $date_start) {
                throw new Exception('End date must be after start date');
            }
        } catch (Exception $e) {
            $this->redirect_with_error($this->get_admin_url(), __('Invalid date fields', 'koko-analytics'));
            exit;
        }

        $this->redirect($this->get_admin_url(), [
            'koko_analytics_action' => 'wp_statistics_import_chunk',
            'date-start' => $date_start->format('Y-m-d'),
            'date-end' => $date_end->format('Y-m-d'),
            '_wpnonce' => wp_create_nonce('koko_analytics_wp_statistics_import_chunk'),
        ]);
    }

    public function import_chunk(): void
    {
        if (!current_user_can('manage_koko_analytics')) {
            return;
        }

        check_admin_referer('koko_analytics_wp_statistics_import_chunk');

        try {
            $date_start_value = trim(wp_unslash($_GET['date-start'] ?? ''));
            $date_end_value   = trim(wp_unslash($_GET['date-end'] ?? ''));
            if ($date_start_value === '' || $date_end_value === '') {
                throw new Exception('Missing date fields');
            }

            $date_start = new DateTimeImmutable($date_start_value, wp_timezone());
            $date_end   = new DateTimeImmutable($date_end_value, wp_timezone());
            if ($date_end < $date_start) {
                throw new Exception('End date must be after start date');
            }

            $chunk_end = $date_start->modify('+' . (self::CHUNK_SIZE - 1) . ' days');
            if ($chunk_end > $date_end) {
                $chunk_end = $date_end;
            }

            $this->perform_chunk_import($date_start, $chunk_end);
        } catch (Exception $e) {
            $this->redirect_with_error($this->get_admin_url(), $e->getMessage());
            exit;
        }

        $next_date_start = $chunk_end->modify('+1 day');
        if ($next_date_start > $date_end) {
            $this->redirect($this->get_admin_url(), ['success' => 1]);
            exit;
        }

        $url = add_query_arg([
            'koko_analytics_action' => 'wp_statistics_import_chunk',
            'date-start' => $next_date_start->format('Y-m-d'),
            'date-end' => $date_end->format('Y-m-d'),
            '_wpnonce' => wp_create_nonce('koko_analytics_wp_statistics_import_chunk'),
        ]);

        $days_left   = $next_date_start->diff($date_end)->days + 1;
        $chunks_left = (int) ceil($days_left / self::CHUNK_SIZE);
        ?>
        <style>
            body {
                background: #f0f0f1;
                color: #3c434a;
                font-family: sans-serif;
                font-size: 16px;
                line-height: 1.5;
                padding: 32px;
            }
        </style>
        <meta http-equiv="refresh" content="1; url=<?php echo esc_attr($url); ?>">
        <h1><?php esc_html_e('Liberating your data... Please wait.', 'koko-analytics'); ?></h1>
        <p>
            <?php
            echo wp_kses(sprintf(
                /* translators: 1: import start date, 2: import end date. */
                __('Imported stats between %1$s and %2$s.', 'koko-analytics'),
                '<strong>' . esc_html($date_start->format('Y-m-d')) . '</strong>',
                '<strong>' . esc_html($chunk_end->format('Y-m-d')) . '</strong>'
            ), ['strong' => []]);
            ?>
        </p>
        <p><?php esc_html_e('Please do not close this browser tab while the importer is running.', 'koko-analytics'); ?></p>
        <?php /* translators: %s: estimated number of seconds remaining. */ ?>
        <p><?php printf(esc_html__('Estimated time left: %s seconds.', 'koko-analytics'), esc_html((string) round($chunks_left * 1.5))); ?></p>
        <?php
        exit;
    }

    public function perform_chunk_import(DateTimeImmutable $date_start, DateTimeImmutable $date_end): void
    {
        @set_time_limit(90);

        /** @var \wpdb $wpdb */
        global $wpdb;

        if (!$this->source_tables_exist()) {
            throw new Exception(esc_html__('Could not find the WP Statistics database tables.', 'koko-analytics'));
        }

        $date_range = [$date_start->format('Y-m-d'), $date_end->format('Y-m-d')];
        $site_data  = $wpdb->get_results($wpdb->prepare(
            "SELECT date, visitors, views AS pageviews FROM {$wpdb->prefix}statistics_summary_totals WHERE date >= %s AND date <= %s",
            $date_range
        ));
        $this->throw_if_database_error();

        $site_stats = [];
        foreach ($site_data as $row) {
            $site_stats[$row->date] = [$row->date, (int) $row->visitors, (int) $row->pageviews];
        }

        // The summary table excludes today and may not have been backfilled yet.
        $fallback_site_data = $wpdb->get_results($wpdb->prepare(
            "SELECT last_counter AS date, COUNT(ID) AS visitors, SUM(hits) AS pageviews FROM {$wpdb->prefix}statistics_visitor WHERE last_counter >= %s AND last_counter <= %s GROUP BY last_counter",
            $date_range
        ));
        $this->throw_if_database_error();

        foreach ($fallback_site_data as $row) {
            $site_stats[$row->date] ??= [$row->date, (int) $row->visitors, (int) $row->pageviews];
        }
        ksort($site_stats);
        $this->bulk_insert_site_stats(array_values($site_stats));

        $page_query_params = array_merge([$wpdb->esc_like('post_type_') . '%'], $date_range);
        $page_data         = $wpdb->get_results($wpdb->prepare(
            "SELECT p.date, p.uri, CASE WHEN p.type IN ('post', 'page', 'product', 'attachment', 'home') OR p.type LIKE %s THEN p.id ELSE 0 END AS post_id, p.count AS pageviews, COUNT(DISTINCT vr.visitor_id) AS visitors FROM {$wpdb->prefix}statistics_pages p LEFT JOIN {$wpdb->prefix}statistics_visitor_relationships vr ON vr.page_id = p.page_id AND DATE(vr.date) = p.date WHERE p.date >= %s AND p.date <= %s GROUP BY p.page_id",
            $page_query_params
        ));
        $this->throw_if_database_error();

        $page_stats = [];
        foreach ($page_data as $row) {
            $page_stats[] = [$row->date, Path::normalize($row->uri), (int) $row->post_id, (int) $row->visitors, (int) $row->pageviews];
            if (count($page_stats) >= self::INSERT_BATCH_SIZE) {
                $this->bulk_insert_page_stats($page_stats);
                $page_stats = [];
            }
        }
        $this->bulk_insert_page_stats($page_stats);

        $referrer_data = $wpdb->get_results($wpdb->prepare(
            "SELECT last_counter AS date, referred AS referrer, COUNT(ID) AS visitors, SUM(hits) AS pageviews FROM {$wpdb->prefix}statistics_visitor WHERE last_counter >= %s AND last_counter <= %s AND referred != '' GROUP BY last_counter, referred",
            $date_range
        ));
        $this->throw_if_database_error();

        $referrer_stats = [];
        foreach ($referrer_data as $row) {
            $referrer = Referrer::normalize($row->referrer);
            if ($referrer === '') {
                continue;
            }

            $referrer_stats[] = [$row->date, $referrer, (int) $row->visitors, (int) $row->pageviews];
            if (count($referrer_stats) >= self::INSERT_BATCH_SIZE) {
                $this->bulk_insert_referrer_stats($referrer_stats);
                $referrer_stats = [];
            }
        }
        $this->bulk_insert_referrer_stats($referrer_stats);
    }

    private function source_tables_exist(): bool
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        $tables = ['summary_totals', 'visitor', 'pages', 'visitor_relationships'];
        foreach ($tables as $suffix) {
            $table = $wpdb->prefix . 'statistics_' . $suffix;
            if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table))) !== $table) {
                return false;
            }
        }

        return true;
    }

    private function throw_if_database_error(): void
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        if ($wpdb->last_error !== '') {
            throw new Exception(esc_html__("A database error occurred: ", 'koko-analytics') . esc_html(" {$wpdb->last_error}"));
        }
    }
}
