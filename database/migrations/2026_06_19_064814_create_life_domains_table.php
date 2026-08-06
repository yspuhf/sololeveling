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
        Schema::create('life_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('health_physical_score')->default(10);
            $table->unsignedInteger('health_mental_score')->default(10);
            $table->unsignedInteger('finance_score')->default(10);
            $table->unsignedInteger('relationship_score')->default(10);
            $table->unsignedInteger('career_score')->default(10);
            $table->unsignedInteger('spirituality_score')->default(10);
            $table->unsignedInteger('overall_life_score')->default(10);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('life_domains');
    }
};
