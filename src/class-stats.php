<?php

namespace KokoAnalytics;

class Stats
{
    public function get_totals(string $start_date, string $end_date): ?object
    {
        global $wpdb;

        // if end date is a future date, cap it at today so that relative differences to previous period are fair
        $today = gmdate('Y-m-d');
        if ($end_date > $today) {
            $end_date = $today;
        }
        $previous_start_date = gmdate('Y-m-d', strtotime($start_date) - (strtotime($end_date . ' 23:59:59') - strtotime($start_date)));

        $sql                = $wpdb->prepare("SELECT
			        cur.*,
			        cur.visitors - prev.visitors AS visitors_change,
			        cur.pageviews - prev.pageviews AS pageviews_change,
			        cur.visitors / prev.visitors - 1 AS visitors_change_rel,
			        cur.pageviews / prev.pageviews - 1 AS pageviews_change_rel
			    FROM
			        (SELECT COALESCE(SUM(visitors), 0) AS visitors, COALESCE(SUM(pageviews), 0) AS pageviews FROM {$wpdb->prefix}koko_analytics_site_stats s WHERE s.date >= %s AND s.date <= %s) AS cur,
			        (SELECT COALESCE(SUM(visitors), 0) AS visitors, COALESCE(SUM(pageviews), 0) AS pageviews FROM {$wpdb->prefix}koko_analytics_site_stats s WHERE s.date >= %s AND s.date < %s) AS prev;
			", array( $start_date, $end_date, $previous_start_date, $start_date ));
        return $wpdb->get_row($sql);
    }

    public function get_stats(string $start_date, string $end_date, string $group): array
    {
        global $wpdb;
        if ($group === 'month') {
            $date_format = '%Y-%m';
        } else {
            $date_format = '%Y-%m-%d';
        }

        $sql = $wpdb->prepare(
            "
                SELECT DATE_FORMAT(d.date, %s) AS date, COALESCE(SUM(visitors), 0) AS visitors, COALESCE(SUM(pageviews), 0) AS pageviews
                FROM {$wpdb->prefix}koko_analytics_dates d
                    LEFT JOIN {$wpdb->prefix}koko_analytics_site_stats s ON s.date = d.date
                WHERE d.date >= %s AND d.date <= %s
                GROUP BY date",
            array( $date_format, $start_date, $end_date )
        );
        $result = $wpdb->get_results($sql);
        return array_map(function ($row) {
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
            array( $start_date, $end_date, $offset, $limit )
        );
        $results = $wpdb->get_results($sql);

        return array_map(function ($row) {
            // special handling of records with ID 0 (indicates a view of the front page when front page is not singular)
            if ($row->id == 0) {
                $row->post_permalink = home_url();
                $row->post_title     = get_bloginfo('name');
            } else {
                /* TODO: Optimize this */
                $post = get_post($row->id);
                $row->post_title = get_the_title($post);
                $row->post_permalink = get_permalink($post);
            }

            $row->pageviews = (int) $row->pageviews;
            $row->visitors  = (int) $row->visitors;
            return $row;
        }, $results);
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
            array( $start_date, $end_date, $offset, $limit )
        );
        return $wpdb->get_results($sql);
    }
}
