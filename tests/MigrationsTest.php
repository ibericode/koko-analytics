<?php

declare(strict_types=1);

namespace KokoAnalytics\Tests;

use KokoAnalytics\Migrations;
use PHPUnit\Framework\TestCase;

class MigrationsTest extends TestCase
{
    private $dir = '/tmp/koko-analytics-tests/migrations';

    public function setUp(): void
    {
        if (! file_exists($this->dir)) {
            mkdir($this->dir, 0700, true);
        }
    }

    public function testCanInstantiate()
    {
        $instance = new Migrations('1.0', '1.1', $this->dir);
        $this->assertInstanceOf(Migrations::class, $instance);
    }

    public function tearDown(): void
    {
        array_map('unlink', glob($this->dir . '/*.php'));
        if (file_exists($this->dir)) {
            rmdir($this->dir);
        }
    }
}
