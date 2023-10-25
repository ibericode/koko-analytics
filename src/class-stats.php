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
}
