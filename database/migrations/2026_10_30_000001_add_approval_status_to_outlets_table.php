<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outlets', function (Blueprint $table): void {
            $table->string('approval_status')->default('approved')->after('is_active');
            $table->timestamp('approved_at')->nullable()->after('approval_status');
            $table->foreignId('approved_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable()->after('approved_by');

            $table->index(['shop_id', 'approval_status']);
        });
    }

    public function down(): void
    {
        Schema::table('outlets', function (Blueprint $table): void {
            $table->dropIndex(['shop_id', 'approval_status']);
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['approval_status', 'approved_at', 'rejection_reason']);
        });
    }
};

