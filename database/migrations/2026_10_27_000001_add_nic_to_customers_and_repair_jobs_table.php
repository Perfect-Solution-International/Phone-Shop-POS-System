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
        Schema::table('customers', function (Blueprint $table): void {
            if (! Schema::hasColumn('customers', 'nic')) {
                $table->string('nic', 50)->nullable()->index()->after('phone');
            }
        });

        Schema::table('repair_jobs', function (Blueprint $table): void {
            if (! Schema::hasColumn('repair_jobs', 'customer_nic')) {
                $table->string('customer_nic', 50)->nullable()->index()->after('imei');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table): void {
            if (Schema::hasColumn('customers', 'nic')) {
                $table->dropColumn('nic');
            }
        });

        Schema::table('repair_jobs', function (Blueprint $table): void {
            if (Schema::hasColumn('repair_jobs', 'customer_nic')) {
                $table->dropColumn('customer_nic');
            }
        });
    }
};

