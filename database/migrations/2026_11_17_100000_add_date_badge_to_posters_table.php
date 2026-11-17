<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Date badge on a poster: shown or not, in which corner, and an optional
     * fixed date (empty = the day the user shares the poster).
     */
    public function up(): void
    {
        Schema::table('posters', function (Blueprint $table) {
            $table->boolean('show_date')->default(false)->after('footer_below');
            $table->string('date_position', 16)->default('top_right')->after('show_date');
            $table->date('date_on')->nullable()->after('date_position');
        });
    }

    public function down(): void
    {
        Schema::table('posters', fn (Blueprint $table) => $table->dropColumn(['show_date', 'date_position', 'date_on']));
    }
};
