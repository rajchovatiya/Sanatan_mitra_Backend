<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);
            $table->unsignedBigInteger('item_id');
            $table->timestamp('created_at')->nullable();
            $table->unique(['user_id', 'type', 'item_id']);
        });

        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('message');
            $table->string('contact', 100)->nullable();
            $table->string('platform', 10)->nullable();
            $table->string('app_version', 20)->nullable();
            $table->boolean('is_seen')->default(false)->index();
            $table->timestamps();
        });

        // Fixed pages such as privacy policy and terms; the admin edits their text.
        Schema::create('pages', function (Blueprint $table) {
            $table->string('slug', 30)->primary();
            $table->json('title');
            $table->json('body')->nullable();
            $table->timestamps();
        });

        DB::table('pages')->insert(array_map(fn ($slug, $title) => [
            'slug' => $slug, 'title' => json_encode(['en' => $title]), 'created_at' => now(), 'updated_at' => now(),
        ], ['privacy', 'terms', 'about'], ['Privacy Policy', 'Terms & Conditions', 'About Sanatan Mitra']));
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
        Schema::dropIfExists('feedback');
        Schema::dropIfExists('favorites');
    }
};
