<?php

namespace AP;

class Aggregator
{
    public function init()
    {
        add_filter('cron_schedules', array($this, 'add_interval'));
        add_action('ap_aggregate_stats', array($this, 'aggregate'));
        add_action('init', array($this, 'schedule'));

        if (isset($_GET['aggregate_stats'])) {
        	$this->aggregate();
		}
    }

    public function add_interval($intervals)
    {
        $intervals['ap_stats_aggregate_interval'] = array(
            'interval' => 1 * 60, // 1 minute
            'display'  => __('Every minute', 'analytics-plugin'),
        );
        return $intervals;
    }

    public function schedule()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' && (!defined('DOING_CRON') || !DOING_CRON)) {
            return;
        }

        if (!wp_next_scheduled('ap_aggregate_stats')) {
            wp_schedule_event(time() + 1, 'ap_stats_aggregate_interval', 'ap_aggregate_stats');
        }
    }

    public function aggregate()
    {
        global $wpdb;

        // read file
		$wp_upload_dir = wp_get_upload_dir();
		$filename = $wp_upload_dir['basedir'] . '/pageviews.php';

		// read file into array
		$pageviews = file($filename);

		// empty file right away
		file_put_contents($filename, '<?php exit; ?>' . PHP_EOL, LOCK_EX);

		// remove first line (PHP header that prevents direct file access)
		array_shift($pageviews); // remove first line

		// add to stats
		$stats = array(
			0 => array(
				'visitors' => 0,
				'pageviews' => 0,
			)
		);
		$referrers = array();

		foreach($pageviews as $p) {
			$p = explode(',', $p);
			$post_id = (int) $p[0];
			$new_visitor = (int) $p[1];
			$unique_pageview = (int) $p[2];
			$referrer = $p[3];

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

			// increment referrals
			if ($referrer !== '') {
                if (!isset($referrers[$referrer])) {
                    $referrers[$referrer] = array(
                        'pageviews' => 0,
                        'visitors' => 0,
                    );
                }

                $referrers[$referrer]['pageviews'] += 1;
                if ($new_visitor) {
                    $referrers[$referrer]['visitors'] += 1;
                }
            }
		}

		// bail if nothing happened
		if ($stats[0]['pageviews'] === 0) {
			return;
		}

		// store as local date using the timezone specified in WP settings
		$date = gmdate("Y-m-d",time() + get_option('gmt_offset') * HOUR_IN_SECONDS);
		$values = array();
		$placeholders = array();

		foreach($stats as $post_id => $s) {
			$placeholders[] = '(%s, %d, %s, %d, %d)';
			array_push($values, 'post', $post_id, $date, $s['visitors'], $s['pageviews']);
		}

        // TODO: Add table for storing normalized referrers (to cut down on table size for repeating referrer url string every day)
        // TODO: Replace $url with $referrer_id here, and use that for inserting
		foreach($referrers as $url => $r) {
            $placeholders[] = '(%s, %d, %s, %d, %d)';
            array_push($values, 'referrer', 0, $date, $r['visitors'], $r['pageviews']);
        }

		$placeholders = join(', ', $placeholders);

		// insert or update in a single query
        $sql = $wpdb->prepare("INSERT INTO {$wpdb->prefix}ap_stats(type, id, date, visitors, pageviews, value) VALUES {$placeholders} ON DUPLICATE KEY UPDATE visitors = visitors + VALUES(visitors), pageviews = pageviews + VALUES(pageviews)", $values );
		$wpdb->query($sql);
    }

}
