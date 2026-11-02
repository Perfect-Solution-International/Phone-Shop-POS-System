<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (! Schema::hasColumn('sales', 'outlet_id')) {
                $table->foreignId('outlet_id')->nullable()->after('shop_id')->constrained('outlets')->nullOnDelete();
                $table->index(['shop_id', 'outlet_id']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'outlet_id')) {
                $table->dropForeign(['outlet_id']);
                $table->dropIndex(['shop_id', 'outlet_id']);
                $table->dropColumn('outlet_id');
            }
        });
    }
};

