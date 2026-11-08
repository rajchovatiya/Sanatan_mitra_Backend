<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Text columns marked json hold {"en": "...", "hi": "...", "gu": "..."}. */
    public function up(): void
    {
        // Muhurat types (Vivah, Griha Pravesh...) are categories of type "muhurat" with a description.
        Schema::table('categories', function (Blueprint $table) {
            $table->json('description')->nullable()->after('name');
        });

        Schema::create('muhurat_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->date('date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->json('note')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['category_id', 'is_active', 'date']);
        });

        // Reference content for Gems, Rudraksha, Tarot, Numerology, Palmistry and Graphology.
        Schema::create('guides', function (Blueprint $table) {
            $table->id();
            $table->string('module', 20);
            $table->json('title');
            $table->json('subtitle')->nullable();
            $table->json('body')->nullable();
            // One "Label: value" per line (e.g. "Planet: Surya").
            $table->json('facts')->nullable();
            // One point per line (benefits, readings...).
            $table->json('points')->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['module', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guides');
        Schema::dropIfExists('muhurat_dates');
        Schema::table('categories', fn (Blueprint $table) => $table->dropColumn('description'));
    }
};
