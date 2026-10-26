<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table): void {
            if (! Schema::hasColumn('shops', 'address')) {
                $table->string('address', 255)->nullable()->after('name');
            }
            if (! Schema::hasColumn('shops', 'country')) {
                $table->string('country', 100)->nullable()->default('Sri Lanka')->after('address');
            }
            if (! Schema::hasColumn('shops', 'business_reg_number')) {
                $table->string('business_reg_number', 100)->nullable()->after('country');
            }
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table): void {
            $table->dropColumn(['address', 'country', 'business_reg_number']);
        });
    }
};

