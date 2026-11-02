<?php

use App\Models\Platform\Shop;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function (): void {
    $this->seed(DatabaseSeeder::class);
});

test('super administrator can access shop dashboard and all management pages', function (): void {
    $shop = Shop::factory()->create(['name' => 'Metro Super Store']);

    $superAdmin = User::factory()->create([
        'email' => 'superadmin@pos.test',
        'email_verified_at' => now(),
        'shop_id' => $shop->id,
    ]);
    $superAdmin->assignRole('Super Administrator');

    // Super Admin can access Shop Dashboard
    $this->actingAs($superAdmin, 'web')
        ->get(route('shop.dashboard'))
        ->assertOk()
        ->assertViewIs('shop.dashboard.index')
        ->assertSee('Super Admin Mode', false);

    // Super Admin can access POS Counter
    $this->actingAs($superAdmin, 'web')
        ->get(route('shop.pos'))
        ->assertOk()
        ->assertViewIs('shop.pos.index');

    // Super Admin can access Dual IMEI Phones
    $this->actingAs($superAdmin, 'web')
        ->get(route('shop.phones'))
        ->assertOk()
        ->assertViewIs('shop.phones.index');

    // Super Admin can access Products & Accessories
    $this->actingAs($superAdmin, 'web')
        ->get(route('shop.products'))
        ->assertOk()
        ->assertViewIs('shop.products.index');

    // Super Admin can access Spare Parts Stock
    $this->actingAs($superAdmin, 'web')
        ->get(route('shop.spare-parts'))
        ->assertOk()
        ->assertViewIs('shop.spare-parts.index');

    // Super Admin can access Repairs
    $this->actingAs($superAdmin, 'web')
        ->get(route('shop.repairs'))
        ->assertOk()
        ->assertViewIs('shop.repairs.index');

    // Super Admin can access Warranties
    $this->actingAs($superAdmin, 'web')
        ->get(route('shop.warranties'))
        ->assertOk()
        ->assertViewIs('shop.warranties.index');

    // Super Admin can access Customers
    $this->actingAs($superAdmin, 'web')
        ->get(route('shop.customers'))
        ->assertOk()
        ->assertViewIs('shop.customers.index');

    // Super Admin can access Outlets & Branches
    $this->actingAs($superAdmin, 'web')
        ->get(route('shop.outlets.index'))
        ->assertOk()
        ->assertViewIs('shop.outlets.index');
});

test('super administrator can switch between different shop workspaces', function (): void {
    $shopA = Shop::factory()->create(['name' => 'Store Alpha']);
    $shopB = Shop::factory()->create(['name' => 'Store Bravo']);

    $superAdmin = User::factory()->create([
        'email' => 'admin@platform.test',
        'email_verified_at' => now(),
        'shop_id' => $shopA->id,
    ]);
    $superAdmin->assignRole('Super Administrator');

    // Switch to Store Bravo
    $response = $this->actingAs($superAdmin, 'web')
        ->post(route('superadmin.shops.switch', $shopB));

    $response->assertRedirect(route('shop.dashboard'));
    $response->assertSessionHas('superadmin_active_shop_id', $shopB->id);

    // Visiting shop dashboard now renders Store Bravo
    $dashboardResponse = $this->actingAs($superAdmin, 'web')
        ->get(route('shop.dashboard'));

    $dashboardResponse->assertOk()
        ->assertSee('Store Bravo');
});

test('super administrator passes gate permission checks everywhere', function (): void {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('Super Administrator');

    expect($superAdmin->can('hrm.view'))->toBeTrue();
    expect($superAdmin->can('pos.sell'))->toBeTrue();
    expect($superAdmin->can('products.manage'))->toBeTrue();
    expect($superAdmin->can('warranties.claim'))->toBeTrue();
    expect($superAdmin->can('repairs.manage'))->toBeTrue();
});

