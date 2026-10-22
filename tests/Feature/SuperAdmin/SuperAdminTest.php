<?php

use App\Models\Platform\Shop;
use App\Models\Platform\Subscription;
use App\Models\Platform\SubscriptionPayment;
use App\Models\Platform\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function (): void {
    $this->seed(DatabaseSeeder::class);
});

test('unauthorized users without superadmin role cannot access superadmin dashboard', function (): void {
    $regularUser = User::factory()->create();
    $regularUser->assignRole('Administrator');

    $this->actingAs($regularUser, 'web')
        ->get('/super-admin')
        ->assertForbidden();
});

test('superadmin can access dashboard and view platform metrics', function (): void {
    $superAdmin = User::factory()->create(['shop_id' => null]);
    $superAdmin->assignRole('Super Administrator');

    $shopA = Shop::factory()->create(['name' => 'Cellular Zone']);
    $plan = SubscriptionPlan::where('slug', 'starter')->first();

    Subscription::create([
        'shop_id' => $shopA->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'current_period_starts_at' => now(),
        'current_period_ends_at' => now()->addDays(30),
    ]);

    $this->actingAs($superAdmin, 'web')
        ->get('/super-admin')
        ->assertOk()
        ->assertSee('Cellular Zone')
        ->assertSee('SaaS Platform Overview');
});

test('superadmin can list and filter shops', function (): void {
    $superAdmin = User::factory()->create(['shop_id' => null]);
    $superAdmin->assignRole('Super Administrator');

    $shopA = Shop::factory()->create(['name' => 'Metro Cellular']);
    $shopB = Shop::factory()->create(['name' => 'Tech World']);

    $this->actingAs($superAdmin, 'web')
        ->get('/super-admin/shops?search=Metro')
        ->assertOk()
        ->assertSee('Metro Cellular')
        ->assertDontSee('Tech World');
});

test('superadmin can toggle shop suspension', function (): void {
    $superAdmin = User::factory()->create(['shop_id' => null]);
    $superAdmin->assignRole('Super Administrator');

    $shop = Shop::factory()->create(['name' => 'City Phones']);
    $plan = SubscriptionPlan::where('slug', 'starter')->first();

    $sub = Subscription::create([
        'shop_id' => $shop->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'current_period_starts_at' => now(),
        'current_period_ends_at' => now()->addDays(30),
    ]);

    $this->actingAs($superAdmin, 'web')
        ->post("/super-admin/shops/{$shop->id}/toggle-suspension")
        ->assertRedirect();

    expect($sub->fresh()->status)->toBe('suspended');

    // Toggle back
    $this->actingAs($superAdmin, 'web')
        ->post("/super-admin/shops/{$shop->id}/toggle-suspension")
        ->assertRedirect();

    expect($sub->fresh()->status)->toBe('active');
});

test('superadmin can create and update subscription plans', function (): void {
    $superAdmin = User::factory()->create(['shop_id' => null]);
    $superAdmin->assignRole('Super Administrator');

    $payload = [
        'name' => 'Multi-Branch Ultimate',
        'description' => 'Unlimited branches with real-time stock sync.',
        'monthly_price' => 19500.00,
        'trial_days' => 14,
        'max_branches' => 10,
        'max_staff' => 25,
        'has_imei_tracking' => true,
        'has_repair_management' => true,
        'has_warranty_management' => true,
        'has_accounting_reports' => true,
        'has_multi_branch' => true,
        'has_customer_portal' => true,
    ];

    $this->actingAs($superAdmin, 'web')
        ->post('/super-admin/plans', $payload)
        ->assertRedirect();

    $this->assertDatabaseHas('subscription_plans', [
        'name' => 'Multi-Branch Ultimate',
        'monthly_price' => 19500.00,
        'max_branches' => 10,
    ]);
});

test('superadmin can approve bank transfer payments and activate subscription', function (): void {
    $superAdmin = User::factory()->create(['shop_id' => null]);
    $superAdmin->assignRole('Super Administrator');

    $shop = Shop::factory()->create(['name' => 'Kandy Cellular']);
    $plan = SubscriptionPlan::where('slug', 'growth')->first();

    $payment = SubscriptionPayment::create([
        'shop_id' => $shop->id,
        'plan_id' => $plan->id,
        'amount' => 8500.00,
        'payment_method' => 'bank_transfer',
        'status' => 'under_review',
        'reference_number' => 'BOC-998822',
    ]);

    $this->actingAs($superAdmin, 'web')
        ->post("/super-admin/payments/{$payment->id}/approve")
        ->assertRedirect();

    expect($payment->fresh()->status)->toBe('approved');
    expect($payment->fresh()->verified_by)->toBe($superAdmin->id);

    // Associated subscription is active for 30 days
    $sub = Subscription::where('shop_id', $shop->id)->first();
    expect($sub)->not->toBeNull();
    expect($sub->status)->toBe('active');
    expect($sub->plan_id)->toBe($plan->id);
});

test('superadmin can reject bank transfer payments with reason', function (): void {
    $superAdmin = User::factory()->create(['shop_id' => null]);
    $superAdmin->assignRole('Super Administrator');

    $shop = Shop::factory()->create();
    $plan = SubscriptionPlan::where('slug', 'starter')->first();

    $payment = SubscriptionPayment::create([
        'shop_id' => $shop->id,
        'plan_id' => $plan->id,
        'amount' => 4500.00,
        'payment_method' => 'bank_transfer',
        'status' => 'pending',
        'reference_number' => 'FAKE-SLIP-123',
    ]);

    $this->actingAs($superAdmin, 'web')
        ->post("/super-admin/payments/{$payment->id}/reject", [
            'rejection_reason' => 'Bank deposit slip is illegible.',
        ])
        ->assertRedirect();

    expect($payment->fresh()->status)->toBe('rejected');
    expect($payment->fresh()->rejection_reason)->toBe('Bank deposit slip is illegible.');
});

test('new shop registration automatically initializes 7-day free trial on default plan', function (): void {
    $payload = [
        'shop_name' => 'Nexus Mobile Care',
        'name' => 'Sunil Shantha',
        'phone' => '0712345678',
        'email' => 'sunil@nexuscare.test',
        'password' => 'StrongPass1234!',
        'password_confirmation' => 'StrongPass1234!',
        'terms' => '1',
    ];

    $this->post('/register', $payload)->assertRedirect(route('verification.notice'));

    $shop = Shop::where('name', 'Nexus Mobile Care')->first();
    expect($shop)->not->toBeNull();

    $sub = Subscription::where('shop_id', $shop->id)->first();
    expect($sub)->not->toBeNull();
    expect($sub->status)->toBe('trial');
    expect($sub->plan->slug)->toBe('starter');
    expect($sub->trial_ends_at)->not->toBeNull();
});

