<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

use Exception;

class Migrations
{
    protected $prefix;
    protected $option_name;
    protected $version_from;
    protected $version_to;
    protected $migrations_dir;

    public function __construct(string $prefix, string $version_to, string $migrations_dir)
    {
        $this->prefix = rtrim($prefix, '_');
        $this->option_name = str_ends_with($this->prefix, '_version') ? $this->prefix : "{$this->prefix}_version";
        $this->version_from = isset($_GET["{$this->prefix}_migrate_from_version"]) && current_user_can('manage_options') ? $_GET["{$this->prefix}_migrate_from_version"] : get_option($this->option_name, '0.0.0');
        $this->version_to = $version_to;
        $this->migrations_dir = $migrations_dir;
    }

    public function maybe_run(): void
    {
        if (\version_compare($this->version_from, $this->version_to, '>=')) {
            return;
        }

        // check if migrations not already running
        $transient_key = "{$this->prefix}_migrations_running";
        $transient_timeout = 10;
        $previous_run_start = get_transient($transient_key);
        if ($previous_run_start && $previous_run_start > time() - $transient_timeout) {
            return;
        }

        set_transient($transient_key, time(), $transient_timeout);
        $this->run();
        delete_transient($transient_key);
    }

    /**
     * Run the various migration files, all the way up to the latest version
     */
    protected function run(): void
    {
        $files = glob(rtrim($this->migrations_dir, '/') . '/*.php');
        if (! is_array($files)) {
            return;
        }

        // run each migration file
        foreach ($files as $file) {
            $this->handle_file($file);
        }

        // update database version to current code version
        update_option($this->option_name, $this->version_to, true);
    }

    /**
     * @param string Absolute path to migration file
     */
    protected function handle_file(string $file): void
    {
        $migration = basename($file);
        $parts     = explode('-', $migration);
        $migration_version   = $parts[0];

        // check if migration file is not for an even higher version
        if (version_compare($migration_version, $this->version_to, '>')) {
            return;
        }

        // check if we ran migration file before.
        if (version_compare($this->version_from, $migration_version, '>=')) {
            return;
        }

        // run migration file
        include $file;

        // update option so later runs start after this migration
        $this->version_from = $migration_version;
        update_option($this->option_name, $migration_version, true);
    }
}
