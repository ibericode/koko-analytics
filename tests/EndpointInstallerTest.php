<?php
declare(strict_types=1);

use KokoAnalytics\Endpoint_Installer;
use PHPUnit\Framework\TestCase;

final class EndpointInstallerTest extends TestCase
{
    public function testCanInstantiate() : void
    {
        $i = new Endpoint_Installer();
        self::assertTrue($i instanceof Endpoint_Installer);
    }
}
