<?php

declare(strict_types=1);

namespace KokoAnalytics\Tests;

use KokoAnalytics\Shortcode_Most_Viewed_Posts;
use PHPUnit\Framework\TestCase;

final class ShortcodeMostViewedPostsTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        $i = new Shortcode_Most_Viewed_Posts();
        self::assertTrue($i instanceof Shortcode_Most_Viewed_Posts);
    }
}
