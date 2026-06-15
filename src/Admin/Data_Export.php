<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics\Admin;

use DateTimeImmutable;

class Data_Export
{
    /** @var \wpdb */
    private $db;

    public function __construct()
    {
        $this->db = $GLOBALS['wpdb'];
    }

    public function action_listener(): void
    {
        if (!current_user_can('manage_koko_analytics') || ! check_admin_referer('koko_analytics_export_data')) {
            return;
        }

        $this->run();
    }

    public function run(): void
    {
        $date     = (new DateTimeImmutable('now', wp_timezone()))->format('Y-m-d');
        $site_url = parse_url(get_site_url(), PHP_URL_HOST);

        header('Content-Type: application/x-ndjson');
        header("Content-Disposition: attachment;filename={$site_url}-koko-analytics-export-{$date}.ndjson");
        $fh = fopen('php://output', 'w');
        foreach (Data_Transfer_Tables::get() as $table => $spec) {
            $this->export_table($fh, $table, $spec['columns']);
        }
        fclose($fh);
        exit;
    }

    /**
     * @param resource $stream
     * @param string[] $columns
     */
    private function export_table($stream, string $table, array $columns): void
    {
        $this->write_json_line($stream, [
            'table' => $table,
            'columns' => $columns,
        ]);

        $prefixed_table = $this->db->prefix . $table;
        $column_sql     = implode(', ', array_map(static function (string $column): string {
            return '`' . $column . '`';
        }, $columns));
        $offset         = 0;
        $limit          = Data_Transfer_Tables::BATCH_SIZE;

        do {
            $rows = $this->db->get_results(
                $this->db->prepare("SELECT {$column_sql} FROM {$prefixed_table} LIMIT %d OFFSET %d", [$limit, $offset]),
                ARRAY_N
            );

            if (! is_array($rows)) {
                $rows = [];
            } elseif ($rows) {
                $this->write_json_line($stream, $rows);
            }

            $offset += $limit;
        } while (count($rows) === $limit);
    }

    /**
     * @param resource $stream
     * @param mixed $data
     */
    private function write_json_line($stream, $data): void
    {
        fwrite($stream, (string) wp_json_encode($data));
        fwrite($stream, "\n");
    }
}
