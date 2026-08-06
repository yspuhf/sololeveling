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
        Schema::create('system_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('duration_days'); // 7, 21, 51, 71
            $table->string('difficulty'); // Easy, Medium, Hard, Elite
            $table->unsignedInteger('xp_reward');
            $table->unsignedInteger('gold_reward');
            $table->string('status')->default('active'); // active, failed, completed
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_contracts');
    }
};
