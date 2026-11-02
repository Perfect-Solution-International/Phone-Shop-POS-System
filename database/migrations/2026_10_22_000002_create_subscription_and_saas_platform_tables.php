<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('monthly_price', 12, 2)->default(0.00);
            $table->decimal('annual_price', 12, 2)->nullable();
            $table->unsignedSmallInteger('trial_days')->default(7);
            $table->unsignedSmallInteger('max_branches')->default(1);
            $table->unsignedSmallInteger('max_staff')->default(3);
            $table->unsignedInteger('max_products')->nullable();
            $table->boolean('has_imei_tracking')->default(true);
            $table->boolean('has_repair_management')->default(true);
            $table->boolean('has_warranty_management')->default(true);
            $table->boolean('has_accounting_reports')->default(true);
            $table->boolean('has_multi_branch')->default(false);
            $table->boolean('has_customer_portal')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('subscription_plans')->restrictOnDelete();
            $table->string('status')->default('trial'); // trial, active, grace_period, suspended, cancelled, expired
            $table->dateTime('trial_ends_at')->nullable();
            $table->dateTime('current_period_starts_at');
            $table->dateTime('current_period_ends_at');
            $table->dateTime('grace_period_ends_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['shop_id', 'status']);
        });

        Schema::create('subscription_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('subscription_plans')->restrictOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method')->default('bank_transfer'); // online, bank_transfer
            $table->string('status')->default('pending'); // pending, under_review, approved, rejected, refunded
            $table->string('transaction_id')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('receipt_url')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->dateTime('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['shop_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('subscription_plans');
    }
};

