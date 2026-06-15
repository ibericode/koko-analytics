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
    public const MAX_PUBLIC_RANGE_DAYS = 366;

    protected Stats $stats;

    public function __construct()
    {
        $this->stats = new Stats();
    }

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
                        'validate_callback' => [$this, 'validate_since_param'],
                    ],
                ],
                'permission_callback' => [$this, 'permission_callback'],
            ]
        );
    }

    public function permission_callback(): bool
    {
        $settings            = get_settings();
        $is_dashboard_public = $settings['is_dashboard_public'];
        return $is_dashboard_public || current_user_can('view_koko_analytics');
    }

    private function respond($data): \WP_REST_Response
    {
        return new \WP_REST_Response($data, 200);
    }

    public function validate_date_param($param, $one, $two): bool
    {
        if (! is_string($param) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $param)) {
            return false;
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $param, wp_timezone());
        return $date instanceof DateTimeImmutable && $date->format('Y-m-d') === $param;
    }

    public function validate_since_param($param, $one, $two): bool
    {
        return is_string($param) && \strtotime($param) !== false;
    }

    public function sanitize_bool_param($value, $request, $param): bool
    {
        return ! \in_array($value, ['no', 'false', '0'], true);
    }

    /**
     * @return array{0: string, 1: string}|\WP_Error
     */
    private function get_date_range(\WP_REST_Request $request)
    {
        $timezone   = wp_timezone();
        $params     = $request->get_query_params();
        $start_date = $params['start_date'] ?? (new DateTimeImmutable('first day of this month', $timezone))->format('Y-m-d');
        $end_date   = $params['end_date'] ?? (new DateTimeImmutable('now', $timezone))->format('Y-m-d');
        $start      = new DateTimeImmutable($start_date, $timezone);
        $end        = new DateTimeImmutable($end_date, $timezone);

        if ($start > $end) {
            return new \WP_Error('invalid_date_range', __('start_date must be before or equal to end_date.', 'koko-analytics'), ['status' => 400]);
        }

        if (! current_user_can('view_koko_analytics') && $start->diff($end)->days > self::MAX_PUBLIC_RANGE_DAYS) {
            return new \WP_Error('date_range_too_large', __('Date range is too large.', 'koko-analytics'), ['status' => 400]);
        }

        return [$start_date, $end_date];
    }

    /**
     * Returns a daily tally of visitors and pageviews between two dates
     */
    public function get_stats(\WP_REST_Request $request)
    {
        $params = $request->get_query_params();
        $range  = $this->get_date_range($request);
        if (is_wp_error($range)) {
            return $range;
        }

        [$start_date, $end_date] = $range;
        $group                   = ($params['monthly'] ?? false) ? 'month' : 'day';
        $page                    = $params['page'] ?? 0;
        $result                  = $this->stats->get_stats($start_date, $end_date, $group, $page);
        return $this->respond($result);
    }

    /**
     * Returns the total number of visitos and pageviews between two dates.
     */
    public function get_totals(\WP_REST_Request $request)
    {
        $params = $request->get_query_params();
        $range  = $this->get_date_range($request);
        if (is_wp_error($range)) {
            return $range;
        }

        [$start_date, $end_date] = $range;
        $page                    = $params['page'] ?? 0;
        $result                  = $this->stats->get_totals($start_date, $end_date, $page);
        return $this->respond($result);
    }

    /**
     * Returns the total number of pageviews and visitors per post, ordered by most pageviews first.
     */
    public function get_posts(\WP_REST_Request $request)
    {
        $params = $request->get_query_params();
        $range  = $this->get_date_range($request);
        if (is_wp_error($range)) {
            return $range;
        }

        [$start_date, $end_date] = $range;
        $offset                  = Dashboard::clamp_offset($params['offset'] ?? null);
        $limit                   = Dashboard::clamp_limit($params['limit'] ?? null);
        $results                 = $this->stats->get_posts($start_date, $end_date, $offset, $limit);
        return $this->respond($results);
    }

    /**
     * Returns the total number of visitors and pageviews per referrer URL, ordered by most pageviews first.
     */
    public function get_referrers(\WP_REST_Request $request)
    {
        $params = $request->get_query_params();
        $range  = $this->get_date_range($request);
        if (is_wp_error($range)) {
            return $range;
        }

        [$start_date, $end_date] = $range;
        $offset                  = Dashboard::clamp_offset($params['offset'] ?? null);
        $limit                   = Dashboard::clamp_limit($params['limit'] ?? null);
        $results                 = $this->stats->get_referrers($start_date, $end_date, $offset, $limit);
        return $this->respond($results);
    }

    /**
     * Returns the total number of recorded pageviews in the last hour
     *
     * @return int|mixed
     */
    public function get_realtime_pageview_count(\WP_REST_Request $request)
    {
        $params = $request->get_query_params();
        return get_realtime_pageview_count($params['since'] ?? null);
    }
}
