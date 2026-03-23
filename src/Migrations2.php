<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

class Migrations2
{
    public function __construct(
        protected string $directory,
        protected string $option_name
    ) {
    }

    public function get_pending(): array
    {
        $version_from = (int) get_option($this->option_name, 0);
        $files = scandir($this->directory, SCANDIR_SORT_ASCENDING);
        if ($files === false || count($files) === 0) {
            return [];
        }

        return array_values(array_filter($files, function ($file) use ($version_from) {
            if (! preg_match('/^(\d+)\-[a-zA-Z0-9_\-]+\.php$/', $file, $matches)) {
                return false;
            }
            $version = (int) $matches[1];
            return $version > $version_from;
        }));
    }

    public function acquire_lock(): bool
    {
        // check if migrations not already running
        $transient_key = "{$this->option_name}_lock";
        $transient_timeout = 10;
        $previous_run_start = get_transient($transient_key);
        if ($previous_run_start && $previous_run_start > time() - $transient_timeout) {
            return false;
        }

        set_transient($transient_key, time(), $transient_timeout);

        return true;
    }

    public function release_lock(): void {
        $transient_key = "{$this->option_name}_lock";
        delete_transient($transient_key);
    }

    public function run(): void
    {
        $pending = $this->get_pending();
        if (count($pending) === 0) {
            return;
        }

        if (! $this->acquire_lock()) {
            return;
        }

        foreach ($pending as $file) {
            // execute migration file in scoped function to prevent variable leakage
            $this->execute($this->directory . DIRECTORY_SEPARATOR . $file);

            // extract version from filename and update option
            $version = (int) strtok($file, '-');
            update_option($this->option_name, $version, true);
        }

        $this->release_lock();
    }

    protected function execute(string $file): void
    {
        include $file;
    }
}
