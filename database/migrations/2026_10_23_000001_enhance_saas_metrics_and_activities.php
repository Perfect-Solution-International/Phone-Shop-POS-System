<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Enhance subscriptions with financial & cycle precision
        Schema::table('subscriptions', function (Blueprint $table): void {
            if (! Schema::hasColumn('subscriptions', 'billing_cycle')) {
                $table->string('billing_cycle', 20)->default('monthly')->after('status');
            }
            if (! Schema::hasColumn('subscriptions', 'recurring_amount')) {
                $table->decimal('recurring_amount', 12, 2)->nullable()->after('billing_cycle');
            }
            if (! Schema::hasColumn('subscriptions', 'discount_amount')) {
                $table->decimal('discount_amount', 12, 2)->default(0.00)->after('recurring_amount');
            }
            if (! Schema::hasColumn('subscriptions', 'last_paid_at')) {
                $table->dateTime('last_paid_at')->nullable()->after('discount_amount');
            }
        });

        // 2. Enhance subscription payments with refunds and cycle fields
        Schema::table('subscription_payments', function (Blueprint $table): void {
            if (! Schema::hasColumn('subscription_payments', 'billing_cycle')) {
                $table->string('billing_cycle', 20)->default('monthly')->after('payment_method');
            }
            if (! Schema::hasColumn('subscription_payments', 'discount_amount')) {
                $table->decimal('discount_amount', 12, 2)->default(0.00)->after('amount');
            }
            if (! Schema::hasColumn('subscription_payments', 'refunded_amount')) {
                $table->decimal('refunded_amount', 12, 2)->default(0.00)->after('discount_amount');
            }
            if (! Schema::hasColumn('subscription_payments', 'refunded_at')) {
                $table->dateTime('refunded_at')->nullable()->after('verified_at');
            }
            if (! Schema::hasColumn('subscription_payments', 'refund_reason')) {
                $table->text('refund_reason')->nullable()->after('rejection_reason');
            }
        });

        // 3. Create system activities table for administrative & security audit logging
        if (! Schema::hasTable('system_activities')) {
            Schema::create('system_activities', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('shop_id')->nullable()->constrained('shops')->nullOnDelete();
                $table->string('action', 80)->index();
                $table->string('description', 255);
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->json('properties')->nullable();
                $table->timestamp('created_at')->useCurrent()->index();

                $table->index(['shop_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('system_activities');

        Schema::table('subscription_payments', function (Blueprint $table): void {
            $table->dropColumn([
                'billing_cycle',
                'discount_amount',
                'refunded_amount',
                'refunded_at',
                'refund_reason',
            ]);
        });

        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->dropColumn([
                'billing_cycle',
                'recurring_amount',
                'discount_amount',
                'last_paid_at',
            ]);
        });
    }
};

