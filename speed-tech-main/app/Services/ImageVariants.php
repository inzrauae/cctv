<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

/**
 * Smaller WebP copies of a photo so phones don't download desktop-sized images.
 *
 * "assets/hero/solar-roof.webp" gets "solar-roof-640w.webp" and "solar-roof-1024w.webp" beside it.
 * Pages list them in srcset and the browser picks the smallest one that is sharp enough.
 */
class ImageVariants
{
    public const WIDTHS = [320, 640, 1024];

    private const QUALITY = 78;

    /** @var array<string, string> srcset strings already built in this request */
    private static array $cache = [];

    /** Create the missing variants for a file in public/. Returns how many were written. */
    public static function generate(string $relativePath, bool $force = false): int
    {
        $source = public_path($relativePath);
        if (! is_file($source) || self::isVariant($source) || ! function_exists('imagewebp')) {
            return 0;
        }

        $size = @getimagesize($source);
        if (! $size) {
            return 0;
        }
        [$width, $height] = $size;

        $image = null;
        $written = 0;
        foreach (self::WIDTHS as $target) {
            $out = self::variantPath($source, $target);
            if ($target >= $width || (! $force && is_file($out))) {
                continue;
            }
            $image ??= self::load($source, $size[2]);
            if (! $image) {
                return $written;
            }

            $resized = imagecreatetruecolor($target, (int) round($height * $target / $width));
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, imagesx($resized), imagesy($resized), $width, $height);
            imagewebp($resized, $out, self::QUALITY);
            imagedestroy($resized);
            $written++;
        }
        if ($image) {
            imagedestroy($image);
        }

        return $written;
    }

    /** Remove a file's variants (used when an uploaded product photo is replaced or deleted). */
    public static function delete(string $relativePath): void
    {
        foreach (self::WIDTHS as $target) {
            File::delete(self::variantPath(public_path($relativePath), $target));
        }
    }

    /**
     * srcset value for an image in public/: its variants plus the original at full width.
     * Empty when there are no variants, so the plain src is used.
     */
    public static function srcset(?string $relativePath): string
    {
        if (! $relativePath || str_contains($relativePath, '://')) {
            return '';
        }

        return self::$cache[$relativePath] ??= (function () use ($relativePath) {
            $source = public_path($relativePath);
            $size = is_file($source) ? @getimagesize($source) : false;
            if (! $size) {
                return '';
            }

            $candidates = [];
            foreach (self::WIDTHS as $target) {
                if ($target < $size[0] && is_file(self::variantPath($source, $target))) {
                    $candidates[] = asset(self::variantPath($relativePath, $target)).' '.$target.'w';
                }
            }
            if (! $candidates) {
                return '';
            }
            $candidates[] = asset($relativePath).' '.$size[0].'w';

            return implode(', ', $candidates);
        })();
    }

    public static function variantPath(string $path, int $width): string
    {
        $info = pathinfo($path);
        $dir = $info['dirname'] === '.' ? '' : $info['dirname'].'/';

        return $dir.$info['filename'].'-'.$width.'w.webp';
    }

    public static function isVariant(string $path): bool
    {
        return (bool) preg_match('/-\d+w\.webp$/', $path);
    }

    private static function load(string $path, int $type): ?\GdImage
    {
        $image = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_WEBP => @imagecreatefromwebp($path),
            default => false,
        };
        if (! $image) {
            return null;
        }
        if (! imageistruecolor($image)) {
            imagepalettetotruecolor($image);
        }

        return $image;
    }
}
