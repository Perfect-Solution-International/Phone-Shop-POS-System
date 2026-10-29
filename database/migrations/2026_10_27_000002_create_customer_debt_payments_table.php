<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_debt_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method')->default('cash'); // cash, card, bank_transfer
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->date('payment_date');
            $table->timestamps();

            $table->index(['shop_id', 'customer_id']);
            $table->index(['shop_id', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_debt_payments');
    }
};

