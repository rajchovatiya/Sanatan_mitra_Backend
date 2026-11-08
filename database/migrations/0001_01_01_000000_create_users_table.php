<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * App users sign in with a mobile OTP, so there is no password or email.
     * Admins get their own table when the admin panel is built.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('mobile', 15)->unique();
            $table->string('name', 80)->nullable();
            $table->string('gender', 10)->nullable();
            $table->date('birth_date')->nullable();
            $table->time('birth_time')->nullable();
            $table->string('birth_place', 120)->nullable();
            $table->decimal('birth_lat', 9, 6)->nullable();
            $table->decimal('birth_lng', 9, 6)->nullable();
            $table->string('city', 80)->nullable();
            // 0 = Mesha ... 11 = Meena.
            $table->unsignedTinyInteger('rashi')->nullable();
            $table->string('language', 2)->default('en');
            $table->boolean('notifications_enabled')->default(true);
            $table->string('photo_path')->nullable();

            // Details printed on the user's posters.
            $table->string('business_type', 20)->nullable();
            $table->string('business_name', 100)->nullable();
            $table->string('business_address', 255)->nullable();
            $table->string('business_logo_path')->nullable();
            $table->string('other_details', 255)->nullable();

            $table->string('status', 10)->default('active')->index();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('users');
    }
};
