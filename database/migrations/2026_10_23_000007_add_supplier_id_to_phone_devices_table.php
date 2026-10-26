<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phone_devices', function (Blueprint $table): void {
            if (! Schema::hasColumn('phone_devices', 'supplier_id')) {
                $table->foreignId('supplier_id')->nullable()->after('product_id')->constrained('suppliers')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('phone_devices', function (Blueprint $table): void {
            if (Schema::hasColumn('phone_devices', 'supplier_id')) {
                $table->dropConstrainedForeignId('supplier_id');
            }
        });
    }
};

