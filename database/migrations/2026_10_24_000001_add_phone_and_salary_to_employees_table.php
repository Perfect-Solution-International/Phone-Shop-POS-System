<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            if (! Schema::hasColumn('employees', 'phone')) {
                $table->string('phone', 50)->nullable()->after('email');
            }
            if (! Schema::hasColumn('employees', 'basic_salary')) {
                $table->decimal('basic_salary', 12, 2)->default(0.00)->after('position');
            }
            if (! Schema::hasColumn('employees', 'outlet_id')) {
                $table->foreignId('outlet_id')->nullable()->after('shop_id')->constrained('outlets')->nullOnDelete();
            }
            if (! Schema::hasColumn('employees', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('outlet_id')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('user_id');
            $table->dropConstrainedForeignId('outlet_id');
            $table->dropColumn(['phone', 'basic_salary']);
        });
    }
};

