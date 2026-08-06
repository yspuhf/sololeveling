<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('level')->default(1);
            $table->unsignedInteger('xp')->default(0);
            $table->unsignedBigInteger('gold')->default(0);
            $table->unsignedInteger('skill_points')->default(0);
            $table->unsignedInteger('current_streak')->default(0);
            $table->unsignedInteger('highest_streak')->default(0);
            $table->string('rank')->default('E-Rank'); // E-Rank, D-Rank, C-Rank, B-Rank, A-Rank, S-Rank, National Rank, Monarch Rank
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['level', 'xp', 'gold', 'skill_points', 'current_streak', 'highest_streak', 'rank']);
        });
    }
};
