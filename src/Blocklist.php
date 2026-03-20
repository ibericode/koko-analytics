<?php

namespace KokoAnalytics;

// TODO: This class should not use any static variables
// TODO: This class should handle the entire blocklist logic, including the filter hook, instead of just providing a method to check if a URL is on the blocklist. This would allow us to remove all blocklist-related code from the Pruner class and keep all blocklist logic in one place.
class Blocklist
{
    protected array $list = [];

    protected function loadFromFile(): array
    {
        $filename = KOKO_ANALYTICS_PLUGIN_DIR . '/data/referrer-blocklist';
        if (!is_file($filename)) {
            return [];
        }

        return \file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    }

    protected function load(): void
    {
        if (count($this->list) > 0) {
            return;
        }

        // run custom blocklist first
        // @see https://github.com/ibericode/koko-analytics/blob/main/code-snippets/add-domains-to-referrer-blocklist.php
        $custom_blocklist = apply_filters('koko_analytics_referrer_blocklist', []);
        $this->list = array_merge($custom_blocklist, $this->loadFromFile());
    }

    public function all(): array
    {
        $this->load();
        return $this->list;
    }

    public function contains(string $url): bool
    {
        if ($url === '') {
            return false;
        }

        $this->load();

        foreach ($this->list as $domain) {
            if ($domain !== '' && \str_contains($url, $domain)) {
                return true;
            }
        }

        return false;
    }
}
