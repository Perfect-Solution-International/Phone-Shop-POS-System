<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\LeaveController;
use App\Http\Controllers\Api\PayrollController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SettingController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('v1.')->group(function (): void {
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('auth.login');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('auth/me', [AuthController::class, 'me'])->name('auth.me');
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

        Route::middleware('shop')->group(function (): void {
            Route::apiResource('employees', EmployeeController::class);
            Route::apiResource('attendance', AttendanceController::class)->parameters(['attendance' => 'attendance']);
            Route::apiResource('leave', LeaveController::class)->parameters(['leave' => 'leave']);
            Route::apiResource('payroll', PayrollController::class)->parameters(['payroll' => 'payroll']);
            Route::get('dashboard', [ReportController::class, 'dashboard'])->middleware('can:hrm.view')->name('dashboard');
            Route::get('settings', [SettingController::class, 'show'])->name('settings.show');
            Route::put('settings', [SettingController::class, 'update'])->middleware('can:settings.manage')->name('settings.update');
            Route::middleware(['can:reports.view', 'can:payroll.view'])->group(function (): void {
                Route::get('reports/payroll.pdf', [ReportController::class, 'payrollPdf'])->name('reports.payroll.pdf');
                Route::get('reports/payroll.xlsx', [ReportController::class, 'payrollExcel'])->name('reports.payroll.excel');
            });
        });
    });
});
