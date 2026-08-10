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
        match ($mime) {
            'image/jpeg' => @imagejpeg($image, $path, 90),
            'image/png' => @imagepng($image, $path, 6),
            'image/webp' => function_exists('imagewebp') ? @imagewebp($image, $path, 90) : false,
            default => false,
        };
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
