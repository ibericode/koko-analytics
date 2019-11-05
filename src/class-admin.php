<?php

namespace KokoAnalytics;

class Admin {

    public function init()
    {
        add_action('init', array($this, 'maybe_run_migrations'));
        add_action('init', array($this, 'maybe_seed'));
        add_action('admin_menu', array($this, 'register_menu'));
    }

    public function register_menu()
    {
        add_submenu_page('index.php', __('Koko Analytics', 'koko-analytics'), __('Analytics', 'koko-analytics'), 'manage_options', 'koko-analytics', array($this, 'show_page'));
    }

    public function show_page()
    {
        $user_roles = array();
        foreach (wp_roles()->roles as $key => $role) {
            $user_roles[$key] = $role['name'];
        }

        $start_of_week = (int) get_option('start_of_week');
        $settings = get_settings();

        require KOKO_ANALYTICS_PLUGIN_DIR . '/views/admin-page.php';
    }

    public function maybe_run_migrations()
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        $from = isset($_GET['koko_analytics_migrate_from_version']) ? $_GET['koko_analytics_migrate_from_version'] : get_option('koko_analytics_version', '0.0.1');
        if (version_compare($from, KOKO_ANALYTICS_VERSION, '>=')) {
            return;
        }

        $migrations = new Migrations($from, KOKO_ANALYTICS_VERSION, KOKO_ANALYTICS_PLUGIN_DIR . '/migrations/');
        $migrations->run();
        update_option('koko_analytics_version', KOKO_ANALYTICS_VERSION);
    }

    public function maybe_seed()
    {
        global $wpdb;

        if (!isset($_GET['koko_analytics_seed']) || !current_user_can('manage_options')) {
            return;
        }

        $wpdb->suppress_errors(true);

        $query = new \WP_Query();
        $posts = $query->query(array(
        	'posts_per_page' => 12,
			'post_type' => 'any',
			'post_status' => 'publish',
		));
        $post_count = count($posts);
        $referrer_urls = array();
        foreach (array(
					 'https://www.wordpress.org/',
					 'https://www.wordpress.org/plugins/koko-analytics',
					 'https://www.ibericode.com/',
					 'https://duckduckgo.com/',
					 'https://www.mozilla.org/',
					 'https://www.eff.org/',
					 'https://letsencrypt.org/',
					 'https://dannyvankooten.com/',
					 'https://github.com/ibericode/koko-analytics',
					 'https://lobste.rs/',
					 'https://joinmastodon.org/',
					 'https://www.php.net/',
					 'https://mariadb.org/',
				 ) as $url) {
			$wpdb->insert($wpdb->prefix . 'koko_analytics_referrer_urls', array(
				'url' => $url,
			));
			$referrer_urls[$wpdb->insert_id] = $url;
		}
        $referrer_count = count($referrer_urls);

        $n = 3*365;
        for ($i = 0; $i < $n; $i++) {
            $date = date("Y-m-d", strtotime(sprintf('-%d days', $i)));
            $pageviews = rand(500, 1000) / $n * ($n-$i) ;
            $visitors = $pageviews * rand(2, 6) / 10;

            $wpdb->insert($wpdb->prefix . 'koko_analytics_site_stats', array(
               'date' => $date,
               'pageviews' => $pageviews,
               'visitors' => $visitors,
			));

            foreach ($posts as $post) {
				$wpdb->insert($wpdb->prefix . 'koko_analytics_post_stats', array(
					'date' => $date,
					'id' => $post->ID,
					'pageviews' => round($pageviews / $post_count * rand(5, 15) / 10 ),
					'visitors' => round($visitors / $post_count * rand(5, 15) / 10),
				));
			}

            foreach ($referrer_urls as $id => $referrer_url) {
				$wpdb->insert($wpdb->prefix . 'koko_analytics_referrer_stats', array(
					'date' => $date,
					'id' => $id,
					'pageviews' => round($pageviews / $referrer_count * rand(5, 15) / 10),
					'visitors' => round($visitors / $referrer_count * rand(5, 15) / 10)
				));
			}
        }
    }
}
