<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

use DateTime;
use KokoAnalytics\Normalizers\Normalizer;

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
        [$timestamp, $path, $post_id, $new_visitor, $unique_pageview, $referrer_url] = $params;

        // Ignore entire line (request) if referrer URL is on blocklist
        if ($referrer_url && $this->ignore_referrer_url($referrer_url)) {
            return;
        }

        // Sanity check on $path, it could be coming from an 1.8.x buffer file
        // TODO: Remove in 2.1.x
        if (is_numeric($path)) {
            [$timestamp, $post_id, $new_visitor, $unique_pageview, $referrer_url] = $params;
            $path = parse_url(get_permalink($post_id), PHP_URL_PATH);
        }

        // convert unix timestamp to local datetime
        $dt = new DateTime('', wp_timezone());
        $dt->setTimestamp($timestamp);
        $date_key = $dt->format('Y-m-d');

        if (!isset($this->site_stats[$date_key])) {
            $this->site_stats[$date_key] = ['visitors' => 0, 'pageviews' => 0];
        }

        // update site stats
        $this->site_stats[$date_key]['pageviews'] += 1;
        if ($new_visitor) {
            $this->site_stats[$date_key]['visitors'] += 1;
        }

        // update page stats
        $path = Normalizer::path($path);
        if (!isset($this->post_stats[$date_key])) {
            $this->post_stats[$date_key] = [];
        }
        if (! isset($this->post_stats[$date_key][$path])) {
            $this->post_stats[$date_key][$path] = ['visitors' => 0, 'pageviews' => 0, 'post_id' => $post_id];
        }

        $this->post_stats[$date_key][$path]['pageviews'] += 1;

        if ($unique_pageview) {
            $this->post_stats[$date_key][$path]['visitors'] += 1;
        }

        // increment referrals
        if ($referrer_url) {
            $referrer_url = Normalizer::referrer($referrer_url);
            if ($referrer_url !== '') {
                if (!isset($this->referrer_stats[$date_key])) {
                    $this->referrer_stats[$date_key] = [];
                }

                if (! isset($this->referrer_stats[$date_key][$referrer_url])) {
                    $this->referrer_stats[$date_key][$referrer_url] = ['visitors' => 0, 'pageviews' => 0];
                }

                // increment stats
                $this->referrer_stats[$date_key][$referrer_url]['pageviews'] += 1;
                if ($new_visitor) {
                    $this->referrer_stats[$date_key][$referrer_url]['visitors'] += 1;
                }
            }
        }

        // increment realtime if this pageview is recent enough
        if ($timestamp > \time() - 60 * 60) {
            $key = (string) (\floor($timestamp / 60) * 60);
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
            $sql = $wpdb->prepare("INSERT INTO {$wpdb->prefix}koko_analytics_site_stats(date, visitors, pageviews) VALUES(%s, %d, %d) ON DUPLICATE KEY UPDATE visitors = visitors + VALUES(visitors), pageviews = pageviews + VALUES(pageviews)", [$date, $stats['visitors'], $stats['pageviews']]);
            $wpdb->query($sql);
        }

        $this->site_stats = [];
    }

    private function commit_post_stats(): void
    {
        global $wpdb;

        // insert page-specific stats
        foreach ($this->post_stats as $date => $stats) {
            $path_ids = Path_Repository::upsert(array_keys($stats));
            $values = [];
            foreach ($stats as $path => $r) {
                array_push($values, $date, $path_ids[$path], $r['post_id'], $r['visitors'], $r['pageviews']);
            }
            $placeholders = rtrim(str_repeat('(%s,%d,%d,%d,%d),', count($stats)), ',');
            $wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}koko_analytics_post_stats(date, path_id, post_id, visitors, pageviews) VALUES {$placeholders} ON DUPLICATE KEY UPDATE visitors = visitors + VALUES(visitors), pageviews = pageviews + VALUES(pageviews)", $values));
        }

        $this->post_stats = [];
    }

    private function commit_referrer_stats(): void
    {
        global $wpdb;

        // insert referrer stats
        foreach ($this->referrer_stats as $date => $stats) {
            $referrer_ids = Referrer_Repository::upsert(array_keys($stats));
            $values = [];
            foreach ($stats as $url => $r) {
                array_push($values, $date, $referrer_ids[$url], $r['visitors'], $r['pageviews']);
            }
            $placeholders = rtrim(str_repeat('(%s,%d,%d,%d),', count($stats)), ',');
            $wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}koko_analytics_referrer_stats(date, id, visitors, pageviews) VALUES {$placeholders} ON DUPLICATE KEY UPDATE visitors = visitors + VALUES(visitors), pageviews = pageviews + VALUES(pageviews)", $values));
        }

        $this->referrer_stats = [];
    }

    private function update_realtime_pageview_count(): void
    {
        $counts       = (array) get_option('koko_analytics_realtime_pageview_count', []);

        // remove all data older than 60 minutes
        $one_hour_ago = \time() - 60 * 60;
        foreach ($counts as $timestamp => $unused) {
            // delete all data older than one hour
            if ((int) $timestamp < $one_hour_ago) {
                unset($counts[$timestamp]);
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

    private function ignore_referrer_url($url): bool
    {
        $url = strtolower($url);

        // run custom blocklist first
        // @see https://github.com/ibericode/koko-analytics/blob/main/code-snippets/add-domains-to-referrer-blocklist.php
        $custom_blocklist = apply_filters('koko_analytics_referrer_blocklist', []);
        foreach ($custom_blocklist as $blocklisted_domain) {
            if (false !== strpos($url, $blocklisted_domain)) {
                return true;
            }
        }

        // check community maintained blocklist
        if ((new Blocklist())->contains($url)) {
            return true;
        }

        // run return value through filter so user can apply more advanced logic to determine whether to ignore referrer  url
        // @see https://github.com/ibericode/koko-analytics/blob/main/code-snippets/ignore-some-referrer-traffic-using-regex.php
        return apply_filters('koko_analytics_ignore_referrer_url', false, $url);
    }
}
