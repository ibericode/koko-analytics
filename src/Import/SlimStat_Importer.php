<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics\Import;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use KokoAnalytics\Normalizers\Path;
use KokoAnalytics\Normalizers\Referrer;

class SlimStat_Importer extends Importer
{
    private const CHUNK_SIZE = 30;

    protected function get_admin_url(): string
    {
        return admin_url('options-general.php?page=koko-analytics-settings&tab=slimstat_importer');
    }

    /**
     * @return array{start: string, end: string}|null
     */
    public function get_available_date_range(): ?array
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        $tables = $this->get_source_tables();
        if (count($tables) === 0) {
            return null;
        }

        $source = $this->build_source_query($tables);
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $range = $wpdb->get_row("SELECT MIN(dt) AS start, MAX(dt) AS end FROM ({$source}) slimstat_stats");
        if (!$range || !$range->start || !$range->end) {
            return null;
        }

        return [
            'start' => gmdate('Y-m-d', (int) $range->start),
            'end' => gmdate('Y-m-d', (int) $range->end),
        ];
    }

    public function start_import(): void
    {
        if (!current_user_can('manage_koko_analytics') || !check_admin_referer('koko_analytics_start_slimstat_import')) {
            return;
        }

        if (count($this->get_source_tables()) === 0) {
            $this->redirect_with_error($this->get_admin_url(), __('Could not find the SlimStat Analytics database table.', 'koko-analytics'));
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
            'koko_analytics_action' => 'slimstat_import_chunk',
            'date-start' => $date_start->format('Y-m-d'),
            'date-end' => $date_end->format('Y-m-d'),
            '_wpnonce' => wp_create_nonce('koko_analytics_slimstat_import_chunk'),
        ]);
    }

    public function import_chunk(): void
    {
        if (!current_user_can('manage_koko_analytics')) {
            return;
        }

        check_admin_referer('koko_analytics_slimstat_import_chunk');

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
            'koko_analytics_action' => 'slimstat_import_chunk',
            'date-start' => $next_date_start->format('Y-m-d'),
            'date-end' => $date_end->format('Y-m-d'),
            '_wpnonce' => wp_create_nonce('koko_analytics_slimstat_import_chunk'),
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

        $tables = $this->get_source_tables();
        if (count($tables) === 0) {
            throw new Exception(esc_html__('Could not find the SlimStat Analytics database table.', 'koko-analytics'));
        }

        $source     = $this->build_source_query($tables);
        $site_stats = [];
        $utc        = new DateTimeZone('UTC');
        $date       = new DateTimeImmutable($date_start->format('Y-m-d'), $utc);
        $last_date  = new DateTimeImmutable($date_end->format('Y-m-d'), $utc);

        while ($date <= $last_date) {
            $next_date = $date->modify('+1 day');
            $range     = [$date->getTimestamp(), $next_date->getTimestamp()];
            $date_key  = $date->format('Y-m-d');

            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $site = $wpdb->get_row($wpdb->prepare("SELECT COUNT(*) AS pageviews, COUNT(DISTINCT NULLIF(ip, '')) AS visitors FROM ({$source}) slimstat_stats WHERE dt >= %d AND dt < %d AND visit_id > 0 AND COALESCE(browser_type, 0) <> 1", $range));
            $this->throw_if_database_error();

            if ($site && (int) $site->pageviews > 0) {
                $site_stats[] = [$date_key, (int) $site->visitors, (int) $site->pageviews];
            }

            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $pages = $wpdb->get_results($wpdb->prepare("SELECT resource, MAX(CASE WHEN content_type IN ('post', 'page') OR LEFT(content_type, 4) = 'cpt:' THEN content_id ELSE 0 END) AS post_id, COUNT(*) AS pageviews, COUNT(DISTINCT NULLIF(ip, '')) AS visitors FROM ({$source}) slimstat_stats WHERE dt >= %d AND dt < %d AND visit_id > 0 AND COALESCE(browser_type, 0) <> 1 AND resource IS NOT NULL AND resource != '' GROUP BY resource", $range));
            $this->throw_if_database_error();

            $page_stats = [];
            foreach ($pages as $page) {
                $page_stats[] = [$date_key, Path::normalize($page->resource), (int) $page->post_id, (int) $page->visitors, (int) $page->pageviews];
            }
            $this->bulk_insert_page_stats($page_stats);

            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $referrers = $wpdb->get_results($wpdb->prepare("SELECT referer, COUNT(*) AS pageviews, COUNT(DISTINCT NULLIF(ip, '')) AS visitors FROM ({$source}) slimstat_stats WHERE dt >= %d AND dt < %d AND visit_id > 0 AND COALESCE(browser_type, 0) <> 1 AND referer IS NOT NULL AND referer != '' GROUP BY referer", $range));
            $this->throw_if_database_error();

            $referrer_stats = [];
            foreach ($referrers as $referrer) {
                $value = Referrer::normalize($referrer->referer);
                if ($value !== '') {
                    $referrer_stats[] = [$date_key, $value, (int) $referrer->visitors, (int) $referrer->pageviews];
                }
            }
            $this->bulk_insert_referrer_stats($referrer_stats);

            $date = $next_date;
        }

        $this->bulk_insert_site_stats($site_stats);
    }

    /**
     * @return array<string>
     */
    private function get_source_tables(): array
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        $tables = [];
        foreach (['slim_stats', 'slim_stats_archive'] as $suffix) {
            $table = $wpdb->prefix . $suffix;
            if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table))) === $table) {
                $tables[] = $table;
            }
        }

        return $tables;
    }

    /**
     * @param array<string> $tables
     */
    private function build_source_query(array $tables): string
    {
        return implode(' UNION ALL ', array_map(function (string $table): string {
            return "SELECT dt, ip, referer, resource, visit_id, browser_type, content_type, content_id FROM {$table}";
        }, $tables));
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
