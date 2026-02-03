<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

class Pruner
{
    public function run()
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        $settings = get_settings();
        if ($settings['prune_data_after_months'] <= 0) {
            return;
        }

        $date = (new \DateTime("-{$settings['prune_data_after_months']} months", wp_timezone()))->format('Y-m-d');

        // delete stats older than date above
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}koko_analytics_site_stats WHERE date < %s", $date));
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}koko_analytics_post_stats WHERE date < %s", $date));
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}koko_analytics_referrer_stats WHERE date < %s", $date));

        $this->delete_orphaned_referrer_urls();
        $this->delete_orphaned_paths();
        $this->delete_blocked_referrers();
    }

    protected function delete_orphaned_referrer_urls(): void
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        // delete unused referrer urls
        $results = $wpdb->get_results("SELECT id FROM {$wpdb->prefix}koko_analytics_referrer_urls WHERE id NOT IN (SELECT DISTINCT(id) FROM {$wpdb->prefix}koko_analytics_referrer_stats)");

        // we explicitly delete the rows one-by-one here because the bulk with subquery approach we used before
        // would hang on certain MySQL installations (according to user reports)
        foreach ($results as $r) {
            $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}koko_analytics_referrer_urls WHERE id = %d LIMIT 1", [$r->id]));
        }
    }

    protected function delete_orphaned_paths(): void
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        $results = $wpdb->get_results("SELECT id FROM {$wpdb->prefix}koko_analytics_paths WHERE id NOT IN (SELECT DISTINCT(path_id) FROM {$wpdb->prefix}koko_analytics_post_stats)");

        // we explicitly delete the rows one-by-one here because the bulk with subquery approach we used before
        // would hang on certain MySQL installations (according to user reports)
        foreach ($results as $r) {
            $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}koko_analytics_paths WHERE id = %d LIMIT 1", [$r->id]));
        }
    }

    protected function delete_blocked_referrers(): void
    {
        global $wpdb;

        $blocklist = new Blocklist();
        $list = array_merge($blocklist->read(), apply_filters('koko_analytics_referrer_blocklist', []));
        $count = count($list);

        // process list in batches of 100
        for ($offset = 0; $offset < $count; $offset += 100) {
            $chunk = array_slice($list, $offset, 100);
            $chunk = array_map(function ($v) use ($wpdb) {
                return $wpdb->esc_like("%{$v}%");
            }, $chunk);

            $where = str_repeat("url LIKE %s OR ", count($chunk));
            $where = substr($where, 0, strlen($where) - 4);

            $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}koko_analytics_referrer_urls WHERE {$where}", $chunk));
        }
    }
}
