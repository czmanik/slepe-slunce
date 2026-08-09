<?php

namespace App\Support;

class YouTube
{
    public static function id(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $patterns = [
            '~youtu\.be/([A-Za-z0-9_-]{11})~',
            '~youtube(?:-nocookie)?\.com/(?:watch\?v=|embed/|shorts/)([A-Za-z0-9_-]{11})~',
            '~[?&]v=([A-Za-z0-9_-]{11})~',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }
}
