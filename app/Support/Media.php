<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class Media
{
    public static function disk(): string
    {
        return config('valletta.media_disk');
    }

    public static function url(?string $path): string
    {
        return $path ? Storage::disk(self::disk())->url($path) : '';
    }
}
