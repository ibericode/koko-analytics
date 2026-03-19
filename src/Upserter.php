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
    )
    {
        $this->db = $db ?: $GLOBALS['wpdb'];
        $this->table = $this->db->prefix . 'koko_analytics_' . $table;
        $this->column = $column;
    }

    public function upsert(array $values): array
    {
        $values = array_unique($values);

        // INSERT IGNORE all deduplicated values into the database table 
        $placeholders = rtrim(str_repeat('(%s),', count($values)), ',');
        $this->db->query($this->db->prepare("INSERT IGNORE INTO {$this->table}({$this->column}) VALUES {$placeholders}", $values));

        // retrieve all entries from the database table to get their normalized ID's
        $placeholders = rtrim(str_repeat('%s,', count($values)), ',');
        $results = $this->db->get_results($this->db->prepare("SELECT id, {$this->column} FROM {$this->table} WHERE {$this->column} IN({$placeholders})", $values));

        $map = [];
        foreach ($results as $r) {
            $map[$r->{$this->column}] = $r->id;
        }
        return $map;    

    }

}