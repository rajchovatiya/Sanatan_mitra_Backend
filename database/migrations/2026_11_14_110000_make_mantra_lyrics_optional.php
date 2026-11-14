<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Lyrics are optional: an audio-only mantra shows the deity picture in the app. */
    public function up(): void
    {
        Schema::table('mantras', fn (Blueprint $table) => $table->json('lyrics')->nullable()->change());
    }

    public function down(): void
    {
        Schema::table('mantras', fn (Blueprint $table) => $table->json('lyrics')->nullable(false)->change());
    }
};
