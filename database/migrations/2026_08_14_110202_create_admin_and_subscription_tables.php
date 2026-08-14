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
        // 1. roles
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // 2. user_roles
        Schema::create('user_roles', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->primary(['user_id', 'role_id']);
        });

        // 3. plans
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedInteger('price'); // in Rs
            $table->unsignedInteger('duration'); // in days
            $table->unsignedInteger('contract_limit');
            $table->boolean('elite_skill_access');
            $table->boolean('personal_domain_access');
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();
        });

        // 4. subscriptions
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('active'); // active, expired, cancelled
            $table->timestamp('started_at');
            $table->timestamp('expires_at');
            $table->timestamps();
        });

        // 5. payments
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->nullable()->constrained()->onDelete('set null');
            $table->string('transaction_id')->unique();
            $table->unsignedInteger('amount');
            $table->string('currency')->default('INR');
            $table->string('gateway')->default('UPI/Razorpay');
            $table->string('status')->default('successful'); // successful, failed, pending, refunded
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        // 6. feature_flags
        Schema::create('feature_flags', function (Blueprint $table) {
            $table->id();
            $table->string('feature_key')->unique(); // e.g. registration, contracts, skills, domains
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        // 7. user_feature_overrides
        Schema::create('user_feature_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('feature_key');
            $table->boolean('enabled');
            $table->text('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // 8. admin_audit_logs
        Schema::create('admin_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->string('action');
            $table->string('target_type')->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->text('reason')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_audit_logs');
        Schema::dropIfExists('user_feature_overrides');
        Schema::dropIfExists('feature_flags');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plans');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('roles');
    }
};
