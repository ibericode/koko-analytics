<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

use DateTimeImmutable;
use DateTime;
use DateTimeInterface;
use wpdb;

class Stats
{
    protected wpdb $db;

    /**
     * @param wpdb|null $db Optional database connection, mainly for testing purposes. Defaults to global $wpdb instance.
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

    private function get_post_id_filter($page): int
    {
        if (is_int($page)) {
            return max(0, $page);
        }

        if (is_string($page) && ctype_digit($page)) {
            return (int) $page;
        }

        return 0;
    }

    /**
     *
     * @param DateTimeInterface|string $start_date
     * @param DateTimeInterface|string $end_date
     * @param int|string $page
     * @return object{ visitors: int, pageviews: int }
     */
    public function get_totals($start_date, $end_date, $page = 0, $unused = null): object
    {
        $start_date = $start_date instanceof DateTimeInterface ? $start_date->format("Y-m-d") : $start_date;
        $end_date   = $end_date instanceof DateTimeInterface ? $end_date->format("Y-m-d") : $end_date;
        $from       = "{$this->db->prefix}koko_analytics_site_stats s";
        $where      = 's.date >= %s AND s.date <= %s';
        $args       = [$start_date, $end_date];
        $post_id    = $this->get_post_id_filter($page);

        if ($post_id > 0) {
            $from   = "{$this->db->prefix}koko_analytics_post_stats s";
            $where .= ' AND s.post_id = %d';
            $args[] = $post_id;
        } elseif ($page) {
            $from   = "{$this->db->prefix}koko_analytics_post_stats s LEFT JOIN {$this->db->prefix}koko_analytics_paths p ON p.id = s.path_id";
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

    /**
     * @param DateTimeImmutable|DateTime|string $start_date
     * @param DateTimeImmutable|DateTime|string $end_date
     */
    public function generate_date_range($start_date, $end_date, string $group = 'day'): array
    {
        $start_date     = $start_date instanceof DateTimeInterface ? $start_date : new \DateTimeImmutable($start_date, wp_timezone());
        $end_date       = $end_date instanceof DateTimeInterface ? $end_date : new \DateTimeImmutable($end_date, wp_timezone());
        $week_starts_on = (int) get_option('start_of_week', 0);

        // align start date to the beginning of the period
        switch ($group) {
            case 'week':
                $day_of_week = (int) $start_date->format('w');
                $diff        = ($day_of_week - $week_starts_on + 7) % 7;
                $start_date  = $start_date->modify("-{$diff} days");
                break;
            case 'month':
                $start_date = $start_date->modify('first day of this month');
                break;
            case 'year':
                $start_date = $start_date->modify('first day of january this year');
                break;
        }

        $intervals = [
            'day' => '+1 day',
            'week' => '+1 week',
            'month' => '+1 month',
            'year' => '+1 year',
        ];
        $interval  = $intervals[$group];

        $dates   = [];
        $current = $start_date;
        while ($current <= $end_date) {
            $dates[] = $current->format('Y-m-d');
            $current = $current->modify($interval);
        }

        return $dates;
    }

    /**
     * Get aggregated statistics (per day, week or month) between the two given dates.
     * Without the $page parameter this returns the site-wide statistics.
     *
     * @param DateTimeInterface|string $start_date
     * @param DateTimeInterface|string $end_date
     * @param string $group `day`, `week` or `month`
     * @param int|string $page
     * @return array
     */
    public function get_stats($start_date, $end_date, string $group = 'day', $page = ''): array
    {
        $start_date           = $start_date instanceof DateTimeInterface ? $start_date->format("Y-m-d") : $start_date;
        $end_date             = $end_date instanceof DateTimeInterface ? $end_date->format("Y-m-d") : $end_date;
        $week_starts_on       = (int) get_option('start_of_week', 0);
        $date_key_expressions = [
            'day' => 's.date',
            'week' => "DATE(DATE_SUB(s.date, INTERVAL MOD(DAYOFWEEK(s.date) - 1 - {$week_starts_on} + 7, 7) DAY))",
            'month' => 'DATE(DATE_SUB(s.date, INTERVAL DAYOFMONTH(s.date) - 1 DAY))',
            'year' => 'MAKEDATE(YEAR(s.date), 1)',
        ];
        $date_key_expr        = $date_key_expressions[$group];

        $post_id = $this->get_post_id_filter($page);

        if ($post_id > 0) {
            $from  = "{$this->db->prefix}koko_analytics_post_stats s";
            $args  = [$start_date, $end_date, $post_id];
            $where = 's.date BETWEEN %s AND %s AND s.post_id = %d';
        } elseif ($page) {
            $from  = "{$this->db->prefix}koko_analytics_post_stats s JOIN {$this->db->prefix}koko_analytics_paths p ON p.path = %s AND p.id = s.path_id";
            $args  = [$page, $start_date, $end_date];
            $where = 's.date BETWEEN %s AND %s';
        } else {
            $from  = "{$this->db->prefix}koko_analytics_site_stats s";
            $args  = [$start_date, $end_date];
            $where = 's.date BETWEEN %s AND %s';
        }

        $rows = array_map(function ($row) {
            $row->pageviews = (int) $row->pageviews;
            $row->visitors  = (int) $row->visitors;
            return $row;
        }, $this->db->get_results($this->db->prepare(
            "SELECT {$date_key_expr} AS `date`, SUM(COALESCE(visitors, 0)) AS visitors, SUM(COALESCE(pageviews, 0)) AS pageviews
                FROM {$from}
                WHERE {$where}
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
        $results    = [];
        foreach ($date_range as $date) {
            $results[] = $stats_by_date[$date] ?? (object) [
                'date' => $date,
                'visitors' => 0,
                'pageviews' => 0,
            ];
        }

        return $results;
    }

    /**
     * @param DateTimeInterface|string $start_date
     * @param DateTimeInterface|string $end_date
     */
    public function get_posts($start_date, $end_date, int $offset = 0, int $limit = 10): array
    {
        $start_date = $start_date instanceof DateTimeInterface ? $start_date->format("Y-m-d") : $start_date;
        $end_date   = $end_date instanceof DateTimeInterface ? $end_date->format("Y-m-d") : $end_date;

        $results = $this->db->get_results($this->db->prepare(
            "SELECT p.path, s.post_id, p.path AS label, s.visitors, s.pageviews
                FROM (
                    SELECT MAX(path_id) AS path_id, MAX(post_id) AS post_id, SUM(visitors) AS visitors, SUM(pageviews) AS pageviews
                    FROM {$this->db->prefix}koko_analytics_post_stats
                    WHERE date BETWEEN %s AND %s
                    GROUP BY
                        CASE WHEN post_id > 0 THEN 1 ELSE 0 END,
                        CASE WHEN post_id > 0 THEN post_id ELSE path_id END
                ) s
                JOIN {$this->db->prefix}koko_analytics_paths p ON p.id = s.path_id
                ORDER BY s.pageviews DESC, s.visitors DESC, s.path_id ASC
                LIMIT %d, %d",
            [$start_date, $end_date, $offset, $limit]
        ));

        $post_ids = array_values(array_filter(array_map('intval', array_column($results, 'post_id'))));
        if (count($post_ids) > 0) {
            get_posts([
                'post__in' => $post_ids,
                'post_type' => 'any',
                'post_status' => 'any',
                'posts_per_page' => count($post_ids),
            ]);
        }

        return array_map(function ($row) {
            $row->pageviews = (int) $row->pageviews;
            $row->post_id   = (int) $row->post_id;
            $row->visitors  = max(1, (int) $row->visitors);

            if ($row->post_id > 0) {
                $permalink = get_permalink($row->post_id);
                if ($permalink) {
                    $row->path  = parse_url($permalink, PHP_URL_PATH) ?: $row->path;
                    $row->label = get_the_title($row->post_id) ?: $row->label;
                }
            }

            // for backwards compatibility with versions before 2.0
            // set post_title and post_permalink property
            $row->post_permalink = home_url($row->path);
            $row->post_title     = $row->label;

            return $row;
        }, $results);
    }

    /**
     * @param DateTimeInterface|string $start_date
     * @param DateTimeInterface|string $end_date
     */
    public function count_posts($start_date, $end_date): int
    {
        $start_date = $start_date instanceof DateTimeInterface ? $start_date->format("Y-m-d") : $start_date;
        $end_date   = $end_date instanceof DateTimeInterface ? $end_date->format("Y-m-d") : $end_date;
        return (int) $this->db->get_var($this->db->prepare(
            "SELECT COUNT(*)
                FROM (
                    SELECT 1
                    FROM {$this->db->prefix}koko_analytics_post_stats
                    WHERE date BETWEEN %s AND %s
                    GROUP BY
                        CASE WHEN post_id > 0 THEN 1 ELSE 0 END,
                        CASE WHEN post_id > 0 THEN post_id ELSE path_id END
                ) s",
            [$start_date, $end_date]
        ));
    }

    /**
     * @since 2.3.0
     */
    public function sum_posts(DateTimeInterface $start_date, DateTimeInterface $end_date): int
    {
        return (int) $this->db->get_var($this->db->prepare(
            "SELECT SUM(s.pageviews)
                FROM {$this->db->prefix}koko_analytics_post_stats s
                WHERE s.date BETWEEN %s AND %s",
            [$start_date->format("Y-m-d"), $end_date->format("Y-m-d")]
        ));
    }

    /**
     * @param DateTimeInterface|string $start_date
     * @param DateTimeInterface|string $end_date
     */
    public function get_referrers($start_date, $end_date, int $offset = 0, int $limit = 10): array
    {
        $start_date = $start_date instanceof DateTimeInterface ? $start_date : new DateTimeImmutable($start_date, wp_timezone());
        $end_date   = $end_date instanceof DateTimeInterface ? $end_date : new DateTimeImmutable($end_date, wp_timezone());
        return array_map(function ($row) {
            $row->url       = $row->value;
            $row->pageviews = $row->hits;
            $row->visitors  = $row->unique_hits;
            return $row;
        }, (new Table('referrer'))->get($start_date, $end_date, $offset, $limit));
    }

    /**
     * @param DateTimeInterface|string $start_date
     * @param DateTimeInterface|string $end_date
     */
    public function count_referrers($start_date, $end_date): int
    {
        $start_date = $start_date instanceof DateTimeInterface ? $start_date : new DateTimeImmutable($start_date, wp_timezone());
        $end_date   = $end_date instanceof DateTimeInterface ? $end_date : new DateTimeImmutable($end_date, wp_timezone());
        return (new Table('referrer'))->count($start_date, $end_date);
    }

    /**
     * @param DateTimeInterface|string $start_date
     * @param DateTimeInterface|string $end_date
     */
    public function sum_referrers($start_date, $end_date): int
    {
        $start_date = $start_date instanceof DateTimeInterface ? $start_date : new DateTimeImmutable($start_date, wp_timezone());
        $end_date   = $end_date instanceof DateTimeInterface ? $end_date : new DateTimeImmutable($end_date, wp_timezone());
        return (new Table('referrer'))->sum($start_date, $end_date);
    }
}
