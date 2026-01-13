<?php

namespace KokoAnalytics;

use Exception;

class Blocklist
{
    public function getFilename(): string
    {
        return  KOKO_ANALYTICS_PLUGIN_DIR . '/data/referrer-blocklist';
    }

    public function read(): array
    {
        $filename = $this->getFilename();
        if (!is_file($filename)) {
            return [];
        }

        return \file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    }

    public function contains(string $url): bool
    {
        if ($url === '') {
            return false;
        }

        static $list;
        if ($list === null) {
            $list = $this->read();
        }

        foreach ($list as $domain) {
            $domain = trim($domain);

            if ($domain === '') {
                continue;
            }

            if (str_contains($url, $domain)) {
                return true;
            }
        }

        return false;
    }
}
