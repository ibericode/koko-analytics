<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

use DateTimeImmutable;

class Rest
{
    public function action_rest_api_init(): void
    {
        $route_namespace = 'koko-analytics/v1';
        register_rest_route(
            $route_namespace,
            '/stats',
            [
                'callback'            => [$this, 'get_stats'],
                'args'                => [
                    'start_date' => [
                        'validate_callback' => [$this, 'validate_date_param'],
                    ],
                    'end_date'   => [
                        'validate_callback' => [$this, 'validate_date_param'],
                    ],
                    'monthly' => [
                        'sanitize_callback' => [$this, 'sanitize_bool_param'],
                    ],
                ],
                'permission_callback' => [$this, 'permission_callback'],
            ]
        );

        register_rest_route(
            $route_namespace,
            '/totals',
            [
                'callback'            => [$this, 'get_totals'],
                'args'                => [
                    'start_date' => [
                        'validate_callback' => [$this, 'validate_date_param'],
                    ],
                    'end_date'   => [
                        'validate_callback' => [$this, 'validate_date_param'],
                    ],
                ],
                'permission_callback' => [$this, 'permission_callback'],
            ]
        );

        register_rest_route(
            $route_namespace,
            '/posts',
            [
                'callback'            => [$this, 'get_posts'],
                'args'                => [
                    'start_date' => [
                        'validate_callback' => [$this, 'validate_date_param'],
                    ],
                    'end_date'   => [
                        'validate_callback' => [$this, 'validate_date_param'],
                    ],
                ],
                'permission_callback' => [$this, 'permission_callback'],
            ]
        );

        register_rest_route(
            $route_namespace,
            '/referrers',
            [
                'callback'            => [$this, 'get_referrers'],
                'args'                => [
                    'start_date' => [
                        'validate_callback' => [$this, 'validate_date_param'],
                    ],
                    'end_date'   => [
                        'validate_callback' => [$this, 'validate_date_param'],
                    ],
                ],
                'permission_callback' => [$this, 'permission_callback'],
            ]
        );

        register_rest_route(
            $route_namespace,
            '/realtime',
            [
                'callback'            => [$this, 'get_realtime_pageview_count'],
                'args'                => [
                    'since' => [
                        'validate_callback' => [$this, 'validate_date_param'],
                    ],
                ],
                'permission_callback' => [$this, 'permission_callback'],
            ]
        );
    }

    public function permission_callback(): bool
    {
        $settings = get_settings();
        $is_dashboard_public = $settings['is_dashboard_public'];
        return $is_dashboard_public || current_user_can('view_koko_analytics');
    }

    private function respond($data): \WP_REST_Response
    {
        return new \WP_REST_Response($data, 200);
    }

    public function validate_date_param($param, $one, $two): bool
    {
        return \strtotime($param) !== false;
    }

    public function sanitize_bool_param($value, $request, $param): bool
    {
        return ! \in_array($value, ['no', 'false', '0'], true);
    }

    /**
     * Returns a daily tally of visitors and pageviews between two dates
     */
    public function get_stats(\WP_REST_Request $request): \WP_REST_Response
    {
        $timezone = wp_timezone();
        $params             = $request->get_query_params();
        $start_date         = $params['start_date'] ?? (new DateTimeImmutable('first day of this month', $timezone))->format('Y-m-d');
        $end_date           = $params['end_date'] ?? (new DateTimeImmutable('now', $timezone))->format('Y-m-d');
        $group = ($params['monthly'] ?? false) ? 'month' : 'day';
        $page = $params['page'] ?? 0;
        $result = (new Stats())->get_stats($start_date, $end_date, $group, $page);
        return $this->respond($result);
    }

    /**
     * Returns the total number of visitos and pageviews between two dates.
     */
    public function get_totals(\WP_REST_Request $request): \WP_REST_Response
    {
        $timezone = wp_timezone();
        $params     = $request->get_query_params();
        $start_date = $params['start_date'] ?? (new DateTimeImmutable('first day of this month', $timezone))->format('Y-m-d');
        $end_date   = $params['end_date'] ?? (new DateTimeImmutable('now', $timezone))->format('Y-m-d');
        $page = $params['page'] ?? 0;
        $result = (new Stats())->get_totals($start_date, $end_date, $page);
        return $this->respond($result);
    }

    /**
     * Returns the total number of pageviews and visitors per post, ordered by most pageviews first.
     */
    public function get_posts(\WP_REST_Request $request): \WP_REST_Response
    {
        $timezone = wp_timezone();
        $params     = $request->get_query_params();
        $start_date = $params['start_date'] ?? (new DateTimeImmutable('first day of this month', $timezone))->format('Y-m-d');
        $end_date   = $params['end_date'] ?? (new DateTimeImmutable('now', $timezone))->format('Y-m-d');
        $offset     = isset($params['offset']) ? absint($params['offset']) : 0;
        $limit      = isset($params['limit']) ? absint($params['limit']) : 10;
        $results = (new Stats())->get_posts($start_date, $end_date, $offset, $limit);
        return $this->respond($results);
    }

    /**
     * Returns the total number of visitors and pageviews per referrer URL, ordered by most pageviews first.
     */
    public function get_referrers(\WP_REST_Request $request): \WP_REST_Response
    {
        $timezone = wp_timezone();
        $params             = $request->get_query_params();
        $start_date         = $params['start_date'] ?? (new DateTimeImmutable('first day of this month', $timezone))->format('Y-m-d');
        $end_date           = $params['end_date'] ?? (new DateTimeImmutable('now', $timezone))->format('Y-m-d');
        $offset             = isset($params['offset']) ? absint($params['offset']) : 0;
        $limit              = isset($params['limit']) ? absint($params['limit']) : 10;
        $results = (new Stats())->get_referrers($start_date, $end_date, $offset, $limit);
        return $this->respond($results);
    }

    /**
     * Returns the total number of recorded pageviews in the last hour
     * @return int|mixed
     */
    public function get_realtime_pageview_count(\WP_REST_Request $request)
    {
        $params = $request->get_query_params();
        return get_realtime_pageview_count($params['since'] ?? null);
    }
}
