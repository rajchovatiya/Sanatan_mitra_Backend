<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * For posters already full of text: the app puts the user's name and
     * contacts in a strip under the picture instead of over its bottom.
     */
    public function up(): void
    {
        Schema::table('posters', fn (Blueprint $table) => $table->boolean('footer_below')->default(false)->after('frame'));
    }

    public function down(): void
    {
        Schema::table('posters', fn (Blueprint $table) => $table->dropColumn('footer_below'));
    }
};
