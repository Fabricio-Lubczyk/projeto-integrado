<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->unsignedBigInteger('steam_app_id')->nullable()->unique();
            $table->string('cover_url', 2048)->nullable();
            $table->json('screenshots')->nullable();
        });
        Schema::table('reviews', function (Blueprint $table) {
            $table->boolean('is_demo')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('reviews', fn (Blueprint $table) => $table->dropColumn('is_demo'));
        Schema::table('games', function (Blueprint $table) {
            $table->dropUnique(['steam_app_id']);
            $table->dropColumn(['steam_app_id', 'cover_url', 'screenshots']);
        });
    }
};
