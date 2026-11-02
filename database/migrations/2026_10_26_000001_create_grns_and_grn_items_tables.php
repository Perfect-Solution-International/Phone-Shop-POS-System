<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Goods Received Notes (GRN) Table
        Schema::create('grns', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('purchase_id')->nullable()->constrained('purchases')->nullOnDelete();
            $table->foreignId('received_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('grn_number')->index();
            $table->string('supplier_invoice_ref')->nullable()->index();
            $table->date('received_date');
            $table->string('status')->default('completed'); // completed, draft, cancelled
            $table->decimal('total_cost', 12, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['shop_id', 'grn_number']);
            $table->index(['shop_id', 'supplier_id']);
        });

        // 2. GRN Line Items Table
        Schema::create('grn_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('grn_id')->constrained('grns')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('spare_part_id')->nullable()->constrained('spare_parts')->nullOnDelete();
            $table->foreignId('phone_device_id')->nullable()->constrained('phone_devices')->nullOnDelete();
            $table->string('item_type'); // product, spare_part, phone
            $table->string('item_name');
            $table->integer('quantity_received');
            $table->decimal('unit_cost', 12, 2)->default(0.00);
            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->string('batch_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['shop_id', 'grn_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grn_items');
        Schema::dropIfExists('grns');
    }
};

