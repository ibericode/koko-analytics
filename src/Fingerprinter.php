<?php

namespace KokoAnalytics;

class Fingerprinter
{
    public function create_storage_dir(): void
    {
        $upload_dir = get_upload_dir();
        $sessions_dir = "{$upload_dir}/sessions";
        $seed_file = "{$sessions_dir}/.daily_seed";

        if (! is_dir($upload_dir)) {
            mkdir($upload_dir, 0775, true);
        }

        if (! is_dir($sessions_dir)) {
            mkdir($sessions_dir, 0775, true);
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

    public function run_daily_maintenance(): void
    {
        $settings = get_settings();
        if ($settings['tracking_method'] !== 'fingerprint') {
            return;
        }

        $upload_dir = get_upload_dir();
        $sessions_dir = "{$upload_dir}/sessions";
        $seed_file = "{$sessions_dir}/.daily_seed";

        // ensure directory exists
        $this->create_storage_dir();

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
}
