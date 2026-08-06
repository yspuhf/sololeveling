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
        // Guilds Table
        Schema::create('guilds', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->foreignId('master_id')->constrained('users')->onDelete('cascade');
            $table->unsignedInteger('xp')->default(0);
            $table->unsignedInteger('level')->default(1);
            $table->timestamps();
        });

        // Guild Members Pivot Table
        Schema::create('guild_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guild_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('role')->default('member'); // master, officer, member
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();

            $table->unique(['guild_id', 'user_id']);
        });

        // Achievements Table
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique();
            $table->text('description');
            $table->unsignedInteger('xp_reward')->default(100);
            $table->unsignedInteger('gold_reward')->default(100);
            $table->string('badge_path')->nullable();
            $table->timestamps();
        });

        // User Achievements Pivot Table
        Schema::create('user_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('achievement_id')->constrained()->onDelete('cascade');
            $table->timestamp('unlocked_at')->useCurrent();
            $table->timestamps();

            $table->unique(['user_id', 'achievement_id']);
        });

        // Daily Quests Table
        Schema::create('daily_quests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('quest_date');
            $table->string('description');
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_quests');
        Schema::dropIfExists('user_achievements');
        Schema::dropIfExists('achievements');
        Schema::dropIfExists('guild_members');
        Schema::dropIfExists('guilds');
    }
};
