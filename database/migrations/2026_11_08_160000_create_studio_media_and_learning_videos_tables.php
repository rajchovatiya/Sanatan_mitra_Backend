<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Studio wallpapers and video templates (categories of type "wallpaper" / "video").
        Schema::create('studio_media', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 10);
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->json('title');
            $table->string('image_path');
            $table->string('video_path')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['kind', 'category_id', 'is_active', 'sort_order']);
        });

        // Live darshan/aarti streams and course lessons (YouTube links). Courses are categories of type "course".
        Schema::create('learning_videos', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 10);
            $table->foreignId('category_id')->nullable()->constrained()->restrictOnDelete();
            $table->json('title');
            $table->json('description')->nullable();
            $table->string('video_url');
            $table->string('thumbnail_path')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['kind', 'category_id', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_videos');
        Schema::dropIfExists('studio_media');
    }
};
