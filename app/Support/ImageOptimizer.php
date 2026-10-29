<?php

namespace App\Support;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Re-encodes uploads to WebP with a bounded long edge, so the API never serves
 * multi-megabyte camera originals. GD is used deliberately: it is already a
 * requirement of the `image` validation rule, so this adds no dependency.
 */
final class ImageOptimizer
{
    public const MAX_EDGE = 1920;

    public const QUALITY = 82;

    /** Decoding anything larger risks exhausting PHP's memory limit. */
    private const MAX_PIXELS = 24_000_000;

    public static function store(UploadedFile $file, string $directory): string
    {
        $bytes = self::encode($file);

        if ($bytes === null) {
            return $file->store($directory, 'public') ?: throw new RuntimeException('Unable to store the uploaded image.');
        }

        $path = trim($directory, '/').'/'.Str::random(40).'.webp';

        Storage::disk('public')->put($path, $bytes);

        return $path;
    }

    /**
     * Store the upload as-is. Used for finished Canva cards where re-encoding
     * only risks memory fatals on shared hosting.
     */
    public static function storeOriginal(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, 'public')
            ?: throw new RuntimeException('Unable to store the uploaded image.');
    }

    private static function encode(UploadedFile $file): ?string
    {
        $source = $file->getRealPath();

        if ($source === false || $source === '' || ! is_readable($source)) {
            return null;
        }

        $size = @getimagesize($source);

        if ($size === false || $size[0] * $size[1] > self::MAX_PIXELS) {
            return null;
        }

        $image = match ($size[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($source),
            IMAGETYPE_PNG => @imagecreatefrompng($source),
            IMAGETYPE_WEBP => @imagecreatefromwebp($source),
            default => null,
        };

        if (! $image instanceof GdImage) {
            return null;
        }

        $image = self::shrink(self::orient($image, $source, $size[2]));

        imagepalettetotruecolor($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        ob_start();
        $encoded = imagewebp($image, null, self::QUALITY);
        $bytes = (string) ob_get_clean();

        return $encoded && $bytes !== '' ? $bytes : null;
    }

    /** Phone photos carry their rotation in EXIF; browsers ignore it for raw files. */
    private static function orient(GdImage $image, string $source, int $type): GdImage
    {
        if ($type !== IMAGETYPE_JPEG || ! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($source);

        $angle = match ($exif['Orientation'] ?? 1) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);

        return $rotated instanceof GdImage ? $rotated : $image;
    }

    private static function shrink(GdImage $image): GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $edge = max($width, $height);

        if ($edge <= self::MAX_EDGE) {
            return $image;
        }

        $scale = self::MAX_EDGE / $edge;
        $width = (int) round($width * $scale);
        $height = (int) round($height * $scale);

        // libgd's bicubic filter refuses to downscale on some builds; bilinear is the safe choice.
        $scaled = imagescale($image, $width, $height, IMG_BILINEAR_FIXED)
            ?: imagescale($image, $width, $height, IMG_NEAREST_NEIGHBOUR);

        return $scaled instanceof GdImage ? $scaled : $image;
    }
}
