<?php

namespace KokoAnalytics;

use DateTimeInterface;
use wpdb;

/**
 * This class provides an abstraction layer for some of the generic database tables
 *
 * This currently covers the following tables:
 * - wp_koko_analytics_referrer_stats / wp_koko_analytics_referrer_labels
 *
 * Other table candidates:
 * - wp_koko_analytics_event_* (one table per event type)
 * - wp_koko_analytics_utm_sources
 * - wp_koko_analytics_utm_mediums
 * - wp_koko_analytics_utm_campaigns
 *
 * TODO:
 * - Make this class an interface, have post_stats also use it (through a subclass).
 * - Register all tables in a central place to ease up pruning, resetting and destroying.
 */
class Table
{
    protected wpdb $db;
    protected string $stats;
    protected string $labels;

    public function __construct(
        string $name,
        ?wpdb $db = null
    ) {
        $this->db = $db ?? $GLOBALS['wpdb'];
        $this->stats = "{$this->db->prefix}koko_analytics_{$name}_stats";
        $this->labels = "{$this->db->prefix}koko_analytics_{$name}_labels";
    }

    public function get(DateTimeInterface $start, DateTimeInterface $end, int $offset, int $limit): array
    {
        return array_map(function ($row) {
            $row->hits = (int) $row->hits;
            $row->unique_hits = max(1, (int) $row->unique_hits);
            return $row;
        }, $this->db->get_results($this->db->prepare(
            "SELECT s.id, l.value, SUM(hits) As hits, SUM(unique_hits) AS unique_hits
                FROM {$this->stats} s
                JOIN {$this->labels} l ON l.id = s.id
                WHERE s.date BETWEEN %s AND %s
                GROUP BY s.id
                ORDER BY hits DESC, l.id ASC
                LIMIT %d, %d",
            [$start->format('Y-m-d'), $end->format('Y-m-d'), $offset, $limit]
        )));
    }

    public function count(DateTimeInterface $start, DateTimeInterface $end): int
    {
        return (int) $this->db->get_var($this->db->prepare(
            "SELECT COUNT(DISTINCT(s.id)) FROM {$this->stats} s WHERE s.date BETWEEN %s AND %s",
            [$start->format('Y-m-d'), $end->format('Y-m-d')]
        ));
    }

    public function sum(DateTimeInterface $start, DateTimeInterface $end, string $property = 'hits'): int
    {
        if (!in_array($property, ['hits', 'unique_hits'])) {
            throw new \InvalidArgumentException("Invalid property: {$property}");
        }

        return (int) $this->db->get_var($this->db->prepare(
            "SELECT SUM({$property}) FROM {$this->stats} s WHERE s.date BETWEEN %s AND %s",
            [$start->format('Y-m-d'), $end->format('Y-m-d')]
        ));
    }

    public function create(): void
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS {$this->labels} (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            value VARCHAR(255) NOT NULL,
            UNIQUE INDEX (value)
        ) ENGINE=INNODB CHARACTER SET=ascii COLLATE=ascii_general_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS {$this->stats} (
            date DATE NOT NULL,
            id INT UNSIGNED NOT NULL,
            hits INT UNSIGNED NOT NULL DEFAULT 0,
            unique_hits INT UNSIGNED NOT NULL DEFAULT 0,
            PRIMARY KEY (date, id)
        ) ENGINE=INNODB CHARACTER SET=ascii COLLATE=ascii_general_ci");

        // Add index on stats.id column to speed up orphan deletion queries
        $this->db->query("ALTER TABLE {$this->stats} ADD INDEX (id)");
    }

    public function destroy(): void
    {
        $this->db->query("DROP TABLE IF EXISTS {$this->stats}");
        $this->db->query("DROP TABLE IF EXISTS {$this->labels}");
    }

    public function reset(): void
    {
        $this->db->query("TRUNCATE TABLE {$this->stats}");
        $this->db->query("TRUNCATE TABLE {$this->labels}");
    }
}
