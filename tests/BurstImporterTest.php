<?php

declare(strict_types=1);

// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses

namespace KokoAnalytics\Tests;

use DateTimeImmutable;
use KokoAnalytics\Import\Burst_Importer;
use PHPUnit\Framework\TestCase;

final class BurstImporterTest extends TestCase
{
    private \wpdb $original_db;

    protected function setUp(): void
    {
        $this->original_db = $GLOBALS['wpdb'];
    }

    protected function tearDown(): void
    {
        $GLOBALS['wpdb'] = $this->original_db;
    }

    public function testImportsDailySiteAndPageStatsFromBurst(): void
    {
        $db               = new BurstImporterTestDb();
        $GLOBALS['wpdb']  = $db;
        $importer         = new Burst_Importer();
        $date             = new DateTimeImmutable('2026-06-01', wp_timezone());

        $importer->perform_chunk_import($date, $date);

        self::assertSame(
            [1780272000, 1780358400],
            $db->getPreparedParameters('FROM wp_burst_statistics WHERE time >= %d AND time < %d')[0]
        );
        self::assertSame(
            ['2026-06-01', 11, 5, 3, 4, '2026-06-01', 12, 0, 2, 3],
            $db->getPreparedParameters('INSERT INTO wp_koko_analytics_post_stats')[0]
        );
        self::assertSame(
            ['2026-06-01', 4, 7],
            $db->getPreparedParameters('INSERT INTO wp_koko_analytics_site_stats')[0]
        );
    }
}

class BurstImporterTestDb extends \wpdb
{
    public $prefix = 'wp_';
    public $last_error = '';

    /** @var array<int, array{query: string, parameters: array}> */
    private array $prepared = [];

    public function __construct()
    {
    }

    public function prepare($query, ...$params)
    {
        $this->prepared[] = [
            'query' => $query,
            'parameters' => $params,
        ];
        return $query;
    }

    public function esc_like($text)
    {
        return addcslashes($text, '_%\\');
    }

    public function get_var($query = null, $x = 0, $y = 0)
    {
        return 'wp_burst_statistics';
    }

    public function get_row($query = null, $output = OBJECT, $y = 0)
    {
        return (object) [
            'visitors' => 4,
            'pageviews' => 7,
        ];
    }

    public function get_results($query = null, $output = OBJECT)
    {
        if (str_contains($query, 'FROM wp_burst_statistics')) {
            return [
                (object) [
                    'page_url' => '/first/',
                    'post_id' => 5,
                    'visitors' => 3,
                    'pageviews' => 4,
                ],
                (object) [
                    'page_url' => '/archive/',
                    'post_id' => 0,
                    'visitors' => 2,
                    'pageviews' => 3,
                ],
            ];
        }

        return [
            (object) ['id' => 11, 'path' => '/first/'],
            (object) ['id' => 12, 'path' => '/archive/'],
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public function getPreparedParameters(string $query_fragment): array
    {
        foreach ($this->prepared as $call) {
            if (str_contains($call['query'], $query_fragment)) {
                return $call['parameters'];
            }
        }

        return [];
    }
}
