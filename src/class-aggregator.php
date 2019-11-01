<?php

namespace KokoAnalytics;

class Aggregator
{
    public function init()
    {
        add_filter('cron_schedules', array($this, 'add_interval'));
        add_action('koko_analytics_aggregate_stats', array($this, 'aggregate'));
        add_action('init', array($this, 'maybe_schedule'));
        add_action('init', array($this, 'maybe_aggregate'));
    }

    public function add_interval($intervals)
    {
        $intervals['koko_analytics_stats_aggregate_interval'] = array(
            'interval' => 1 * 60, // 1 minute
            'display'  => __('Every minute', 'koko-analytics'),
        );
        return $intervals;
    }

    public function maybe_schedule()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' && (!defined('DOING_CRON') || !DOING_CRON)) {
            return;
        }

        if (!wp_next_scheduled('koko_analytics_aggregate_stats')) {
            wp_schedule_event(time() + 1, 'koko_analytics_stats_aggregate_interval', 'koko_analytics_aggregate_stats');
        }
    }

    public function maybe_aggregate()
	{
		if (!isset($_GET['koko_analytics_aggregate']) || !current_user_can('manage_options')) {
			return;
		}

		$this->aggregate();
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
            $referrer_url = trim($p[3]);

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
			if ($referrer_url !== '') {
                if (!isset($referrers[$referrer_url])) {
                    $referrers[$referrer_url] = array(
                        'pageviews' => 0,
                        'visitors' => 0,
                    );
                }

                $referrers[$referrer_url]['pageviews'] += 1;
                if ($new_visitor) {
                    $referrers[$referrer_url]['visitors'] += 1;
                }
            }
		}

		// bail if nothing happened
		if ($stats[0]['pageviews'] === 0) {
			return;
		}

		if (count($referrers) > 0) {

		    // retrieve ID's for known referrers
            $referrer_urls = array_keys($referrers);
            $placeholders = array_fill(0, count($referrer_urls), '%s');
            $placeholders = join(',', $placeholders);
            $sql = $wpdb->prepare("SELECT id, url FROM {$wpdb->prefix}koko_analytics_referrers r WHERE r.url IN({$placeholders})", $referrer_urls);
            $results = $wpdb->get_results($sql);
            foreach ($results as $r) {
                $referrers[$r->url]['id'] = $r->id;
            }

            // build query for new referrers
            $placeholders = array();
            $values = array();
            foreach ($referrers as $url => $r) {
                if (! isset($r['id'])) {
                    $placeholders[] = '(%s)';
                    $values[] = $url;
                }
            }

            // insert new referrers and set ID in map
            if (count($values) > 0) {
                // insert new referrer URL's and add ID's to map
                $placeholders = join(',', $placeholders);
                $wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}koko_analytics_referrers(url) VALUES {$placeholders}", $values));
                $last_insert_id = $wpdb->insert_id;
                foreach (array_reverse($values) as $url) {
                    $referrers[$url]['id'] = $last_insert_id--;
                }
            }
        }

        // store as local date using the timezone specified in WP settings
		$date = gmdate('Y-m-d',time() + get_option('gmt_offset') * HOUR_IN_SECONDS);
		$values = array();
		$placeholders = array();

		foreach($stats as $post_id => $s) {
			$placeholders[] = '(%s, %d, %s, %d, %d)';
			array_push($values, 'post', $post_id, $date, $s['visitors'], $s['pageviews']);
		}

		foreach($referrers as $referrer_url => $r) {
            $placeholders[] = '(%s, %d, %s, %d, %d)';
            array_push($values, 'referrer', $r['id'], $date, $r['visitors'], $r['pageviews']);
        }

		$placeholders = join(',', $placeholders);

		// insert or update in a single query
        $sql = $wpdb->prepare("INSERT INTO {$wpdb->prefix}koko_analytics_stats(type, id, date, visitors, pageviews) VALUES {$placeholders} ON DUPLICATE KEY UPDATE visitors = visitors + VALUES(visitors), pageviews = pageviews + VALUES(pageviews)", $values );
		$wpdb->query($sql);
    }

}
