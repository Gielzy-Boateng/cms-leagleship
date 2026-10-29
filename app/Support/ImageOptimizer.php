<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use RuntimeException;

/**
 * Stores uploads on the public disk as-is. Shared hosting often fatals when GD
 * re-encodes large Canva / camera exports, so we never decode images in PHP.
 * Oversized files are still compressed in the browser before Livewire upload.
 */
final class ImageOptimizer
{
    public static function store(UploadedFile $file, string $directory): string
    {
        return self::storeOriginal($file, $directory);
    }

    /**
     * Store the upload without re-encoding.
     */
    public static function storeOriginal(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, 'public')
            ?: throw new RuntimeException('Unable to store the uploaded image.');
    }
}
