<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shops', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('shop_id')->nullable()->constrained()->restrictOnDelete();
        });
        Schema::create('employees', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained()->restrictOnDelete();
            $table->string('employee_code', 50);
            $table->string('name');
            $table->string('email');
            $table->string('department', 100)->nullable();
            $table->string('position', 100)->nullable();
            $table->date('joined_on');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['shop_id', 'employee_code']);
            $table->unique(['shop_id', 'email']);
        });
        Schema::create('attendances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained()->restrictOnDelete();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            $table->date('work_date');
            $table->dateTime('clocked_in_at');
            $table->dateTime('clocked_out_at')->nullable();
            $table->timestamps();
            $table->unique(['shop_id', 'employee_id', 'work_date']);
        });
        Schema::create('leaves', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained()->restrictOnDelete();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            $table->date('starts_on');
            $table->date('ends_on');
            $table->string('type', 50);
            $table->text('reason')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamps();
            $table->index(['shop_id', 'status']);
        });
        Schema::create('payrolls', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained()->restrictOnDelete();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            $table->date('period');
            $table->decimal('basic_salary', 12, 2);
            $table->decimal('allowances', 12, 2)->default(0);
            $table->decimal('deductions', 12, 2)->default(0);
            $table->decimal('net_salary', 12, 2);
            $table->string('status', 20)->default('draft');
            $table->timestamps();
            $table->unique(['shop_id', 'employee_id', 'period']);
        });
        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->unique()->constrained()->restrictOnDelete();
            $table->string('timezone')->default('UTC');
            $table->string('currency', 3)->default('LKR');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['settings', 'payrolls', 'leaves', 'attendances', 'employees'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('users', fn (Blueprint $table) => $table->dropConstrainedForeignId('shop_id'));
        Schema::dropIfExists('shops');
    }
};
