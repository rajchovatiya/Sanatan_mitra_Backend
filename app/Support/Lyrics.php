<?php

namespace App\Support;

/**
 * Lyrics may carry a time before each line, LRC style: "[01:05.20] Jai Hanuman".
 * The app uses the times to highlight the line being sung; plain text is kept
 * for older app versions and for sharing.
 */
final class Lyrics
{
    /** One or more "[mm:ss]" / "[mm:ss.xx]" tags at the start of a line. */
    private const TAGS = '/^[ \t]*(?:\[\d{1,3}:\d{2}(?:[.:]\d{1,3})?\][ \t]*)+/m';

    public static function hasTimes(?string $text): bool
    {
        return $text !== null && preg_match(self::TAGS, $text) === 1;
    }

    /** The lyrics without any time tags. */
    public static function plain(?string $text): ?string
    {
        return $text === null ? null : preg_replace(self::TAGS, '', $text);
    }
}
