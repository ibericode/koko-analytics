<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

use Throwable;

class Migrations_v2
{
    protected string $directory;
    protected string $option_name;

    /**
     * @param string $directory Directory where migration files are located.
     * @param string $option_name Name of the option where the current migration version is stored.
     */
    public function __construct(
        string $directory,
        string $option_name
    ) {
        $this->directory   = rtrim($directory, '/');
        $this->option_name = $option_name;
    }

    public function get_pending(): array
    {
        $version_from = $this->get_current_version();
        return array_column(array_filter($this->get_all(), function ($migration) use ($version_from) {
            return $migration['version'] > $version_from;
        }), 'file');
    }

    public function get_current_version(): int
    {
        return (int) get_option($this->option_name, 0);
    }

    public function get_latest_version(): int
    {
        $migrations = $this->get_all();
        if (count($migrations) === 0) {
            return 0;
        }

        return max(array_column($migrations, 'version'));
    }

    /**
     * @return array<int, array{file: string, version: int}>
     */
    protected function get_all(): array
    {
        $files = scandir($this->directory, SCANDIR_SORT_ASCENDING);
        if ($files === false || count($files) === 0) {
            return [];
        }

        $migrations = [];
        foreach ($files as $file) {
            if (! preg_match('/^(\d+)\-[a-zA-Z0-9_\-]+\.php$/', $file, $matches)) {
                continue;
            }

            $migrations[] = [
                'file' => $file,
                'version' => (int) $matches[1],
            ];
        }

        return $migrations;
    }

    public function acquire_lock(): bool
    {
        $transient_key     = "{$this->option_name}_lock";
        $transient_timeout = 300;

        // return false if a lock is already active
        $previous_run_start = (int) get_transient($transient_key);
        if ($previous_run_start > time() - $transient_timeout) {
            return false;
        }

        set_transient($transient_key, time(), $transient_timeout);
        return true;
    }

    public function update_lock(): void
    {
        $transient_key     = "{$this->option_name}_lock";
        $transient_timeout = 300;
        set_transient($transient_key, time(), $transient_timeout);
    }

    public function release_lock(): void
    {
        $transient_key = "{$this->option_name}_lock";
        delete_transient($transient_key);
    }

    /**
     * Potentially runs all pending database migrations.
     * Only returns true if database is at current version.
     *
     * @return bool
     */
    public function ensure_current(): bool
    {
        $pending = $this->get_pending();
        if (count($pending) === 0) {
            return true;
        }

        if (! $this->acquire_lock()) {
            return false;
        }

        // try to increase time limit to 5 minutes
        @set_time_limit(300);

        try {
            foreach ($pending as $file) {
                $this->execute($file);
                $this->update_lock();
            }
        } catch (Throwable $e) {
            error_log("Koko Analytics: error running database migrations. " . (string) $e);
            return false;
        } finally {
            $this->release_lock();
        }

        return count($this->get_pending()) === 0;
    }

    /**
     * @param string $file Filename of the migration to execute, relative to the migrations directory.
     */
    protected function execute(string $file): void
    {
        include $this->directory . DIRECTORY_SEPARATOR . $file;

        // extract version from filename and update option
        // we explicitly do this after each individual migration
        // so that if executing multiple migrations fails halfway through
        // so that the next time we run, it continues from the last successful migration instead of starting over
        $version = (int) $file;
        update_option($this->option_name, $version, true);
    }
}
