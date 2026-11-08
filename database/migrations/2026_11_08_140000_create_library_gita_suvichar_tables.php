<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Text columns marked json hold {"en": "...", "hi": "...", "gu": "..."}. */
    public function up(): void
    {
        // Vrat Katha, Aarti & Prayer, Beej Mantra, Chalisa & Stotra.
        Schema::create('library_items', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);
            $table->json('title');
            $table->json('subtitle')->nullable();
            $table->json('body');
            $table->string('image_path')->nullable();
            $table->string('audio_path')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['type', 'is_active', 'sort_order']);
        });

        Schema::create('gita_chapters', function (Blueprint $table) {
            $table->unsignedTinyInteger('number')->primary();
            $table->json('name');
            $table->json('summary')->nullable();
            $table->unsignedSmallInteger('verse_count');
            $table->timestamps();
        });

        Schema::create('gita_verses', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('chapter');
            $table->unsignedSmallInteger('verse');
            $table->text('sanskrit');
            $table->text('transliteration')->nullable();
            $table->json('meaning');
            // Featured verses rotate as the "verse of the day".
            $table->boolean('is_featured')->default(false)->index();
            $table->timestamps();
            $table->unique(['chapter', 'verse']);
            $table->foreign('chapter')->references('number')->on('gita_chapters');
        });

        Schema::create('suvichars', function (Blueprint $table) {
            $table->id();
            // Set for a special day; empty ones rotate daily.
            $table->date('date')->nullable()->index();
            $table->json('text');
            $table->json('author')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // The 18 chapters are fixed, so they ship with the schema; admins add translations and verses.
        $chapters = [
            ['Arjuna Vishada Yoga', 47], ['Sankhya Yoga', 72], ['Karma Yoga', 43], ['Jnana Karma Sanyasa Yoga', 42],
            ['Karma Sanyasa Yoga', 29], ['Dhyana Yoga', 47], ['Jnana Vijnana Yoga', 30], ['Akshara Brahma Yoga', 28],
            ['Raja Vidya Raja Guhya Yoga', 34], ['Vibhuti Yoga', 42], ['Vishwaroopa Darshana Yoga', 55], ['Bhakti Yoga', 20],
            ['Kshetra Kshetrajna Vibhaga Yoga', 35], ['Gunatraya Vibhaga Yoga', 27], ['Purushottama Yoga', 20],
            ['Daivasura Sampad Vibhaga Yoga', 24], ['Shraddhatraya Vibhaga Yoga', 28], ['Moksha Sanyasa Yoga', 78],
        ];
        DB::table('gita_chapters')->insert(array_map(fn ($c, $i) => [
            'number' => $i + 1, 'name' => json_encode(['en' => $c[0]]), 'verse_count' => $c[1],
            'created_at' => now(), 'updated_at' => now(),
        ], $chapters, array_keys($chapters)));
    }

    public function down(): void
    {
        Schema::dropIfExists('suvichars');
        Schema::dropIfExists('gita_verses');
        Schema::dropIfExists('gita_chapters');
        Schema::dropIfExists('library_items');
    }
};
