<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

class Stats
{
    /**
     * @return object{ visitors: int, pageviews: int }
     */
    public function get_totals(string $start_date, string $end_date, int $page = 0, $unused = null): object
    {
        /** @var wpdb $wpdb */
        global $wpdb;

        $table = $wpdb->prefix . 'koko_analytics_site_stats s';
        $where = 's.date >= %s AND s.date <= %s';
        $args = [$start_date, $end_date];

        if ($page > 0) {
            $table = $wpdb->prefix . 'koko_analytics_post_stats s';
            $where .= ' AND s.id = %d';
            $args[] = $page;
        }

        $sql = $wpdb->prepare("
            SELECT COALESCE(SUM(visitors), 0) AS visitors, COALESCE(SUM(pageviews), 0) AS pageviews
		    FROM {$table}
            WHERE {$where}
			", $args);
        $result = $wpdb->get_row($sql);

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
     * Get aggregated statistics (per day or per month) between the two given dates.
     * Without the $page parameter this returns the site-wide statistics.
     *
     * @param string $start_date
     * @param string $end_date
     * @param string $group `day`, `week` or `month`
     * @param int $page
     * @return array
     */
    public function get_stats(string $start_date, string $end_date, string $group = 'day', int $page = 0): array
    {
        /** @var wpdb $wpdb */
        global $wpdb;

        $week_starts_on = (int) get_option('start_of_week', 0);
        $available_groupings = [
            'day' => '%Y-%m-%d',
            'week' => $week_starts_on === 1 ? '%Y-%u' : '%Y-%U',
            'month' => '%Y-%m',
        ];
        $date_format = $available_groupings[$group];

        if ($page > 0) {
            $table = $wpdb->prefix . 'koko_analytics_post_stats';
            $join_on = 's.date = d.date AND s.id = %d';
            $args = [$page, $start_date, $end_date, $date_format];
        } else {
            $table = $wpdb->prefix . 'koko_analytics_site_stats';
            $args = [$start_date, $end_date, $date_format];
            $join_on = 's.date = d.date';
        }

        $sql = $wpdb->prepare(
            "SELECT d.date, COALESCE(SUM(visitors), 0) AS visitors, COALESCE(SUM(pageviews), 0) AS pageviews
                FROM {$wpdb->prefix}koko_analytics_dates d
                    LEFT JOIN {$table} s ON {$join_on}
                WHERE d.date >= %s AND d.date <= %s
                GROUP BY DATE_FORMAT(d.date, %s)",
            $args
        );
        $result = $wpdb->get_results($sql);
        return \array_map(function ($row) {
            $row->pageviews = (int) $row->pageviews;
            $row->visitors  = (int) $row->visitors;
            return $row;
        }, $result);
    }

    public function get_posts(string $start_date, string $end_date, int $offset = 0, int $limit = 10): array
    {
        /** @var wpdb $wpdb */
        global $wpdb;
        $sql = $wpdb->prepare(
            "SELECT s.id, SUM(visitors) AS visitors, SUM(pageviews) AS pageviews
                FROM {$wpdb->prefix}koko_analytics_post_stats s
                WHERE s.date >= %s AND s.date <= %s
                GROUP BY s.id
                ORDER BY pageviews DESC, s.id ASC
                LIMIT %d, %d",
            [$start_date, $end_date, $offset, $limit]
        );

        $results = $wpdb->get_results($sql);

        // this prevents n+1 queries in the array_map callback below
        // because get_posts primes wp_cache entries for each post object
        $ids = wp_list_pluck($results, 'id');
        get_posts(['include' => $ids ]);

        return array_map(function ($row) {
            // special handling of records with ID 0 (indicates a view of the front page when front page is not singular)
            if ($row->id == 0) {
                $row->post_permalink = home_url();
                $row->post_title     = get_bloginfo('name');
            } else {
                $post = get_post($row->id);
                $row->post_title = get_page_title($post);
                $row->post_permalink = $post ? get_permalink($post) : '';
            }

            $row->pageviews = (int) $row->pageviews;
            $row->visitors  = (int) $row->visitors;
            return $row;
        }, $results);
    }

    public function count_posts(string $start_date, string $end_date): int
    {
        /** @var wpdb $wpdb */
        global $wpdb;
        $sql = $wpdb->prepare(
            "SELECT COUNT(DISTINCT(s.id))
                FROM {$wpdb->prefix}koko_analytics_post_stats s
                WHERE s.date >= %s AND s.date <= %s",
            [$start_date, $end_date]
        );
        return (int) $wpdb->get_var($sql);
    }

    public function get_referrers(string $start_date, string $end_date, int $offset = 0, int $limit = 10): array
    {
        /** @var wpdb $wpdb */
        global $wpdb;
        $sql = $wpdb->prepare(
            "SELECT s.id, url, SUM(visitors) As visitors, SUM(pageviews) AS pageviews
                FROM {$wpdb->prefix}koko_analytics_referrer_stats s
                    JOIN {$wpdb->prefix}koko_analytics_referrer_urls r ON r.id = s.id
                WHERE s.date >= %s AND s.date <= %s
                GROUP BY s.id
                ORDER BY pageviews DESC, r.id ASC
                LIMIT %d, %d",
            [$start_date, $end_date, $offset, $limit]
        );
        return $wpdb->get_results($sql);
    }

    public function count_referrers(string $start_date, string $end_date): int
    {
        /** @var wpdb $wpdb */
        global $wpdb;
        $sql = $wpdb->prepare(
            "SELECT COUNT(DISTINCT(s.id))
                FROM {$wpdb->prefix}koko_analytics_referrer_stats s
                WHERE s.date >= %s AND s.date <= %s",
            [$start_date, $end_date]
        );
        return (int) $wpdb->get_var($sql);
    }
}
