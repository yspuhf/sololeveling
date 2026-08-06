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
        Schema::create('elite_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('skill_name'); // e.g. Emotional Management (Shadow), Innovative Thinking (Elite), etc.
            $table->unsignedInteger('level')->default(1);
            $table->unsignedInteger('xp')->default(0);
            $table->json('sub_tracks_scores')->nullable(); // JSON format for internal metrics tracking
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('elite_skills');
    }
};
