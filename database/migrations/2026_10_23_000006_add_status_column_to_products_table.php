<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'status')) {
                $table->string('status', 50)->default('active')->after('is_active')->index();
            }
        });

        // Sync existing data
        if (Schema::hasColumn('products', 'is_active')) {
            DB::table('products')->where('is_active', false)->update(['status' => 'inactive']);
            DB::table('products')->where('is_active', true)->update(['status' => 'active']);
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (Schema::hasColumn('products', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};

