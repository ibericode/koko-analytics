<?php

declare(strict_types=1);

namespace KokoAnalytics\Tests;

use KokoAnalytics\Aggregator;
use PHPUnit\Framework\TestCase;

final class AggregatorTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        $i = new Aggregator();
        self::assertTrue($i instanceof Aggregator);
    }

    public function testGenerateTmpFilename(): void
    {
        self::assertNotEquals('dir/file.php', Aggregator::generate_tmp_filename('dir/file.php'));
        self::assertStringEndsWith('.php', Aggregator::generate_tmp_filename('dir/file.php'));
        self::assertStringStartsWith('dir/file', Aggregator::generate_tmp_filename('dir/file.php'));
    }
}
