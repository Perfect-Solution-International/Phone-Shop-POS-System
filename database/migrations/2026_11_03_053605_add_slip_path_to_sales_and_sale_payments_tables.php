<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table): void {
            if (! Schema::hasColumn('sales', 'bank_slip_path')) {
                $table->string('bank_slip_path')->nullable()->after('payment_method');
            }
        });

        Schema::table('sale_payments', function (Blueprint $table): void {
            if (! Schema::hasColumn('sale_payments', 'slip_path')) {
                $table->string('slip_path')->nullable()->after('reference');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table): void {
            if (Schema::hasColumn('sales', 'bank_slip_path')) {
                $table->dropColumn('bank_slip_path');
            }
        });

        Schema::table('sale_payments', function (Blueprint $table): void {
            if (Schema::hasColumn('sale_payments', 'slip_path')) {
                $table->dropColumn('slip_path');
            }
        });
    }
};
