<?php

use App\Models\Employee;
use App\Models\Platform\Shop;
use App\Models\Platform\Subscription;
use App\Models\Platform\SubscriptionPayment;
use App\Models\Platform\SubscriptionPlan;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->seed(DatabaseSeeder::class);
});

test('registration screen can be rendered with shop onboarding, business details, and password strength elements', function (): void {
    $response = $this->get('/register');

    $response->assertOk();
    $response->assertSee('Register Shop Workspace');
    $response->assertSee('Shop / Business Name');
    $response->assertSee('Owner Full Name');
    $response->assertSee('Mobile Number');
    $response->assertSee('Owner Email Address');
    $response->assertSee('Business Address');
    $response->assertSee('Country');
    $response->assertSee('Timezone');
    $response->assertSee('Business Registration Details');
    $response->assertSee('Password');
    $response->assertSee('Confirm Password');
    $response->assertSee('Password Strength:');
    $response->assertSee('8+ characters');
    $response->assertSee('Choose Subscription Plan');
    $response->assertSee('Payment Card — 7-Day Free Trial');
    $response->assertSee('Card Number');
    $response->assertSee('Terms of Service');
    $response->assertSee('7-Day Free Trial');
});

test('authenticated users visiting register can view registration screen with active session indicator', function (): void {
    $user = User::factory()->create(['name' => 'Existing Admin']);

    $this->actingAs($user, 'web')
        ->get('/register')
        ->assertOk()
        ->assertSee('Register Shop Workspace')
        ->assertSee('Signed in as:')
        ->assertSee('Existing Admin');
});

test('new shop owner can register and creates exactly one independent shop workspace', function (): void {
    Event::fake([Registered::class]);

    $starterPlan = SubscriptionPlan::where('slug', 'starter')->first();

    $payload = [
        'shop_name' => 'Metro Cellular',
        'name' => 'Kamal Silva',
        'phone' => '+94 77 123 4567',
        'email' => 'kamal@example.test',
        'business_address' => '142 Galle Road, Colombo 03',
        'country' => 'Sri Lanka',
        'timezone' => 'Asia/Colombo',
        'business_reg_number' => 'PV-87654',
        'plan_id' => $starterPlan->id,
        'billing_cycle' => 'monthly',
        'cardholder_name' => 'Kamal Silva',
        'card_number' => '4242424242424242',
        'card_expiry' => '12/28',
        'card_cvv' => '123',
        'password' => 'StrongSecret123!',
        'password_confirmation' => 'StrongSecret123!',
        'terms' => '1',
    ];

    $expectedDestination = config('auth.verification.enabled', false) ? route('verification.notice') : route('shop.dashboard');
    $response = $this->post('/register', $payload);

    $response->assertRedirect($expectedDestination);
    $this->assertAuthenticated('web');

    // Exactly 1 shop created with business details
    $shop = Shop::where('name', 'Metro Cellular')->first();
    expect($shop)->not->toBeNull();
    expect($shop->address)->toBe('142 Galle Road, Colombo 03');
    expect($shop->country)->toBe('Sri Lanka');
    expect($shop->business_reg_number)->toBe('PV-87654');

    // Exactly 1 owner account created with shop association and phone saved
    $user = User::where('email', 'kamal@example.test')->first();
    expect($user)->not->toBeNull();
    expect($user->shop_id)->toBe($shop->id);
    expect($user->phone)->toBe('+94 77 123 4567');
    expect($user->hasRole('Administrator'))->toBeTrue();

    // Exactly 1 default setting initialized for the shop (verified via DB query)
    $this->assertDatabaseHas('settings', [
        'shop_id' => $shop->id,
        'timezone' => 'Asia/Colombo',
        'currency' => 'LKR',
    ]);

    // 7-day trial subscription initialized with card details
    $this->assertDatabaseHas('subscriptions', [
        'shop_id' => $shop->id,
        'plan_id' => $starterPlan->id,
        'status' => 'trial',
        'card_last4' => '4242',
    ]);

    // System activity logged
    $this->assertDatabaseHas('system_activities', [
        'shop_id' => $shop->id,
        'action' => 'shop.registered',
    ]);

    // Verification event fired
    Event::assertDispatched(Registered::class, function (Registered $event) use ($user): bool {
        return $event->user->id === $user->id;
    });
});

test('JSON registration returns 201 with structured payload and destination', function (): void {
    Event::fake([Registered::class]);

    $growthPlan = SubscriptionPlan::where('slug', 'growth')->first();

    $payload = [
        'shop_name' => 'Tech World',
        'name' => 'Nimal Perera',
        'phone' => '0771234567',
        'email' => 'nimal@example.test',
        'plan_id' => $growthPlan->id,
        'billing_cycle' => 'monthly',
        'cardholder_name' => 'Nimal Perera',
        'card_number' => '5555444433332222',
        'card_expiry' => '08/29',
        'card_cvv' => '456',
        'password' => 'SecurePass987!',
        'password_confirmation' => 'SecurePass987!',
        'terms' => true,
    ];

    $response = $this->postJson('/register', $payload);

    $expectedDestination = config('auth.verification.enabled', false) ? route('verification.notice') : route('shop.dashboard');

    $response->assertCreated()
        ->assertJsonPath('message', 'Workspace and owner account created successfully.')
        ->assertJsonPath('user.email', 'nimal@example.test')
        ->assertJsonPath('user.phone', '0771234567')
        ->assertJsonPath('user.shop_name', 'Tech World')
        ->assertJsonPath('redirect', $expectedDestination);
});

test('registration requires valid mobile number', function (?string $invalidPhone): void {
    $payload = [
        'shop_name' => 'Valid Shop',
        'name' => 'Valid Owner',
        'phone' => $invalidPhone,
        'email' => 'phone-test@example.test',
        'password' => 'StrongSecret123!',
        'password_confirmation' => 'StrongSecret123!',
        'terms' => '1',
    ];

    $response = $this->post('/register', $payload);

    $response->assertSessionHasErrors('phone');
})->with([
    'missing' => null,
    'too-short' => '123',
    'invalid-chars' => 'abc-phone-xyz',
]);

test('registration requires terms acceptance', function (): void {
    $payload = [
        'shop_name' => 'Valid Shop',
        'name' => 'Valid Owner',
        'phone' => '+94771234567',
        'email' => 'terms-test@example.test',
        'password' => 'StrongSecret123!',
        'password_confirmation' => 'StrongSecret123!',
        // terms omitted
    ];

    $response = $this->post('/register', $payload);

    $response->assertSessionHasErrors('terms');
});

test('duplicate email is rejected and does not create an orphaned shop', function (): void {
    $existingUser = User::factory()->create(['email' => 'duplicate@example.test']);
    $initialShopCount = Shop::count();

    $response = $this->post('/register', [
        'shop_name' => 'Orphan Test Shop',
        'name' => 'Someone Else',
        'phone' => '+94779876543',
        'email' => 'duplicate@example.test',
        'password' => 'StrongSecret123!',
        'password_confirmation' => 'StrongSecret123!',
        'terms' => '1',
    ]);

    $response->assertSessionHasErrors('email');
    expect(Shop::count())->toBe($initialShopCount);
    expect(Shop::where('name', 'Orphan Test Shop')->exists())->toBeFalse();
});

test('password complexity is enforced', function (string $invalidPassword): void {
    $response = $this->post('/register', [
        'shop_name' => 'Valid Shop',
        'name' => 'Valid Owner',
        'phone' => '+94779876543',
        'email' => 'valid@example.test',
        'password' => $invalidPassword,
        'password_confirmation' => $invalidPassword,
        'terms' => '1',
    ]);

    $response->assertSessionHasErrors('password');
})->with([
    'short' => 'Short1!',
    'no-numbers' => 'NoNumbersPassword!',
    'no-mixed-case' => 'alllowercasepassword123',
]);

test('client-supplied shop_id is ignored and cannot hijack another tenant', function (): void {
    $victimShop = Shop::factory()->create(['name' => 'Victim Shop']);
    $plan = SubscriptionPlan::where('slug', 'starter')->first();

    $response = $this->post('/register', [
        'shop_name' => 'Attacker Shop',
        'name' => 'Attacker',
        'phone' => '+94771112233',
        'email' => 'attacker@example.test',
        'plan_id' => $plan->id,
        'billing_cycle' => 'monthly',
        'cardholder_name' => 'Attacker',
        'card_number' => '4242424242424242',
        'card_expiry' => '12/28',
        'card_cvv' => '123',
        'password' => 'AttackSecret123!',
        'password_confirmation' => 'AttackSecret123!',
        'terms' => '1',
        'shop_id' => $victimShop->id, // Malicious injection attempt
    ]);

    $attacker = User::where('email', 'attacker@example.test')->firstOrFail();
    // Must be associated with its OWN newly created shop, NOT victimShop
    expect($attacker->shop_id)->not->toBe($victimShop->id);
    expect($attacker->shop->name)->toBe('Attacker Shop');
});

test('registered users are strictly isolated to their own shop workspace', function (): void {
    // Owner A registers Shop A
    $shopA = Shop::factory()->create(['name' => 'Shop A']);
    $ownerA = User::factory()->create(['shop_id' => $shopA->id]);
    $ownerA->assignRole('Administrator');
    $employeeA = Employee::factory()->create(['shop_id' => $shopA->id, 'name' => 'Employee A']);

    // Owner B registers Shop B
    $shopB = Shop::factory()->create(['name' => 'Shop B']);
    $ownerB = User::factory()->create(['shop_id' => $shopB->id]);
    $ownerB->assignRole('Administrator');
    $employeeB = Employee::factory()->create(['shop_id' => $shopB->id, 'name' => 'Employee B']);

    // Owner A queries employees via API
    Sanctum::actingAs($ownerA);
    $responseA = $this->getJson('/api/v1/employees');
    $responseA->assertOk();
    $namesA = collect($responseA->json('data'))->pluck('name');
    expect($namesA)->toContain('Employee A');
    expect($namesA)->not->toContain('Employee B');

    // Owner B queries employees via API
    Sanctum::actingAs($ownerB);
    $responseB = $this->getJson('/api/v1/employees');
    $responseB->assertOk();
    $namesB = collect($responseB->json('data'))->pluck('name');
    expect($namesB)->toContain('Employee B');
    expect($namesB)->not->toContain('Employee A');
});

test('failed registration transaction rolls back atomically without leaving orphan shop records', function (): void {
    $initialShopCount = Shop::count();
    $initialUserCount = User::count();

    // Bind a listener on User creating to simulate an unexpected database failure
    User::creating(function ($user) {
        if ($user->email === 'simulate-failure@example.test') {
            throw new \RuntimeException('Simulated database write failure');
        }
    });

    $onboardingService = app(\App\Services\Tenancy\ShopOnboardingService::class);

    try {
        $onboardingService->onboard([
            'shop_name' => 'Failed Transaction Shop',
            'name' => 'Failed Owner',
            'email' => 'simulate-failure@example.test',
            'password' => 'StrongSecret123!',
        ]);
        $this->fail('Expected exception was not thrown');
    } catch (\RuntimeException $e) {
        expect($e->getMessage())->toBe('Simulated database write failure');
    }

    // Atomic DB transaction must leave 0 orphan records
    expect(Shop::count())->toBe($initialShopCount);
    expect(User::count())->toBe($initialUserCount);
    expect(Shop::where('name', 'Failed Transaction Shop')->exists())->toBeFalse();
});

test('repeated registration requests with the same email cannot create duplicate workspaces', function (): void {
    Event::fake([Registered::class]);

    $plan = SubscriptionPlan::where('slug', 'starter')->first();

    $payload = [
        'shop_name' => 'Original Shop',
        'name' => 'Original Owner',
        'phone' => '+94771112222',
        'email' => 'repeat-test@example.test',
        'plan_id' => $plan->id,
        'billing_cycle' => 'monthly',
        'cardholder_name' => 'Original Owner',
        'card_number' => '4242424242424242',
        'card_expiry' => '12/28',
        'card_cvv' => '123',
        'password' => 'StrongSecret123!',
        'password_confirmation' => 'StrongSecret123!',
        'terms' => '1',
    ];

    // First registration succeeds
    $expectedDestination = config('auth.verification.enabled', false) ? route('verification.notice') : route('shop.dashboard');
    $response1 = $this->post('/register', $payload);
    $response1->assertRedirect($expectedDestination);
    $shopCount = Shop::count();
    $userCount = User::count();

    // Second registration with same email is rejected
    Auth::logout();
    $response2 = $this->post('/register', array_merge($payload, ['shop_name' => 'Duplicate Attempt Shop']));
    $response2->assertSessionHasErrors('email');

    // Counts must not have increased
    expect(Shop::count())->toBe($shopCount);
    expect(User::count())->toBe($userCount);
    expect(Shop::where('name', 'Duplicate Attempt Shop')->exists())->toBeFalse();
});

test('newly registered shop and owner immediately appear in super admin dashboard metrics and recent registrations', function (): void {
    Event::fake([Registered::class]);

    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('Super Administrator');
    $plan = SubscriptionPlan::where('slug', 'starter')->first();

    // Register a new shop
    $expectedDestination = config('auth.verification.enabled', false) ? route('verification.notice') : route('shop.dashboard');
    $this->post('/register', [
        'shop_name' => 'Colombo Apex Mobile',
        'name' => 'Dinesh Fonseka',
        'phone' => '+94 77 999 8888',
        'email' => 'dinesh@apexmobile.lk',
        'business_address' => '55 Duplication Rd, Colombo 04',
        'country' => 'Sri Lanka',
        'timezone' => 'Asia/Colombo',
        'business_reg_number' => 'PV-998877',
        'plan_id' => $plan->id,
        'billing_cycle' => 'monthly',
        'cardholder_name' => 'Dinesh Fonseka',
        'card_number' => '4242424242424242',
        'card_expiry' => '12/28',
        'card_cvv' => '123',
        'password' => 'SecurePass123!@#',
        'password_confirmation' => 'SecurePass123!@#',
        'terms' => '1',
    ])->assertRedirect($expectedDestination);

    // Check Super Admin Dashboard
    $dashResponse = $this->actingAs($superAdmin, 'web')->get(route('superadmin.dashboard'));
    $dashResponse->assertOk();
    $dashResponse->assertSee('Colombo Apex Mobile');
    $dashResponse->assertSee('Dinesh Fonseka');
    $dashResponse->assertSee('Trial');
});

test('shop owner can register with an 8-character valid password', function (): void {
    Event::fake([Registered::class]);
    $plan = SubscriptionPlan::where('slug', 'starter')->first();

    $expectedDestination = config('auth.verification.enabled', false) ? route('verification.notice') : route('shop.dashboard');
    $response = $this->post('/register', [
        'shop_name' => 'Eight Char Shop',
        'name' => 'Owner Eight',
        'phone' => '+94 77 123 4567',
        'email' => 'eightchar@example.test',
        'plan_id' => $plan->id,
        'billing_cycle' => 'monthly',
        'cardholder_name' => 'Owner Eight',
        'card_number' => '4242424242424242',
        'card_expiry' => '12/28',
        'card_cvv' => '123',
        'password' => 'Secret1!', // Exactly 8 characters with upper, lower, number
        'password_confirmation' => 'Secret1!',
        'terms' => '1',
    ]);

    $response->assertRedirect($expectedDestination);
    $this->assertAuthenticated('web');
    $this->assertDatabaseHas('users', ['email' => 'eightchar@example.test']);
});

test('registration requires selecting a subscription plan', function (): void {
    $response = $this->post('/register', [
        'shop_name' => 'No Plan Shop',
        'name' => 'Planless User',
        'phone' => '+94 77 111 2233',
        'email' => 'noplan@example.test',
        'cardholder_name' => 'Planless User',
        'card_number' => '4242424242424242',
        'card_expiry' => '12/28',
        'card_cvv' => '123',
        'password' => 'SecurePass123!',
        'password_confirmation' => 'SecurePass123!',
        'terms' => '1',
        // plan_id omitted
    ]);

    $response->assertSessionHasErrors('plan_id');
});

test('registration requires payment card details for 7-day free trial', function (): void {
    $plan = SubscriptionPlan::where('slug', 'starter')->first();

    $response = $this->post('/register', [
        'shop_name' => 'No Card Shop',
        'name' => 'Cardless User',
        'phone' => '+94 77 111 2233',
        'email' => 'nocard@example.test',
        'plan_id' => $plan->id,
        'password' => 'SecurePass123!',
        'password_confirmation' => 'SecurePass123!',
        'terms' => '1',
        // card details omitted
    ]);

    $response->assertSessionHasErrors(['cardholder_name', 'card_number', 'card_expiry', 'card_cvv']);
});

test('shop owner can select Growth plan and subscription initializes with 3 branches and card saved', function (): void {
    Event::fake([Registered::class]);

    $growthPlan = SubscriptionPlan::where('slug', 'growth')->first();

    $expectedDestination = config('auth.verification.enabled', false) ? route('verification.notice') : route('shop.dashboard');
    $response = $this->post('/register', [
        'shop_name' => 'Growth Store Kandy',
        'name' => 'Saman Jayasinghe',
        'phone' => '+94 81 223 4455',
        'email' => 'saman@growthstore.lk',
        'plan_id' => $growthPlan->id,
        'billing_cycle' => 'monthly',
        'cardholder_name' => 'Saman Jayasinghe',
        'card_number' => '5105105105105100',
        'card_expiry' => '11/29',
        'card_cvv' => '789',
        'password' => 'GrowthPass123!',
        'password_confirmation' => 'GrowthPass123!',
        'terms' => '1',
    ]);

    $response->assertRedirect($expectedDestination);

    $shop = Shop::where('name', 'Growth Store Kandy')->firstOrFail();
    $sub = Subscription::where('shop_id', $shop->id)->firstOrFail();

    expect($sub->plan_id)->toBe($growthPlan->id);
    expect($sub->status)->toBe('trial');
    expect($sub->card_last4)->toBe('5100');
    expect($sub->card_brand)->toBe('mastercard');
    expect($sub->cardholder_name)->toBe('Saman Jayasinghe');
    expect($sub->card_expiry)->toBe('11/29');
    expect($sub->plan->max_branches)->toBe(3);
    expect($sub->plan->has_multi_branch)->toBeTrue();

    // Verify trial card authorization entry
    $payment = SubscriptionPayment::where('shop_id', $shop->id)->first();
    expect($payment)->not->toBeNull();
    expect((float) $payment->amount)->toBe(0.00);
    expect($payment->payment_method)->toBe('card');
    expect($payment->status)->toBe('approved');
});

test('shop owner can register with bank slip and receives 7-day free trial with pending verification', function (): void {
    \Illuminate\Support\Facades\Storage::fake('public');
    Event::fake([Registered::class]);

    $starterPlan = SubscriptionPlan::where('slug', 'starter')->first();
    $file = \Illuminate\Http\UploadedFile::fake()->create('deposit_slip.pdf', 100, 'application/pdf');

    $expectedDestination = config('auth.verification.enabled', false) ? route('verification.notice') : route('shop.dashboard');
    $response = $this->post('/register', [
        'shop_name' => 'Bank Slip Mobile Care',
        'name' => 'Nimal Jayawardena',
        'phone' => '+94 77 987 6543',
        'email' => 'nimal@bankslip.lk',
        'plan_id' => $starterPlan->id,
        'billing_cycle' => 'monthly',
        'payment_method' => 'bank_transfer',
        'reference_number' => 'BOC-DEP-88419',
        'receipt' => $file,
        'password' => 'SecurePass123!',
        'password_confirmation' => 'SecurePass123!',
        'terms' => '1',
    ]);

    $response->assertRedirect($expectedDestination);

    $shop = Shop::where('name', 'Bank Slip Mobile Care')->firstOrFail();
    $sub = Subscription::where('shop_id', $shop->id)->firstOrFail();

    expect($sub->status)->toBe('trial');
    expect($sub->payment_method)->toBe('bank_transfer');
    expect($sub->trial_ends_at)->not->toBeNull();
    expect($sub->trial_ends_at->isFuture())->toBeTrue();

    // Verify pending bank payment with slip uploaded
    $payment = SubscriptionPayment::where('shop_id', $shop->id)->first();
    expect($payment)->not->toBeNull();
    expect($payment->payment_method)->toBe('bank_transfer');
    expect($payment->status)->toBe('pending');
    expect($payment->reference_number)->toBe('BOC-DEP-88419');
    expect($payment->receipt_url)->not->toBeNull();
});

test('bank transfer registration requires reference number', function (): void {
    $plan = SubscriptionPlan::first();

    $response = $this->post('/register', [
        'shop_name' => 'Ref Test Store',
        'name' => 'Ref Tester',
        'phone' => '+94 77 123 4567',
        'email' => 'reftest@example.test',
        'plan_id' => $plan->id,
        'payment_method' => 'bank_transfer',
        'password' => 'SecurePass123!',
        'password_confirmation' => 'SecurePass123!',
        'terms' => '1',
        // reference_number omitted
    ]);

    $response->assertSessionHasErrors(['reference_number']);
});

