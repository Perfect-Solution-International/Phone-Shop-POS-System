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
    Route::get('/sales', [\App\Http\Controllers\Shop\SaleViewController::class, 'index'])->name('sales');
    Route::get('/sales/{sale}', [\App\Http\Controllers\Shop\SaleViewController::class, 'show'])->name('sales.show');

    // Serialized Phone Inventory & Dual IMEI (Section 8)
    Route::get('/phones', [\App\Http\Controllers\Shop\PhoneViewController::class, 'index'])->name('phones');
    Route::post('/phones', [\App\Http\Controllers\Shop\PhoneViewController::class, 'store'])->name('phones.store');
    Route::put('/phones/{phone}', [\App\Http\Controllers\Shop\PhoneViewController::class, 'update'])->name('phones.update');
    Route::delete('/phones/{phone}', [\App\Http\Controllers\Shop\PhoneViewController::class, 'destroy'])->name('phones.destroy');

    // Phone Repairs & 8-Stage Pipeline (Section 12)
    Route::get('/repairs', [\App\Http\Controllers\Shop\RepairViewController::class, 'index'])->name('repairs');
    Route::post('/repairs', [\App\Http\Controllers\Shop\RepairViewController::class, 'store'])->name('repairs.store');
    Route::patch('/repairs/{repair}/status', [\App\Http\Controllers\Shop\RepairViewController::class, 'updateStatus'])->name('repairs.status');
    Route::post('/repairs/{repair}/parts', [\App\Http\Controllers\Shop\RepairViewController::class, 'addPart'])->name('repairs.parts');

    // Warranty Management & Claims (Section 13)
    Route::get('/warranties', [\App\Http\Controllers\Shop\WarrantyViewController::class, 'index'])->name('warranties');
    Route::post('/warranties', [\App\Http\Controllers\Shop\WarrantyViewController::class, 'store'])->name('warranties.store');
    Route::post('/warranties/{warranty}/claim', [\App\Http\Controllers\Shop\WarrantyViewController::class, 'claim'])->name('warranties.claim');
    Route::post('/warranties/claims/{claim}/review', [\App\Http\Controllers\Shop\WarrantyViewController::class, 'reviewClaim'])->name('warranties.claims.review');

    // Spare Parts Inventory (Section 14)
    Route::get('/spare-parts', [\App\Http\Controllers\Shop\SparePartViewController::class, 'index'])->name('spare-parts');
    Route::post('/spare-parts', [\App\Http\Controllers\Shop\SparePartViewController::class, 'store'])->name('spare-parts.store');
    Route::put('/spare-parts/{sparePart}', [\App\Http\Controllers\Shop\SparePartViewController::class, 'update'])->name('spare-parts.update');
    Route::delete('/spare-parts/{sparePart}', [\App\Http\Controllers\Shop\SparePartViewController::class, 'destroy'])->name('spare-parts.destroy');
    Route::delete('/spare-parts/{sparePart}/image', [\App\Http\Controllers\Shop\SparePartViewController::class, 'deleteImage'])->name('spare-parts.delete-image');

    // Products & Accessories (Section 9)
    Route::get('/products', [\App\Http\Controllers\Shop\ProductViewController::class, 'index'])->name('products');
    Route::post('/products', [\App\Http\Controllers\Shop\ProductViewController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [\App\Http\Controllers\Shop\ProductViewController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [\App\Http\Controllers\Shop\ProductViewController::class, 'destroy'])->name('products.destroy');
    Route::delete('/products/{product}/image', [\App\Http\Controllers\Shop\ProductViewController::class, 'deleteImage'])->name('products.delete-image');

    // Categories & Brands Catalog Management
    Route::post('/categories', [\App\Http\Controllers\Shop\CatalogController::class, 'storeCategory'])->name('categories.store');
    Route::put('/categories/{category}', [\App\Http\Controllers\Shop\CatalogController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{category}', [\App\Http\Controllers\Shop\CatalogController::class, 'destroyCategory'])->name('categories.destroy');
    Route::post('/brands', [\App\Http\Controllers\Shop\CatalogController::class, 'storeBrand'])->name('brands.store');
    Route::put('/brands/{brand}', [\App\Http\Controllers\Shop\CatalogController::class, 'updateBrand'])->name('brands.update');
    Route::delete('/brands/{brand}', [\App\Http\Controllers\Shop\CatalogController::class, 'destroyBrand'])->name('brands.destroy');
    Route::get('/catalog/data', [\App\Http\Controllers\Shop\CatalogController::class, 'getCatalog'])->name('catalog.data');
    Route::post('/catalog/seed-defaults', [\App\Http\Controllers\Shop\CatalogController::class, 'seedDefaults'])->name('catalog.seed-defaults');

    // Outlets & Multi-Branch Management
    Route::get('/outlets', [\App\Http\Controllers\Shop\OutletController::class, 'index'])->name('outlets.index');
    Route::post('/outlets', [\App\Http\Controllers\Shop\OutletController::class, 'store'])->name('outlets.store');
    Route::put('/outlets/{outlet}', [\App\Http\Controllers\Shop\OutletController::class, 'update'])->name('outlets.update');
    Route::delete('/outlets/{outlet}', [\App\Http\Controllers\Shop\OutletController::class, 'destroy'])->name('outlets.destroy');
    Route::post('/outlets/{outlet}/switch', [\App\Http\Controllers\Shop\OutletController::class, 'switchOutlet'])->name('outlets.switch');
    Route::post('/outlets/{outlet}/toggle-main', [\App\Http\Controllers\Shop\OutletController::class, 'toggleMain'])->name('outlets.toggle-main');

    // Customer Directory (Section 15)
    Route::get('/customers', [\App\Http\Controllers\Shop\CustomerViewController::class, 'index'])->name('customers');
    Route::post('/customers', [\App\Http\Controllers\Shop\CustomerViewController::class, 'store'])->name('customers.store');

    // Purchasing & Suppliers (Section 10)
    Route::get('/suppliers', [\App\Http\Controllers\Shop\SupplierViewController::class, 'index'])->name('suppliers');
    Route::post('/suppliers', [\App\Http\Controllers\Shop\SupplierViewController::class, 'store'])->name('suppliers.store');
    Route::put('/suppliers/{supplier}', [\App\Http\Controllers\Shop\SupplierViewController::class, 'update'])->name('suppliers.update');
    Route::delete('/suppliers/{supplier}', [\App\Http\Controllers\Shop\SupplierViewController::class, 'destroy'])->name('suppliers.destroy');

    Route::get('/purchases', [\App\Http\Controllers\Shop\PurchaseViewController::class, 'index'])->name('purchases');
    Route::post('/purchases', [\App\Http\Controllers\Shop\PurchaseViewController::class, 'store'])->name('purchases.store');

    // Expenses & Daily Cashbook (Section 16)
    Route::get('/expenses', [\App\Http\Controllers\Shop\ExpenseViewController::class, 'index'])->name('expenses');
    Route::post('/expenses', [\App\Http\Controllers\Shop\ExpenseViewController::class, 'store'])->name('expenses.store');
    Route::delete('/expenses/{expense}', [\App\Http\Controllers\Shop\ExpenseViewController::class, 'destroy'])->name('expenses.destroy');
    Route::get('/cashbook', [\App\Http\Controllers\Shop\CashbookViewController::class, 'index'])->name('cashbook');

    // Stock Movement Audit & Inventory (Section 11)
    Route::get('/inventory', [\App\Http\Controllers\Shop\InventoryMovementViewController::class, 'index'])->name('inventory');
    Route::post('/inventory/adjust', [\App\Http\Controllers\Shop\InventoryMovementViewController::class, 'adjust'])->name('inventory.adjust');
    Route::get('/inventory/grns', [\App\Http\Controllers\Shop\GrnViewController::class, 'index'])->name('inventory.grns');
    Route::post('/inventory/grns', [\App\Http\Controllers\Shop\GrnViewController::class, 'store'])->name('inventory.grns.store');
    Route::get('/inventory/grns/{grn}', [\App\Http\Controllers\Shop\GrnViewController::class, 'show'])->name('inventory.grns.show');

    // Sales Returns & Refunds (Section 7.3)
    Route::get('/sales-returns', [\App\Http\Controllers\Shop\SaleReturnViewController::class, 'index'])->name('sales.returns');
    Route::post('/sales-returns', [\App\Http\Controllers\Shop\SaleReturnViewController::class, 'store'])->name('sales.returns.store');

    // Shop Settings & Invoicing Defaults (Section 26)
    Route::get('/settings', [\App\Http\Controllers\Shop\ShopSettingsController::class, 'index'])->name('settings');
    Route::put('/settings', [\App\Http\Controllers\Shop\ShopSettingsController::class, 'update'])->name('settings.update');

    // Reports & Analytics (Section 19)
    Route::get('/reports', [\App\Http\Controllers\Shop\ShopReportViewController::class, 'index'])->name('reports');

    // Staff HRM & Payroll
    Route::get('/staff', [\App\Http\Controllers\Shop\StaffViewController::class, 'index'])->name('staff');
    Route::post('/staff/employees', [\App\Http\Controllers\Shop\StaffViewController::class, 'storeEmployee'])->name('staff.employees.store');
    Route::put('/staff/employees/{employee}', [\App\Http\Controllers\Shop\StaffViewController::class, 'updateEmployee'])->name('staff.employees.update');
    Route::delete('/staff/employees/{employee}', [\App\Http\Controllers\Shop\StaffViewController::class, 'destroyEmployee'])->name('staff.employees.destroy');
    Route::post('/staff/attendance', [\App\Http\Controllers\Shop\StaffViewController::class, 'clockAttendance'])->name('staff.attendance.clock');
    Route::post('/staff/leaves', [\App\Http\Controllers\Shop\StaffViewController::class, 'storeLeave'])->name('staff.leaves.store');
    Route::patch('/staff/leaves/{leave}/status', [\App\Http\Controllers\Shop\StaffViewController::class, 'updateLeaveStatus'])->name('staff.leaves.status');
    Route::post('/staff/payroll', [\App\Http\Controllers\Shop\StaffViewController::class, 'storePayroll'])->name('staff.payroll.store');
    Route::post('/staff/payroll/auto-generate', [\App\Http\Controllers\Shop\StaffViewController::class, 'autoGeneratePayroll'])->name('staff.payroll.auto');
    Route::put('/staff/payroll/{payroll}', [\App\Http\Controllers\Shop\StaffViewController::class, 'updatePayroll'])->name('staff.payroll.update');
    Route::delete('/staff/payroll/{payroll}', [\App\Http\Controllers\Shop\StaffViewController::class, 'destroyPayroll'])->name('staff.payroll.destroy');
    Route::patch('/staff/payroll/{payroll}/paid', [\App\Http\Controllers\Shop\StaffViewController::class, 'markPayrollPaid'])->name('staff.payroll.paid');

    // High Priority: Used Phone Buying & Trade-Ins (Section 8.3)
    Route::get('/trade-ins', [\App\Http\Controllers\Shop\TradeInViewController::class, 'index'])->name('trade-ins.index');
    Route::post('/trade-ins', [\App\Http\Controllers\Shop\TradeInViewController::class, 'store'])->name('trade-ins.store');

    // High Priority: Branch-to-Branch Stock Transfers (Section 17)
    Route::get('/transfers', [\App\Http\Controllers\Shop\StockTransferViewController::class, 'index'])->name('transfers.index');
    Route::post('/transfers', [\App\Http\Controllers\Shop\StockTransferViewController::class, 'store'])->name('transfers.store');
    Route::patch('/transfers/{transfer}/receive', [\App\Http\Controllers\Shop\StockTransferViewController::class, 'receive'])->name('transfers.receive');
    Route::patch('/transfers/{transfer}/cancel', [\App\Http\Controllers\Shop\StockTransferViewController::class, 'cancel'])->name('transfers.cancel');

    // High Priority: Quotations & Proforma Invoices (Section 7.1)
    Route::get('/quotations', [\App\Http\Controllers\Shop\QuotationViewController::class, 'index'])->name('quotations.index');
    Route::post('/quotations', [\App\Http\Controllers\Shop\QuotationViewController::class, 'store'])->name('quotations.store');
    Route::post('/quotations/{quotation}/convert', [\App\Http\Controllers\Shop\QuotationViewController::class, 'convertToSale'])->name('quotations.convert');

    // Medium Priority 1: Barcode & Thermal IMEI Label Generator (Section 8.4)
    Route::get('/labels', [\App\Http\Controllers\Shop\BarcodeLabelController::class, 'index'])->name('labels.index');
    Route::get('/labels/print', [\App\Http\Controllers\Shop\BarcodeLabelController::class, 'print'])->name('labels.print');

    // Medium Priority 2: Customer SMS & WhatsApp Notifications (Section 20)
    Route::get('/notifications', [\App\Http\Controllers\Shop\NotificationViewController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/send', [\App\Http\Controllers\Shop\NotificationViewController::class, 'sendCustomSms'])->name('notifications.send');
    Route::post('/notifications/settings', [\App\Http\Controllers\Shop\NotificationViewController::class, 'updateSettings'])->name('notifications.settings');

    // Medium Priority 3: Subscription & Online Card Payment Gateway (Section 5.1 & 18)
    Route::get('/subscription', [\App\Http\Controllers\Shop\SubscriptionViewController::class, 'index'])->name('subscription.index');
    Route::post('/subscription/checkout', [\App\Http\Controllers\Shop\SubscriptionViewController::class, 'checkout'])->name('subscription.checkout');
    Route::post('/subscription/bank-transfer', [\App\Http\Controllers\Shop\SubscriptionViewController::class, 'bankTransfer'])->name('subscription.bank-transfer');
    Route::post('/subscription/gateway/initiate', [\App\Http\Controllers\Payment\GatewayCallbackController::class, 'initiate'])->name('subscription.gateway.initiate');
    Route::get('/subscription/gateway/sandbox-checkout', [\App\Http\Controllers\Payment\GatewayCallbackController::class, 'sandboxCheckout'])->name('subscription.gateway.sandbox-checkout');
    Route::post('/subscription/gateway/simulate-action', [\App\Http\Controllers\Payment\GatewayCallbackController::class, 'simulateSandboxAction'])->name('subscription.gateway.simulate-action');
    Route::get('/subscription/gateway/return', [\App\Http\Controllers\Payment\GatewayCallbackController::class, 'handleReturn'])->name('subscription.gateway.return');
    Route::get('/subscription/gateway/cancel', [\App\Http\Controllers\Payment\GatewayCallbackController::class, 'handleCancel'])->name('subscription.gateway.cancel');
});

// SaaS Super Admin Panel
Route::prefix('super-admin')->name('superadmin.')->middleware(['auth', 'superadmin'])->group(function (): void {
    Route::get('/', [\App\Http\Controllers\SuperAdmin\DashboardController::class, 'index'])->name('dashboard');

    // Shops
    Route::get('/shops', [\App\Http\Controllers\SuperAdmin\ShopController::class, 'index'])->name('shops.index');
    Route::get('/shops/{shop}', [\App\Http\Controllers\SuperAdmin\ShopController::class, 'show'])->name('shops.show');
    Route::post('/shops/{shop}/toggle-suspension', [\App\Http\Controllers\SuperAdmin\ShopController::class, 'toggleSuspension'])->name('shops.toggle-suspension');
    Route::post('/shops/{shop}/switch', function (\App\Models\Platform\Shop $shop) {
        session(['superadmin_active_shop_id' => $shop->id]);
        return redirect()->route('shop.dashboard')->with('success', "Switched workspace to store '{$shop->name}'. You now have full access to manage this store.");
    })->name('shops.switch');

    // Shop Owners Management
    Route::get('/owners', [\App\Http\Controllers\SuperAdmin\OwnerController::class, 'index'])->name('owners.index');
    Route::post('/owners', [\App\Http\Controllers\SuperAdmin\OwnerController::class, 'store'])->name('owners.store');
    Route::put('/owners/{user}', [\App\Http\Controllers\SuperAdmin\OwnerController::class, 'update'])->name('owners.update');
    Route::delete('/owners/{user}', [\App\Http\Controllers\SuperAdmin\OwnerController::class, 'destroy'])->name('owners.destroy');
    Route::post('/owners/{user}/access', [\App\Http\Controllers\SuperAdmin\OwnerController::class, 'loginAs'])->name('owners.access');

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
    Route::post('/payments/{payment}/refund', [\App\Http\Controllers\SuperAdmin\PaymentController::class, 'refund'])->name('payments.refund');
});
