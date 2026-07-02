<?php

declare(strict_types=1);

// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses

namespace KokoAnalytics\Tests;

use DateTimeImmutable;
use KokoAnalytics\Import\WP_Statistics_Importer;
use PHPUnit\Framework\TestCase;

final class WPStatisticsImporterTest extends TestCase
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

    public function testImportsSitePageAndReferrerStats(): void
    {
        $db              = new WPStatisticsImporterTestDb();
        $GLOBALS['wpdb'] = $db;
        $importer        = new WP_Statistics_Importer();
        $date            = new DateTimeImmutable('2026-06-01', wp_timezone());

        $importer->perform_chunk_import($date, $date);

        self::assertSame(
            ['2026-06-01', 10, 20],
            $db->getPreparedParameters('INSERT INTO wp_koko_analytics_site_stats')[0]
        );
        self::assertSame(
            ['2026-06-01', 11, 5, 3, 4, '2026-06-01', 12, 0, 2, 3],
            $db->getPreparedParameters('INSERT INTO wp_koko_analytics_post_stats')[0]
        );
        self::assertSame(
            ['2026-06-01', 13, 2, 3],
            $db->getPreparedParameters('INSERT INTO wp_koko_analytics_referrer_stats')[0]
        );
    }
}

class WPStatisticsImporterTestDb extends \wpdb
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

    public function get_results($query = null, $output = OBJECT)
    {
        if (str_contains($query, 'statistics_summary_totals')) {
            return [
                (object) [
                    'date' => '2026-06-01',
                    'visitors' => 10,
                    'pageviews' => 20,
                ],
            ];
        }

        if (str_contains($query, 'COUNT(ID) AS visitors') && !str_contains($query, 'referred')) {
            return [
                (object) [
                    'date' => '2026-06-01',
                    'visitors' => 9,
                    'pageviews' => 19,
                ],
            ];
        }

        if (str_contains($query, 'statistics_pages p')) {
            return [
                (object) [
                    'date' => '2026-06-01',
                    'uri' => '/first/',
                    'post_id' => 5,
                    'visitors' => 3,
                    'pageviews' => 4,
                ],
                (object) [
                    'date' => '2026-06-01',
                    'uri' => '/category/news/',
                    'post_id' => 0,
                    'visitors' => 2,
                    'pageviews' => 3,
                ],
            ];
        }

        if (str_contains($query, 'referred AS referrer')) {
            return [
                (object) [
                    'date' => '2026-06-01',
                    'referrer' => 'https://www.google.com/search?q=koko',
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
}
