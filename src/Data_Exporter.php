<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

class Data_Exporter
{
    private $db;

    public function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
    }

    public function run(): void
    {
        // write to HTTP stream
        $date = create_local_datetime('now')->format("Y-m-d");
        header('Content-Type: application/sql');
        header("Content-Disposition: attachment;filename=koko-analytics-export-{$date}.sql");
        $fh = fopen('php://output', 'w');

        $this->export_site_stats($fh);
        $this->export_paths($fh);
        $this->export_post_stats($fh);
        $this->export_referrer_urls($fh);
        $this->export_referrer_stats($fh);

        do_action('koko_analytics_write_data_export', $fh);

        fclose($fh);
        exit;
    }

    private function export_site_stats($stream): void
    {
        $rows = $this->db->get_results("SELECT * FROM {$this->db->prefix}koko_analytics_site_stats;");
        if (!$rows) {
            return;
        }

        fwrite($stream, "INSERT INTO {$this->db->prefix}koko_analytics_site_stats (date, visitors, pageviews) VALUES ");
        $prefix = '';
        foreach ($rows as $s) {
            fwrite($stream, "{$prefix}(\"{$s->date}\",{$s->visitors},{$s->pageviews})");
            $prefix = ',';
        }
        fwrite($stream, ";\n");
        unset($rows);
    }

    private function export_paths($stream): void
    {
        $rows = $this->db->get_results("SELECT * FROM {$this->db->prefix}koko_analytics_paths;");
        if (!$rows) {
            return;
        }

        fwrite($stream, "INSERT INTO {$this->db->prefix}koko_analytics_paths (id, path) VALUES ");
        $prefix = '';
        foreach ($rows as $s) {
            fwrite($stream, "{$prefix}({$s->id},\"{$s->path}\")");
            $prefix = ',';
        }
        fwrite($stream, ";\n");
        unset($rows);
    }

    private function export_post_stats($stream): void
    {
        $rows = $this->db->get_results("SELECT * FROM {$this->db->prefix}koko_analytics_post_stats;");
        if (!$rows) {
            return;
        }

        fwrite($stream, "INSERT INTO {$this->db->prefix}koko_analytics_post_stats (date, path_id, post_id, visitors, pageviews) VALUES ");
        $prefix = '';
        foreach ($rows as $s) {
            fwrite($stream, "{$prefix}(\"{$s->date}\",{$s->path_id},{$s->post_id},{$s->visitors},{$s->pageviews})");
            $prefix = ',';
        }
        fwrite($stream, ";\n");
        unset($rows);
    }

    private function export_referrer_urls($stream): void
    {
        $rows = $this->db->get_results("SELECT * FROM {$this->db->prefix}koko_analytics_referrer_urls;");
        if (!$rows) {
            return;
        }

        fwrite($stream, "INSERT INTO {$this->db->prefix}koko_analytics_referrer_urls (id, url) VALUES ");
        $prefix = '';
        foreach ($rows as $s) {
            fwrite($stream, "{$prefix}({$s->id},\"{$s->url}\")");
            $prefix = ',';
        }
        fwrite($stream, ";\n");
        unset($rows);
    }

    private function export_referrer_stats($stream): void
    {
        $rows = $this->db->get_results("SELECT * FROM {$this->db->prefix}koko_analytics_referrer_stats;");
        if (!$rows) {
            return;
        }

        fwrite($stream, "INSERT INTO {$this->db->prefix}koko_analytics_referrer_stats (date, id, visitors, pageviews) VALUES ");
        $prefix = '';
        foreach ($rows as $s) {
            fwrite($stream, "{$prefix}(\"{$s->date}\",{$s->id},{$s->visitors},{$s->pageviews})");
            $prefix = ',';
        }
        fwrite($stream, ";\n");
        unset($rows);
    }
}
