<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

use DateTime;

class Pageview_Aggregator
{
    protected array $site_stats     = [];
    protected array $post_stats     = [];
    protected array $referrer_stats = [];
    protected array $realtime = [];

    public function line(string $type, array $params): void
    {
        // bail if this record doesn't contain data for a pageview
        if ($type !== 'p') {
            return;
        }

        // unpack line
        [$timestamp, $post_id, $new_visitor, $unique_pageview, $referrer_url] = $params;

        // Ignore entire line (request) if referrer URL is on blocklist
        if ($referrer_url !== '' && $this->ignore_referrer_url($referrer_url)) {
            return;
        }

        // convert unix timestamp to local datetime
        $dt = new DateTime('', wp_timezone());
        $dt->setTimestamp($timestamp);
        $date_key = $dt->format('Y-m-d');

        if (!isset($this->site_stats[$date_key])) {
            $this->site_stats[$date_key] = [ 'visitors' => 0, 'pageviews' => 0 ];
        }

        // update site stats
        $this->site_stats[$date_key]['pageviews'] += 1;
        if ($new_visitor) {
            $this->site_stats[$date_key]['visitors'] += 1;
        }

        // update page stats
        $post_id = (string) $post_id;
        if (!isset($this->post_stats[$date_key])) {
            $this->post_stats[$date_key] = [];
        }
        if (! isset($this->post_stats[$date_key][$post_id])) {
            $this->post_stats[$date_key][$post_id] = [ 'visitors' => 0, 'pageviews' => 0 ];
        }

        $this->post_stats[$date_key][$post_id]['pageviews'] += 1;

        if ($unique_pageview) {
            $this->post_stats[$date_key][$post_id]['visitors'] += 1;
        }


        // increment referrals
        if ($referrer_url !== '' && $this->is_valid_url($referrer_url)) {
            $referrer_url = $this->clean_url($referrer_url);
            $referrer_url = $this->normalize_url($referrer_url);

            if (!isset($this->referrer_stats[$date_key])) {
                $this->referrer_stats[$date_key] = [];
            }

            if (! isset($this->referrer_stats[$date_key][$referrer_url])) {
                $this->referrer_stats[$date_key][$referrer_url] = [ 'visitors' => 0, 'pageviews' => 0 ];
            }

            // increment stats
            $this->referrer_stats[$date_key][$referrer_url]['pageviews'] += 1;
            if ($new_visitor) {
                $this->referrer_stats[$date_key][$referrer_url]['visitors'] += 1;
            }
        }

        // increment realtime if this pageview is recent enough
        if ($timestamp > \time() - 60 * 60) {
            $key = (string) (floor($timestamp / 60) * 60);
            $this->realtime[$key] ??= 0;
            $this->realtime[$key]++;
        }
    }

    public function finish(): void
    {
        $this->commit_site_stats();
        $this->commit_post_stats();
        $this->commit_referrer_stats();
        $this->update_realtime_pageview_count();
    }

    private function commit_site_stats(): void
    {
        global $wpdb;

        // insert site stats
        foreach ($this->site_stats as $date => $stats) {
            $sql = $wpdb->prepare("INSERT INTO {$wpdb->prefix}koko_analytics_site_stats(date, visitors, pageviews) VALUES(%s, %d, %d) ON DUPLICATE KEY UPDATE visitors = visitors + VALUES(visitors), pageviews = pageviews + VALUES(pageviews)", [ $date, $stats['visitors'], $stats['pageviews'] ]);
            $wpdb->query($sql);
        }

        $this->site_stats = [];
    }

    private function commit_post_stats(): void
    {
        global $wpdb;

        // insert pathnames
        $pathnames = [];

        // TODO: Maybe strip duplicates here
        foreach ($this->post_stats as $date => $stats) {
            foreach ($stats as $post_id_or_pathname => $s) {
                if (!is_numeric($post_id_or_pathname)) {
                    $pathnames[] = $post_id_or_pathname;
                }
            }
        }
        if (count($pathnames) > 0) {
            $placeholders = \rtrim(\str_repeat('(%s),', \count($pathnames)), ',');
            $query = "INSERT IGNORE INTO {$wpdb->prefix}koko_analytics_paths (path) VALUES {$placeholders}";
            $wpdb->query($wpdb->prepare($query, $pathnames));

            // select pathname ID's
            $placeholders = \rtrim(\str_repeat('%s,', \count($pathnames)), ',');
            $pathnames_map = $wpdb->get_results($wpdb->prepare("SELECT path, id FROM {$wpdb->prefix}koko_analytics_paths WHERE path IN ({$placeholders})", $pathnames), OBJECT_K);
        }

        // insert post stats
        foreach ($this->post_stats as $date => $stats) {
            $values = [];
            foreach ($stats as $post_id_or_pathname => $s) {
                $is_post = is_numeric($post_id_or_pathname);
                $post_or_path_id = $is_post ? $post_id_or_pathname : $pathnames_map[$post_id_or_pathname]->id;
                array_push($values, $date, $is_post ? 'post' : 'path', $post_or_path_id, $s['visitors'], $s['pageviews']);
            }
            $placeholders = rtrim(str_repeat('(%s,%s,%d,%d,%d),', count($stats)), ',');
            $sql          = $wpdb->prepare("INSERT INTO {$wpdb->prefix}koko_analytics_post_stats(date, type, id, visitors, pageviews) VALUES {$placeholders} ON DUPLICATE KEY UPDATE visitors = visitors + VALUES(visitors), pageviews = pageviews + VALUES(pageviews)", $values);
            $wpdb->query($sql);
        }

        $this->post_stats     = [];
    }

    private function commit_referrer_stats(): void
    {
        global $wpdb;

        // insert referrer stats
        foreach ($this->referrer_stats as $date => $stats) {
            // retrieve ID's for known referrer urls
            $referrer_urls = array_keys($stats);
            $placeholders  = rtrim(str_repeat('%s,', count($referrer_urls)), ',');
            $sql           = $wpdb->prepare("SELECT id, url FROM {$wpdb->prefix}koko_analytics_referrer_urls r WHERE r.url IN({$placeholders})", $referrer_urls);
            $results       = $wpdb->get_results($sql);
            foreach ($results as $r) {
                $stats[ $r->url ]['id'] = $r->id;
            }

            // build query for new referrer urls
            $new_referrer_urls = [];
            foreach ($stats as $url => $r) {
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
                    $stats[ $url ]['id'] = $last_insert_id--;
                }
            }

            // insert referrer stats
            $values = [];
            foreach ($stats as $r) {
                array_push($values, $date, $r['id'], $r['visitors'], $r['pageviews']);
            }
            $placeholders = rtrim(str_repeat('(%s,%d,%d,%d),', count($stats)), ',');
            $sql          = $wpdb->prepare("INSERT INTO {$wpdb->prefix}koko_analytics_referrer_stats(date, id, visitors, pageviews) VALUES {$placeholders} ON DUPLICATE KEY UPDATE visitors = visitors + VALUES(visitors), pageviews = pageviews + VALUES(pageviews)", $values);
            $wpdb->query($sql);
        }

        $this->referrer_stats = [];
    }

    private function update_realtime_pageview_count(): void
    {
        $counts       = (array) get_option('koko_analytics_realtime_pageview_count', []);

        // remove all data older than 60 minutes
        $one_hour_ago = \time() - 60 * 60;
        foreach ($counts as $timestamp => $v) {
            // delete all data older than one hour
            if ((int) $timestamp < $one_hour_ago) {
                unset($counts[ $timestamp ]);
            }
        }

        // add latest counts (keyed by the minute)
        foreach ($this->realtime as $timestamp_minute => $count) {
            $counts[$timestamp_minute] ??= 0;
            $counts[$timestamp_minute] += $count;
        }

        update_option('koko_analytics_realtime_pageview_count', $counts, false);

        $this->realtime = [];
    }

    private function ignore_referrer_url(string $url): bool
    {
        $url = strtolower($url);

        // run custom blocklist first
        // @see https://github.com/ibericode/koko-analytics/blob/master/code-snippets/add-domains-to-referrer-blocklist.php
        $custom_blocklist = apply_filters('koko_analytics_referrer_blocklist', []);
        foreach ($custom_blocklist as $blocklisted_domain) {
            if (false !== strpos($url, $blocklisted_domain)) {
                return true;
            }
        }

        // read built-in blocklist file line-by-line to prevent OOM errors
        $fh = fopen(KOKO_ANALYTICS_PLUGIN_DIR . '/data/referrer-blocklist', "r");
        if ($fh) {
            while (($blocklisted_domain = fgets($fh)) !== false) {
                // trim newline and other whitespace
                $blocklisted_domain = rtrim($blocklisted_domain);
                if ($blocklisted_domain === '') {
                    continue;
                }

                // simply check if domain is in referrer string
                if (false !== strpos($url, $blocklisted_domain)) {
                    fclose($fh);
                    return true;
                }
            }

            fclose($fh);
        }

        // run return value through filter so user can apply more advanced logic to determine whether to ignore referrer  url
        // @see https://github.com/ibericode/koko-analytics/blob/master/code-snippets/ignore-some-referrer-traffic-using-regex.php
        return apply_filters('koko_analytics_ignore_referrer_url', false, $url);
    }

    public function clean_url(string $url): string
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

            $params = [];
            parse_str($query_str, $params);

            // strip all but the following query parameters from the URL
            $allowed_params = [ 'page_id', 'p', 'cat', 'product' ];
            $new_params     = array_intersect_key($params, array_flip($allowed_params));
            $new_query_str  = http_build_query($new_params);
            $new_url        = substr($url, 0, $pos + 1) . $new_query_str;

            // trim trailing question mark & replace url with new sanitized url
            $url = rtrim($new_url, '?');
        }

        // limit URL to 255 chars
        // TODO: Maybe limit to just host and TLD?
        if (strlen($url) > 255) {
            $url = substr($url, 0, 255);
        }

        // trim trailing slash if URL has no path component
        $path = parse_url($url, PHP_URL_PATH);
        if ($path === '' || $path === '/') {
            return rtrim($url, '/');
        }

        return $url;
    }

    public function normalize_url(string $url): string
    {
        if ($url === '') {
            return $url;
        }

        // if URL has no protocol, assume HTTP
        // we change this to HTTPS for sites that are known to support it
        if (strpos($url, '://') === false) {
            $url = 'http://' . $url;
        }

        static $aggregations = [
            '/^android-app:\/\/com\.(www\.)?google\.android\.googlequicksearchbox.*/' => 'https://www.google.com',
            '/^android-app:\/\/com\.www\.google\.android\.gm$/' => 'https://www.google.com',
            '/^https?:\/\/(?:www\.)?(google|bing|ecosia)\.([a-z]{2,4}(?:\.[a-z]{2,4})?)(?:\/search|\/url)?/' => 'https://www.$1.$2',
            '/^android-app:\/\/com\.facebook\.(.+)/' => 'https://facebook.com',
            '/^https?:\/\/(?:[a-z-]{1,32}\.)?l?facebook\.com(?:\/l\.php)?/' => 'https://facebook.com',
            '/^https?:\/\/(?:[a-z-]{1,32}\.)?l?instagram\.com(?:\/l\.php)?/' => 'https://www.instagram.com',
            '/^https?:\/\/(?:www\.)?linkedin\.com\/feed.*/' => 'https://www.linkedin.com',
            '/^https?:\/\/(?:www\.)?pinterest\.com/' => 'https://pinterest.com',
            '/^https?:\/\/(?:www|m)\.baidu\.com.*/' => 'https://www.baidu.com',
            '/^https?:\/\/yandex\.ru\/clck.*/' => 'https://yandex.ru',
            '/^https?:\/\/yandex\.ru\/search/' => 'https://yandex.ru',
            '/^https?:\/\/(?:[a-z-]{1,32}\.)?search\.yahoo\.com\/(?:search)?[^?]*(.*)/' => 'https://search.yahoo.com/search$1',
            '/^https?:\/\/(out|new|old|www|m)\.reddit\.com(.*)/' => 'https://reddit.com$2',
            '/^https?:\/\/(?:[a-z0-9]{1,8}\.)+sendib(?:m|t)[0-9]\.com.*/' => 'https://www.brevo.com',
        ];

        $aggregations = apply_filters('koko_analytics_url_aggregations', $aggregations);
        $normalized_url = (string) preg_replace(array_keys($aggregations), array_values($aggregations), $url, 1);
        if (preg_last_error() !== PREG_NO_ERROR) {
            error_log("Koko Analytics: preg_replace error in Pageview_Aggregator::normalize_url('$url'): " . preg_last_error_msg());
            return $url;
        }

        return $normalized_url;
    }

    public function is_valid_url(string $url): bool
    {
        return \strlen($url) >= 7 && \filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}
