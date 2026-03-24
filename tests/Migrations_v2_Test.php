<?php

declare(strict_types=1);

namespace KokoAnalytics\Tests;

use KokoAnalytics\Migrations_v2;
use PHPUnit\Framework\TestCase;

final class MigrationsTest extends TestCase
{
    public function testWithAllMigrationsPending(): void
    {
        $m = new Migrations_v2(__DIR__ . '/migrations', 'koko_analytics_version');
        self::assertEquals([
            '001-the-first.php',
            '002-the-middle.php',
            '010-the-last.php',
        ], $m->get_pending());
    }

    public function testWithNoMigrationsPending(): void
    {
        update_option('koko_analytics_version', 10);
        $m = new Migrations_v2(__DIR__ . '/migrations', 'koko_analytics_version');
        self::assertEquals([], $m->get_pending());
        delete_option('koko_analytics_version');
    }

    public function testWithSomeMigrationsPending(): void
    {
        update_option('koko_analytics_version', 1);
        $m = new Migrations_v2(__DIR__ . '/migrations', 'koko_analytics_version');
        self::assertEquals([
            '002-the-middle.php',
            '010-the-last.php',
        ], $m->get_pending());
        delete_option('koko_analytics_version');
    }

    public function testRun(): void
    {
        $m = new Migrations_v2(__DIR__ . '/migrations', 'koko_analytics_version');
        $m->run();
        self::assertEquals(10, get_option('koko_analytics_version'));
        self::assertEquals([], $m->get_pending());
        delete_option('koko_analytics_version');
    }

    public function testLocking(): void
    {
        $m = new Migrations_v2(__DIR__ . '/migrations', 'koko_analytics_version');
        self::assertTrue($m->acquire_lock());
        self::assertFalse($m->acquire_lock());
        $m->release_lock();
        self::assertTrue($m->acquire_lock());
        $m->release_lock();
    }
}
