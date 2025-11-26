<?php

namespace KokoAnalytics;

use Exception;

class Blocklist
{
    public static function setup_scheduled_event(): void
    {
        if (! wp_next_scheduled('koko_analytics_update_referrer_blocklist')) {
            $time_next_midnight = (new \DateTimeImmutable('tomorrow 4AM', wp_timezone()))->getTimestamp();
            wp_schedule_event($time_next_midnight, 'weekly', 'koko_analytics_update_referrer_blocklist');
        }
    }

    public static function run_scheduled_event(): void
    {
        (new Blocklist())->update();
    }

    public static function clear_scheduled_event(): void
    {
        wp_clear_scheduled_hook('koko_analytics_update_referrer_blocklist');
    }

    protected function getFilename(): string
    {
        $uploads = wp_upload_dir();
        return rtrim($uploads['basedir'], '/') . '/koko-analytics/referrer-blocklist.txt';
    }

    public function update(bool $force = false): bool
    {
        $filename = $this->getFilename();

        // only update once per day unless $force is true
        if (!$force && is_file($filename) && filemtime($filename) > time() - 24 * 60 * 60) {
            return false;
        }

        $blocklist = file_get_contents("https://raw.githubusercontent.com/matomo-org/referrer-spam-blacklist/master/spammers.txt");
        if (!$blocklist) {
            throw new Exception("Error downloading blocklist");
        }

        $directory = dirname($filename);
        if (is_dir($directory) === false) {
            mkdir($directory, 0775);
        }

        if (!file_put_contents($this->getFilename(), $blocklist)) {
            throw new Exception("Error writing blocklist to file");
        }

        return true;
    }

    protected function read(): array
    {
        $filename = $this->getFilename();
        if (!is_file($filename)) {
            return [];
        }

        return \file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    }

    public function contains(string $domain): bool
    {
        static $list;
        if ($list === null) {
            $list = $this->read();
        }

        foreach ($list as $item) {
            $item = trim($item);

            if ($item === '') {
                continue;
            }

            if (str_contains($item, $domain)) {
                return true;
            }
        }

        return false;
    }
}
