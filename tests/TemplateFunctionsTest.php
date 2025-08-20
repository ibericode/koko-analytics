<?php

declare(strict_types=1);

namespace KokoAnalytics\Tests;

use PHPUnit\Framework\TestCase;

use function KokoAnalytics\get_referrer_url_href;
use function KokoAnalytics\get_referrer_url_label;
use function KokoAnalytics\percent_format_i18n;

class TemplateFunctionsTest extends TestCase
{
    public function setUp(): void
    {
        require_once dirname(__DIR__, 1) . '/src/Resources/functions/template.php';
    }

    public function testPercentFormatI18n(): void
    {
        $this->assertEquals(percent_format_i18n(0), '');
        $this->assertEquals(percent_format_i18n(0.00), '');
        $this->assertEquals(percent_format_i18n(1.00), '+100%');
        $this->assertEquals(percent_format_i18n(-1.00), '-100%');
        $this->assertEquals(percent_format_i18n(0.55), '+55%');
        $this->assertEquals(percent_format_i18n(-0.55), '-55%');
    }

    public function testGetReferrerUrlLabel(): void
    {
        self::assertEquals('', get_referrer_url_label(''));
        self::assertEquals('kokoanalytics.com', get_referrer_url_label('https://www.kokoanalytics.com/'));
        self::assertEquals('kokoanalytics.com/about', get_referrer_url_label('https://www.kokoanalytics.com/about'));
    }
}
