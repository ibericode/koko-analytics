<?php
declare(strict_types=1);

use KokoAnalytics\Shortcode_Most_Viewed_Posts;
use PHPUnit\Framework\TestCase;

final class Shortcode_Most_Viewed_Posts_Tests extends TestCase
{
	public function testCanInstantiate() : void
	{
		$i = new Shortcode_Most_Viewed_Posts();
		self::assertTrue($i instanceof Shortcode_Most_Viewed_Posts);
	}
}
