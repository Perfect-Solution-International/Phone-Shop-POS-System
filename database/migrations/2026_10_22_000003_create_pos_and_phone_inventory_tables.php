<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Categories & Brands
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->timestamps();

            $table->unique(['shop_id', 'slug']);
        });

        Schema::create('brands', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->timestamps();

            $table->unique(['shop_id', 'slug']);
        });

        // 2. Customers
        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->string('name');
            $table->string('phone')->index();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('customer_type')->default('retail'); // retail, wholesale, corporate
            $table->decimal('credit_limit', 12, 2)->default(0.00);
            $table->decimal('balance', 12, 2)->default(0.00); // customer debt or store credit
            $table->timestamps();
        });

        // 3. Suppliers
        Schema::create('suppliers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->decimal('balance', 12, 2)->default(0.00); // amount owed to supplier
            $table->timestamps();
        });

        // 4. Products
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->string('name');
            $table->string('code')->index(); // SKU or Barcode
            $table->string('type')->default('standard'); // standard, phone_device, spare_part, service
            $table->decimal('cost_price', 12, 2)->default(0.00);
            $table->decimal('retail_price', 12, 2)->default(0.00);
            $table->decimal('wholesale_price', 12, 2)->default(0.00);
            $table->integer('stock_quantity')->default(0);
            $table->integer('alert_quantity')->default(5);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['shop_id', 'code']);
        });

        // 5. Phone Inventory & Serialized Dual-IMEI Tracking
        Schema::create('phone_devices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('brand');
            $table->string('model');
            $table->string('storage')->nullable();
            $table->string('ram')->nullable();
            $table->string('color')->nullable();
            $table->string('condition')->default('new'); // new, used, refurbished
            $table->string('imei_1')->index();
            $table->string('imei_2')->nullable()->index();
            $table->string('serial_number')->nullable()->index();
            $table->decimal('purchase_cost', 12, 2)->default(0.00);
            $table->decimal('selling_price', 12, 2)->default(0.00);
            $table->string('status')->default('in_stock'); // in_stock, reserved, sold, defective, returned, in_transfer
            $table->dateTime('sold_at')->nullable();
            $table->unsignedBigInteger('sale_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['shop_id', 'imei_1']);
            $table->index(['shop_id', 'status']);
        });

        // 6. POS Sales & Invoices
        Schema::create('sales', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete(); // cashier
            $table->string('invoice_number')->index();
            $table->date('sale_date');
            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->decimal('discount_amount', 12, 2)->default(0.00);
            $table->decimal('tax_amount', 12, 2)->default(0.00);
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->decimal('paid_amount', 12, 2)->default(0.00);
            $table->string('payment_method')->default('cash'); // cash, card, bank_transfer, split, credit
            $table->string('status')->default('completed'); // completed, partial, cancelled, returned
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['shop_id', 'invoice_number']);
            $table->index(['shop_id', 'sale_date']);
        });

        // 7. Sale Items (connecting standard products or IMEI phones)
        Schema::create('sale_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('phone_device_id')->nullable()->constrained('phone_devices')->nullOnDelete();
            $table->string('item_name');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('discount', 12, 2)->default(0.00);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });

        // 8. Sale Split Payments
        Schema::create('sale_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->string('payment_method'); // cash, card, bank_transfer, store_credit
            $table->decimal('amount', 12, 2);
            $table->string('reference')->nullable();
            $table->timestamps();
        });

        // 9. Stock Movement Audit Trail
        Schema::create('stock_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('phone_device_id')->nullable()->constrained('phone_devices')->nullOnDelete();
            $table->string('type'); // purchase, sale, return, adjustment, transfer
            $table->integer('quantity'); // positive or negative
            $table->string('reference_type')->nullable(); // Sale, Purchase, Adjustment
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['shop_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('sale_payments');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('phone_devices');
        Schema::dropIfExists('products');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('categories');
    }
};

