<?php

use App\Models\Platform\Shop;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->seed(DatabaseSeeder::class);
    config(['auth.verification.enabled' => true]);
});

test('verification notification is sent upon shop registration', function (): void {
    Notification::fake();

    $plan = \App\Models\Platform\SubscriptionPlan::where('slug', 'starter')->first()
        ?? \App\Models\Platform\SubscriptionPlan::first();

    $payload = [
        'shop_name' => 'Verify Phone Shop',
        'name' => 'Kasun Silva',
        'phone' => '+94771234567',
        'email' => 'kasun@phoneshop.test',
        'password' => 'StrongSecret123!',
        'password_confirmation' => 'StrongSecret123!',
        'terms' => '1',
        'plan_id' => $plan ? $plan->id : 1,
        'cardholder_name' => 'Kasun Silva',
        'card_number' => '4242424242424242',
        'card_expiry' => '12/28',
        'card_cvv' => '123',
    ];

    $this->post('/register', $payload);

    $user = User::where('email', 'kasun@phoneshop.test')->firstOrFail();
    expect($user->hasVerifiedEmail())->toBeFalse();

    Notification::assertSentTo($user, VerifyEmail::class);
});

test('email verification screen can be rendered for unverified user', function (): void {
    $shop = Shop::factory()->create(['name' => 'Metro Cellular']);
    $user = User::factory()->unverified()->create(['shop_id' => $shop->id]);

    $response = $this->actingAs($user, 'web')->get('/email/verify');

    $response->assertOk();
    $response->assertSee('Verify Your Email');
    $response->assertSee('Metro Cellular');
    $response->assertSee($user->email);
    $response->assertSee('Verification Pending');
    $response->assertSee('Resend Verification Email');
});

test('verified user visiting verification notice is redirected to app', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user, 'web')
        ->get('/email/verify')
        ->assertRedirect(route('app'));
});

test('email can be verified with a valid signed url', function (): void {
    Event::fake([Verified::class]);

    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
    );

    $response = $this->actingAs($user, 'web')->get($verificationUrl);

    Event::assertDispatched(Verified::class);
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    $response->assertRedirect(route('app'));
    $response->assertSessionHas('status', 'Your email address has been verified successfully! Welcome to your shop workspace.');
});

test('already verified user clicking verification link is redirected with friendly status', function (): void {
    $user = User::factory()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
    );

    $response = $this->actingAs($user, 'web')->get($verificationUrl);

    $response->assertRedirect(route('app'));
    $response->assertSessionHas('status', 'Your email address is already verified.');
});

test('cannot verify email with an invalid hash', function (): void {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1('wrong-email@example.test')]
    );

    $response = $this->actingAs($user, 'web')->get($verificationUrl);

    $response->assertForbidden();
    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('cannot verify email with an expired signed url', function (): void {
    $user = User::factory()->unverified()->create();

    // Expired 15 minutes ago
    $expiredUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->subMinutes(15),
        ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
    );

    $response = $this->actingAs($user, 'web')->get($expiredUrl);

    $response->assertStatus(403);
    $response->assertSee('Verification Link Expired');
    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('user cannot verify another users account (tenant cross-account unauthorized access rejected)', function (): void {
    $shopA = Shop::factory()->create(['name' => 'Shop A']);
    $userA = User::factory()->unverified()->create(['shop_id' => $shopA->id, 'email' => 'usera@example.test']);

    $shopB = Shop::factory()->create(['name' => 'Shop B']);
    $userB = User::factory()->unverified()->create(['shop_id' => $shopB->id, 'email' => 'userb@example.test']);

    // Signed link generated for User A
    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $userA->id, 'hash' => sha1($userA->getEmailForVerification())]
    );

    // Attacker User B attempts to access User A's verification link
    $response = $this->actingAs($userB, 'web')->get($verificationUrl);

    $response->assertForbidden();
    $response->assertSee('This verification link belongs to another user account.');

    expect($userA->fresh()->hasVerifiedEmail())->toBeFalse();
    expect($userB->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('verification notification can be resent', function (): void {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user, 'web')->post('/email/verification-notification');

    $response->assertSessionHas('status', 'verification-link-sent');
    Notification::assertSentTo($user, VerifyEmail::class);
});

test('verification notification resend requests are rate limited', function (): void {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    // 6 allowed attempts within 1 minute
    for ($i = 0; $i < 6; $i++) {
        $this->actingAs($user, 'web')
            ->post('/email/verification-notification')
            ->assertSessionHas('status', 'verification-link-sent');
    }

    // 7th attempt within same minute must be throttled (HTTP 429)
    $response = $this->actingAs($user, 'web')->post('/email/verification-notification');
    $response->assertStatus(429);
});

test('unverified web user is blocked from accessing protected workspace app shell', function (): void {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user, 'web')->get('/app');

    $response->assertRedirect(route('verification.notice'));
});

test('unverified api user is blocked from accessing protected shop resources', function (): void {
    $shop = Shop::factory()->create();
    $user = User::factory()->unverified()->create(['shop_id' => $shop->id]);
    $user->assignRole('Administrator');

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/employees');

    $response->assertForbidden();
    $response->assertJsonPath('message', 'Your email address is not verified.');
});

test('when email verification is paused, unverified user can access protected shop routes directly', function (): void {
    config(['auth.verification.enabled' => false]);

    $shop = Shop::factory()->create();
    $user = User::factory()->unverified()->create(['shop_id' => $shop->id]);
    $user->assignRole('Shop Owner');

    $response = $this->actingAs($user, 'web')->get('/shop/dashboard');

    $response->assertOk();
    $response->assertViewIs('shop.dashboard.index');
});

test('when email verification is paused, newly registered owner lands directly on dashboard without notice', function (): void {
    config(['auth.verification.enabled' => false]);

    $plan = \App\Models\Platform\SubscriptionPlan::where('slug', 'starter')->first()
        ?? \App\Models\Platform\SubscriptionPlan::first();

    $payload = [
        'shop_name' => 'Instant Access Shop',
        'name' => 'Nuwan Perera',
        'phone' => '+94779876543',
        'email' => 'nuwan@instantshop.test',
        'password' => 'StrongSecret123!',
        'password_confirmation' => 'StrongSecret123!',
        'terms' => '1',
        'plan_id' => $plan ? $plan->id : 1,
        'cardholder_name' => 'Nuwan Perera',
        'card_number' => '4242424242424242',
        'card_expiry' => '12/28',
        'card_cvv' => '123',
    ];

    $response = $this->post('/register', $payload);

    $user = User::where('email', 'nuwan@instantshop.test')->firstOrFail();
    expect($user->hasVerifiedEmail())->toBeTrue();
    $response->assertRedirect(route('shop.dashboard'));
});
