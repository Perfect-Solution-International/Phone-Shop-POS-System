<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Used Phone Buying & Trade-Ins
        Schema::create('phone_trade_ins', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained()->restrictOnDelete();
            $table->foreignId('outlet_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('phone_device_id')->nullable()->constrained('phone_devices')->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_phone', 50);
            $table->string('customer_nic', 50)->nullable();
            $table->string('brand');
            $table->string('model');
            $table->string('storage', 50)->nullable();
            $table->string('ram', 50)->nullable();
            $table->string('color', 50)->nullable();
            $table->string('imei_1');
            $table->string('imei_2')->nullable();
            $table->json('condition_checklist')->nullable();
            $table->decimal('valuation_amount', 12, 2);
            $table->string('payout_type', 30)->default('cash'); // cash, trade_in_credit, bank_transfer
            $table->string('status', 30)->default('completed'); // completed, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['shop_id', 'status']);
        });

        // 2. Branch-to-Branch Stock Transfers
        Schema::create('stock_transfers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained()->restrictOnDelete();
            $table->string('transfer_number', 50);
            $table->foreignId('from_outlet_id')->constrained('outlets')->restrictOnDelete();
            $table->foreignId('to_outlet_id')->constrained('outlets')->restrictOnDelete();
            $table->foreignId('dispatched_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('in_transit'); // pending, in_transit, received, cancelled
            $table->text('notes')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();

            $table->index(['shop_id', 'status']);
            $table->unique(['shop_id', 'transfer_number']);
        });

        Schema::create('stock_transfer_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained()->restrictOnDelete();
            $table->foreignId('stock_transfer_id')->constrained('stock_transfers')->cascadeOnDelete();
            $table->string('item_type', 30); // phone, product, spare_part
            $table->foreignId('phone_device_id')->nullable()->constrained('phone_devices')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('spare_part_id')->nullable()->constrained('spare_parts')->nullOnDelete();
            $table->string('item_name')->nullable();
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });

        // 3. Quotations & Proforma Invoices
        Schema::create('quotations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained()->restrictOnDelete();
            $table->foreignId('outlet_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('converted_sale_id')->nullable()->constrained('sales')->nullOnDelete();
            $table->string('quotation_number', 50);
            $table->string('customer_name');
            $table->string('customer_phone', 50)->nullable();
            $table->string('customer_email')->nullable();
            $table->date('quotation_date');
            $table->date('valid_until');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('status', 30)->default('pending'); // pending, converted, expired, cancelled
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->timestamps();

            $table->index(['shop_id', 'status']);
            $table->unique(['shop_id', 'quotation_number']);
        });

        Schema::create('quotation_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained()->restrictOnDelete();
            $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete();
            $table->string('item_type', 30)->default('product'); // product, phone, spare_part, custom
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('phone_device_id')->nullable()->constrained('phone_devices')->nullOnDelete();
            $table->string('item_name');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
        Schema::dropIfExists('quotations');
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('phone_trade_ins');
    }
};

