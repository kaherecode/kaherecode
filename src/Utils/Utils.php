<?php

namespace App\Utils;

class Utils
{
    private const WORD_PER_MIN = 250;

    public static function calculateReadTime(string $content): int
    {
        $wordCount = str_word_count(strip_tags($content));
        return (int) ceil($wordCount / self::WORD_PER_MIN);
    }
}
