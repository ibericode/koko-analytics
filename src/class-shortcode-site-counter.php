<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Anil Kulkarni, Danny van Kooten
 * @since 1.3.7
 *
 * Adds support for a shortcode to display the number of times a page or a site has been viewed

 * Options:
 *  days: How many previous days to count. Defaults to -1 which means show views for all time
 *  metric: Either "pageviews" or "visitors"
 *  global: Set to true to show count for entire site instead of the current page.
 */

namespace KokoAnalytics;

class ShortCode_Site_Counter
{
    const SHORTCODE = 'koko_analytics_counter';

    public function init()
    {
        add_shortcode(self::SHORTCODE, array($this, 'content'));
    }

    public function content($args)
    {
        $default_args = array(
            'days' => 365 * 10,
            'metric' => 'visitors',
            'global' => false,
        );
        $args = shortcode_atts($default_args, $args, self::SHORTCODE);

        $id = $args['global'] && $args['global'] !== 'false' ? 0 : (int) get_the_ID();
        $start_date = create_local_datetime("-{$args['days']} days")->format('Y-m-d');
        $end_date = create_local_datetime('tomorrow midnight')->format('Y-m-d');

        $cache_key = 'ka_counter_' . $id . $args['metric'][0] . $args['days'];
        $count = get_transient($cache_key);
        if (!$count) {
            $stats = new Stats();
            $totals = $stats->get_totals($start_date, $end_date, $id);
            $count = $args['metric'] == 'pageviews' ? $totals->pageviews : $totals->visitors;
            set_transient($cache_key, $count, 60);
        }

        return '<span class="koko-analytics-counter">' . $count . '</span>';
    }
}
