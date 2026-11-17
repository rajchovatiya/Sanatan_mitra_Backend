<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Days of the week (1 = Monday … 7 = Sunday) on which the app shows this
     * poster first. Empty = no particular day.
     */
    public function up(): void
    {
        Schema::table('posters', fn (Blueprint $table) => $table->json('weekdays')->nullable()->after('date_on'));
    }

    public function down(): void
    {
        Schema::table('posters', fn (Blueprint $table) => $table->dropColumn('weekdays'));
    }
};
