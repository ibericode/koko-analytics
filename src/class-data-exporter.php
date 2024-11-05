<?php

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
        $out = fopen('php://output', 'w');

        $this->export_site_stats($out);
        $this->export_post_stats($out);
        $this->export_referrer_urls($out);
        $this->export_referrer_stats($out);

        // TODO: Add hooks so that Pro can write Pro tables

        fclose($out);
        exit;
    }

    private function export_site_stats($stream): void
    {
        $rows = $this->db->get_results("SELECT * FROM {$this->db->prefix}koko_analytics_site_stats;");
        if (!$rows) {
            return;
        }

        fwrite($stream, "INSERT INTO {$this->db->prefix}koko_analytics_site_stats (date, visitors, pageviews) VALUES ");
        foreach ($rows as $i => $s) {
            $prefix = $i === 0 ? '' : ',';
            fwrite($stream, "{$prefix}(\"{$s->date}\", {$s->visitors}, {$s->pageviews})");
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

        fwrite($stream, "INSERT INTO {$this->db->prefix}koko_analytics_post_stats (date, id, visitors, pageviews) VALUES ");
        foreach ($rows as $i => $s) {
            $prefix = $i === 0 ? '' : ',';
            fwrite($stream, "{$prefix}(\"{$s->date}\",{$s->id},{$s->visitors},{$s->pageviews})");
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
        foreach ($rows as $i => $s) {
            $prefix = $i === 0 ? '' : ',';
            fwrite($stream, "{$prefix}({$s->id},\"{$s->url}\")");
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
        foreach ($rows as $i => $s) {
            $prefix = $i === 0 ? '' : ',';
            fwrite($stream, "{$prefix}(\"{$s->date}\",{$s->id},{$s->visitors},{$s->pageviews})");
        }
        fwrite($stream, ";\n");
        unset($rows);
    }
}
