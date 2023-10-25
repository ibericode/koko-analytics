<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

class Rest
{
    public function init()
    {
        add_action('rest_api_init', array( $this, 'register_routes' ));
    }

    public function register_routes()
    {
        register_rest_route(
            'koko-analytics/v1',
            '/stats',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'get_stats' ),
                'args'                => array(
                    'start_date' => array(
                        'validate_callback' => array( $this, 'validate_date_param' ),
                    ),
                    'end_date'   => array(
                        'validate_callback' => array( $this, 'validate_date_param' ),
                    ),
                    'monthly' => array(
                        'validate_callback' => 'absint',
                    ),
                ),
                'permission_callback' => function () {
                    return current_user_can('view_koko_analytics');
                },
            )
        );

        register_rest_route(
            'koko-analytics/v1',
            '/totals',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'get_totals' ),
                'args'                => array(
                    'start_date' => array(
                        'validate_callback' => array( $this, 'validate_date_param' ),
                    ),
                    'end_date'   => array(
                        'validate_callback' => array( $this, 'validate_date_param' ),
                    ),
                ),
                'permission_callback' => function () {
                    return current_user_can('view_koko_analytics');
                },
            )
        );

        register_rest_route(
            'koko-analytics/v1',
            '/posts',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'get_posts' ),
                'args'                => array(
                    'start_date' => array(
                        'validate_callback' => array( $this, 'validate_date_param' ),
                    ),
                    'end_date'   => array(
                        'validate_callback' => array( $this, 'validate_date_param' ),
                    ),
                ),
                'permission_callback' => function () {
                    return current_user_can('view_koko_analytics');
                },
            )
        );

        register_rest_route(
            'koko-analytics/v1',
            '/referrers',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'get_referrers' ),
                'args'                => array(
                    'start_date' => array(
                        'validate_callback' => array( $this, 'validate_date_param' ),
                    ),
                    'end_date'   => array(
                        'validate_callback' => array( $this, 'validate_date_param' ),
                    ),
                ),
                'permission_callback' => function () {
                    return current_user_can('view_koko_analytics');
                },
            )
        );

        register_rest_route(
            'koko-analytics/v1',
            '/realtime',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'get_realtime_pageview_count' ),
                'args'                => array(
                    'since' => array(
                        'validate_callback' => array( $this, 'validate_date_param' ),
                    ),
                ),
                'permission_callback' => function () {
                    return current_user_can('view_koko_analytics');
                },
            )
        );
    }

    private function is_request_for_completed_date_range(\WP_REST_Request $request): bool
    {
        $end_date = $request->get_param('end_date');
        if ($end_date === null) {
            return false;
        }

        $today = gmdate('Y-m-d', (int) (time() + get_option('gmt_offset') * HOUR_IN_SECONDS));

        return $end_date < $today;
    }

    private function respond($data, bool $send_cache_headers = false): \WP_REST_Response
    {
        $result = new \WP_REST_Response($data, 200);

        // instruct browsers to cache the response for 7 days
        if ($send_cache_headers) {
            $result->set_headers(array( 'Cache-Control' => 'max-age=604800' ));
        }
        return $result;
    }

    public function validate_date_param($param, $one, $two): bool
    {
        return strtotime($param) !== false;
    }

    /**
     * Returns a daily tally of visitors and pageviews between two dates
     *
     * @param \WP_REST_Request $request
     *
     * @return \WP_REST_Response
     */
    public function get_stats(\WP_REST_Request $request): \WP_REST_Response
    {
        global $wpdb;
        $params             = $request->get_query_params();
        $start_date         = $params['start_date'] ?? gmdate('Y-m-d', strtotime('1st of this month') + get_option('gmt_offset', 0) * HOUR_IN_SECONDS);
        $end_date           = $params['end_date'] ?? gmdate('Y-m-d', time() + get_option('gmt_offset', 0) * HOUR_IN_SECONDS);
        $date_format        = ($params['monthly'] ?? false) ? '%Y-%m' : '%Y-%m-%d';
        $sql                = $wpdb->prepare("SELECT DATE_FORMAT(d.date, %s) AS date, COALESCE(SUM(visitors), 0) AS visitors, COALESCE(SUM(pageviews), 0) AS pageviews FROM {$wpdb->prefix}koko_analytics_dates d LEFT JOIN {$wpdb->prefix}koko_analytics_site_stats s ON s.date = d.date WHERE d.date >= %s AND d.date <= %s GROUP BY date", array( $date_format, $start_date, $end_date ));
        $result             = $wpdb->get_results($sql);
        $result             = is_array($result) ? array_map(function ($row) {
            $row->pageviews = (int) $row->pageviews;
            $row->visitors  = (int) $row->visitors;
            return $row;
        }, $result) : $result;
        $send_cache_headers = WP_DEBUG === false && $this->is_request_for_completed_date_range($request);
        return $this->respond($result, $send_cache_headers);
    }

    /**
     * Returns the total number of visitos and pageviews between two dates.
     *
     * @param \WP_REST_Request $request
     *
     * @return \WP_REST_Response
     */
    public function get_totals(\WP_REST_Request $request): \WP_REST_Response
    {
        $params     = $request->get_query_params();
        $start_date = $params['start_date'] ?? gmdate('Y-m-d', strtotime('1st of this month') + get_option('gmt_offset', 0) * HOUR_IN_SECONDS);
        $end_date   = $params['end_date'] ?? gmdate('Y-m-d', time() + get_option('gmt_offset', 0) * HOUR_IN_SECONDS);
        $result = (new Stats())->get_totals($start_date, $end_date);
        $send_cache_headers = WP_DEBUG === false && $this->is_request_for_completed_date_range($request);
        return $this->respond($result, $send_cache_headers);
    }

    /**
     * Returns the total number of pageviews and visitors per post, ordered by most pageviews first.
     *
     * @param \WP_REST_Request $request
     *
     * @return \WP_REST_Response
     */
    public function get_posts(\WP_REST_Request $request): \WP_REST_Response
    {
        global $wpdb;
        $send_cache_headers = WP_DEBUG === false && $this->is_request_for_completed_date_range($request);

        $params     = $request->get_query_params();
        $start_date = $params['start_date'] ?? gmdate('Y-m-d', strtotime('1st of this month') + get_option('gmt_offset', 0) * HOUR_IN_SECONDS);
        $end_date   = $params['end_date'] ?? gmdate('Y-m-d', time() + get_option('gmt_offset', 0) * HOUR_IN_SECONDS);
        $offset     = isset($params['offset']) ? absint($params['offset']) : 0;
        $limit      = isset($params['limit']) ? absint($params['limit']) : 10;
        $sql        = $wpdb->prepare("SELECT s.id, SUM(visitors) AS visitors, SUM(pageviews) AS pageviews, COALESCE(NULLIF(p.post_title, ''), p.post_name) AS post_title FROM {$wpdb->prefix}koko_analytics_post_stats s LEFT JOIN {$wpdb->posts} p ON p.ID = s.id WHERE s.date >= %s AND s.date <= %s GROUP BY s.id ORDER BY pageviews DESC, s.id ASC LIMIT %d, %d", array( $start_date, $end_date, $offset, $limit ));
        $results    = $wpdb->get_results($sql);

        // add permalink to each result
        $results = array_map(function ($row) {
            // special handling of records with ID 0 (indicates a view of the front page when front page is not singular)
            if ($row->id == 0) {
                $row->post_permalink = home_url();
                $row->post_title     = get_bloginfo('name');
            } else {
                /* TODO: Optimize this */
                $row->post_permalink = get_permalink($row->id);
            }

            $row->pageviews = (int) $row->pageviews;
            $row->visitors  = (int) $row->visitors;
            return $row;
        }, $results);

        return $this->respond($results, $send_cache_headers);
    }

    /**
     * Returns the total number of visitors and pageviews per referrer URL, ordered by most pageviews first.
     *
     * @param \WP_REST_Request $request
     *
     * @return \WP_REST_Response
     */
    public function get_referrers(\WP_REST_Request $request): \WP_REST_Response
    {
        global $wpdb;
        $params             = $request->get_query_params();
        $start_date         = $params['start_date'] ?? gmdate('Y-m-d', strtotime('1st of this month') + get_option('gmt_offset', 0) * HOUR_IN_SECONDS);
        $end_date           = $params['end_date'] ?? gmdate('Y-m-d', time() + get_option('gmt_offset', 0) * HOUR_IN_SECONDS);
        $offset             = isset($params['offset']) ? absint($params['offset']) : 0;
        $limit              = isset($params['limit']) ? absint($params['limit']) : 10;
        $sql                = $wpdb->prepare("SELECT s.id, url, SUM(visitors) As visitors, SUM(pageviews) AS pageviews FROM {$wpdb->prefix}koko_analytics_referrer_stats s JOIN {$wpdb->prefix}koko_analytics_referrer_urls r ON r.id = s.id WHERE s.date >= %s AND s.date <= %s GROUP BY s.id ORDER BY pageviews DESC, r.id ASC LIMIT %d, %d", array( $start_date, $end_date, $offset, $limit ));
        $results            = $wpdb->get_results($sql);
        $send_cache_headers = WP_DEBUG === false && $this->is_request_for_completed_date_range($request);
        return $this->respond($results, $send_cache_headers);
    }

    /**
     * Returns the total number of recorded pageviews in the last hour
     *
     * @param \WP_REST_Request $request
     *
     * @return int|mixed
     */
    public function get_realtime_pageview_count(\WP_REST_Request $request)
    {
        $params = $request->get_query_params();
        $since  = isset($params['since']) ? strtotime($params['since']) : null;
        return get_realtime_pageview_count($since);
    }
}
