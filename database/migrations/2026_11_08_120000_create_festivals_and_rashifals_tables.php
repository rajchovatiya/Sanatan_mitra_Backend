<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Text columns marked json hold {"en": "...", "hi": "...", "gu": "..."}. */
    public function up(): void
    {
        Schema::create('festivals', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_vrat')->default(false);
            $table->json('name');
            $table->json('tithi')->nullable();
            $table->json('about')->nullable();
            // One ritual step per line, per language.
            $table->json('rituals')->nullable();
            $table->date('date');
            $table->string('image_path')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['is_active', 'date']);
        });

        // One row per rashi per day, written by the admin.
        Schema::create('rashifals', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->unsignedTinyInteger('rashi');
            $table->json('overall');
            $table->json('love')->nullable();
            $table->json('career')->nullable();
            $table->json('health')->nullable();
            $table->json('money')->nullable();
            // {"overall": 4, "love": 3, ...}, 1 to 5.
            $table->json('stars')->nullable();
            $table->unsignedTinyInteger('lucky_number')->nullable();
            $table->json('lucky_colour')->nullable();
            $table->string('lucky_time', 30)->nullable();
            $table->json('lucky_direction')->nullable();
            $table->timestamps();
            $table->unique(['date', 'rashi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rashifals');
        Schema::dropIfExists('festivals');
    }
};
