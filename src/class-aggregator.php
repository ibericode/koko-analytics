<?php

namespace ZP;

class Aggregator
{
    public function init()
    {
        add_filter('cron_schedules', array($this, 'add_interval'));
        add_action('zp_aggregate_stats', array($this, 'aggregate'));
        add_action('init', array($this, 'schedule'));

        if (isset($_GET['aggregate_stats'])) {
        	$this->aggregate();
		}
    }

    public function add_interval($intervals)
    {
        $intervals['zp_stats_aggregate_interval'] = [
            'interval' => 1 * 60, // 1 minute
            'display'  => __( 'Every minute', 'zero-pageviews' ),
        ];
        return $intervals;
    }

    public function schedule()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' && (!defined('DOING_CRON') || !DOING_CRON)) {
            return;
        }

        if (!wp_next_scheduled('zp_aggregate_stats')) {
            wp_schedule_event(time() + 1, 'zp_stats_aggregate_interval', 'zp_aggregate_stats');
        }
    }

    public function aggregate()
    {
        global $wpdb;

        // read file
		$wp_upload_dir = wp_get_upload_dir();
		$filename = $wp_upload_dir['basedir'] . '/pageviews.php';
		$pageviews = file($filename);
		file_put_contents($filename, '<?php exit; ?>' . PHP_EOL, LOCK_EX);
		array_shift($pageviews); // remove first line

		// add to stats
		$stats = array(
			0 => array(
				'visitors' => 0,
				'pageviews' => 0,
			)
		);

		// TODO: Use timestamp from pageview row
		foreach($pageviews as $p) {
			$p = explode(',', $p);
			$timestamp = $p[0];
			$post_id = (int) $p[1];
			$new_visitor = (int) $p[2];
			$unique_pageview = (int) $p[3];

			if (!isset($stats[$post_id])) {
				$stats[$post_id] = array(
					'visitors' => 0,
					'pageviews' => 0,
				);
			}

			// update site stats
			$stats[0]['pageviews'] += 1;
			if ($new_visitor) {
				$stats[0]['visitors'] += 1;
			}

			// update page stats (if received)
			if ($post_id > 0) {
				$stats[$post_id]['pageviews'] += 1;

				if ($unique_pageview) {
					$stats[$post_id]['visitors'] += 1;
				}
			}
		}

		// bail if nothing happened
		if ($stats[0]['pageviews'] === 0) {
			return;
		}

		$date = date("Y-m-d"); // TODO: Handle UTC offset
		$values = array();
		$placeholders = array();

		foreach($stats as $post_id => $s) {
			$placeholders[] = '(%s, %d, %s, %d, %d)';
			array_push($values, 'post', $post_id, $date, $s['visitors'], $s['pageviews']);
		}

		$placeholders = join(', ', $placeholders);

		// insert or update in a single query
		$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}zp_stats(type, id, date, visitors, pageviews) VALUES {$placeholders} ON DUPLICATE KEY UPDATE visitors = visitors + VALUES(visitors), pageviews = pageviews + VALUES(pageviews)", $values ));
    }

}
