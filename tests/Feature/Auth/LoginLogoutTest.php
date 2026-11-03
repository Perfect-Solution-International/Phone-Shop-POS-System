<?php

use App\Models\Platform\Shop;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Auth;

beforeEach(function (): void {
    $this->seed(DatabaseSeeder::class);
});

test('web form login authenticates user, regenerates session, and redirects to app', function (): void {
    $shop = Shop::factory()->create();
    $user = User::factory()->create(['shop_id' => $shop->id, 'password' => bcrypt('StrongSecret123!')]);
    $user->assignRole('Administrator');

    $guestSessionId = session()->getId();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'StrongSecret123!',
    ]);

    $response->assertRedirect(route('app'));
    $this->assertAuthenticatedAs($user, 'web');
    expect(session()->getId())->not->toBe($guestSessionId);
});

test('web form login with invalid credentials fails and keeps user as guest', function (): void {
    $user = User::factory()->create(['password' => bcrypt('CorrectPassword123!')]);

    $response = $this->from('/login')->post('/login', [
        'email' => $user->email,
        'password' => 'WrongPassword123!',
    ]);

    $response->assertRedirect('/login');
    $response->assertSessionHasErrors('email');
    $this->assertGuest('web');
});

test('unverified user logging in is redirected safely to email verification notice', function (): void {
    config(['auth.verification.enabled' => true]);

    $shop = Shop::factory()->create();
    $user = User::factory()->unverified()->create(['shop_id' => $shop->id, 'password' => bcrypt('StrongSecret123!')]);
    $user->assignRole('Administrator');

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'StrongSecret123!',
    ]);

    $response->assertRedirect(route('verification.notice'));
    $this->assertAuthenticatedAs($user, 'web');
});

test('web form logout terminates session, regenerates CSRF token, and redirects to login', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user, 'web');
    $this->assertAuthenticatedAs($user, 'web');

    $oldSessionId = session()->getId();

    $response = $this->post('/logout');

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('status', 'You have been successfully signed out.');
    $this->assertGuest('web');
    expect(session()->getId())->not->toBe($oldSessionId);
});

test('guest cannot access logout route', function (): void {
    $response = $this->post('/logout');

    $response->assertRedirect(route('login'));
});

test('login redirection prevents open redirects to external domains', function (): void {
    $user = User::factory()->create();
    $user->assignRole('Administrator');

    // Attempt open redirect via malicious URL query or manipulation
    $response = $this->withSession(['url.intended' => 'https://evil-phishing-site.test'])
        ->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

    // Redirect must be internal to the application
    $targetUrl = $response->headers->get('Location');
    expect($targetUrl)->not->toContain('evil-phishing-site.test');
    expect($targetUrl)->toContain('/app');
});

test('super admin logging in redirects directly to superadmin dashboard', function (): void {
    $superadmin = User::factory()->create(['shop_id' => null, 'password' => bcrypt('password')]);
    $superadmin->assignRole('Super Administrator');

    $response = $this->post('/login', [
        'email' => $superadmin->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('superadmin.dashboard'));
});

test('shop owner logging in redirects directly to phone shop management panel', function (): void {
    $shop = Shop::factory()->create();
    $owner = User::factory()->create(['shop_id' => $shop->id, 'password' => bcrypt('password')]);
    $owner->assignRole('Shop Owner');

    $response = $this->post('/login', [
        'email' => $owner->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('shop.dashboard'));
});

test('customer logging in redirects directly to customer care portal', function (): void {
    $shop = Shop::factory()->create();
    $customer = User::factory()->create(['shop_id' => $shop->id, 'password' => bcrypt('password')]);
    $customer->assignRole('Customer');

    $response = $this->post('/login', [
        'email' => $customer->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('customer.portal'));
});

test('cashier logging in redirects directly to pos counter', function (): void {
    $shop = Shop::factory()->create();
    $cashier = User::factory()->create(['shop_id' => $shop->id, 'password' => bcrypt('password')]);
    $cashier->assignRole('Cashier');

    $response = $this->post('/login', [
        'email' => $cashier->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('shop.pos'));
});

test('technician logging in redirects directly to repairs workbench', function (): void {
    $shop = Shop::factory()->create();
    $tech = User::factory()->create(['shop_id' => $shop->id, 'password' => bcrypt('password')]);
    $tech->assignRole('Technician');

    $response = $this->post('/login', [
        'email' => $tech->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('shop.repairs'));
});

test('staff login convenience route loads login view with staff initial mode', function (): void {
    $response = $this->get('/staff/login');

    $response->assertOk();
    $response->assertViewHas('initialMode', 'staff');
    $response->assertSee('Login');
});

test('get request to logout terminates session and redirects to login with success flash', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user, 'web');

    $response = $this->get('/logout');

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('status', 'You have been successfully signed out.');
    $this->assertGuest('web');
});

