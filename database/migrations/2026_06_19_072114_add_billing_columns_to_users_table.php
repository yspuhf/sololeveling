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
            $table->timestamp('contracts_trial_started_at')->nullable();
            $table->timestamp('domains_trial_started_at')->nullable();
            $table->timestamp('skills_trial_started_at')->nullable();
            $table->boolean('is_contracts_paid')->default(false);
            $table->boolean('is_domains_paid')->default(false);
            $table->boolean('is_skills_paid')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'contracts_trial_started_at',
                'domains_trial_started_at',
                'skills_trial_started_at',
                'is_contracts_paid',
                'is_domains_paid',
                'is_skills_paid',
            ]);
        });
    }
};
