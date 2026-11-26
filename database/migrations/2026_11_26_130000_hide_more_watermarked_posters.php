<?php

use App\Models\Poster;
use Illuminate\Database\Migrations\Migration;

/**
 * Hides 18 more posters that carry another owner's watermark, logo, name or
 * a real temple / guru photo (copyright risk for the Play Store), found in the
 * 2026-11-26 review. Matched by image file. Only hidden: Admin → Posters can
 * show them again with the owner's permission.
 */
return new class extends Migration
{
    private const IMAGES = [
        // ID 303: Radhakrishna Dev
        'posters/0SIabOIHyBChngPhE5DDZ6ZqvI0t2CEpR2RLLsId.jpg',
        // ID 308: Download (45)
        'posters/QehIO69qnXFhOG5VXfTIRMsHiCOVYgYTRhyYWRTy.jpg',
        // ID 309: Shree Swaminarayan Mandir Vadtal
        'posters/1pT6MZxBn37pIW2cNp95EgJXuEOUlfMuTHS7WZmF.jpg',
        // ID 313: Download (43)
        'posters/gqAIDfAtBRlHjy07s67HVk10aEIHkbKGu4agPbMM.jpg',
        // ID 314: Harikrushna Maharaj
        'posters/KVSd1fVcogz24tnRQCY1Qm52tTt3WRhyPzmUCk65.jpg',
        // ID 84: Download (28)
        'posters/O6EczyoZx6kktnVTRnDeT2Q8jPXB5hYYRDV3DmIv.jpg',
        // ID 61: खाटू श्याम जी हारे का सहारा बाबा श्याम हमारा 
        'posters/9jz3Zsw8yNj3ABsmsWJumbNqQHFbLw5pCAZeb9OR.jpg',
        // ID 44: a
        'posters/M4Ks5j2BTNc5Uwunm2bHk53AQtSnJkD0snhrNjnB.jpg',
        // ID 42: h
        'posters/HVF69qGiF1BSFhmuPP3Weef6axhoPickYjJCJuNr.jpg',
        // ID 39: a
        'posters/xhEDoGQpOmiCNpwc6vSLWpLylKmD5jMUmNLijmpi.jpg',
        // ID 23: a
        'posters/DbO7F1b5gFOJniFpS4QvpjdKDxmnLO3XSSeBsRtc.jpg',
        // ID 13: Good Morning
        'posters/sample-suprabhat-10.jpg',
        // ID 19: Bhagwan
        'posters/jT3Dm7BkC47FW5WEyfxh0bSVICvt7YiwmWBNenGf.jpg',
        // ID 301: Download (47)
        'posters/SOM4iDnTrLVhFS6zuzHUhbst5OVRJyfsH57ou9xL.jpg',
        // ID 302: Download (46)
        'posters/zctGmmUQzbp0ZOEKhLjf7vV8UQlBPKziDevYQoL1.jpg',
        // ID 307: Shree Swaminarayan Mandir
        'posters/jRAKPbsMfarxzezFpQ7fylom0QrhTE79y9cVEtHV.jpg',
        // ID 310: Download (44)
        'posters/IqDfSA1s7gmFvGf2Y49c4XJp9jhnvIPu0jDgfm88.jpg',
        // ID 114: dasrhan
        'posters/h0QPxLNRCHmDimSJQ9ZIaef3eIRzpSuYkn22WH0S.jpg',
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
