<?php

declare(strict_types=1);

namespace KokoAnalytics\Tests;

use PHPUnit\Framework\TestCase;

use function KokoAnalytics\extract_pageview_data;
use function KokoAnalytics\extract_event_data;
use function KokoAnalytics\get_client_ip;
use function KokoAnalytics\is_automated_request;
use function KokoAnalytics\get_buffer_filename;
use function KokoAnalytics\get_settings;
use function KokoAnalytics\get_realtime_pageview_count;
use function KokoAnalytics\get_request_params;
use function KokoAnalytics\determine_uniqueness_cookie;
use function KokoAnalytics\determine_uniqueness_fingerprint;
use function KokoAnalytics\collect_in_file;

final class FunctionsTest extends TestCase
{
    public function testExtractPageviewData(): void
    {
        // incomplete params
        $this->assertEquals(extract_pageview_data([]), []);
        $this->assertEquals(extract_pageview_data(['r' => 'http://www.kokoanalytics.com']), []);
        $this->assertEquals(extract_pageview_data(['pa' => '/']), []);

        // complete but invalid
        $this->assertEquals(extract_pageview_data(['po' => '']), []);
        $this->assertEquals(extract_pageview_data(['po' => '1', 'r' => 'not an url']), []);
        $this->assertEquals(extract_pageview_data(['pa' => [], 'po' => '1']), []);
        $this->assertEquals(extract_pageview_data(['pa' => true, 'po' => '1']), []);
        $this->assertEquals(extract_pageview_data(['pa' => '/', 'po' => []]), []);
        $this->assertEquals(extract_pageview_data(['pa' => '/', 'po' => '1', 'r' => []]), []);
        $this->assertEquals(extract_pageview_data(['pa' => '/', 'po' => '1', 'r' => true]), []);

        // complete and valid
        foreach (
            [
            [['pa' => '/', 'po' => '1'], ['p', null, '/', 1, 1, 1, '']],

            ] as [$input, $expected]
        ) {
            $actual = extract_pageview_data($input);
            $this->assertGreaterThan(0, count($actual));
            $this->assertEquals($expected[0], $actual[0]);  // type indicator
            $this->assertIsInt($actual[1]); // timestamp
            $this->assertEquals($expected[2], $actual[2]);  // path
            $this->assertEquals($expected[3], $actual[3]);  // post id
            $this->assertEquals($expected[4], $actual[4]);
            $this->assertEquals($expected[5], $actual[5]);
            $this->assertEquals($expected[6], $actual[6]);
        }
    }

    public function testExtractEventData(): void
    {
        // incomplete
        $this->assertEquals(extract_event_data([]), []);
        $this->assertEquals(extract_event_data(['e' => 'Event']), []);
        $this->assertEquals(extract_event_data(['p' => 'Param']), []);
        $this->assertEquals(extract_event_data(['v' => '1']), []);
        $this->assertEquals(extract_event_data(['e' => 'Event', 'v' => '1']), []);
        $this->assertEquals(extract_event_data(['p' => 'Param', 'v' => '1']), []);

        // complete but invalid
        $this->assertEquals(extract_event_data(['e' => 'Event', 'p' => 'Param', 'v' => 'nan']), []);
        $this->assertEquals(extract_event_data(['e' => '', 'p' => 'Param', 'v' => '100']), []);
        $this->assertEquals(extract_event_data(['e' => [], 'p' => 'Param', 'v' => '100']), []);
        $this->assertEquals(extract_event_data(['e' => true, 'p' => 'Param', 'v' => '100']), []);
        $this->assertEquals(extract_event_data(['e' => 'Event', 'p' => [], 'v' => '100']), []);
        $this->assertEquals(extract_event_data(['e' => 'Event', 'p' => true, 'v' => '100']), []);
        $this->assertEquals(extract_event_data(['e' => 'Event', 'p' => 'Param', 'v' => []]), []);

        // complete and valid
        $actual   = extract_event_data(['e' => 'Event', 'p' => 'Param', 'v' => '100']);
        $expected = ['e', 'Event', 'Param', 1, 100];
        $this->assertEquals($expected[0], $actual[0]);
        $this->assertEquals($expected[1], $actual[1]);
        $this->assertEquals($expected[2], $actual[2]);
        $this->assertEquals($expected[3], $actual[3]);
        $this->assertEquals($expected[4], $actual[4]);
    }

    public function testGetRequestParamsUnslashesWordPressRequestData(): void
    {
        $_GET  = [
            'pa' => '/quotes-\\\'test\\\'/slashes',
            'po' => '1',
            'r' => 'https://example.com/?q=\\\'test\\\'',
        ];
        $_POST = [
            'e' => 'Click \\\'button\\\'',
            'p' => 'Label \\\'primary\\\'',
            'v' => '100',
        ];

        $actual = get_request_params();

        $this->assertEquals("/quotes-'test'/slashes", $actual['pa']);
        $this->assertEquals("https://example.com/?q='test'", $actual['r']);
        $this->assertEquals("Click 'button'", $actual['e']);
        $this->assertEquals("Label 'primary'", $actual['p']);

        $_GET  = [];
        $_POST = [];
    }

    public function testGetSettingsReturnsDefaults(): void
    {
        delete_option('koko_analytics_settings');

        $this->assertSame([
            'tracking_method' => 'cookie',
            'exclude_user_roles' => [],
            'exclude_ip_addresses' => [],
            'prune_data_after_months' => 36,
            'default_view' => 'last_28_days',
            'is_dashboard_public' => 0,
            'component_order' => [],
        ], get_settings());
    }

    public function testGetSettingsMergesStoredOptions(): void
    {
        update_option('koko_analytics_settings', [
            'tracking_method' => 'none',
            'exclude_ip_addresses' => ['127.0.0.1'],
            'default_view' => 'today',
        ]);

        $settings = get_settings();

        $this->assertSame('none', $settings['tracking_method']);
        $this->assertSame(['127.0.0.1'], $settings['exclude_ip_addresses']);
        $this->assertSame('today', $settings['default_view']);
        $this->assertSame([], $settings['exclude_user_roles']);
        $this->assertSame(36, $settings['prune_data_after_months']);

        delete_option('koko_analytics_settings');
    }

    public function testGetSettingsAppliesFilter(): void
    {
        global $hooks;

        $existing_filters                 = $hooks['koko_analytics_settings'] ?? [];
        $hooks['koko_analytics_settings'] = [];

        try {
            add_filter('koko_analytics_settings', static function (array $settings): array {
                $settings['tracking_method'] = 'fingerprint';
                $settings['component_order'] = ['pages', 'referrers'];
                return $settings;
            });

            $settings = get_settings();

            $this->assertSame('fingerprint', $settings['tracking_method']);
            $this->assertSame(['pages', 'referrers'], $settings['component_order']);
        } finally {
            $hooks['koko_analytics_settings'] = $existing_filters;
        }
    }

    public function testGetRealtimePageviewCountSumsCountsAfterTimestamp(): void
    {
        update_option('koko_analytics_realtime_pageview_count', [
            999 => 2,
            1000 => 3,
            1001 => 5,
            1002 => '7',
        ]);

        $this->assertSame(12, get_realtime_pageview_count(1000));

        delete_option('koko_analytics_realtime_pageview_count');
    }

    public function testGetRealtimePageviewCountAcceptsStringTimestamp(): void
    {
        update_option('koko_analytics_realtime_pageview_count', [
            999999999 => 2,
            1000000000 => 3,
            1000000001 => 5,
        ]);

        $this->assertSame(5, get_realtime_pageview_count('2001-09-09 01:46:40 UTC'));

        delete_option('koko_analytics_realtime_pageview_count');
    }

    public function testGetBufferFilenameOnlyReusesGeneratedBufferFiles(): void
    {
        $upload_dir = '/tmp/koko-analytics';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0775, true);
        }

        foreach (glob("{$upload_dir}/buffer-*") ?: [] as $filename) {
            unlink($filename);
        }

        touch("{$upload_dir}/buffer-old.txt");
        touch("{$upload_dir}/buffer-123.csv.bak");
        touch("{$upload_dir}/buffer-00000000000000000000000000000000.csv.busy");
        touch("{$upload_dir}/buffer-00000000000000000000000000000000.csv");

        $this->assertEquals("{$upload_dir}/buffer-00000000000000000000000000000000.csv", get_buffer_filename());

        foreach (glob("{$upload_dir}/buffer-*") ?: [] as $filename) {
            unlink($filename);
        }
    }

    public function testCollectInFileWritesSerializedLine(): void
    {
        $upload_dir = '/tmp/koko-analytics';
        if (is_dir($upload_dir)) {
            foreach (glob("{$upload_dir}/buffer-*") ?: [] as $filename) {
                unlink($filename);
            }
        }

        $data = ['p', 123, '/', 1, 1, 1, ''];

        $this->assertTrue(collect_in_file($data));

        $filenames = glob("{$upload_dir}/buffer-*.csv") ?: [];
        $this->assertCount(1, $filenames);
        $this->assertMatchesRegularExpression('/\/buffer-[a-f0-9]{32}\.csv$/', $filenames[0]);
        $this->assertEquals(serialize($data) . PHP_EOL, file_get_contents($filenames[0]));

        unlink($filenames[0]);
    }

    public function testDetermineUniquenessFingerprintHandlesMissingStorage(): void
    {
        $sessions_dir = '/tmp/koko-analytics/sessions';
        if (is_dir($sessions_dir)) {
            foreach (new \DirectoryIterator($sessions_dir) as $file) {
                if ($file->isDot()) {
                    continue;
                }
                unlink($file->getPathname());
            }
            rmdir($sessions_dir);
        }

        $_SERVER['HTTP_USER_AGENT'] = 'Unit Test';
        $_SERVER['REMOTE_ADDR']     = '1.1.1.1';

        $this->assertEquals([true, true], determine_uniqueness_fingerprint('pageview', 'abc'));
        $this->assertDirectoryDoesNotExist($sessions_dir);

        unset($_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR']);
    }

    public function testDetermineUniquenessCookieReturnsUniqueForMissingCookie(): void
    {
        unset($_COOKIE['_koko_analytics_pages_viewed']);

        self::assertSame([true, true], determine_uniqueness_cookie('pageview', 'abc'));
    }

    public function testDetermineUniquenessCookieReturnsKnownTypeAndKnownThingAsNotUnique(): void
    {
        $_COOKIE['_koko_analytics_pages_viewed'] = 'p-abc';

        try {
            self::assertSame([false, false], determine_uniqueness_cookie('pageview', 'abc'));
        } finally {
            unset($_COOKIE['_koko_analytics_pages_viewed']);
        }
    }

    public function testDetermineUniquenessCookieReturnsKnownTypeAndNewThingAsUniqueThing(): void
    {
        $_COOKIE['_koko_analytics_pages_viewed'] = 'p-abc';

        try {
            self::assertSame([false, true], determine_uniqueness_cookie('pageview', 'def'));
        } finally {
            unset($_COOKIE['_koko_analytics_pages_viewed']);
        }
    }

    public function testGetClientIp(): void
    {
        $this->assertEquals(get_client_ip(), '');

        $_SERVER['REMOTE_ADDR'] = '1.1.1.1';
        $this->assertEquals(get_client_ip(), '1.1.1.1');

        $_SERVER['HTTP_X_FORWARDED_FOR'] = '2.2.2.2';
        $this->assertEquals(get_client_ip(), '2.2.2.2');

        $_SERVER['HTTP_X_FORWARDED_FOR'] = '3.3.3.3, 2.2.2.2';
        $this->assertEquals(get_client_ip(), '3.3.3.3');

        $_SERVER['HTTP_X_FORWARDED_FOR'] = 'not-an-ip';
        $this->assertEquals(get_client_ip(), '1.1.1.1');
    }

    public function testIsAutomatedRequestByUserAgent(): void
    {
        $automated = [
            '', // no user agent at all
            'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
            'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; ClaudeBot/1.0)',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/126.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh) AppleWebKit/538.1 (KHTML, like Gecko) PhantomJS/2.1.1 Safari/538.1',
            'python-requests/2.32.3',
            'curl/8.7.1',
            'Wget/1.21.4',
            'Go-http-client/2.0',
            'Apache-HttpClient/4.5.14 (Java/1.8.0_402)',
            'okhttp/4.12.0',
            'Scrapy/2.11.1 (+https://scrapy.org)',
            'Pingdom.com_bot_version_1.4',
            'Better Uptime Bot',
            'Mozilla/5.0 (compatible; Chrome-Lighthouse)',
            'facebookexternalhit/1.1',
        ];
        foreach ($automated as $user_agent) {
            $_SERVER['HTTP_USER_AGENT'] = $user_agent;
            $this->assertTrue(is_automated_request(), "Expected '$user_agent' to be treated as automated");
        }

        $humans = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Safari/605.1.15',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (X11; Linux x86_64; rv:127.0) Gecko/20100101 Firefox/127.0',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36 Edg/126.0.0.0',

            // the optimized endpoint verifies itself using wp_remote_post(), which must not be filtered out
            'WordPress/6.5.5; https://example.com',
        ];
        foreach ($humans as $user_agent) {
            $_SERVER['HTTP_USER_AGENT'] = $user_agent;
            $this->assertFalse(is_automated_request(), "Expected '$user_agent' to be treated as a real request");
        }

        unset($_SERVER['HTTP_USER_AGENT']);
    }

    public function testIsAutomatedRequestByRequestHeaders(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36';

        // a beacon from our own tracking script
        $_SERVER['HTTP_SEC_FETCH_MODE'] = 'no-cors';
        $this->assertFalse(is_automated_request());

        // browsers predating Sec-Fetch-* send no such headers at all
        unset($_SERVER['HTTP_SEC_FETCH_MODE']);
        $this->assertFalse(is_automated_request());

        // someone opening the endpoint URL directly
        $_SERVER['HTTP_SEC_FETCH_MODE'] = 'navigate';
        $this->assertTrue(is_automated_request());
        unset($_SERVER['HTTP_SEC_FETCH_MODE']);

        // prefetched or prerendered by the browser
        $_SERVER['HTTP_SEC_PURPOSE'] = 'prefetch';
        $this->assertTrue(is_automated_request());
        $_SERVER['HTTP_SEC_PURPOSE'] = 'prefetch;prerender';
        $this->assertTrue(is_automated_request());
        unset($_SERVER['HTTP_SEC_PURPOSE']);

        // legacy prefetch header
        $_SERVER['HTTP_PURPOSE'] = 'prefetch';
        $this->assertTrue(is_automated_request());
        unset($_SERVER['HTTP_PURPOSE']);

        $this->assertFalse(is_automated_request());
        unset($_SERVER['HTTP_USER_AGENT']);
    }
}
