<?php

declare(strict_types=1);

// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses

namespace KokoAnalytics\Tests;

use KokoAnalytics\Aggregator;
use PHPUnit\Framework\TestCase;

\defined('OBJECT') || \define('OBJECT', 'OBJECT');

final class AggregatorTest extends TestCase
{
    private \wpdb $original_db;

    protected function setUp(): void
    {
        $this->original_db = $GLOBALS['wpdb'];
    }

    protected function tearDown(): void
    {
        $GLOBALS['wpdb'] = $this->original_db;

        foreach (glob('/tmp/koko-analytics/buffer-*') ?: [] as $filename) {
            unlink($filename);
        }
    }

    public function testFlushesNonEmptyChunksAndFinishesBufferOnce(): void
    {
        $cases = [
            'empty' => [0, 0],
            'exact chunk' => [10000, 1],
            'chunk plus one' => [10001, 2],
            'multiple chunks' => [20001, 3],
        ];

        foreach ($cases as $name => [$record_count, $expected_chunks]) {
            $this->assertBufferIsChunked($name, $record_count, $expected_chunks);
        }
    }

    private function assertBufferIsChunked(string $name, int $record_count, int $expected_chunks): void
    {
        global $hooks, $options, $option_updates;

        $directory = '/tmp/koko-analytics';
        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        foreach (glob("{$directory}/buffer-*") ?: [] as $filename) {
            unlink($filename);
        }

        $filename = "{$directory}/buffer-00000000000000000000000000000000.csv";
        $handle   = fopen($filename, 'w');
        self::assertIsResource($handle);
        for ($i = 0; $i < $record_count; ++$i) {
            fwrite($handle, serialize(['p', 1723370400, '/same-path', 0, 1, 1, '']) . PHP_EOL);
        }
        fclose($handle);

        $chunk_count  = 0;
        $finish_count = 0;
        $chunk_hooks  = $hooks['koko_analytics_aggregate_chunk_finish'] ?? [];
        $finish_hooks = $hooks['koko_analytics_aggregate_finish'] ?? [];

        $hooks['koko_analytics_aggregate_chunk_finish'] = [function () use (&$chunk_count): void {
            ++$chunk_count;
        }];
        $hooks['koko_analytics_aggregate_finish']       = [function () use (&$finish_count): void {
            ++$finish_count;
        }];

        $db              = new AggregatorTestDb();
        $GLOBALS['wpdb'] = $db;
        $options['koko_analytics_realtime_pageview_count']        = [1 => 5];
        $option_updates['koko_analytics_realtime_pageview_count'] = 0;

        try {
            (new Aggregator())->run();
        } finally {
            $hooks['koko_analytics_aggregate_chunk_finish'] = $chunk_hooks;
            $hooks['koko_analytics_aggregate_finish']       = $finish_hooks;
        }

        self::assertSame($expected_chunks, $chunk_count, $name);
        self::assertSame(1, $finish_count, $name);
        self::assertSame($record_count, $db->site_pageviews, $name);
        self::assertSame(1, $option_updates['koko_analytics_realtime_pageview_count'], $name);
        self::assertSame([], $options['koko_analytics_realtime_pageview_count'], $name);
        self::assertFileDoesNotExist($filename, $name);
        self::assertFileDoesNotExist($filename . '.busy', $name);
    }

    public function testInvalidLinesDoNotCountTowardsChunkSize(): void
    {
        global $hooks;

        $directory = '/tmp/koko-analytics';
        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        foreach (glob("{$directory}/buffer-*") ?: [] as $filename) {
            unlink($filename);
        }

        $filename = "{$directory}/buffer-00000000000000000000000000000000.csv";
        $handle   = fopen($filename, 'w');
        self::assertIsResource($handle);
        for ($i = 0; $i < 10000; ++$i) {
            fwrite($handle, serialize(['p', 1723370400, '/same-path', 0, 1, 1, '']) . PHP_EOL);
        }
        fwrite($handle, serialize(false) . PHP_EOL);
        fwrite($handle, serialize(['p', 1723370400, '/same-path', 0, 1, 1, '']) . PHP_EOL);
        fclose($handle);

        $chunk_count = 0;
        $chunk_hooks = $hooks['koko_analytics_aggregate_chunk_finish'] ?? [];
        $hooks['koko_analytics_aggregate_chunk_finish'] = [function () use (&$chunk_count): void {
            ++$chunk_count;
        }];

        $GLOBALS['wpdb'] = new AggregatorTestDb();

        try {
            (new Aggregator())->run();
        } finally {
            $hooks['koko_analytics_aggregate_chunk_finish'] = $chunk_hooks;
        }

        self::assertSame(2, $chunk_count);
    }
}

class AggregatorTestDb extends \wpdb
{
    public $prefix = 'wp_';
    public int $site_pageviews = 0;

    /** @var array<int, mixed> */
    private array $last_parameters = [];

    /** @var array<string, int> */
    private array $ids = [];

    public function __construct()
    {
    }

    public function prepare($query, ...$params)
    {
        $this->last_parameters = count($params) === 1 && is_array($params[0]) ? $params[0] : $params;
        if (str_contains($query, 'INSERT INTO wp_koko_analytics_site_stats')) {
            $this->site_pageviews += $this->last_parameters[2];
        }
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
}
