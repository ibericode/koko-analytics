<?php

declare(strict_types=1);

// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses

namespace KokoAnalytics\Tests;

use KokoAnalytics\Database_Stats;
use PHPUnit\Framework\TestCase;

\defined('OBJECT') || \define('OBJECT', 'OBJECT');

final class Database_Stats_Test extends TestCase
{
    public function testGetReturnsTableAndTotalStats(): void
    {
        $db    = new Database_Stats_Test_Db();
        $stats = (new Database_Stats($db))->get();

        self::assertSame(2, count($stats['tables']));
        self::assertSame(1250, $stats['total_rows']);
        self::assertSame(12288, $stats['total_size']);

        self::assertSame('wp_koko_analytics_post_stats', $stats['tables'][0]['name']);
        self::assertSame(1000, $stats['tables'][0]['rows']);
        self::assertSame(8192, $stats['tables'][0]['total_size']);

        self::assertSame('wp_koko_analytics_site_stats', $stats['tables'][1]['name']);
        self::assertSame(250, $stats['tables'][1]['rows']);
        self::assertSame(4096, $stats['tables'][1]['total_size']);
    }
}

// phpcs:ignore
class Database_Stats_Test_Db extends \wpdb
{
    public $prefix = 'wp_';

    public function __construct()
    {
    }

    public function get_results($query = null, $output = OBJECT)
    {
        return [
            (object) [
                'Name' => 'wp_koko_analytics_site_stats',
                'Rows' => '250',
                'Data_length' => '2048',
                'Index_length' => '2048',
            ],
            (object) [
                'Name' => 'wp_koko_analytics_post_stats',
                'Rows' => '1000',
                'Data_length' => '6144',
                'Index_length' => '2048',
            ],
        ];
    }

    public function esc_like($text)
    {
        return addcslashes($text, '_%\\');
    }
}
