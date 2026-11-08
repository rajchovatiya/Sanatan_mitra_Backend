<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Text columns marked json hold {"en": "...", "hi": "...", "gu": "..."}. */
    public function up(): void
    {
        Schema::create('temples', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->default('other');
            $table->json('name');
            $table->json('location')->nullable();
            $table->string('state', 60)->nullable();
            $table->json('deity')->nullable();
            $table->json('about')->nullable();
            $table->json('timings')->nullable();
            $table->json('best_time')->nullable();
            $table->decimal('lat', 9, 6)->nullable();
            $table->decimal('lng', 9, 6)->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['is_active', 'type', 'sort_order']);
            $table->index('state');
        });

        // Today's darshan photo (and optional live/YouTube link); the latest one up to today is shown.
        Schema::create('darshans', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->foreignId('temple_id')->nullable()->constrained()->nullOnDelete();
            $table->json('title');
            $table->string('image_path');
            $table->string('video_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('darshans');
        Schema::dropIfExists('temples');
    }
};
