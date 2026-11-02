<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outlets', function (Blueprint $table): void {
            $table->string('billing_type')->default('included')->after('approval_status');
            $table->string('payment_status')->default('paid')->after('billing_type');
            $table->decimal('additional_fee', 10, 2)->default(0.00)->after('payment_status');
            $table->string('payment_method')->nullable()->after('additional_fee');
            $table->string('payment_reference')->nullable()->after('payment_method');
            $table->string('payment_receipt_url')->nullable()->after('payment_reference');
            $table->timestamp('paid_at')->nullable()->after('payment_receipt_url');

            $table->index(['shop_id', 'billing_type']);
            $table->index(['shop_id', 'payment_status']);
        });
    }

    public function down(): void
    {
        Schema::table('outlets', function (Blueprint $table): void {
            $table->dropIndex(['shop_id', 'billing_type']);
            $table->dropIndex(['shop_id', 'payment_status']);
            $table->dropColumn([
                'billing_type',
                'payment_status',
                'additional_fee',
                'payment_method',
                'payment_reference',
                'payment_receipt_url',
                'paid_at',
            ]);
        });
    }
};

