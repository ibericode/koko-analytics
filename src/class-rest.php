<?php

namespace KokoAnalytics;

class Rest
{
	public function init()
	{
		add_action( 'rest_api_init', array($this, 'register_routes'));
	}

	function register_routes()
	{
		register_rest_route( 'koko-analytics/v1', '/stats', array(
			'methods' => 'GET',
			'callback' => array($this, 'get_stats'),
			'args' => array(
				'start_date' => array(
					'validate_callback' => array($this, 'validate_date_param')
				),
				'end_date' => array(
					'validate_callback' => array($this, 'validate_date_param')
				),
			),
			'permission_callback' => function () {
				return current_user_can( 'manage_options' );
			}
		));

        register_rest_route( 'koko-analytics/v1', '/posts', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_posts'),
            'args' => array(
                'start_date' => array(
                    'validate_callback' => array($this, 'validate_date_param')
                ),
                'end_date' => array(
                    'validate_callback' => array($this, 'validate_date_param')
                ),
            ),
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            }
        ));

        register_rest_route( 'koko-analytics/v1', '/referrers', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_referrers'),
            'args' => array(
                'start_date' => array(
                    'validate_callback' => array($this, 'validate_date_param')
                ),
                'end_date' => array(
                    'validate_callback' => array($this, 'validate_date_param')
                ),
            ),
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            }
        ));
	}

	public function validate_date_param($param, $one, $two)
    {
        return strtotime($param) !== false;
    }

	public function get_stats(\WP_REST_Request $request)
	{
	    global $wpdb;
	    $params = $request->get_query_params();
	    $start_date = isset($params['start_date']) ? $params['start_date'] : date("Y-m-d", strtotime('1st of this month'));
        $end_date = isset($params['end_date']) ? $params['end_date'] : date("Y-m-d");
        $sql = $wpdb->prepare("SELECT date, visitors, pageviews FROM {$wpdb->prefix}koko_analytics_site_stats s WHERE s.date >= %s AND s.date <= %s", [ $start_date, $end_date ]);
	    $result = $wpdb->get_results($sql);
		return $result;
	}

    public function get_posts(\WP_REST_Request $request)
    {
        global $wpdb;
        $params = $request->get_query_params();
        $start_date = isset($params['start_date']) ? $params['start_date'] : date("Y-m-d", strtotime('1st of this month'));
        $end_date = isset($params['end_date']) ? $params['end_date'] : date("Y-m-d");
        $sql = $wpdb->prepare("SELECT id, SUM(visitors) As visitors, SUM(pageviews) AS pageviews FROM {$wpdb->prefix}koko_analytics_post_stats s WHERE s.date >= %s AND s.date <= %s GROUP BY s.id ORDER BY pageviews DESC LIMIT 0, 10", [ $start_date, $end_date ]);
        $results = $wpdb->get_results($sql);
        if (empty($results)) {
        	return array();
		}

        // create hashmap of found posts
        $ids = wp_list_pluck($results, 'id');
        $q = new \WP_Query;
        $_posts = $q->query(array('posts_per_page' => -1, 'post__in' => $ids, 'post_type' => 'any'));
        $posts = array();
        foreach ($_posts as $p) {
            $posts[$p->ID] = $p;
        }

        // add post title & post link to each result row
        foreach($results as $i => $row) {
        	// skip if post does not exist
        	if (!isset($posts[$row->id])) {
        		continue;
			}

            $post = $posts[$row->id];
            $results[$i]->post_title = $post->post_title;
            $results[$i]->post_permalink = get_permalink($post);
        }

        return $results;
    }

    public function get_referrers(\WP_REST_Request $request)
    {
        global $wpdb;
        $params = $request->get_query_params();
        $start_date = isset($params['start_date']) ? $params['start_date'] : date("Y-m-d", strtotime('1st of this month'));
        $end_date = isset($params['end_date']) ? $params['end_date'] : date("Y-m-d");
        $sql = $wpdb->prepare("SELECT url, SUM(visitors) As visitors, SUM(pageviews) AS pageviews FROM {$wpdb->prefix}koko_analytics_referrer_stats s JOIN {$wpdb->prefix}koko_analytics_referrer_urls r ON r.id = s.id WHERE s.date >= %s AND s.date <= %s GROUP BY s.id ORDER BY pageviews DESC LIMIT 0, 10", [ $start_date, $end_date ]);
        $results = $wpdb->get_results($sql);

        return $results;
    }

}
