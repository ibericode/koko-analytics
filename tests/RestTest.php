<?php

declare(strict_types=1);

namespace KokoAnalytics\Tests;

use KokoAnalytics\Rest;
use KokoAnalytics\Dashboard;
use PHPUnit\Framework\TestCase;

final class RestTest extends TestCase
{
    public function test_validate_date_param()
    {
        $rest = new Rest();
        self::assertTrue($rest->validate_date_param('2000-01-01', null, null));
        self::assertFalse($rest->validate_date_param('2000-99-99', null, null));
        self::assertFalse($rest->validate_date_param('-1 year', null, null));
        self::assertFalse($rest->validate_date_param('foobar', null, null));
    }

    public function test_validate_since_param()
    {
        $rest = new Rest();
        self::assertTrue($rest->validate_since_param('-1 hour', null, null));
        self::assertTrue($rest->validate_since_param('2000-01-01', null, null));
        self::assertFalse($rest->validate_since_param('foobar', null, null));
    }

    public function test_clamp_pagination()
    {
        self::assertSame(10, Dashboard::clamp_limit(null));
        self::assertSame(3, Dashboard::clamp_limit(0, 10, 3));
        self::assertSame(100, Dashboard::clamp_limit(1000));
        self::assertSame(0, Dashboard::clamp_offset(null));
        self::assertSame(10000, Dashboard::clamp_offset(100000));
    }

    public function test_sanitize_bool_param()
    {
        $rest = new Rest();
        self::assertTrue($rest->sanitize_bool_param('true', null, null));
        self::assertTrue($rest->sanitize_bool_param('yes', null, null));
        self::assertTrue($rest->sanitize_bool_param('1', null, null));
        self::assertFalse($rest->sanitize_bool_param('0', null, null));
        self::assertFalse($rest->sanitize_bool_param('no', null, null));
        self::assertFalse($rest->sanitize_bool_param('false', null, null));
    }
}
