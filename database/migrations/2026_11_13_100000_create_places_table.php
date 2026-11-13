<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Birth places: every Indian city, town and village plus big world cities (GeoNames, free). */
    public function up(): void
    {
        Schema::create('places', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary(); // GeoNames id
            $table->string('name', 120);
            $table->string('search_name', 120); // lower-case ASCII, for prefix search
            $table->string('district', 120)->nullable();
            $table->string('state', 120)->nullable();
            $table->char('country_code', 2);
            $table->decimal('lat', 9, 6);
            $table->decimal('lng', 9, 6);
            $table->string('timezone', 40);
            $table->unsignedInteger('population')->default(0);
            $table->index(['search_name', 'population']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('places');
    }
};
