<?php

namespace KokoAnalytics;

class Blocklist
{
    protected ?array $list = null;

    protected function loadFromFile(): array
    {
        $filename = KOKO_ANALYTICS_PLUGIN_DIR . '/data/referrer-blocklist';
        if (!is_file($filename)) {
            return [];
        }

        return \file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    }

    /**
     * Ensures the blocklist is loaded into memory.
     */
    protected function load(): void
    {
        if ($this->list !== null) {
            return;
        }

        // run custom blocklist first
        // @see https://github.com/ibericode/koko-analytics/blob/main/code-snippets/add-domains-to-referrer-blocklist.php
        $custom_blocklist = (array) apply_filters('koko_analytics_referrer_blocklist', []);
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

        $url = strtolower($url);
        foreach ($this->list as $domain) {
            if ($domain && \str_contains($url, $domain)) {
                return true;
            }
        }

        return false;
    }
}
