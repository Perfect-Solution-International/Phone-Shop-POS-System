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
    Route::get('public/repairs/track', [\App\Http\Controllers\Api\RepairController::class, 'trackPublic'])->middleware('throttle:30,1')->name('repairs.public_track');
    Route::match(['get', 'post'], 'payments/gateway/callback/{gateway?}', [\App\Http\Controllers\Payment\GatewayCallbackController::class, 'handleCallback'])->name('payments.gateway.callback');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('auth/me', [AuthController::class, 'me'])->name('auth.me');
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

        Route::middleware(['shop', 'verified'])->group(function (): void {
            Route::apiResource('employees', EmployeeController::class);
            Route::apiResource('attendance', AttendanceController::class)->parameters(['attendance' => 'attendance']);
            Route::apiResource('leave', LeaveController::class)->parameters(['leave' => 'leave']);
            Route::apiResource('payroll', PayrollController::class)->parameters(['payroll' => 'payroll']);
            Route::get('dashboard', [ReportController::class, 'dashboard'])->middleware('can:hrm.view')->name('dashboard');
            Route::get('settings', [SettingController::class, 'show'])->name('settings.show');
            Route::put('settings', [SettingController::class, 'update'])->middleware('can:settings.manage')->name('settings.update');

            // POS & Sales Counter (Section 7)
            Route::post('pos/checkout', [\App\Http\Controllers\Api\PosController::class, 'checkout'])->name('pos.checkout');
            Route::get('pos/invoices', [\App\Http\Controllers\Api\PosController::class, 'invoices'])->name('pos.invoices');
            Route::get('pos/invoices/{sale}', [\App\Http\Controllers\Api\PosController::class, 'invoice'])->name('pos.invoice');

            // Products & Accessories (Section 9)
            Route::apiResource('products', \App\Http\Controllers\Api\ProductController::class);

            // Serialized Phone Inventory & Dual IMEI Tracking (Section 8)
            Route::get('phones/lookup', [\App\Http\Controllers\Api\PhoneDeviceController::class, 'lookup'])->name('phones.lookup');
            Route::apiResource('phones', \App\Http\Controllers\Api\PhoneDeviceController::class);

            // Customers (Section 15)
            Route::apiResource('customers', \App\Http\Controllers\Api\CustomerController::class);

            // Phone Repairs & Service Management (Section 12)
            Route::patch('repairs/{repair}/status', [\App\Http\Controllers\Api\RepairController::class, 'updateStatus'])->name('repairs.status');
            Route::post('repairs/{repair}/parts', [\App\Http\Controllers\Api\RepairController::class, 'addPart'])->name('repairs.parts');
            Route::post('repairs/{repair}/approve-estimate', [\App\Http\Controllers\Api\RepairController::class, 'approveEstimate'])->name('repairs.approve_estimate');
            Route::apiResource('repairs', \App\Http\Controllers\Api\RepairController::class);

            // Spare Parts Management (Section 14)
            Route::apiResource('spare-parts', \App\Http\Controllers\Api\SparePartController::class);

            // Warranty Management & Claims (Section 13)
            Route::get('warranties/verify', [\App\Http\Controllers\Api\WarrantyController::class, 'verify'])->name('warranties.verify');
            Route::post('warranties/{warranty}/claim', [\App\Http\Controllers\Api\WarrantyController::class, 'claim'])->name('warranties.claim');
            Route::post('warranties/claims/{claim}/review', [\App\Http\Controllers\Api\WarrantyController::class, 'reviewClaim'])->name('warranties.claims.review');
            Route::apiResource('warranties', \App\Http\Controllers\Api\WarrantyController::class);

            // Shop Overview Analytics (Section 6)
            Route::get('shop/overview', [\App\Http\Controllers\Shop\DashboardController::class, 'index'])->name('shop.overview');
            Route::middleware(['can:reports.view', 'can:payroll.view'])->group(function (): void {
                Route::get('reports/payroll.pdf', [ReportController::class, 'payrollPdf'])->name('reports.payroll.pdf');
                Route::get('reports/payroll.xlsx', [ReportController::class, 'payrollExcel'])->name('reports.payroll.excel');
            });
        });
    });
});
