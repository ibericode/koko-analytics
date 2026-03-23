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

    public function run(): void
    {
        $pending = $this->get_pending();
        foreach ($pending as $file) {
            // execute migration file in scoped function to prevent variable leakage
            $this->execute($this->directory . '/' . $file);

            // extract version from filename and update option
            $version = (int) strtok($file, '-');
            update_option($this->option_name, $version, true);
        }
    }

    protected function execute(string $file): void
    {
        include $file;
    }
}
