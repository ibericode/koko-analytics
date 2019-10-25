<?php

namespace AAA;

class Aggregator
{
    public function init()
    {
        add_filter('cron_schedules', array($this, 'add_interval'));
        add_action('aaa_aggregate_stats', array($this, 'aggregate'));
        add_action('init', array($this, 'schedule'));
    }

    public function add_interval($intervals)
    {
        $intervals['aaa_aggregate_interval'] = [
            'interval' => 5 * 60, // 5 minutes
            'display'  => __( 'Every 5 minutes', 'aaa-analytics' ),
        ];
        return $intervals;
    }

    public function schedule()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' && (!defined('DOING_CRON') || !DOING_CRON)) {
            return;
        }

        if (!wp_next_scheduled('aaa_aggregate_stats')) {
            wp_schedule_event(time() + 1, 'aaa_aggregate_interval', 'aaa_aggregate_stats');
        }
    }

    public function aggregate()
    {
        global $wpdb;

        // TODO: Aggregate pageviews into stats table every 5 minutes
        // TODO: Clear todays_visitors and todays_pageviews tables at midnight (in site's timezone)

    }

}