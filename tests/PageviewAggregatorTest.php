<?php

declare(strict_types=1);

// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses

namespace KokoAnalytics\Tests;

use KokoAnalytics\Pageview_Aggregator;
use PHPUnit\Framework\TestCase;

\defined('OBJECT') || \define('OBJECT', 'OBJECT');

final class PageviewAggregatorTest extends TestCase
{
    public function testBatchesPageAndReferrerDatabaseWrites(): void
    {
        $db         = new PageviewAggregatorTestDb();
        $aggregator = new Pageview_Aggregator($db);

        for ($i = 0; $i < 501; ++$i) {
            $aggregator->line('p', [1723370400, "/path-{$i}", 0, 1, 1, "https://referrer-{$i}.example"]);
        }

        $aggregator->finish();

        $path_upserts = $db->getPreparedParameters('INSERT IGNORE INTO wp_koko_analytics_paths');
        self::assertSame([500, 1], array_map('count', $path_upserts));

        $path_stats = $db->getPreparedParameters('INSERT INTO wp_koko_analytics_post_stats');
        self::assertSame([2500, 5], array_map('count', $path_stats));

        $referrer_upserts = $db->getPreparedParameters('INSERT IGNORE INTO wp_koko_analytics_referrer_labels');
        self::assertSame([500, 1], array_map('count', $referrer_upserts));

        $referrer_stats = $db->getPreparedParameters('INSERT INTO wp_koko_analytics_referrer_stats');
        self::assertSame([2000, 4], array_map('count', $referrer_stats));
    }
}

class PageviewAggregatorTestDb extends \wpdb
{
    public $prefix = 'wp_';

    /** @var array<int, array{query: string, parameters: array}> */
    private array $prepared = [];

    /** @var array<int, mixed> */
    private array $last_parameters = [];

    /** @var array<string, int> */
    private array $ids = [];

    public function __construct()
    {
    }

    public function prepare($query, ...$params)
    {
        $parameters            = count($params) === 1 && is_array($params[0]) ? $params[0] : $params;
        $this->last_parameters = $parameters;
        $this->prepared[]      = [
            'query' => $query,
            'parameters' => $parameters,
        ];
        return $query;
    }

    public function query($query)
    {
        return 1;
    }

    public function get_results($query = null, $output = OBJECT)
    {
        $column = str_contains($query, 'SELECT id, path') ? 'path' : 'value';
        return array_map(function ($value) use ($column) {
            $key             = "{$column}:{$value}";
            $this->ids[$key] ??= count($this->ids) + 1;
            return (object) [
                'id' => $this->ids[$key],
                $column => $value,
            ];
        }, $this->last_parameters);
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function getPreparedParameters(string $query_fragment): array
    {
        return array_values(array_map(function ($call) {
            return $call['parameters'];
        }, array_filter($this->prepared, function ($call) use ($query_fragment) {
            return str_contains($call['query'], $query_fragment);
        })));
    }
}
