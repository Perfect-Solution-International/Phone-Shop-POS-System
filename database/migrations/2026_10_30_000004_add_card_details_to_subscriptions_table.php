<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table): void {
            if (! Schema::hasColumn('subscriptions', 'card_last4')) {
                $table->string('card_last4', 4)->nullable()->after('last_paid_at');
            }
            if (! Schema::hasColumn('subscriptions', 'card_brand')) {
                $table->string('card_brand', 50)->nullable()->after('card_last4');
            }
            if (! Schema::hasColumn('subscriptions', 'cardholder_name')) {
                $table->string('cardholder_name', 100)->nullable()->after('card_brand');
            }
            if (! Schema::hasColumn('subscriptions', 'card_expiry')) {
                $table->string('card_expiry', 10)->nullable()->after('cardholder_name');
            }
            if (! Schema::hasColumn('subscriptions', 'payment_method')) {
                $table->string('payment_method', 50)->default('card')->after('card_expiry');
            }
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->dropColumn([
                'card_last4',
                'card_brand',
                'cardholder_name',
                'card_expiry',
                'payment_method',
            ]);
        });
    }
};

