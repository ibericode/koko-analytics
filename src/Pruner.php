<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

use wpdb;

class Pruner
{
    protected wpdb $db;

    public function __construct(?wpdb $db = null)
    {
        $this->db = $db ?? $GLOBALS['wpdb'];
    }

    public function run()
    {
        $settings = get_settings();
        if ($settings['prune_data_after_months'] <= 0) {
            return;
        }

        $date = (new \DateTime("-{$settings['prune_data_after_months']} months", wp_timezone()))->format('Y-m-d');

        // delete stats older than date above
        $this->db->query($this->db->prepare("DELETE FROM {$this->db->prefix}koko_analytics_site_stats WHERE date < %s", $date));
        $this->db->query($this->db->prepare("DELETE FROM {$this->db->prefix}koko_analytics_post_stats WHERE date < %s", $date));
        $this->db->query($this->db->prepare("DELETE FROM {$this->db->prefix}koko_analytics_referrer_stats WHERE date < %s", $date));

        $this->delete_orphaned_referrer_urls();
        $this->delete_orphaned_paths();
        $this->delete_blocked_referrers();
    }

    protected function delete_orphaned_referrer_urls(): void
    {
        $this->db->query("DELETE r FROM {$this->db->prefix}koko_analytics_referrer_labels r LEFT JOIN {$this->db->prefix}koko_analytics_referrer_stats s ON s.id = r.id WHERE s.id IS NULL");
    }

    protected function delete_orphaned_paths(): void
    {
        $this->db->query("DELETE p FROM {$this->db->prefix}koko_analytics_paths p LEFT JOIN {$this->db->prefix}koko_analytics_post_stats s ON s.path_id = p.id WHERE s.path_id IS NULL");
    }

    protected function delete_blocked_referrers(): void
    {
        $blocklist = new Blocklist();
        $list = array_merge($blocklist->read(), apply_filters('koko_analytics_referrer_blocklist', []));

        foreach (array_chunk($list, 100) as $chunk) {
            $where = str_repeat("value LIKE %s OR ", count($chunk));
            $where = substr($where, 0, strlen($where) - 4);
            $this->db->query($this->db->prepare("DELETE s, r FROM {$this->db->prefix}koko_analytics_referrer_labels r LEFT JOIN {$this->db->prefix}koko_analytics_referrer_stats s ON s.id = r.id WHERE {$where}", array_map(function ($v) {
                return "%" . $this->db->esc_like($v) . "%";
            }, $chunk)));
        }
    }
}
