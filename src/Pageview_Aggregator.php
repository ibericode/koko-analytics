<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

use DateTime;
use DateTimeZone;
use KokoAnalytics\Normalizers\Path;
use KokoAnalytics\Normalizers\Referrer;
use wpdb;

class Pageview_Aggregator
{
    protected wpdb $db;
    protected DateTimeZone $timezone;
    protected array $site_stats     = [];
    protected array $post_stats     = [];
    protected array $referrer_stats = [];
    protected array $realtime = [];
    protected Blocklist $blocklist;

    public function __construct(?wpdb $db = null)
    {
        $this->db = $db ?? $GLOBALS['wpdb'];
        $this->timezone = wp_timezone();
        $this->blocklist = new Blocklist();
    }

    public function line(string $type, array $params): void
    {
        // bail if this record doesn't contain data for a pageview
        if ($type !== 'p') {
            return;
        }

        // unpack line
        [$timestamp, $path, $post_id, $new_visitor, $unique_pageview, $referrer_url] = $params;

        // ignore entire line (request) if referrer URL is on blocklist
        if ($this->ignore_referrer_url($referrer_url)) {
            return;
        }

        // convert unix timestamp to local datetime
        $date_key = (new DateTime('', $this->timezone))
            ->setTimestamp($timestamp)
            ->format('Y-m-d');

        // update site stats
        $this->site_stats[$date_key] ??= (object) ['visitors' => 0, 'pageviews' => 0];
        $s = $this->site_stats[$date_key];
        $s->pageviews += 1;
        $s->visitors += (int) $new_visitor;

        // update page stats
        $path = Path::normalize($path);
        $this->post_stats[$date_key] ??= [];
        $this->post_stats[$date_key][$path] ??= (object) ['visitors' => 0, 'pageviews' => 0, 'post_id' => $post_id];
        $p = $this->post_stats[$date_key][$path];
        $p->pageviews += 1;
        $p->visitors += (int) $unique_pageview;

        // update referrer stats
        $referrer_url = Referrer::normalize($referrer_url);
        if ($referrer_url !== '') {
            $this->referrer_stats[$date_key] ??= [];
            $this->referrer_stats[$date_key][$referrer_url] ??= (object) ['visitors' => 0, 'pageviews' => 0];
            $r = $this->referrer_stats[$date_key][$referrer_url];
            $r->pageviews += 1;
            $r->visitors += (int) $new_visitor;
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
        foreach ($this->site_stats as $date => $stats) {
            $this->db->query($this->db->prepare("INSERT INTO {$this->db->prefix}koko_analytics_site_stats(date, visitors, pageviews) VALUES(%s, %d, %d) ON DUPLICATE KEY UPDATE visitors = visitors + VALUES(visitors), pageviews = pageviews + VALUES(pageviews)", [$date, $stats->visitors, $stats->pageviews]));
        }

        $this->site_stats = [];
    }

    private function commit_post_stats(): void
    {
        $upserter = new Upserter('paths', 'path');

        // insert page-specific stats
        foreach ($this->post_stats as $date => $stats) {
            $path_ids = $upserter->upsert(array_keys($stats));
            $values = [];
            foreach ($stats as $path => $r) {
                array_push($values, $date, $path_ids[$path], $r->post_id, $r->visitors, $r->pageviews);
            }
            $placeholders = rtrim(str_repeat('(%s,%d,%d,%d,%d),', count($stats)), ',');
            $this->db->query($this->db->prepare("INSERT INTO {$this->db->prefix}koko_analytics_post_stats(date, path_id, post_id, visitors, pageviews) VALUES {$placeholders} ON DUPLICATE KEY UPDATE visitors = visitors + VALUES(visitors), pageviews = pageviews + VALUES(pageviews)", $values));
        }

        $this->post_stats = [];
    }

    private function commit_referrer_stats(): void
    {
        $upserter = new Upserter('referrer_urls', 'url');

        // insert referrer stats
        foreach ($this->referrer_stats as $date => $stats) {
            $referrer_ids = $upserter->upsert(array_keys($stats));
            $values = [];
            foreach ($stats as $url => $r) {
                array_push($values, $date, $referrer_ids[$url], $r->visitors, $r->pageviews);
            }
            $placeholders = rtrim(str_repeat('(%s,%d,%d,%d),', count($stats)), ',');
            $this->db->query($this->db->prepare("INSERT INTO {$this->db->prefix}koko_analytics_referrer_stats(date, id, visitors, pageviews) VALUES {$placeholders} ON DUPLICATE KEY UPDATE visitors = visitors + VALUES(visitors), pageviews = pageviews + VALUES(pageviews)", $values));
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

    private function ignore_referrer_url(?string $url): bool
    {
        if ($url === null || $url === '') {
            return false;
        }

        $url = strtolower($url);
        if ($this->blocklist->contains($url)) {
            return true;
        }

        // run return value through filter so user can apply more advanced logic to determine whether to ignore referrer  url
        // @see https://github.com/ibericode/koko-analytics/blob/main/code-snippets/ignore-some-referrer-traffic-using-regex.php
        return apply_filters('koko_analytics_ignore_referrer_url', false, $url);
    }
}
