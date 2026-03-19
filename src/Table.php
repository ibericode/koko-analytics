<?php

namespace KokoAnalytics;

use DateTimeInterface;

/**
 * This class provides an abstraction layer for the database tables used to store analytics data in the plugin.
 *
 * Could in theory cover the following tables, but does not right now:
 * - wp_koko_analytics_referrer_stats
 * - wp_koko_analytics_utm_sources
 * - wp_koko_analytics_utm_mediums
 * - wp_koko_analytics_utm_campaigns
 * - wp_koko_analytics_event_*
 */
class Table
{
    /** @var \wpdb */
    protected $db;
    protected string $stats;
    protected string $labels;

    public function __construct(
        string $name,
        $db = null,
    ) {
        $this->db = $db ?: $GLOBALS['wpdb'];
        $this->stats = "{$this->db->prefix}koko_analytics_{$name}_stats";
        $this->labels = "{$this->db->prefix}koko_analytics_{$name}_labels";
    }

    public function get(DateTimeInterface $start, DateTimeInterface $end, int $offset, int $limit): array
    {
        return array_map(function ($row) {
            $row->pageviews = (int) $row->pageviews;
            $row->visitors = max(1, (int) $row->visitors);
            return $row;
        }, $this->db->get_results($this->db->prepare(
            "SELECT s.id, url, SUM(visitors) As visitors, SUM(pageviews) AS pageviews
                FROM {$this->stats} s
                JOIN {$this->labels} l ON l.id = s.id
                WHERE s.date BETWEEN %s AND %s
                GROUP BY s.id
                ORDER BY pageviews DESC, l.id ASC
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

    public function sum(DateTimeInterface $start, DateTimeInterface $end, string $property = 'pageviews'): int
    {
        return (int) $this->db->get_var($this->db->prepare(
            "SELECT SUM({$property}) FROM {$this->stats} s WHERE s.date BETWEEN %s AND %s",
            [$start->format('Y-m-d'), $end->format('Y-m-d')]
        ));
    }

    public function create(): void
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS {$this->labels} (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            value VARCHAR(180) NOT NULL,
            UNIQUE INDEX (value)
        ) ENGINE=INNODB CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS {$this->stats} (
            date DATE NOT NULL,
            id INT UNSIGNED NOT NULL,
            visitors INT UNSIGNED NOT NULL DEFAULT 0,
            pageviews INT UNSIGNED NOT NULL DEFAULT 0,
            PRIMARY KEY (date, id)
        ) ENGINE=INNODB CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci");
    }

    public function destroy(): void {
        $this->db->query("DROP TABLE IF EXISTS {$this->stats}");
        $this->db->query("DROP TABLE IF EXISTS {$this->labels}");
    }

    public function reset(): void {
        $this->db->query("TRUNCATE TABLE {$this->stats}");
        $this->db->query("TRUNCATE TABLE {$this->labels}");
    }
}
