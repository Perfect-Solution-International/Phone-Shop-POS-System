<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerificationController;
use Illuminate\Support\Facades\Route;

// Public entry & health check
Route::get('/', [AuthController::class, 'entry'])->name('home');
Route::get('/up', fn () => response()->json(['status' => 'ok']))->name('health');
Route::get('/track-repair', [\App\Http\Controllers\PublicRepairTrackingController::class, 'show'])->name('public.repair-track');

// Authentication (Login / Logout)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'browserLogin'])->middleware('throttle:login')->name('login.store');
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// Tenant Onboarding / Registration
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:6,1')->name('register.store');

// Email Verification
Route::get('/email/verify', [VerificationController::class, 'show'])->middleware('auth')->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->middleware(['auth', 'signed', 'throttle:6,1'])->name('verification.verify');
Route::post('/email/verification-notification', [VerificationController::class, 'resend'])->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// Password Reset
Route::get('/forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'store'])->middleware('throttle:6,1')->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'update'])->middleware('throttle:6,1')->name('password.update');

// Protected Workspace SPA Shell
Route::get('/app/preview', [AuthController::class, 'preview'])->name('login.preview');
Route::view('/app/{path?}', 'layouts.master')->middleware(['auth', 'verified'])->where('path', '.*')->name('app');

// Customer Care Portal (Repairs, Warranties, Invoices)
Route::prefix('portal')->name('customer.')->middleware(['auth', 'verified', 'shop'])->group(function (): void {
    Route::get('/', [\App\Http\Controllers\CustomerPortal\PortalController::class, 'index'])->name('portal');
    Route::get('/repairs/{repair}', [\App\Http\Controllers\CustomerPortal\PortalController::class, 'repair'])->name('repairs.show');
    Route::get('/invoices/{sale}', [\App\Http\Controllers\CustomerPortal\PortalController::class, 'invoice'])->name('invoices.show');
});

// Phone Shop Management Panel (Operations, POS, Inventory, Repairs, Warranties)
Route::prefix('shop')->name('shop.')->middleware(['auth', 'verified', 'shop'])->group(function (): void {
    Route::get('/', [\App\Http\Controllers\Shop\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [\App\Http\Controllers\Shop\DashboardController::class, 'index'])->name('dashboard.index');

    // POS Counter & Checkout (Section 7)
    Route::get('/pos', [\App\Http\Controllers\Shop\PosViewController::class, 'index'])->name('pos');
    Route::post('/pos/checkout', [\App\Http\Controllers\Shop\PosViewController::class, 'checkout'])->name('pos.checkout');

    // Serialized Phone Inventory & Dual IMEI (Section 8)
    Route::get('/phones', [\App\Http\Controllers\Shop\PhoneViewController::class, 'index'])->name('phones');
    Route::post('/phones', [\App\Http\Controllers\Shop\PhoneViewController::class, 'store'])->name('phones.store');

    // Phone Repairs & 8-Stage Pipeline (Section 12)
    Route::get('/repairs', [\App\Http\Controllers\Shop\RepairViewController::class, 'index'])->name('repairs');
    Route::post('/repairs', [\App\Http\Controllers\Shop\RepairViewController::class, 'store'])->name('repairs.store');
    Route::patch('/repairs/{repair}/status', [\App\Http\Controllers\Shop\RepairViewController::class, 'updateStatus'])->name('repairs.status');
    Route::post('/repairs/{repair}/parts', [\App\Http\Controllers\Shop\RepairViewController::class, 'addPart'])->name('repairs.parts');

    // Warranty Management & Claims (Section 13)
    Route::get('/warranties', [\App\Http\Controllers\Shop\WarrantyViewController::class, 'index'])->name('warranties');
    Route::post('/warranties', [\App\Http\Controllers\Shop\WarrantyViewController::class, 'store'])->name('warranties.store');
    Route::post('/warranties/{warranty}/claim', [\App\Http\Controllers\Shop\WarrantyViewController::class, 'claim'])->name('warranties.claim');

    // Spare Parts Inventory (Section 14)
    Route::get('/spare-parts', [\App\Http\Controllers\Shop\SparePartViewController::class, 'index'])->name('spare-parts');
    Route::post('/spare-parts', [\App\Http\Controllers\Shop\SparePartViewController::class, 'store'])->name('spare-parts.store');

    // Products & Accessories (Section 9)
    Route::get('/products', [\App\Http\Controllers\Shop\ProductViewController::class, 'index'])->name('products');
    Route::post('/products', [\App\Http\Controllers\Shop\ProductViewController::class, 'store'])->name('products.store');

    // Customer Directory (Section 15)
    Route::get('/customers', [\App\Http\Controllers\Shop\CustomerViewController::class, 'index'])->name('customers');
    Route::post('/customers', [\App\Http\Controllers\Shop\CustomerViewController::class, 'store'])->name('customers.store');
});

// SaaS Super Admin Panel
Route::prefix('super-admin')->name('superadmin.')->middleware(['auth', 'superadmin'])->group(function (): void {
    Route::get('/', [\App\Http\Controllers\SuperAdmin\DashboardController::class, 'index'])->name('dashboard');

    // Shops
    Route::get('/shops', [\App\Http\Controllers\SuperAdmin\ShopController::class, 'index'])->name('shops.index');
    Route::get('/shops/{shop}', [\App\Http\Controllers\SuperAdmin\ShopController::class, 'show'])->name('shops.show');
    Route::post('/shops/{shop}/toggle-suspension', [\App\Http\Controllers\SuperAdmin\ShopController::class, 'toggleSuspension'])->name('shops.toggle-suspension');

    // Subscription Plans
    Route::get('/plans', [\App\Http\Controllers\SuperAdmin\PlanController::class, 'index'])->name('plans.index');
    Route::post('/plans', [\App\Http\Controllers\SuperAdmin\PlanController::class, 'store'])->name('plans.store');
    Route::put('/plans/{plan}', [\App\Http\Controllers\SuperAdmin\PlanController::class, 'update'])->name('plans.update');
    Route::post('/plans/{plan}/toggle-active', [\App\Http\Controllers\SuperAdmin\PlanController::class, 'toggleActive'])->name('plans.toggle-active');

    // Payments & Bank Approvals
    Route::get('/payments', [\App\Http\Controllers\SuperAdmin\PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/approvals', [\App\Http\Controllers\SuperAdmin\PaymentController::class, 'approvals'])->name('payments.approvals');
    Route::post('/payments/{payment}/approve', [\App\Http\Controllers\SuperAdmin\PaymentController::class, 'approve'])->name('payments.approve');
    Route::post('/payments/{payment}/reject', [\App\Http\Controllers\SuperAdmin\PaymentController::class, 'reject'])->name('payments.reject');
});
