<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

class Pageview_Aggregator
{
    protected $site_stats     = array(
        'visitors' => 0,
        'pageviews' => 0,
    );
    protected $post_stats     = array();
    protected $referrer_stats = array();

    public function line(string $type, array $params)
    {
        // bail if this record doesn't contain data for a pageview
        if ($type !== 'p') {
            return;
        }

        $post_id         = (int) $params[0];
        $new_visitor     = (int) $params[1];
        $unique_pageview = (int) $params[2];
        $referrer_url    = trim((string) $params[3]);

        // Ignore entire line (request) if referrer URL is on blocklist
        if ($referrer_url !== '' && $this->ignore_referrer_url($referrer_url)) {
            return;
        }

        // update site stats
        $this->site_stats['pageviews'] += 1;
        if ($new_visitor) {
            $this->site_stats['visitors'] += 1;
        }

        // update page stats (if received)
        if ($post_id >= 0) {
            if (! isset($this->post_stats[ $post_id ])) {
                $this->post_stats[ $post_id ] = array(
                    'visitors'  => 0,
                    'pageviews' => 0,
                );
            }

            $this->post_stats[ $post_id ]['pageviews'] += 1;

            if ($unique_pageview) {
                $this->post_stats[ $post_id ]['visitors'] += 1;
            }
        }

        // increment referrals
        if ($referrer_url !== '') {
            $referrer_url = $this->clean_url($referrer_url);
            $referrer_url = $this->normalize_url($referrer_url);

            if ($this->is_valid_url($referrer_url)) {
                // add to map
                if (! isset($this->referrer_stats[ $referrer_url ])) {
                    $this->referrer_stats[ $referrer_url ] = array(
                        'pageviews' => 0,
                        'visitors'  => 0,
                    );
                }

                // increment stats
                $this->referrer_stats[ $referrer_url ]['pageviews'] += 1;
                if ($new_visitor) {
                    $this->referrer_stats[ $referrer_url ]['visitors'] += 1;
                }
            }
        }
    }

    public function finish()
    {
        global $wpdb;

        // bail if nothing happened
        if ($this->site_stats['pageviews'] === 0) {
            return;
        }

        // store as local date using the timezone specified in WP settings
        $date = create_local_datetime('now')->format('Y-m-d');

        // insert site stats
        $sql = $wpdb->prepare("INSERT INTO {$wpdb->prefix}koko_analytics_site_stats(date, visitors, pageviews) VALUES(%s, %d, %d) ON DUPLICATE KEY UPDATE visitors = visitors + VALUES(visitors), pageviews = pageviews + VALUES(pageviews)", array( $date, $this->site_stats['visitors'], $this->site_stats['pageviews'] ));
        $wpdb->query($sql);

        // insert post stats
        if (count($this->post_stats) > 0) {
            $values = array();
            foreach ($this->post_stats as $post_id => $s) {
                array_push($values, $date, $post_id, $s['visitors'], $s['pageviews']);
            }
            $placeholders = rtrim(str_repeat('(%s,%d,%d,%d),', count($this->post_stats)), ',');
            $sql          = $wpdb->prepare("INSERT INTO {$wpdb->prefix}koko_analytics_post_stats(date, id, visitors, pageviews) VALUES {$placeholders} ON DUPLICATE KEY UPDATE visitors = visitors + VALUES(visitors), pageviews = pageviews + VALUES(pageviews)", $values);
            $wpdb->query($sql);
        }

        if (count($this->referrer_stats) > 0) {
            // retrieve ID's for known referrer urls
            $referrer_urls = array_keys($this->referrer_stats);
            $placeholders  = rtrim(str_repeat('%s,', count($referrer_urls)), ',');
            $sql           = $wpdb->prepare("SELECT id, url FROM {$wpdb->prefix}koko_analytics_referrer_urls r WHERE r.url IN({$placeholders})", $referrer_urls);
            $results       = $wpdb->get_results($sql);
            foreach ($results as $r) {
                $this->referrer_stats[ $r->url ]['id'] = $r->id;
            }

            // build query for new referrer urls
            $new_referrer_urls = array();
            foreach ($this->referrer_stats as $url => $r) {
                if (! isset($r['id'])) {
                    $new_referrer_urls[] = $url;
                }
            }

            // insert new referrer urls and set ID in map
            if (count($new_referrer_urls) > 0) {
                $values       = $new_referrer_urls;
                $placeholders = rtrim(str_repeat('(%s),', count($values)), ',');
                $sql          = $wpdb->prepare("INSERT INTO {$wpdb->prefix}koko_analytics_referrer_urls(url) VALUES {$placeholders}", $values);
                $wpdb->query($sql);
                $last_insert_id = $wpdb->insert_id;
                foreach (array_reverse($values) as $url) {
                    $this->referrer_stats[ $url ]['id'] = $last_insert_id--;
                }
            }

            // insert referrer stats
            $values = array();
            foreach ($this->referrer_stats as $referrer_url => $r) {
                array_push($values, $date, $r['id'], $r['visitors'], $r['pageviews']);
            }
            $placeholders = rtrim(str_repeat('(%s,%d,%d,%d),', count($this->referrer_stats)), ',');
            $sql          = $wpdb->prepare("INSERT INTO {$wpdb->prefix}koko_analytics_referrer_stats(date, id, visitors, pageviews) VALUES {$placeholders} ON DUPLICATE KEY UPDATE visitors = visitors + VALUES(visitors), pageviews = pageviews + VALUES(pageviews)", $values);
            $wpdb->query($sql);
        }

        $this->update_realtime_pageview_count($this->site_stats['pageviews']);

        // reset properties in case aggregation runs again in current request lifecycle
        $this->site_stats = array(
            'visitors' => 0,
            'pageviews' => 0,
        );
        $this->referrer_stats = array();
        $this->post_stats     = array();
    }

    private function update_realtime_pageview_count(int $pageviews)
    {
        $counts       = (array) get_option('koko_analytics_realtime_pageview_count', array());
        $one_hour_ago = strtotime('-60 minutes');

        foreach ($counts as $timestamp => $count) {
            // delete all data older than one hour
            if ((int) $timestamp < $one_hour_ago) {
                unset($counts[ $timestamp ]);
            }
        }

        // add pageviews for this minute
        $counts[ (string) time() ] = $pageviews;
        update_option('koko_analytics_realtime_pageview_count', $counts, false);
    }

    private function ignore_referrer_url(string $url)
    {
        // read blocklist into array
        static $blocklist = null;
        if ($blocklist === null) {
            $blocklist = file(KOKO_ANALYTICS_PLUGIN_DIR . '/data/referrer-blocklist', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            // add result of filter hook to blocklist so user can provide custom domains to block through simple array
            // @see https://github.com/ibericode/koko-analytics/blob/master/code-snippets/add-domains-to-referrer-blocklist.php
            $custom_blocklist = apply_filters('koko_analytics_referrer_blocklist', array());
            $blocklist        = array_merge($blocklist, $custom_blocklist);
        }

        foreach ($blocklist as $blocklisted_domain) {
            if (false !== stripos($url, $blocklisted_domain)) {
                return true;
            }
        }

        // run return value through filter so user can apply more advanced logic to determine whether to ignore referrer  url
        // @see https://github.com/ibericode/koko-analytics/blob/master/code-snippets/ignore-some-referrer-traffic-using-regex.php
        return apply_filters('koko_analytics_ignore_referrer_url', false, $url);
    }

    public function clean_url(string $url)
    {
        if ($url === '') {
            return $url;
        }

        // remove # from URL
        $pos = strpos($url, '#');
        if ($pos !== false) {
            $url = substr($url, 0, $pos);
        }

        // if URL contains query string, parse it and only keep certain parameters
        $pos = strpos($url, '?');
        if ($pos !== false) {
            $query_str = substr($url, $pos + 1);

            $params = array();
            parse_str($query_str, $params);

            // strip all but the following query parameters from the URL
            $allowed_params = array( 'page_id', 'p', 'cat', 'product' );
            $new_params     = array_intersect_key($params, array_flip($allowed_params));
            $new_query_str  = http_build_query($new_params);
            $new_url        = substr($url, 0, $pos + 1) . $new_query_str;

            // trim trailing question mark & replace url with new sanitized url
            $url = rtrim($new_url, '?');
        }

      // trim trailing slash if URL has no path component
        $path = parse_url($url, PHP_URL_PATH);
        if ($path === '' || $path === '/') {
            return rtrim($url, '/');
        }

        return $url;
    }

    public function normalize_url(string $url)
    {
        if ($url === '') {
            return $url;
        }

        // if URL has no protocol, assume HTTP
        // we change this to HTTPS for sites that are known to support it
        if (strpos($url, '://') === false) {
            $url = 'http://' . $url;
        }

        static $aggregations = array(
            '/^android-app:\/\/com\.(www\.)?google\.android\.googlequicksearchbox(\/.+)?$/' => 'https://www.google.com',
            '/^android-app:\/\/com\.www\.google\.android\.gm$/' => 'https://www.google.com',
            '/^https?:\/\/(?:www\.)?(google|bing|ecosia)\.([a-z]{2,3}(?:\.[a-z]{2,3})?)(?:\/search|\/url)?/' => 'https://www.$1.$2',
            '/^android-app:\/\/com\.facebook\.(.+)/' => 'https://facebook.com',
            '/^https?:\/\/(?:[a-z-]+)?\.?l?facebook\.com(?:\/l\.php)?/' => 'https://facebook.com',
            '/^https?:\/\/(?:[a-z-]+)?\.?l?instagram\.com(?:\/l\.php)?/' => 'https://www.instagram.com',
            '/^https?:\/\/(?:www\.)?linkedin\.com\/feed.*/' => 'https://www.linkedin.com',
            '/^https?:\/\/(?:www\.)?pinterest\.com/' => 'https://pinterest.com',
            '/(?:www|m)\.baidu\.com.*/' => 'www.baidu.com',
            '/^https?:\/\/yandex\.ru\/clck.*/' => 'https://yandex.ru',
            '/^https?:\/\/yandex\.ru\/search/' => 'https://yandex.ru',
            '/^https?:\/\/(?:[a-z-]+)?\.?search\.yahoo\.com\/(?:search)?[^?]*(.*)/' => 'https://search.yahoo.com/search$1',
            '/^https?:\/\/(out|new|old)\.reddit\.com(.*)/' => 'https://reddit.com$2',
            '/^https?:\/\/(?:[a-z0-9]+\.?)*\.sendib(?:m|t)[0-9].com(?:.*)/' => 'https://www.brevo.com',
        );

        $aggregations = apply_filters('koko_analytics_url_aggregations', $aggregations);
        return preg_replace(array_keys($aggregations), array_values($aggregations), $url, 1);
    }

    public function is_valid_url(string $url)
    {
        if ($url === '' || strlen($url) < 4) {
            return false;
        }

        return filter_var($url, FILTER_VALIDATE_URL);
    }
}
