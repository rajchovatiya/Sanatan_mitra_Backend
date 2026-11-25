<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Login moves from mobile OTP (paid SMS) to Google Sign-In. The mobile number
 * stays as a normal profile field (printed on posters), so it is optional and
 * no longer unique.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id', 64)->nullable()->unique()->after('id');
            $table->string('email', 191)->nullable()->index()->after('google_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['mobile']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('mobile', 15)->nullable()->change();
            $table->index('mobile');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['mobile']);
            $table->dropIndex(['email']);
            $table->dropUnique(['google_id']);
            $table->dropColumn(['google_id', 'email']);
        });
    }
};
