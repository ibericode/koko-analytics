<?php

namespace KokoAnalytics;

class Fingerprinter
{
    public static function create_storage_dir(): void
    {
        $upload_dir = get_upload_dir();
        $sessions_dir = "{$upload_dir}/sessions";
        $seed_file = "{$sessions_dir}/.daily_seed";

        if (! is_dir($sessions_dir)) {
            \mkdir($sessions_dir, 0775, true);
        }

        if (! is_file($seed_file)) {
            file_put_contents($seed_file, bin2hex(random_bytes(16)));
        }

        // create empty index.html to prevent directory listing
        file_put_contents("$sessions_dir/index.html", '');

        // create .htaccess in case we're using apache
        $lines = [
            '<IfModule !authz_core_module>',
            'Order deny,allow',
            'Deny from all',
            '</IfModule>',
            '<IfModule authz_core_module>',
            'Require all denied',
            '</IfModule>',
            '',
        ];
        file_put_contents("$sessions_dir/.htaccess", join(PHP_EOL, $lines));
    }

    public static function run_daily_maintenance(): void
    {
        $settings = get_settings();
        if ($settings['tracking_method'] !== 'fingerprint') {
            return;
        }

        $upload_dir = get_upload_dir();
        $sessions_dir = "{$upload_dir}/sessions";
        $seed_file = "{$sessions_dir}/.daily_seed";

        // ensure directory exists
        self::create_storage_dir();

        // remove every file in directory
        foreach (new \DirectoryIterator($sessions_dir) as $f) {
            if ($f->isDot()) {
                continue;
            }

            unlink($f->getPathname());
        }

        // create new seed file
        file_put_contents($seed_file, bin2hex(random_bytes(16)));
    }

    public static function setup_scheduled_event(): void
    {
        if (! wp_next_scheduled('koko_analytics_rotate_fingerprint_seed')) {
            $time_next_midnight = (new \DateTimeImmutable('tomorrow, midnight', wp_timezone()))->getTimestamp();
            wp_schedule_event($time_next_midnight, 'daily', 'koko_analytics_rotate_fingerprint_seed');
        }
    }

    public static function clear_scheduled_event(): void
    {
        wp_clear_scheduled_hook('koko_analytics_rotate_fingerprint_seed');
    }
}
