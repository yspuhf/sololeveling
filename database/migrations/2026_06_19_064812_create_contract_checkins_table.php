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
        Schema::create('contract_checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('system_contracts')->onDelete('cascade');
            $table->unsignedInteger('day_number'); // 1 to duration_days (up to 71)
            $table->timestamp('completed_at')->nullable();
            $table->boolean('is_checked')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_checkins');
    }
};
