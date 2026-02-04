<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

class Stats
{
    public function get_total_date_range(): array
    {
        global $wpdb;
        $result = $wpdb->get_row("select MIN(date) AS start, MAX(date) AS end FROM {$wpdb->prefix}koko_analytics_site_stats WHERE date IS NOT NULL;");
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
        /** @var \wpdb $wpdb */
        global $wpdb;

        $from = "{$wpdb->prefix}koko_analytics_site_stats s";
        $where = 's.date >= %s AND s.date <= %s';
        $args = [$start_date, $end_date];

        if ($page) {
            $from = "{$wpdb->prefix}koko_analytics_post_stats s LEFT JOIN {$wpdb->prefix}koko_analytics_paths p ON p.id = s.path_id";
            $where .= ' AND p.path = %s';
            $args[] = $page;
        }

        $result = $wpdb->get_row($wpdb->prepare("
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
        /** @var \wpdb $wpdb */
        global $wpdb;

        $week_starts_on = (int) get_option('start_of_week', 0);
        $available_groupings = [
            'day' => '%Y-%m-%d',
            'week' => $week_starts_on === 1 ? '%Y-%u' : '%Y-%U',
            'month' => '%Y-%m',
            'year' => '%Y',
        ];
        $date_format = $available_groupings[$group];

        $from = "{$wpdb->prefix}koko_analytics_dates d";
        $where = "d.date >= %s AND d.date <= %s";
        $args = [$start_date, $end_date];

        if ($page) {
            // join page-specific stats
            $from .= " LEFT JOIN {$wpdb->prefix}koko_analytics_post_stats s JOIN {$wpdb->prefix}koko_analytics_paths p ON p.path = %s AND p.id = s.path_id ON s.date = d.date ";
            $args = [$page, $start_date, $end_date];
        } else {
            // join site-wide stats
            $from .= " LEFT JOIN {$wpdb->prefix}koko_analytics_site_stats s ON s.date = d.date";
        }

        $args[] = $date_format;

        $result = $wpdb->get_results($wpdb->prepare(
            "SELECT d.date, SUM(COALESCE(visitors, 0)) AS visitors, SUM(COALESCE(pageviews, 0)) AS pageviews
                FROM {$from}
                WHERE {$where}
                GROUP BY DATE_FORMAT(d.date, %s)
                ORDER BY d.date ASC",
            $args
        ));
        return \array_map(function ($row) {
            $row->pageviews = (int) $row->pageviews;
            $row->visitors  = (int) $row->visitors;
            return $row;
        }, $result);
    }

    public function get_posts(string $start_date, string $end_date, int $offset = 0, int $limit = 10): array
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT p.path, s.post_id, IFNULL(NULLIF(wp.post_title, ''), p.path) AS label, SUM(visitors) AS visitors, SUM(pageviews) AS pageviews
                FROM {$wpdb->prefix}koko_analytics_post_stats s
                JOIN {$wpdb->prefix}koko_analytics_paths p ON p.id = s.path_id
                LEFT JOIN {$wpdb->prefix}posts wp ON wp.ID = s.post_id
                WHERE s.date >= %s AND s.date <= %s
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
        /** @var \wpdb $wpdb */
        global $wpdb;
        return (int) $wpdb->get_var($wpdb->prepare(
            "
            SELECT COUNT(*)
            FROM (
                SELECT COUNT(*) AS count
                FROM {$wpdb->prefix}koko_analytics_post_stats s
                JOIN {$wpdb->prefix}koko_analytics_paths p ON p.id = s.path_id
                WHERE s.date >= %s AND s.date <= %s
                GROUP BY p.path, s.post_id
            ) AS a",
            [$start_date, $end_date]
        ));
    }

    public function get_referrers(string $start_date, string $end_date, int $offset = 0, int $limit = 10): array
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        return array_map(function ($row) {
            $row->pageviews = (int) $row->pageviews;
            $row->visitors = max(1, (int) $row->visitors);
            return $row;
        }, $wpdb->get_results($wpdb->prepare(
            "SELECT s.id, url, SUM(visitors) As visitors, SUM(pageviews) AS pageviews
                FROM {$wpdb->prefix}koko_analytics_referrer_stats s
                JOIN {$wpdb->prefix}koko_analytics_referrer_urls r ON r.id = s.id
                WHERE s.date >= %s AND s.date <= %s
                GROUP BY s.id
                ORDER BY pageviews DESC, r.id ASC
                LIMIT %d, %d",
            [$start_date, $end_date, $offset, $limit]
        )));
    }

    public function count_referrers(string $start_date, string $end_date): int
    {
        /** @var \wpdb $wpdb */
        global $wpdb;
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT(s.id))
                FROM {$wpdb->prefix}koko_analytics_referrer_stats s
                WHERE s.date >= %s AND s.date <= %s",
            [$start_date, $end_date]
        ));
    }

    public function sum_referrers(string $start_date, string $end_date): int
    {
        /** @var \wpdb $wpdb */
        global $wpdb;
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(s.pageviews)
                FROM {$wpdb->prefix}koko_analytics_referrer_stats s
                WHERE s.date >= %s AND s.date <= %s",
            [$start_date, $end_date]
        ));
    }
}
