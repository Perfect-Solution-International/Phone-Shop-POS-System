<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table): void {
            if (! Schema::hasColumn('shops', 'phone')) {
                $table->string('phone', 50)->nullable()->after('address');
            }
            if (! Schema::hasColumn('shops', 'email')) {
                $table->string('email', 150)->nullable()->after('phone');
            }
            if (! Schema::hasColumn('shops', 'invoice_header_note')) {
                $table->text('invoice_header_note')->nullable()->after('business_reg_number');
            }
            if (! Schema::hasColumn('shops', 'invoice_footer_note')) {
                $table->text('invoice_footer_note')->nullable()->after('invoice_header_note');
            }
            if (! Schema::hasColumn('shops', 'warranty_terms')) {
                $table->text('warranty_terms')->nullable()->after('invoice_footer_note');
            }
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table): void {
            $table->dropColumn(['phone', 'email', 'invoice_header_note', 'invoice_footer_note', 'warranty_terms']);
        });
    }
};

