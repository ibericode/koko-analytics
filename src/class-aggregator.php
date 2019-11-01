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
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
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
		$pageviews = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

		// empty file right away
		file_put_contents($filename, '<?php exit; ?>' . PHP_EOL, LOCK_EX);

		// remove first line (PHP header that prevents direct file access)
		array_shift($pageviews); // remove first line

		// combine stats for each table
        $site_stats = array(
            'visitors' => 0,
            'pageviews' => 0,
        );
        $post_stats = array();
		$referrer_stats = array();

		// read blacklist into array
		$blacklist = $list = file(KOKO_ANALYTICS_PLUGIN_DIR . '/data/referrer-blacklist', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

		foreach($pageviews as $p) {
			$p = explode(',', $p);
			$post_id = (int) $p[0];
			$new_visitor = (int) $p[1];
			$unique_pageview = (int) $p[2];
            $referrer_url = trim($p[3]);

			// update site stats
            $site_stats['pageviews'] += 1;
			if ($new_visitor) {
                $site_stats['visitors'] += 1;
			}

			// update page stats (if received)
			if ($post_id > 0) {
                if (!isset($post_stats[$post_id])) {
                    $post_stats[$post_id] = array(
                        'visitors' => 0,
                        'pageviews' => 0,
                    );
                }

                $post_stats[$post_id]['pageviews'] += 1;

				if ($unique_pageview) {
                    $post_stats[$post_id]['visitors'] += 1;
				}
			}

			// increment referrals
			if ($referrer_url !== '' && ! $this->in_blacklist($referrer_url, $blacklist)) {
                if (!isset($referrer_stats[$referrer_url])) {
                    $referrer_stats[$referrer_url] = array(
                        'pageviews' => 0,
                        'visitors' => 0,
                    );
                }

                $referrer_stats[$referrer_url]['pageviews'] += 1;
                if ($new_visitor) {
                    $referrer_stats[$referrer_url]['visitors'] += 1;
                }
            }
		}

		// bail if nothing happened
		if ($site_stats['pageviews'] === 0) {
			return;
		}

		if (count($referrer_stats) > 0) {

		    // retrieve ID's for known referrers
            $referrer_urls = array_keys($referrer_stats);
            $placeholders = array_fill(0, count($referrer_urls), '%s');
            $placeholders = join(',', $placeholders);
            $sql = $wpdb->prepare("SELECT id, url FROM {$wpdb->prefix}koko_analytics_referrer_urls r WHERE r.url IN({$placeholders})", $referrer_urls);
            $results = $wpdb->get_results($sql);
            foreach ($results as $r) {
                $referrer_stats[$r->url]['id'] = $r->id;
            }

            // build query for new referrers
            $placeholders = array();
            $values = array();
            foreach ($referrer_stats as $url => $r) {
                if (! isset($r['id'])) {
                    $placeholders[] = '(%s)';
                    $values[] = $url;
                }
            }

            // insert new referrers and set ID in map
            if (count($values) > 0) {
                // insert new referrer URL's and add ID's to map
                $placeholders = join(',', $placeholders);
                $sql = $wpdb->prepare("INSERT INTO {$wpdb->prefix}koko_analytics_referrer_urls(url) VALUES {$placeholders}", $values);
                $wpdb->query($sql);
                $last_insert_id = $wpdb->insert_id;
                foreach (array_reverse($values) as $url) {
                    $referrer_stats[$url]['id'] = $last_insert_id--;
                }
            }
        }

        // store as local date using the timezone specified in WP settings
		$date = gmdate('Y-m-d',time() + get_option('gmt_offset') * HOUR_IN_SECONDS);

		// insert site stats
        $sql = $wpdb->prepare("INSERT INTO {$wpdb->prefix}koko_analytics_site_stats(date, visitors, pageviews) VALUES(%s, %d, %d) ON DUPLICATE KEY UPDATE visitors = visitors + VALUES(visitors), pageviews = pageviews + VALUES(pageviews)", array($date, $site_stats['visitors'], $site_stats['pageviews']));
        $wpdb->query($sql);

        // insert post stats
        if (count($post_stats) > 0) {
            $values = array();
            $placeholders = array();
            foreach ($post_stats as $post_id => $s) {
                $placeholders[] = '(%s, %d, %d, %d)';
                array_push($values, $date, $post_id, $s['visitors'], $s['pageviews']);
            }
            $placeholders = join(',', $placeholders);
            $sql = $wpdb->prepare("INSERT INTO {$wpdb->prefix}koko_analytics_post_stats(date, id, visitors, pageviews) VALUES {$placeholders} ON DUPLICATE KEY UPDATE visitors = visitors + VALUES(visitors), pageviews = pageviews + VALUES(pageviews)", $values);
            $wpdb->query($sql);
        }

        // insert referrer stats
        if (count($referrer_stats) > 0) {
            $values = array();
            $placeholders = array();
            foreach ($referrer_stats as $referrer_url => $r) {
                $placeholders[] = '(%s, %d, %d, %d)';
                array_push($values, $date, $r['id'], $r['visitors'], $r['pageviews']);
            }
            $placeholders = join(',', $placeholders);
            $sql = $wpdb->prepare("INSERT INTO {$wpdb->prefix}koko_analytics_referrer_stats(date, id, visitors, pageviews) VALUES {$placeholders} ON DUPLICATE KEY UPDATE visitors = visitors + VALUES(visitors), pageviews = pageviews + VALUES(pageviews)", $values);
            $wpdb->query($sql);
        }
    }

    private function in_blacklist($url, $blacklist)
	{
		foreach ($blacklist as $blacklisted_domain) {
			if (false !== stripos($url, $blacklisted_domain)) {
				return true;
			}
		}

		return false;
	}

}
