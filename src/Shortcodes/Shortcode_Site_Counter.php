<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Anil Kulkarni, Danny van Kooten
 * @since 1.3.7
 *
 * Adds support for a shortcode to display the number of times a page or a site has been viewed

 * Options:
 *  days: How many previous days to count. (default: 3650)
 *  metric: Either "pageviews" or "visitors" (default: pageviews)
 *  global: Set to true to show count for entire site instead of the current page.
 */

namespace KokoAnalytics\Shortcodes;

use DateTime;
use KokoAnalytics\Normalizers\Normalizer;
use KokoAnalytics\Stats;

class Shortcode_Site_Counter
{
    private const SHORTCODE = 'koko_analytics_counter';

    public function content($args)
    {
        $default_args = [
            'days' => 365 * 10,
            'metric' => 'pageviews',
            'global' => false,
        ];
        $args = shortcode_atts($default_args, $args, self::SHORTCODE);
        $args['days'] = abs((int) $args['days']);
        $path = $args['global'] && $args['global'] !== 'false' && $args['global'] !== '0' && $args['global'] !== 'no' ? '' : $this->get_post_path();

        $start_date_str = $args['days'] === 0 ? 'today midnight' : "-{$args['days']} days";
        $timezone = wp_timezone();
        $start_date = (new DateTime($start_date_str, $timezone))->format('Y-m-d');
        $end_date = (new DateTime('tomorrow, midnight', $timezone))->format('Y-m-d');

        $cache_key = "ka_counter_" . md5("{$path}-{$args['metric']}-{$args['days']}");
        $count = get_transient($cache_key);
        if (false === $count) {
            $stats = new Stats();
            $totals = $stats->get_totals($start_date, $end_date, $path);
            $count = $args['metric'] === 'visitors' ? $totals->visitors : $totals->pageviews;
            set_transient($cache_key, $count, 5 * 60);
        }

        return '<span class="koko-analytics-counter">' . $count . '</span>';
    }

    // Gets the path to whatever post is currently in "the loop"
    public function get_post_path()
    {
        $permalink = get_the_permalink();
        $url_parts = parse_url($permalink);
        $path = $url_parts['path'];
        if (!empty($url_parts['query'])) {
            $path .= '?' . $url_parts['query'];
        }

        return Normalizer::path($path);
    }
}
