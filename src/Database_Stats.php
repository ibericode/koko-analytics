<?php

namespace KokoAnalytics;

use wpdb;

class Database_Stats
{
    protected wpdb $db;

    public function __construct(?wpdb $db = null)
    {
        $this->db = $db ?? $GLOBALS['wpdb'];
    }

    /**
     * @return array{tables: array<int, array{name: string, rows: int, data_size: int, index_size: int, total_size: int}>, total_rows: int, total_size: int}
     */
    public function get(): array
    {
        $tables = $this->get_tables();

        return [
            'tables' => $tables,
            'total_rows' => (int) array_sum(array_column($tables, 'rows')),
            'total_size' => (int) array_sum(array_column($tables, 'total_size')),
        ];
    }

    /**
     * @return array<int, array{name: string, rows: int, data_size: int, index_size: int, total_size: int}>
     */
    protected function get_tables(): array
    {
        $results = $this->db->get_results($this->db->prepare(
            'SHOW TABLE STATUS LIKE %s',
            $this->db->esc_like($this->db->prefix . 'koko_analytics_') . '%'
        ));

        $tables = [];
        foreach ($results as $result) {
            $row        = (array) $result;
            $data_size  = (int) ($row['Data_length'] ?? 0);
            $index_size = (int) ($row['Index_length'] ?? 0);

            $tables[] = [
                'name' => (string) ($row['Name'] ?? ''),
                'rows' => (int) ($row['Rows'] ?? 0),
                'data_size' => $data_size,
                'index_size' => $index_size,
                'total_size' => $data_size + $index_size,
            ];
        }

        usort($tables, function ($a, $b) {
            return $b['total_size'] <=> $a['total_size'];
        });

        return $tables;
    }
}
