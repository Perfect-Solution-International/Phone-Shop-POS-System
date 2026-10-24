<?php

use App\Models\Platform\Shop;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function (): void {
    $this->seed(DatabaseSeeder::class);
});

test('super admin can directly open customer portal in browser', function (): void {
    $shop = Shop::factory()->create();
    $superadmin = User::factory()->create(['shop_id' => null]);
    $superadmin->assignRole('Super Administrator');
    $this->actingAs($superadmin, 'web');

    $response = $this->get('/portal');
    $response->assertOk();
});

test('shop owner can directly open customer portal in browser', function (): void {
    $shop = Shop::factory()->create();
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');
    $this->actingAs($owner, 'web');

    $response = $this->get('/portal');
    $response->assertOk();
});

test('admin logged in can open login page directly in another tab without being redirected', function (): void {
    $superadmin = User::factory()->create(['shop_id' => null]);
    $superadmin->assignRole('Super Administrator');
    $this->actingAs($superadmin, 'web');

    $response = $this->get('/login');
    $response->assertOk();
    $response->assertSee('Signed in as:');
    $response->assertSee($superadmin->email);
});

test('admin logged in can open portal page directly in another tab without being redirected to admin dashboard', function (): void {
    $shop = Shop::factory()->create();
    $superadmin = User::factory()->create(['shop_id' => null]);
    $superadmin->assignRole('Super Administrator');
    $this->actingAs($superadmin, 'web');

    $response = $this->get('/portal');
    $response->assertOk();
    $response->assertSee('Customer Care Portal');
});

test('super admin can access phone shop management portal with platform access', function (): void {
    $shop = Shop::factory()->create();
    $superadmin = User::factory()->create(['shop_id' => null]);
    $superadmin->assignRole('Super Administrator');
    $this->actingAs($superadmin, 'web');

    $response = $this->get('/shop');
    $response->assertOk();
});

test('registered shop owner can access phone shop management portal', function (): void {
    $shop = Shop::factory()->create();
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');
    $this->actingAs($owner, 'web');

    $response = $this->get('/shop');
    $response->assertOk();
    $response->assertSee('Overview');
});
