<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phone_devices', function (Blueprint $table): void {
            if (! Schema::hasColumn('phone_devices', 'image_path')) {
                $table->string('image_path')->nullable()->after('color');
            }
        });
    }

    public function down(): void
    {
        Schema::table('phone_devices', function (Blueprint $table): void {
            if (Schema::hasColumn('phone_devices', 'image_path')) {
                $table->dropColumn('image_path');
            }
        });
    }
};

