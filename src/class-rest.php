<?php

namespace AAA;

class Rest
{
	public function init()
	{
		add_action( 'rest_api_init', array($this, 'register_routes'));
	}

	function register_routes()
	{
		register_rest_route( 'aaa-stats/v1', '/stats', array(
			'methods' => 'GET',
			'callback' => array($this, 'get_stats'),
			'args' => array(
				'start_date' => array(
					'validate_callback' => array($this, 'validate_date_param')
				),
				'end_date' => array(
					'validate_callback' => array($this, 'validate_date_param')
				),
				'period' => array(
					'default' => 'this_month',
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
	    $post_id = 0;
	    $start_date = isset($params['start_date']) ? $params['start_date'] : date("Y-m-d", strtotime('1st of this month'));
        $end_date = isset($params['end_date']) ? $params['end_date'] : date("Y-m-d");
        $sql = $wpdb->prepare("SELECT date, visitors, pageviews FROM {$wpdb->prefix}aaa_stats s WHERE s.id = %d AND s.date >= %s AND s.date <= %s", [ $post_id, $start_date, $end_date ]);
	    $result = $wpdb->get_results($sql);
		return $result;
	}

}
