<?php

declare(strict_types=1);

namespace KokoAnalytics\Tests;

use PHPUnit\Framework\TestCase;

use function KokoAnalytics\extract_pageview_data;
use function KokoAnalytics\extract_event_data;
use function KokoAnalytics\get_client_ip;
use function KokoAnalytics\percent_format_i18n;

class TemplateFunctionsTest extends TestCase
{
    public function testPercentFormatI18n(): void
    {
        $this->assertEquals(percent_format_i18n(0), '');
        $this->assertEquals(percent_format_i18n(0.00), '');
        $this->assertEquals(percent_format_i18n(1.00), '+100%');
        $this->assertEquals(percent_format_i18n(-1.00), '-100%');
        $this->assertEquals(percent_format_i18n(0.55), '+55%');
        $this->assertEquals(percent_format_i18n(-0.55), '-55%');
    }
}
