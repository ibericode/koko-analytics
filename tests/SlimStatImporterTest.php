<?php

declare(strict_types=1);

// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses

namespace KokoAnalytics\Tests;

use DateTimeImmutable;
use KokoAnalytics\Import\SlimStat_Importer;
use PHPUnit\Framework\TestCase;

final class SlimStatImporterTest extends TestCase
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

    public function testImportsLiveAndArchivedSitePageAndReferrerStats(): void
    {
        $db              = new SlimStatImporterTestDb();
        $GLOBALS['wpdb'] = $db;
        $importer        = new SlimStat_Importer();
        $date            = new DateTimeImmutable('2026-06-01', wp_timezone());

        $importer->perform_chunk_import($date, $date);

        self::assertTrue($db->hasPreparedQuery('UNION ALL SELECT dt, ip, referer, resource, visit_id, browser_type, content_type, content_id FROM wp_slim_stats_archive'));
        self::assertSame(
            [1780272000, 1780358400],
            $db->getPreparedParameters('SELECT COUNT(*) AS pageviews')[0]
        );
        self::assertSame(
            ['2026-06-01', 11, 5, 3, 4, '2026-06-01', 12, 0, 2, 3],
            $db->getPreparedParameters('INSERT INTO wp_koko_analytics_post_stats')[0]
        );
        self::assertSame(
            ['2026-06-01', 13, 2, 3],
            $db->getPreparedParameters('INSERT INTO wp_koko_analytics_referrer_stats')[0]
        );
        self::assertSame(
            ['2026-06-01', 4, 7],
            $db->getPreparedParameters('INSERT INTO wp_koko_analytics_site_stats')[0]
        );
    }
}

class SlimStatImporterTestDb extends \wpdb
{
    public $prefix = 'wp_';
    public $last_error = '';

    /** @var array<int, array{query: string, parameters: array}> */
    private array $prepared = [];
    private array $last_parameters = [];

    public function __construct()
    {
    }

    public function prepare($query, ...$params)
    {
        $this->last_parameters = $params;
        $this->prepared[]      = [
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
        return str_replace('\\', '', $this->last_parameters[0]);
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
        if (str_contains($query, 'GROUP BY resource')) {
            return [
                (object) [
                    'resource' => '/first/',
                    'post_id' => 5,
                    'visitors' => 3,
                    'pageviews' => 4,
                ],
                (object) [
                    'resource' => '/category/news/',
                    'post_id' => 0,
                    'visitors' => 2,
                    'pageviews' => 3,
                ],
            ];
        }

        if (str_contains($query, 'GROUP BY referer')) {
            return [
                (object) [
                    'referer' => 'https://www.google.com/search?q=koko',
                    'visitors' => 2,
                    'pageviews' => 3,
                ],
            ];
        }

        if (str_contains($query, 'koko_analytics_paths')) {
            return [
                (object) ['id' => 11, 'path' => '/first/'],
                (object) ['id' => 12, 'path' => '/category/news/'],
            ];
        }

        return [(object) ['id' => 13, 'value' => 'google.com']];
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

    public function hasPreparedQuery(string $query_fragment): bool
    {
        foreach ($this->prepared as $call) {
            if (str_contains($call['query'], $query_fragment)) {
                return true;
            }
        }

        return false;
    }
}
