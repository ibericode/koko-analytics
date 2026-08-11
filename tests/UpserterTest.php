<?php

declare(strict_types=1);

// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses

namespace KokoAnalytics\Tests;

use KokoAnalytics\Upserter;
use PHPUnit\Framework\TestCase;

\defined('OBJECT') || \define('OBJECT', 'OBJECT');

final class UpserterTest extends TestCase
{
    public function testBatchesValuesAndPreservesIdMapKeys(): void
    {
        $db     = new UpserterTestDb();
        $values = ['0'];
        for ($i = 1; $i <= 500; ++$i) {
            $values[] = "label-{$i}";
        }
        $values[] = '0';
        $values[] = 'label-1';

        $ids = (new Upserter('labels', 'value', $db))->upsert($values);

        self::assertCount(501, $ids);
        self::assertSame(1, $ids[0]);
        self::assertSame(2, $ids['label-1']);
        self::assertSame([500, 1], array_map('count', $db->getPreparedParameters('INSERT IGNORE')));
        self::assertSame([500, 1], array_map('count', $db->getPreparedParameters('SELECT id')));
    }
}

class UpserterTestDb extends \wpdb
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
        return array_map(function ($value) {
            $key             = (string) $value;
            $this->ids[$key] ??= count($this->ids) + 1;
            return (object) [
                'id' => $this->ids[$key],
                'value' => $value,
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
