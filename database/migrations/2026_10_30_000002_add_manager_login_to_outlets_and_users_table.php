<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outlets', function (Blueprint $table): void {
            $table->string('manager_email')->nullable()->after('manager_name');
            $table->foreignId('manager_user_id')->nullable()->after('manager_email')->constrained('users')->nullOnDelete();
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('outlet_id')->nullable()->after('shop_id')->constrained('outlets')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('outlet_id');
        });

        Schema::table('outlets', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('manager_user_id');
            $table->dropColumn('manager_email');
        });
    }
};

