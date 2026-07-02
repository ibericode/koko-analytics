<?php

declare(strict_types=1);

// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses

namespace KokoAnalytics\Tests;

use DateTimeImmutable;
use KokoAnalytics\Import\Statify_Importer;
use PHPUnit\Framework\TestCase;

final class StatifyImporterTest extends TestCase
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

    public function testImportsSitePageAndReferrerStatsFromStatify(): void
    {
        $db              = new StatifyImporterTestDb();
        $GLOBALS['wpdb'] = $db;
        $importer        = new Statify_Importer();
        $date            = new DateTimeImmutable('2026-06-01', wp_timezone());

        $importer->perform_chunk_import($date, $date);

        self::assertSame(
            ['2026-06-01', 7, 7],
            $db->getPreparedParameters('INSERT INTO wp_koko_analytics_site_stats')[0]
        );
        self::assertSame(
            ['2026-06-01', 11, 5, 4, 4],
            $db->getPreparedParameters('INSERT INTO wp_koko_analytics_post_stats')[0]
        );
        self::assertSame(
            ['2026-06-01', 12, 3, 3],
            $db->getPreparedParameters('INSERT INTO wp_koko_analytics_referrer_stats')[0]
        );
    }
}

class StatifyImporterTestDb extends \wpdb
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
        return 'wp_statify';
    }

    public function get_results($query = null, $output = OBJECT)
    {
        if (str_contains($query, 'COUNT(id) AS pageviews') && str_contains($query, 'GROUP BY created, target')) {
            return [
                (object) [
                    'date' => '2026-06-01',
                    'target' => '/first/',
                    'pageviews' => 4,
                ],
            ];
        }

        if (str_contains($query, 'COUNT(id) AS pageviews') && str_contains($query, 'GROUP BY created, referrer')) {
            return [
                (object) [
                    'date' => '2026-06-01',
                    'referrer' => 'https://www.google.com/search?q=koko',
                    'pageviews' => 3,
                ],
            ];
        }

        if (str_contains($query, 'COUNT(id) AS pageviews')) {
            return [
                (object) [
                    'date' => '2026-06-01',
                    'pageviews' => 7,
                ],
            ];
        }

        if (str_contains($query, 'koko_analytics_paths')) {
            return [(object) ['id' => 11, 'path' => '/first/']];
        }

        return [(object) ['id' => 12, 'value' => 'google.com']];
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
