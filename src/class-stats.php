<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

class Stats
{
    public function get_totals(string $start_date, string $end_date, int $page = 0): ?object
    {
        global $wpdb;

        $previous_start_date = gmdate('Y-m-d', strtotime($start_date) - (strtotime($end_date . ' 23:59:59') - strtotime($start_date)));

        $table = $wpdb->prefix . 'koko_analytics_site_stats';
        $where_a = 's.date >= %s AND s.date <= %s';
        $args_a = array($start_date, $end_date);
        $where_b = 's.date >= %s AND s.date < %s';
        $args_b = array($previous_start_date, $start_date);

        if ($page > 0) {
            $table = $wpdb->prefix . 'koko_analytics_post_stats';
            $where_a .= ' AND s.id = %d';
            $where_b .= ' AND s.id = %d';
            $args_a[] = $page;
            $args_b[] = $page;
        }

        $sql                = $wpdb->prepare("SELECT
			        cur.*,
                    prev.visitors AS prev_visitors,
			        cur.visitors - prev.visitors AS visitors_change,
			        cur.pageviews - prev.pageviews AS pageviews_change,
			        cur.visitors / prev.visitors - 1 AS visitors_change_rel,
			        cur.pageviews / prev.pageviews - 1 AS pageviews_change_rel
			    FROM
			        (SELECT COALESCE(SUM(visitors), 0) AS visitors, COALESCE(SUM(pageviews), 0) AS pageviews FROM {$table} s WHERE $where_a) AS cur,
			        (SELECT COALESCE(SUM(visitors), 0) AS visitors, COALESCE(SUM(pageviews), 0) AS pageviews FROM {$table} s WHERE $where_b) AS prev;
			", array_merge($args_a, $args_b));
        $result = $wpdb->get_row($sql);

        // ensure we always return a valid object containing the keys we need
        if (!$result) {
            return (object) [
                'pageviews' => 0,
                'pageviews_change' => 0,
                'pageviews_change_rel' => 0,
                'visitors' => 0,
                'visitors_change' => 0,
                'visitors_change_rel' => 0,
            ];
        }

        // sometimes there are pageviews, but no counted visitors
        // this happens when the cookie was valid over a period of 2 calendar days
        // we can make this less obviously wrong by always specifying there was at least 1 visitors
        // whenever we have any pageviews
        if ($result && $result->pageviews > 0 && $result->visitors == 0) {
            $result->visitors = 1;
            $result->visitors_change += $result->visitors_change > 0 ? -1 : 1;
        }

        return $result;
    }

    /**
     * Get aggregated statistics (per day or per month) between the two given dates.
     * Without the $page parameter this returns the site-wide statistics.
     *
     * @param string $start_date
     * @param string $end_date
     * @param string $group
     * @param int $page
     * @return array
     */
    public function get_stats(string $start_date, string $end_date, string $group, int $page = 0): array
    {
        global $wpdb;
        if ($group === 'month') {
            $date_format = '%Y-%m';
        } else {
            $date_format = '%Y-%m-%d';
        }

        if ($page > 0) {
            $table = $wpdb->prefix . 'koko_analytics_post_stats';
            $join_on = 's.date = d.date AND s.id = %d';
            $args = array($date_format, $page, $start_date, $end_date);
        } else {
            $table = $wpdb->prefix . 'koko_analytics_site_stats';
            $args = array($date_format, $start_date, $end_date);
            $join_on = 's.date = d.date';
        }

        $sql = $wpdb->prepare(
            "
                SELECT DATE_FORMAT(d.date, %s) AS _date, COALESCE(SUM(visitors), 0) AS visitors, COALESCE(SUM(pageviews), 0) AS pageviews
                FROM {$wpdb->prefix}koko_analytics_dates d
                    LEFT JOIN {$table} s ON {$join_on}
                WHERE d.date >= %s AND d.date <= %s
                GROUP BY _date",
            $args
        );
        $result = $wpdb->get_results($sql);
        return array_map(function ($row) {
            $row->date = $row->_date;
            unset($row->_date);

            $row->pageviews = (int) $row->pageviews;
            $row->visitors  = (int) $row->visitors;
            return $row;
        }, $result);
    }

    public function get_posts(string $start_date, string $end_date, int $offset = 0, int $limit = 10): array
    {
        global $wpdb;
        $sql = $wpdb->prepare(
            "
                SELECT s.id, SUM(visitors) AS visitors, SUM(pageviews) AS pageviews
                FROM {$wpdb->prefix}koko_analytics_post_stats s
                WHERE s.date >= %s AND s.date <= %s
                GROUP BY s.id
                ORDER BY pageviews DESC, s.id ASC
                LIMIT %d, %d",
            array($start_date, $end_date, $offset, $limit)
        );
        $results = $wpdb->get_results($sql);

        return array_map(function ($row) {
            // special handling of records with ID 0 (indicates a view of the front page when front page is not singular)
            if ($row->id == 0) {
                $row->post_permalink = home_url();
                $row->post_title     = get_bloginfo('name');
            } else {
                $post = get_post($row->id);
                if ($post) {
                    $row->post_title = isset($post->post_title) ? $post->post_title : $post->post_name;
                    $row->post_permalink = get_permalink($post);
                } else {
                    $row->post_title = '(deleted post)';
                    $row->post_permalink = '';
                }
            }

            $row->pageviews = (int) $row->pageviews;
            $row->visitors  = (int) $row->visitors;
            return $row;
        }, $results);
    }

    public function count_posts(string $start_date, string $end_date): int
    {
        global $wpdb;
        $sql = $wpdb->prepare(
            "
                SELECT COUNT(DISTINCT(s.id))
                FROM {$wpdb->prefix}koko_analytics_post_stats s
                WHERE s.date >= %s AND s.date <= %s",
            array($start_date, $end_date)
        );
        return (int) $wpdb->get_var($sql);
    }

    public function get_referrers(string $start_date, string $end_date, int $offset = 0, int $limit = 10): array
    {
        global $wpdb;
        $sql = $wpdb->prepare(
            "
                SELECT s.id, url, SUM(visitors) As visitors, SUM(pageviews) AS pageviews
                FROM {$wpdb->prefix}koko_analytics_referrer_stats s
                    JOIN {$wpdb->prefix}koko_analytics_referrer_urls r ON r.id = s.id
                WHERE s.date >= %s
                  AND s.date <= %s
                GROUP BY s.id
                ORDER BY pageviews DESC, r.id ASC
                LIMIT %d, %d",
            array($start_date, $end_date, $offset, $limit)
        );
        return $wpdb->get_results($sql);
    }

    public function count_referrers(string $start_date, string $end_date): int
    {
        global $wpdb;
        $sql = $wpdb->prepare(
            "
                SELECT COUNT(DISTINCT(s.id))
                FROM {$wpdb->prefix}koko_analytics_referrer_stats s
                WHERE s.date >= %s
                  AND s.date <= %s",
            array($start_date, $end_date)
        );
        return (int) $wpdb->get_var($sql);
    }
}
