<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Birth details a user saves for themselves and family, used for Kundli and Matching. */
    public function up(): void
    {
        Schema::create('kundlis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 80);
            $table->string('relation', 30)->nullable();
            $table->string('gender', 10);
            $table->date('birth_date');
            $table->time('birth_time');
            $table->string('birth_place', 120);
            $table->decimal('lat', 9, 6);
            $table->decimal('lng', 9, 6);
            $table->decimal('tz_offset', 4, 2)->default(5.5);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kundlis');
    }
};
