<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Text columns marked json hold {"en": "...", "hi": "...", "gu": "..."}. */
    public function up(): void
    {
        // One table for every module's categories (poster, mantra, later library, wallpaper...).
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);
            $table->json('name');
            $table->string('icon_path')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['type', 'is_active', 'sort_order']);
        });

        Schema::create('posters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->json('title');
            $table->string('image_path');
            // Which of the app's fixed frames suits this poster (user name/photo/mobile placement).
            $table->string('frame', 30)->default('classic');
            $table->date('show_from')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['category_id', 'is_active', 'sort_order']);
        });

        Schema::create('mantras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->json('title');
            $table->json('deity');
            $table->json('lyrics');
            $table->json('meaning')->nullable();
            $table->string('image_path')->nullable();
            $table->string('audio_path')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['category_id', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mantras');
        Schema::dropIfExists('posters');
        Schema::dropIfExists('categories');
    }
};
