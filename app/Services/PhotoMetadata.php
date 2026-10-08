<?php

namespace App\Services;

class PhotoMetadata
{
    public function location(string $path): ?array
    {
        if (! function_exists('exif_read_data')) return null;
        $exif = @exif_read_data($path, 'GPS', true);
        $gps = $exif['GPS'] ?? [];
        if (! isset($gps['GPSLatitude'], $gps['GPSLatitudeRef'], $gps['GPSLongitude'], $gps['GPSLongitudeRef'])) return null;
        $latitude = $this->coordinate($gps['GPSLatitude'], $gps['GPSLatitudeRef']);
        $longitude = $this->coordinate($gps['GPSLongitude'], $gps['GPSLongitudeRef']);
        return $latitude !== null && $longitude !== null ? ['latitude' => $latitude, 'longitude' => $longitude] : null;
    }

    public function strip(string $path): void
    {
        if (! function_exists('imagecreatefromstring')) return;
        $bytes = @file_get_contents($path); $image = $bytes === false ? false : @imagecreatefromstring($bytes);
        if (! $image) return;
        $mime = function_exists('mime_content_type') ? @mime_content_type($path) : null;
        if ($mime === 'image/jpeg' || ($mime === 'image/webp' && function_exists('imagewebp'))) {
            // Re-encoding strips EXIF, but must not inflate a compressed mobile
            // upload back above the 5 MiB limit.
            $encoded = null;
            for ($scale = 0; $scale < 7; $scale++) {
                for ($quality = 85; $quality >= 45; $quality -= 10) {
                    ob_start();
                    $ok = $mime === 'image/jpeg' ? @imagejpeg($image, null, $quality) : @imagewebp($image, null, $quality);
                    $candidate = ob_get_clean();
                    if ($ok && is_string($candidate) && strlen($candidate) <= 5 * 1024 * 1024) {
                        $encoded = $candidate;
                        break 2;
                    }
                }
                $smaller = imagescale($image, max(1, (int) (imagesx($image) * .8)), max(1, (int) (imagesy($image) * .8)));
                if (! $smaller) break;
                imagedestroy($image);
                $image = $smaller;
            }
            if ($encoded !== null) file_put_contents($path, $encoded);
        } elseif ($mime === 'image/png') {
            @imagepng($image, $path, 6);
        }
        imagedestroy($image);
    }

    private function coordinate(array $parts, string $reference): ?float
    {
        if (count($parts) < 3) return null;
        $values = array_map(fn ($part): float => $this->rational((string) $part), array_values($parts));
        $coordinate = $values[0] + $values[1] / 60 + $values[2] / 3600;
        return in_array(strtoupper($reference), ['S', 'W'], true) ? -$coordinate : $coordinate;
    }

    private function rational(string $value): float
    {
        if (! str_contains($value, '/')) return (float) $value;
        [$numerator, $denominator] = array_map('floatval', explode('/', $value, 2));
        return $denominator == 0.0 ? 0.0 : $numerator / $denominator;
    }
}
