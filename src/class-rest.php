<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

class Rest
{
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_routes'), 10, 0);
    }

    public function register_routes()
    {
        $settings = get_settings();
        $is_dashboard_public = $settings['is_dashboard_public'];

        register_rest_route(
            'koko-analytics/v1',
            '/stats',
            array(
                'methods'             => 'GET',
                'callback'            => array($this, 'get_stats'),
                'args'                => array(
                    'start_date' => array(
                        'validate_callback' => array($this, 'validate_date_param'),
                    ),
                    'end_date'   => array(
                        'validate_callback' => array($this, 'validate_date_param'),
                    ),
                    'monthly' => array(
                        'validate_callback' => 'absint',
                    ),
                ),
                'permission_callback' => function () use ($is_dashboard_public) {
                    return $is_dashboard_public ? true : current_user_can('view_koko_analytics');
                },
            )
        );

        register_rest_route(
            'koko-analytics/v1',
            '/totals',
            array(
                'methods'             => 'GET',
                'callback'            => array($this, 'get_totals'),
                'args'                => array(
                    'start_date' => array(
                        'validate_callback' => array($this, 'validate_date_param'),
                    ),
                    'end_date'   => array(
                        'validate_callback' => array($this, 'validate_date_param'),
                    ),
                ),
                'permission_callback' => function () use ($is_dashboard_public) {
                    return $is_dashboard_public ? true : current_user_can('view_koko_analytics');
                },
            )
        );

        register_rest_route(
            'koko-analytics/v1',
            '/posts',
            array(
                'methods'             => 'GET',
                'callback'            => array($this, 'get_posts'),
                'args'                => array(
                    'start_date' => array(
                        'validate_callback' => array($this, 'validate_date_param'),
                    ),
                    'end_date'   => array(
                        'validate_callback' => array($this, 'validate_date_param'),
                    ),
                ),
                'permission_callback' => function () use ($is_dashboard_public) {
                    return $is_dashboard_public ? true : current_user_can('view_koko_analytics');
                },
            )
        );

        register_rest_route(
            'koko-analytics/v1',
            '/referrers',
            array(
                'methods'             => 'GET',
                'callback'            => array($this, 'get_referrers'),
                'args'                => array(
                    'start_date' => array(
                        'validate_callback' => array($this, 'validate_date_param'),
                    ),
                    'end_date'   => array(
                        'validate_callback' => array($this, 'validate_date_param'),
                    ),
                ),
                'permission_callback' => function () use ($is_dashboard_public) {
                    return $is_dashboard_public ? true : current_user_can('view_koko_analytics');
                },
            )
        );

        register_rest_route(
            'koko-analytics/v1',
            '/realtime',
            array(
                'methods'             => 'GET',
                'callback'            => array($this, 'get_realtime_pageview_count'),
                'args'                => array(
                    'since' => array(
                        'validate_callback' => array($this, 'validate_date_param'),
                    ),
                ),
                'permission_callback' => function () use ($is_dashboard_public) {
                    return $is_dashboard_public ? true : current_user_can('view_koko_analytics');
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

        $end_date = create_local_datetime($end_date);
        $now = create_local_datetime('now');
        return $end_date < $now;
    }

    private function respond($data, bool $send_cache_headers = false): \WP_REST_Response
    {
        $result = new \WP_REST_Response($data, 200);

        // instruct browsers to cache the response for 7 days
        if ($send_cache_headers) {
            $result->set_headers(['Cache-Control' => 'max-age=604800']);
        }
        return $result;
    }

    public function validate_date_param($param, $one, $two): bool
    {
        return strtotime($param) !== false;
    }

    /**
     * Returns a daily tally of visitors and pageviews between two dates
     */
    public function get_stats(\WP_REST_Request $request): \WP_REST_Response
    {
        $params             = $request->get_query_params();
        $start_date         = $params['start_date'] ?? create_local_datetime('1st of this month')->format('Y-m-d');
        $end_date           = $params['end_date'] ?? create_local_datetime('now')->format('Y-m-d');
        $group = ($params['monthly'] ?? false) ? 'month' : 'day';
        $page = $params['page'] ?? 0;
        $result = (new Stats())->get_stats($start_date, $end_date, $group, $page);
        $send_cache_headers = WP_DEBUG === false && $this->is_request_for_completed_date_range($request);
        return $this->respond($result, $send_cache_headers);
    }

    /**
     * Returns the total number of visitos and pageviews between two dates.
     */
    public function get_totals(\WP_REST_Request $request): \WP_REST_Response
    {
        $params     = $request->get_query_params();
        $start_date = $params['start_date'] ?? create_local_datetime('1st of this month')->format('Y-m-d');
        $end_date   = $params['end_date'] ?? create_local_datetime('now')->format('Y-m-d');
        $page = $params['page'] ?? 0;
        $result = (new Stats())->get_totals($start_date, $end_date, $page);
        $send_cache_headers = WP_DEBUG === false && $this->is_request_for_completed_date_range($request);
        return $this->respond($result, $send_cache_headers);
    }

    /**
     * Returns the total number of pageviews and visitors per post, ordered by most pageviews first.
     */
    public function get_posts(\WP_REST_Request $request): \WP_REST_Response
    {
        $send_cache_headers = WP_DEBUG === false && $this->is_request_for_completed_date_range($request);
        $params     = $request->get_query_params();
        $start_date = $params['start_date'] ?? create_local_datetime('1st of this month')->format('Y-m-d');
        $end_date   = $params['end_date'] ?? create_local_datetime('now')->format('Y-m-d');
        $offset     = isset($params['offset']) ? absint($params['offset']) : 0;
        $limit      = isset($params['limit']) ? absint($params['limit']) : 10;
        $results = (new Stats())->get_posts($start_date, $end_date, $offset, $limit);
        return $this->respond($results, $send_cache_headers);
    }

    /**
     * Returns the total number of visitors and pageviews per referrer URL, ordered by most pageviews first.
     */
    public function get_referrers(\WP_REST_Request $request): \WP_REST_Response
    {
        $params             = $request->get_query_params();
        $start_date         = $params['start_date'] ?? create_local_datetime('1st of this month')->format('Y-m-d');
        $end_date           = $params['end_date'] ?? create_local_datetime('now')->format('Y-m-d');
        $offset             = isset($params['offset']) ? absint($params['offset']) : 0;
        $limit              = isset($params['limit']) ? absint($params['limit']) : 10;
        $results = (new Stats())->get_referrers($start_date, $end_date, $offset, $limit);
        $send_cache_headers = WP_DEBUG === false && $this->is_request_for_completed_date_range($request);
        return $this->respond($results, $send_cache_headers);
    }

    /**
     * Returns the total number of recorded pageviews in the last hour
     * @return int|mixed
     */
    public function get_realtime_pageview_count(\WP_REST_Request $request)
    {
        $params = $request->get_query_params();
        $since  = isset($params['since']) ? strtotime($params['since']) : null;
        return get_realtime_pageview_count($since);
    }
}
