<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

use wpdb;

class Stats
{
    protected wpdb $db;

    /**
     * @param \wpdb|null $db Optional database connection, mainly for testing purposes. Defaults to global $wpdb instance.
     */
    public function __construct(?wpdb $db = null)
    {
        $this->db = $db ?? $GLOBALS['wpdb'];
    }

    public function get_total_date_range(): array
    {
        $result = $this->db->get_row("select MIN(date) AS start, MAX(date) AS end FROM {$this->db->prefix}koko_analytics_site_stats WHERE date IS NOT NULL;");
        if (!$result) {
            $today = new \DateTimeImmutable('now', wp_timezone());
            return [$today, $today];
        }

        return [new \DateTimeImmutable($result->start ?? '-28 days', wp_timezone()), new \DateTimeImmutable($result->end ?? 'now', wp_timezone())];
    }

    /**
     * @return object{ visitors: int, pageviews: int }
     */
    public function get_totals(string $start_date, string $end_date, $page = 0, $unused = null): object
    {
        $from = "{$this->db->prefix}koko_analytics_site_stats s";
        $where = 's.date >= %s AND s.date <= %s';
        $args = [$start_date, $end_date];

        if ($page) {
            $from = "{$this->db->prefix}koko_analytics_post_stats s LEFT JOIN {$this->db->prefix}koko_analytics_paths p ON p.id = s.path_id";
            $where .= ' AND p.path = %s';
            $args[] = $page;
        }

        $result = $this->db->get_row($this->db->prepare("
            SELECT COALESCE(SUM(visitors), 0) AS visitors, COALESCE(SUM(pageviews), 0) AS pageviews
            FROM {$from}
            WHERE {$where}
            ", $args));

        // ensure we always return a valid object containing the keys we need
        if (!$result) {
            return (object) [
                'pageviews' => 0,
                'visitors' => 0,
            ];
        }

        // sometimes there are pageviews, but no counted visitors
        // this happens when the cookie was valid over a period of 2 calendar days
        // we can make this less obviously wrong by always specifying there was at least 1 visitors
        // whenever we have any pageviews
        if ($result->visitors == 0 && $result->pageviews > 0) {
            $result->visitors = 1;
        }

        return $result;
    }

    public function generate_date_range(string $start_date, string $end_date, string $group = 'day'): array
    {
        $timezone = wp_timezone();
        $start = new \DateTimeImmutable($start_date, $timezone);
        $end = new \DateTimeImmutable($end_date, $timezone);
        $week_starts_on = (int) get_option('start_of_week', 0);

        // align start date to the beginning of the period
        switch ($group) {
            case 'week':
                $day_of_week = (int) $start->format('w');
                $diff = ($day_of_week - $week_starts_on + 7) % 7;
                $start = $start->modify("-{$diff} days");
                break;
            case 'month':
                $start = $start->modify('first day of this month');
                break;
            case 'year':
                $start = $start->modify('first day of january this year');
                break;
        }

        $intervals = [
            'day' => '+1 day',
            'week' => '+1 week',
            'month' => '+1 month',
            'year' => '+1 year',
        ];
        $interval = $intervals[$group];

        $dates = [];
        $current = $start;
        while ($current <= $end) {
            $dates[] = $current->format('Y-m-d');
            $current = $current->modify($interval);
        }

        return $dates;
    }

    /**
     * Get aggregated statistics (per day, week or month) between the two given dates.
     * Without the $page parameter this returns the site-wide statistics.
     *
     * @param string $start_date
     * @param string $end_date
     * @param string $group `day`, `week` or `month`
     * @param string $page
     * @return array
     */
    public function get_stats(string $start_date, string $end_date, string $group = 'day', $page = ''): array
    {
        $week_starts_on = (int) get_option('start_of_week', 0);
        $date_key_expressions = [
            'day' => 's.date',
            'week' => "DATE(DATE_SUB(s.date, INTERVAL MOD(DAYOFWEEK(s.date) - 1 - {$week_starts_on} + 7, 7) DAY))",
            'month' => 'DATE(DATE_SUB(s.date, INTERVAL DAYOFMONTH(s.date) - 1 DAY))',
            'year' => 'MAKEDATE(YEAR(s.date), 1)',
        ];
        $date_key_expr = $date_key_expressions[$group];

        if ($page) {
            // join page-specific stats
            $from = "{$this->db->prefix}koko_analytics_post_stats s JOIN {$this->db->prefix}koko_analytics_paths p ON p.path = %s AND p.id = s.path_id";
            $args = [$page, $start_date, $end_date];
        } else {
            // join site-wide stats
            $from = "{$this->db->prefix}koko_analytics_site_stats s";
            $args = [$start_date, $end_date];
        }

        $rows = array_map(function ($row) {
            $row->pageviews = (int) $row->pageviews;
            $row->visitors  = (int) $row->visitors;
            return $row;
        }, $this->db->get_results($this->db->prepare(
            "SELECT {$date_key_expr} AS `date`, SUM(COALESCE(visitors, 0)) AS visitors, SUM(COALESCE(pageviews, 0)) AS pageviews
                FROM {$from}
                WHERE s.date BETWEEN %s AND %s
                GROUP BY {$date_key_expr}
                ORDER BY {$date_key_expr} ASC",
            $args
        ) ?? []));

        // ensure we have an entry for each date in the range, even if there are no stats for that date
        $stats_by_date = [];
        foreach ($rows as $row) {
            $stats_by_date[$row->date] = $row;
        }

        // fill in missing dates with zeroed stats
        $date_range = $this->generate_date_range($start_date, $end_date, $group);
        $results = [];
        foreach ($date_range as $date) {
            $results[] = $stats_by_date[$date] ?? (object) [
                'date' => $date,
                'visitors' => 0,
                'pageviews' => 0,
            ];
        }

        return $results;
    }

    public function get_posts(string $start_date, string $end_date, int $offset = 0, int $limit = 10): array
    {
        $results = $this->db->get_results($this->db->prepare(
            "SELECT p.path, s.post_id, IFNULL(NULLIF(wp.post_title, ''), p.path) AS label, SUM(visitors) AS visitors, SUM(pageviews) AS pageviews
                FROM {$this->db->prefix}koko_analytics_post_stats s
                JOIN {$this->db->prefix}koko_analytics_paths p ON p.id = s.path_id
                LEFT JOIN {$this->db->prefix}posts wp ON wp.ID = s.post_id
                WHERE s.date BETWEEN %s AND %s
                GROUP BY p.path, s.post_id
                ORDER BY pageviews DESC, visitors DESC, s.path_id ASC
                LIMIT %d, %d",
            [$start_date, $end_date, $offset, $limit]
        ));

        return array_map(function ($row) {
            $row->pageviews = (int) $row->pageviews;
            $row->visitors = max(1, (int) $row->visitors);

            // for backwards compatibility with versions before 2.0
            // set post_title and post_permalink property
            $row->post_permalink = home_url($row->path);
            $row->post_title = $row->label;

            return $row;
        }, $results);
    }

    public function count_posts(string $start_date, string $end_date): int
    {
        return (int) $this->db->get_var($this->db->prepare(
            "
            SELECT COUNT(*)
            FROM (
                SELECT COUNT(*) AS count
                FROM {$this->db->prefix}koko_analytics_post_stats s
                JOIN {$this->db->prefix}koko_analytics_paths p ON p.id = s.path_id
                WHERE s.date BETWEEN %s AND %s
                GROUP BY p.path, s.post_id
            ) AS a",
            [$start_date, $end_date]
        ));
    }

    public function get_referrers(string $start_date, string $end_date, int $offset = 0, int $limit = 10): array
    {
        return array_map(function ($row) {
            $row->pageviews = (int) $row->pageviews;
            $row->visitors = max(1, (int) $row->visitors);
            return $row;
        }, $this->db->get_results($this->db->prepare(
            "SELECT s.id, url, SUM(visitors) As visitors, SUM(pageviews) AS pageviews
                FROM {$this->db->prefix}koko_analytics_referrer_stats s
                JOIN {$this->db->prefix}koko_analytics_referrer_urls r ON r.id = s.id
                WHERE s.date BETWEEN %s AND %s
                GROUP BY s.id
                ORDER BY pageviews DESC, r.id ASC
                LIMIT %d, %d",
            [$start_date, $end_date, $offset, $limit]
        )));
    }

    public function count_referrers(string $start_date, string $end_date): int
    {
        return (int) $this->db->get_var($this->db->prepare(
            "SELECT COUNT(DISTINCT(s.id))
                FROM {$this->db->prefix}koko_analytics_referrer_stats s
                WHERE s.date BETWEEN %s AND %s",
            [$start_date, $end_date]
        ));
    }

    public function sum_referrers(string $start_date, string $end_date): int
    {
        return (int) $this->db->get_var($this->db->prepare(
            "SELECT SUM(s.pageviews)
                FROM {$this->db->prefix}koko_analytics_referrer_stats s
                WHERE s.date BETWEEN %s AND %s",
            [$start_date, $end_date]
        ));
    }
}
