<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Messages the admin sends to every app user (push + in-app list). */
    public function up(): void
    {
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('body');
            $table->string('image_path')->nullable();
            // Where a tap leads, e.g. "/festivals/5" inside the app, or an https link.
            $table->string('link')->nullable();
            $table->foreignId('admin_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('sent_at')->nullable()->index();
            $table->timestamps();
        });

        // Everything sent after this time counts as unread for the user.
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('notifications_read_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('notifications_read_at'));
        Schema::dropIfExists('app_notifications');
    }
};
