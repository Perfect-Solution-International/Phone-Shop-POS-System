<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create sms_logs table
        if (! Schema::hasTable('sms_logs')) {
            Schema::create('sms_logs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
                $table->string('recipient_phone', 50);
                $table->text('message');
                $table->string('type', 50)->default('custom'); // repair_ready, invoice, warranty, custom, test
                $table->string('status', 30)->default('simulated'); // sent, failed, simulated
                $table->string('provider', 50)->default('mock'); // mock, notifylk, twilio
                $table->text('response_payload')->nullable();
                $table->timestamps();
            });
        }

        // 2. Add SMS Gateway settings to shops table
        Schema::table('shops', function (Blueprint $table): void {
            if (! Schema::hasColumn('shops', 'sms_enabled')) {
                $table->boolean('sms_enabled')->default(false)->after('warranty_terms');
            }
            if (! Schema::hasColumn('shops', 'sms_provider')) {
                $table->string('sms_provider', 50)->default('mock')->after('sms_enabled');
            }
            if (! Schema::hasColumn('shops', 'sms_api_key')) {
                $table->string('sms_api_key', 255)->nullable()->after('sms_provider');
            }
            if (! Schema::hasColumn('shops', 'sms_sender_id')) {
                $table->string('sms_sender_id', 50)->nullable()->after('sms_api_key');
            }
            if (! Schema::hasColumn('shops', 'sms_on_repair_ready')) {
                $table->boolean('sms_on_repair_ready')->default(true)->after('sms_sender_id');
            }
            if (! Schema::hasColumn('shops', 'sms_on_sale')) {
                $table->boolean('sms_on_sale')->default(false)->after('sms_on_repair_ready');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');

        Schema::table('shops', function (Blueprint $table): void {
            $table->dropColumn([
                'sms_enabled',
                'sms_provider',
                'sms_api_key',
                'sms_sender_id',
                'sms_on_repair_ready',
                'sms_on_sale',
            ]);
        });
    }
};

