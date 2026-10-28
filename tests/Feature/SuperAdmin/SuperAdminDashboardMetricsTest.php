<?php

use App\Models\Platform\Shop;
use App\Models\Platform\Subscription;
use App\Models\Platform\SubscriptionPayment;
use App\Models\Platform\SubscriptionPlan;
use App\Models\Platform\SystemActivity;
use App\Models\User;
use App\Services\Platform\SuperAdminMetricsService;
use Database\Seeders\DatabaseSeeder;

beforeEach(function (): void {
    $this->seed(DatabaseSeeder::class);
});

test('super admin dashboard metrics calculate accurately against known database records', function (): void {
    $superAdmin = User::factory()->create(['shop_id' => null]);
    $superAdmin->assignRole('Super Administrator');

    $starterPlan = SubscriptionPlan::where('slug', 'starter')->first();
    $growthPlan = SubscriptionPlan::where('slug', 'growth')->first();

    // 1. Shop 1: Active Paid Subscription (Starter: monthly_price = 4500.00)
    $shop1 = Shop::factory()->create(['name' => 'Metro Mobile']);
    Subscription::create([
        'shop_id' => $shop1->id,
        'plan_id' => $starterPlan->id,
        'status' => 'active',
        'billing_cycle' => 'monthly',
        'current_period_starts_at' => now()->subDays(5),
        'current_period_ends_at' => now()->addDays(25),
    ]);

    // 2. Shop 2: Active Paid Annual Subscription with discount
    // Seeded Growth annual price = 90000.00, discount = 5000.00 => (90000 - 5000) / 12 = 7083.33 / month
    $shop2 = Shop::factory()->create(['name' => 'Apex Cellular']);
    Subscription::create([
        'shop_id' => $shop2->id,
        'plan_id' => $growthPlan->id,
        'status' => 'active',
        'billing_cycle' => 'annual',
        'discount_amount' => 5000.00,
        'current_period_starts_at' => now()->subDays(10),
        'current_period_ends_at' => now()->addDays(355),
    ]);

    // 3. Shop 3: Free 7-Day Trial (Must NOT be counted in active paid, and MRR must be 0)
    $shop3 = Shop::factory()->create(['name' => 'Trial Hub']);
    Subscription::create([
        'shop_id' => $shop3->id,
        'plan_id' => $starterPlan->id,
        'status' => 'trial',
        'trial_ends_at' => now()->addDays(4),
        'current_period_starts_at' => now()->subDays(3),
        'current_period_ends_at' => now()->addDays(4),
    ]);

    // 4. Shop 4: Expired subscription (end date in past, no newer active subscription)
    $shop4 = Shop::factory()->create(['name' => 'Expired Tech']);
    Subscription::create([
        'shop_id' => $shop4->id,
        'plan_id' => $starterPlan->id,
        'status' => 'expired',
        'current_period_starts_at' => now()->subDays(60),
        'current_period_ends_at' => now()->subDays(30),
    ]);

    // 5. Payments:
    // Approved bank payment for shop 1 (LKR 4500.00)
    SubscriptionPayment::create([
        'shop_id' => $shop1->id,
        'plan_id' => $starterPlan->id,
        'amount' => 4500.00,
        'payment_method' => 'bank_transfer',
        'status' => 'approved',
        'reference_number' => 'REF-001',
        'verified_at' => now(),
    ]);

    // Pending bank transfer payment for shop 2 (LKR 8500.00)
    SubscriptionPayment::create([
        'shop_id' => $shop2->id,
        'plan_id' => $growthPlan->id,
        'amount' => 8500.00,
        'payment_method' => 'bank_transfer',
        'status' => 'under_review',
        'reference_number' => 'REF-002',
    ]);

    // Another pending bank transfer payment for shop 3 (LKR 4500.00)
    SubscriptionPayment::create([
        'shop_id' => $shop3->id,
        'plan_id' => $starterPlan->id,
        'amount' => 4500.00,
        'payment_method' => 'bank_transfer',
        'status' => 'pending',
        'reference_number' => 'REF-003',
    ]);

    // Rejected payment (LKR 4500.00)
    SubscriptionPayment::create([
        'shop_id' => $shop4->id,
        'plan_id' => $starterPlan->id,
        'amount' => 4500.00,
        'payment_method' => 'bank_transfer',
        'status' => 'rejected',
        'rejection_reason' => 'Invalid slip',
    ]);

    $metricsService = app(SuperAdminMetricsService::class);
    $stats = $metricsService->getDashboardStatistics();

    // Check counts
    expect($stats['total_shops'])->toBe(4);
    expect($stats['active_subscriptions'])->toBe(2); // Shop 1 and Shop 2 (Shop 3 is trial, Shop 4 is expired)
    expect($stats['trial_shops'])->toBe(1); // Shop 3
    expect($stats['expired_subscriptions'])->toBe(1); // Shop 4
    expect($stats['pending_payments_count'])->toBe(2); // REF-002 and REF-003
    expect($stats['pending_payments_amount'])->toBe(13000.00); // 8500 + 4500

    // Check MRR:
    // Shop 1: 4500.00 (starter monthly)
    // Shop 2: (growth annual_price 90000.00 - 5000.00 discount) / 12 = 7083.33
    // Total MRR = 4500.00 + 7083.33 = 11583.33
    // Free trials and expired accounts are strictly excluded.
    expect($stats['mrr'])->toBe(11583.33);

    // Hit the Super Admin Dashboard endpoint via HTTP
    $response = $this->actingAs($superAdmin, 'web')
        ->get('/super-admin')
        ->assertOk()
        ->assertSee('Metro Mobile')
        ->assertSee('Apex Cellular')
        ->assertSee('LKR 11,583.33')
        ->assertSee('LKR 13,000.00');

    // JSON API format check
    $jsonResponse = $this->actingAs($superAdmin, 'web')
        ->getJson('/super-admin')
        ->assertOk();

    expect($jsonResponse->json('stats.total_shops'))->toBe(4);
    expect($jsonResponse->json('stats.mrr'))->toBe(11583.33);
    expect($jsonResponse->json('stats.pending_payments_count'))->toBe(2);
});

test('mrr calculation handles custom recurring amount and prevents double counting renewals', function (): void {
    $plan = SubscriptionPlan::where('slug', 'starter')->first();
    $shop = Shop::factory()->create(['name' => 'Single Shop']);

    // Shop has an older expired subscription row
    Subscription::create([
        'shop_id' => $shop->id,
        'plan_id' => $plan->id,
        'status' => 'expired',
        'current_period_starts_at' => now()->subDays(60),
        'current_period_ends_at' => now()->subDays(30),
    ]);

    // Shop has an active renewed subscription with custom negotiated recurring_amount = 4000.00
    Subscription::create([
        'shop_id' => $shop->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'recurring_amount' => 4000.00,
        'billing_cycle' => 'monthly',
        'current_period_starts_at' => now()->subDays(2),
        'current_period_ends_at' => now()->addDays(28),
    ]);

    $metricsService = app(SuperAdminMetricsService::class);
    $mrr = $metricsService->calculateMrr();

    // Must evaluate to exactly 4000.00 and not double count or include the expired record
    expect($mrr)->toBe(4000.00);
});

test('subscription expiry alerts accurately detect subscriptions expiring within 7 days and exclude future renewed accounts', function (): void {
    $plan = SubscriptionPlan::where('slug', 'starter')->first();

    // Expiring in 3 days (Should alert)
    $shopA = Shop::factory()->create(['name' => 'Expiring Shop A']);
    Subscription::create([
        'shop_id' => $shopA->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'current_period_starts_at' => now()->subDays(27),
        'current_period_ends_at' => now()->addDays(3),
    ]);

    // Expiring in 20 days (Should NOT alert)
    $shopB = Shop::factory()->create(['name' => 'Safe Shop B']);
    Subscription::create([
        'shop_id' => $shopB->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'current_period_starts_at' => now()->subDays(10),
        'current_period_ends_at' => now()->addDays(20),
    ]);

    // Expired 2 days ago (Should NOT alert as expiring soon; it is already expired)
    $shopC = Shop::factory()->create(['name' => 'Past Shop C']);
    Subscription::create([
        'shop_id' => $shopC->id,
        'plan_id' => $plan->id,
        'status' => 'expired',
        'current_period_starts_at' => now()->subDays(32),
        'current_period_ends_at' => now()->subDays(2),
    ]);

    $metricsService = app(SuperAdminMetricsService::class);
    $alerts = $metricsService->getSubscriptionExpiryAlerts(7);

    expect($alerts->pluck('shop_id'))->toContain($shopA->id);
    expect($alerts->pluck('shop_id'))->not->toContain($shopB->id);
    expect($alerts->pluck('shop_id'))->not->toContain($shopC->id);
    expect($alerts->first()->remaining_days)->toBe(3);
});

test('payment analytics correctly reports successful, pending, failed, and partial refunds', function (): void {
    $shop = Shop::factory()->create();
    $plan = SubscriptionPlan::where('slug', 'starter')->first();

    // Approved payment: 10,000.00 with partial refund of 2,000.00 => net 8,000.00
    SubscriptionPayment::create([
        'shop_id' => $shop->id,
        'plan_id' => $plan->id,
        'amount' => 10000.00,
        'refunded_amount' => 2000.00,
        'status' => 'approved',
        'payment_method' => 'bank_transfer',
        'verified_at' => now(),
    ]);

    // Rejected payment: 5,000.00
    SubscriptionPayment::create([
        'shop_id' => $shop->id,
        'plan_id' => $plan->id,
        'amount' => 5000.00,
        'status' => 'rejected',
        'payment_method' => 'bank_transfer',
    ]);

    // Pending payment: 3,000.00
    SubscriptionPayment::create([
        'shop_id' => $shop->id,
        'plan_id' => $plan->id,
        'amount' => 3000.00,
        'status' => 'pending',
        'payment_method' => 'bank_transfer',
    ]);

    $metricsService = app(SuperAdminMetricsService::class);
    $analytics = $metricsService->getPaymentAnalytics('all');

    expect($analytics['successful']['count'])->toBe(1);
    expect($analytics['successful']['gross_amount'])->toBe(10000.00);
    expect($analytics['successful']['refunded_amount'])->toBe(2000.00);
    expect($analytics['successful']['net_amount'])->toBe(8000.00);

    expect($analytics['pending']['count'])->toBe(1);
    expect($analytics['pending']['amount'])->toBe(3000.00);

    expect($analytics['failed']['count'])->toBe(1);
    expect($analytics['failed']['amount'])->toBe(5000.00);

    expect($analytics['refunded']['count'])->toBe(1);
    expect($analytics['refunded']['amount'])->toBe(2000.00);
});

test('super admin can refund an approved payment partially or fully and logs audit activity', function (): void {
    $superAdmin = User::factory()->create(['shop_id' => null]);
    $superAdmin->assignRole('Super Administrator');

    $shop = Shop::factory()->create(['name' => 'Galle Telecom']);
    $plan = SubscriptionPlan::where('slug', 'starter')->first();

    $payment = SubscriptionPayment::create([
        'shop_id' => $shop->id,
        'plan_id' => $plan->id,
        'amount' => 5000.00,
        'status' => 'approved',
        'payment_method' => 'bank_transfer',
        'verified_at' => now(),
    ]);

    // 1. Partial refund of 1,500.00
    $this->actingAs($superAdmin, 'web')
        ->post("/super-admin/payments/{$payment->id}/refund", [
            'refund_amount' => 1500.00,
            'refund_reason' => 'Customer overpaid on registration discount.',
        ])
        ->assertRedirect();

    $payment->refresh();
    expect((float) $payment->refunded_amount)->toBe(1500.00);
    expect($payment->status)->toBe('approved'); // Still partially active
    expect($payment->isPartiallyRefunded())->toBeTrue();
    expect($payment->netAmount())->toBe(3500.00);

    // 2. Reject over-refund exceeding remaining refundable balance
    $this->actingAs($superAdmin, 'web')
        ->post("/super-admin/payments/{$payment->id}/refund", [
            'refund_amount' => 4000.00, // Remaining is only 3500
            'refund_reason' => 'Exceeds balance',
        ])
        ->assertSessionHasErrors('refund_amount');

    // 3. Complete remaining full refund of 3,500.00
    $this->actingAs($superAdmin, 'web')
        ->post("/super-admin/payments/{$payment->id}/refund", [
            'refund_amount' => 3500.00,
            'refund_reason' => 'Full cancellation requested.',
        ])
        ->assertRedirect();

    $payment->refresh();
    expect((float) $payment->refunded_amount)->toBe(5000.00);
    expect($payment->status)->toBe('refunded');
    expect($payment->isFullyRefunded())->toBeTrue();

    // Verify system activity was logged without sensitive credentials
    $log = SystemActivity::where('action', 'payment.refunded')->latest('id')->first();
    expect($log)->not->toBeNull();
    expect($log->shop_id)->toBe($shop->id);
    expect($log->user_id)->toBe($superAdmin->id);
    expect($log->properties['is_full_refund'])->toBeTrue();
});

test('system activity logger redacts sensitive credentials and passwords', function (): void {
    $shop = Shop::factory()->create();

    $activity = SystemActivity::log(
        'user.credential_change',
        'User modified security credentials',
        $shop->id,
        null,
        [
            'email' => 'admin@shop.test',
            'password' => 'SecretPass123!',
            'token' => 'plain-text-token-abc',
            'safe_field' => 'allowed_value',
        ]
    );

    expect($activity->properties['email'])->toBe('admin@shop.test');
    expect($activity->properties['safe_field'])->toBe('allowed_value');
    expect($activity->properties['password'])->toBe('[REDACTED]');
    expect($activity->properties['token'])->toBe('[REDACTED]');
});

test('non-superadmin users and guests are strictly denied from superadmin dashboard and endpoints', function (): void {
    // 1. Guest redirected to login
    $this->get('/super-admin')->assertRedirect(route('login'));
    $this->getJson('/super-admin')->assertUnauthorized();

    // 2. Regular Shop Administrator receives 403 Forbidden
    $shop = Shop::factory()->create();
    $shopAdmin = User::factory()->create(['shop_id' => $shop->id]);
    $shopAdmin->assignRole('Administrator');

    $this->actingAs($shopAdmin, 'web')
        ->get('/super-admin')
        ->assertForbidden();

    $this->actingAs($shopAdmin, 'web')
        ->getJson('/super-admin')
        ->assertForbidden();
});
