<?php

use App\Models\Poster;
use Illuminate\Database\Migrations\Migration;

/**
 * Hides posters that carry another creator's watermark or logo (copyright
 * risk for the Play Store). Matched by image file, so nothing else changes.
 * They are only hidden: Admin → Posters can show them again.
 */
return new class extends Migration
{
    private const IMAGES = [
        // ID 304: "સર્વોપરી શ્રી હરિ" logo and @sarvoparishvihari watermark.
        'posters/i8VpYn67KC12f3W3DqhvEPWulfoiJnQGrxyRbgcX.jpg',
    ];

    public function up(): void
    {
        Poster::query()->whereIn('image_path', self::IMAGES)->update(['is_active' => false]);
    }

    public function down(): void
    {
        Poster::query()->whereIn('image_path', self::IMAGES)->update(['is_active' => true]);
    }
};
