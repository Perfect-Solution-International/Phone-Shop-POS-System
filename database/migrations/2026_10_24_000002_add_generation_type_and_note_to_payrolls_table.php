<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->string('generation_type', 20)->default('manual')->after('status');
            $table->string('payment_method', 30)->nullable()->after('generation_type');
            $table->timestamp('paid_at')->nullable()->after('payment_method');
            $table->text('note')->nullable()->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['generation_type', 'payment_method', 'paid_at', 'note']);
        });
    }
};

