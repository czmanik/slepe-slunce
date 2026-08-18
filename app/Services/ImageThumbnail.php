<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class ImageThumbnail
{
    /** @var array<string, array{width: int, height: int}> */
    private const VARIANTS = [
        'small' => ['width' => 720, 'height' => 540],
        'medium' => ['width' => 1440, 'height' => 1080],
    ];

    public function supported(): bool
    {
        return function_exists('imagecreatefromstring')
            && function_exists('imagecreatetruecolor')
            && (function_exists('imagewebp') || function_exists('imagejpeg'));
    }

    public function generate(?string $path, bool $force = false): bool
    {
        if (! $path || ! $this->supported()) {
            return false;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            return false;
        }

        $bytes = $disk->get($path);
        $source = @imagecreatefromstring($bytes);

        if (! $source) {
            return false;
        }

        $source = $this->orient($source, $disk->path($path));
        $created = false;

        foreach (self::VARIANTS as $variant => $size) {
            $thumbnailPath = $this->path($path, $variant);

            if (! $force && $disk->exists($thumbnailPath)) {
                $created = true;
                continue;
            }

            $width = imagesx($source);
            $height = imagesy($source);
            $scale = min($size['width'] / $width, $size['height'] / $height, 1);
            $targetWidth = max(1, (int) round($width * $scale));
            $targetHeight = max(1, (int) round($height * $scale));
            $thumbnail = imagecreatetruecolor($targetWidth, $targetHeight);

            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
            $transparent = imagecolorallocatealpha($thumbnail, 0, 0, 0, 127);
            imagefill($thumbnail, 0, 0, $transparent);
            imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

            ob_start();
            $encoded = function_exists('imagewebp')
                ? imagewebp($thumbnail, null, 82)
                : imagejpeg($thumbnail, null, 84);
            $contents = ob_get_clean();
            imagedestroy($thumbnail);

            if ($encoded && is_string($contents)) {
                $disk->put($thumbnailPath, $contents, ['visibility' => 'public']);
                $created = true;
            }
        }

        imagedestroy($source);

        return $created;
    }

    public function url(?string $path, string $variant = 'medium'): ?string
    {
        if (! $path) {
            return null;
        }

        $thumbnailPath = $this->path($path, $variant);

        return Storage::disk('public')->url(
            Storage::disk('public')->exists($thumbnailPath) ? $thumbnailPath : $path,
        );
    }

    public function originalUrl(?string $path): ?string
    {
        return $path ? Storage::disk('public')->url($path) : null;
    }

    public function path(string $path, string $variant): string
    {
        if (! isset(self::VARIANTS[$variant])) {
            throw new \InvalidArgumentException("Unknown thumbnail variant [{$variant}].");
        }

        $extension = function_exists('imagewebp') ? 'webp' : 'jpg';

        return 'thumbnails/'.$variant.'/'.ltrim($path, '/').'.'.$extension;
    }

    private function orient(\GdImage $image, string $path): \GdImage
    {
        if (! function_exists('exif_read_data') || @mime_content_type($path) !== 'image/jpeg') {
            return $image;
        }

        $exif = @exif_read_data($path);
        $orientation = (int) (is_array($exif) ? ($exif['Orientation'] ?? 1) : 1);

        if (in_array($orientation, [2, 4, 5, 7], true) && function_exists('imageflip')) {
            imageflip($image, in_array($orientation, [2, 5], true) ? IMG_FLIP_HORIZONTAL : IMG_FLIP_VERTICAL);
        }

        $angle = match ($orientation) {
            3, 4 => 180,
            5, 6 => -90,
            7, 8 => 90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);

        if ($rotated) {
            imagedestroy($image);

            return $rotated;
        }

        return $image;
    }
}
