<?php

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/** Uploaded images and audio, on the disk set by MEDIA_DISK (this server now, cloud storage later). */
final class Media
{
    public static function disk(): Filesystem
    {
        return Storage::disk(config('sanatan.media_disk'));
    }

    /** Saves under a random name; returns the stored path. */
    public static function store(UploadedFile $file, string $folder): string
    {
        return $file->store($folder, config('sanatan.media_disk'));
    }

    /** Stores the new file (if given) and deletes the old one; returns the path to keep. */
    public static function replace(?UploadedFile $file, ?string $old, string $folder): ?string
    {
        if (! $file) {
            return $old;
        }

        $path = self::store($file, $folder);
        self::delete($old);

        return $path;
    }

    public static function delete(?string ...$paths): void
    {
        $paths = array_filter($paths);
        if ($paths) {
            self::disk()->delete($paths);
        }
    }

    public static function url(?string $path): ?string
    {
        return $path ? self::disk()->url($path) : null;
    }
}
