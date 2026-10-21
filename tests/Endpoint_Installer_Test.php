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

    public function testVerifyUserAgentIsNotTreatedAsAutomated(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = Endpoint_Installer::VERIFY_USER_AGENT;

        self::assertFalse(is_automated_request());

        unset($_SERVER['HTTP_USER_AGENT']);
    }
}
