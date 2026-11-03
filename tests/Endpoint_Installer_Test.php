<?php

declare(strict_types=1);

namespace KokoAnalytics\Tests;

use KokoAnalytics\Endpoint_Installer;
use PHPUnit\Framework\TestCase;

use function KokoAnalytics\is_automated_request;

final class Endpoint_Installer_Test extends TestCase
{
    public function testMakeRelativeToAbspathReturnsRelativePathForFileBelowAbspath(): void
    {
        $installer = new Endpoint_Installer();

        self::assertSame(
            'wp-content/plugins/koko-analytics/src/Resources/functions/collect.php',
            $installer->make_relative_to_abspath(ABSPATH . '/wp-content/plugins/koko-analytics/src/Resources/functions/collect.php')
        );
    }

    public function testMakeRelativeToAbspathLeavesRelativePathUnchanged(): void
    {
        $installer = new Endpoint_Installer();

        self::assertSame(
            'wp-includes/plugin.php',
            $installer->make_relative_to_abspath('wp-includes/plugin.php')
        );
    }

    public function testFileNameIsUniquePerSiteOnMultisite(): void
    {
        global $is_multisite, $current_blog_id;
        $is_multisite    = true;
        $current_blog_id = 3;

        try {
            self::assertSame(rtrim(ABSPATH, '/') . '/koko-analytics-collect-3.php', (new Endpoint_Installer())->get_file_name());
        } finally {
            $is_multisite    = false;
            $current_blog_id = 1;
        }
    }

    public function testVerifyUserAgentIsNotTreatedAsAutomated(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = Endpoint_Installer::VERIFY_USER_AGENT;

        self::assertFalse(is_automated_request());

        unset($_SERVER['HTTP_USER_AGENT']);
    }
}
