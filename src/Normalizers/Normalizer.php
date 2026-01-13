<?php

namespace KokoAnalytics\Normalizers;

class Normalizer
{
    public static function path(string $value): string
    {
        return Path::normalize($value);
    }

    public static function referrer(string $value): string
    {
        return Referrer::normalize($value);
    }
}
