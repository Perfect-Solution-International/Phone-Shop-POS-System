<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('phone_devices')
            ->whereIn('status', ['In Stock', 'in stock', 'IN STOCK'])
            ->update(['status' => 'in_stock']);

        DB::table('phone_devices')
            ->whereIn('status', ['Sold', 'SOLD'])
            ->update(['status' => 'sold']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed as 'in_stock' is the standard status format.
    }
};
