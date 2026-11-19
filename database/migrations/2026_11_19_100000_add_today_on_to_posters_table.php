<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** The date a poster appears in the app's "Today All" (admin picks 5–15 per category for a day). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posters', function (Blueprint $table) {
            $table->date('today_on')->nullable()->index()->after('weekdays');
        });
    }

    public function down(): void
    {
        Schema::table('posters', function (Blueprint $table) {
            $table->dropIndex(['today_on']);
            $table->dropColumn('today_on');
        });
    }
};
