<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Repair Jobs Table (8-stage status workflow)
        Schema::create('repair_jobs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('job_number')->index();
            $table->string('brand');
            $table->string('model');
            $table->string('imei')->nullable()->index();
            $table->text('fault_description');
            $table->text('condition_notes')->nullable();
            $table->string('accessories_received')->nullable();
            $table->decimal('estimated_cost', 12, 2)->default(0.00);
            $table->decimal('advance_amount', 12, 2)->default(0.00);
            $table->decimal('final_cost', 12, 2)->nullable();
            $table->string('status')->default('received'); // received, diagnosing, waiting_for_approval, waiting_for_parts, in_repair, quality_check, ready_for_collection, delivered
            $table->dateTime('estimated_completion_date')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->text('technician_notes')->nullable();
            $table->timestamps();

            $table->unique(['shop_id', 'job_number']);
            $table->index(['shop_id', 'status']);
        });

        // 2. Spare Parts Table
        Schema::create('spare_parts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->string('name');
            $table->string('part_number')->index();
            $table->string('compatible_models')->nullable();
            $table->string('quality_type')->default('original'); // original, oem, compatible
            $table->decimal('cost_price', 12, 2)->default(0.00);
            $table->decimal('selling_price', 12, 2)->default(0.00);
            $table->integer('stock_quantity')->default(0);
            $table->integer('alert_quantity')->default(2);
            $table->timestamps();

            $table->unique(['shop_id', 'part_number']);
        });

        // 3. Repair Parts Used (allocating spare parts to repair jobs)
        Schema::create('repair_parts_used', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('repair_job_id')->constrained('repair_jobs')->cascadeOnDelete();
            $table->foreignId('spare_part_id')->constrained('spare_parts')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_cost', 12, 2);
            $table->decimal('unit_price', 12, 2);
            $table->timestamps();
        });

        // 4. Warranties Table
        Schema::create('warranties', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('phone_device_id')->nullable()->constrained('phone_devices')->nullOnDelete();
            $table->foreignId('repair_job_id')->nullable()->constrained('repair_jobs')->nullOnDelete();
            $table->string('warranty_number')->index();
            $table->string('warranty_type')->default('shop'); // manufacturer, shop, supplier, repair
            $table->string('imei')->nullable()->index();
            $table->date('starts_on');
            $table->date('ends_on');
            $table->unsignedSmallInteger('duration_months')->default(12);
            $table->string('status')->default('active'); // active, expired, voided
            $table->text('terms')->nullable();
            $table->timestamps();

            $table->unique(['shop_id', 'warranty_number']);
            $table->index(['shop_id', 'status']);
        });

        // 5. Warranty Claims
        Schema::create('warranty_claims', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('warranty_id')->constrained('warranties')->cascadeOnDelete();
            $table->string('claim_number')->index();
            $table->date('claim_date');
            $table->text('issue_description');
            $table->string('status')->default('pending'); // pending, inspecting, approved, rejected, settled
            $table->string('settlement_type')->nullable(); // repair, replacement, refund
            $table->text('action_notes')->nullable();
            $table->timestamps();

            $table->unique(['shop_id', 'claim_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warranty_claims');
        Schema::dropIfExists('warranties');
        Schema::dropIfExists('repair_parts_used');
        Schema::dropIfExists('spare_parts');
        Schema::dropIfExists('repair_jobs');
    }
};

