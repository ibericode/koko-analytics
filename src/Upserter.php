<?php

namespace KokoAnalytics;

class Upserter
{
    protected \wpdb $db;
    protected string $table;
    protected string $column;

    public function __construct(
        string $table,
        string $column,
        $db = null,
    ) {
        $this->db = $db ?: $GLOBALS['wpdb'];
        $this->table = $this->db->prefix . 'koko_analytics_' . $table;
        $this->column = $column;
    }

    /**
     * @param array<string> $values
     * @return array<string, int> Map of value to ID in database
     */
    public function upsert(array $values): array
    {
        // return early if there are no values to upsert
        if (count($values) === 0) {
            return [];
        }

        // deduplicate values to avoid unnecessary database queries and potential unique key violations
        $values = array_unique($values);

        // INSERT IGNORE all deduplicated values into the database table
        $placeholders = rtrim(str_repeat('(%s),', count($values)), ',');
        $this->db->query($this->db->prepare("INSERT IGNORE INTO {$this->table}({$this->column}) VALUES {$placeholders}", $values));

        // retrieve all entries from the database table to get their normalized ID's
        $placeholders = rtrim(str_repeat('%s,', count($values)), ',');
        $results = $this->db->get_results($this->db->prepare("SELECT id, {$this->column} FROM {$this->table} WHERE {$this->column} IN({$placeholders})", $values));

        return array_column($results, 'id', $this->column);
    }
}
