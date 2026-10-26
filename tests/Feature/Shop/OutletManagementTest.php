<?php

use App\Models\Outlet;
use App\Models\Platform\Shop;
use App\Models\Platform\Subscription;
use App\Models\Platform\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function (): void {
    $this->seed(DatabaseSeeder::class);
});

test('shop owner visiting outlets page auto-provisions main branch if none exists', function (): void {
    $shop = Shop::factory()->create(['name' => 'Metro Cellular', 'address' => '100 Galle Rd']);
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    expect(Outlet::withoutGlobalScopes()->where('shop_id', $shop->id)->count())->toBe(0);

    $response = $this->actingAs($owner, 'web')
        ->get(route('shop.outlets.index'));

    $response->assertOk();
    $response->assertViewIs('shop.outlets.index');
    $response->assertViewHas('outlets');
    $response->assertSee('Registered Outlets &amp; Showrooms', false);

    $outlets = Outlet::withoutGlobalScopes()->where('shop_id', $shop->id)->get();
    expect($outlets)->toHaveCount(1);
    expect($outlets->first()->is_main)->toBeTrue();
    expect($outlets->first()->code)->toBe('OUT-01');
});

test('shop owner can add a new outlet branch', function (): void {
    $shop = Shop::factory()->create(['name' => 'Metro Cellular']);
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    // Auto-create initial main branch
    Outlet::create([
        'shop_id' => $shop->id,
        'name' => 'Main Outlet',
        'code' => 'OUT-01',
        'address' => 'Colombo 03',
        'is_main' => true,
        'is_active' => true,
    ]);

    $response = $this->actingAs($owner, 'web')
        ->post(route('shop.outlets.store'), [
            'name' => 'Kandy City Showroom',
            'code' => 'KND-01',
            'phone' => '0812233445',
            'email' => 'kandy@metro.com',
            'address' => 'Level 2, KCC, Dalada Veediya',
            'city' => 'Kandy',
            'manager_name' => 'Nuwan Perera',
            'is_active' => '1',
        ]);

    $response->assertRedirect(route('shop.outlets.index'));
    $response->assertSessionHas('success');

    $outlet = Outlet::withoutGlobalScopes()
        ->where('shop_id', $shop->id)
        ->where('code', 'KND-01')
        ->first();

    expect($outlet)->not->toBeNull();
    expect($outlet->name)->toBe('Kandy City Showroom');
    expect($outlet->city)->toBe('Kandy');
    expect($outlet->manager_name)->toBe('Nuwan Perera');
    expect($outlet->is_main)->toBeFalse();
    expect($outlet->is_active)->toBeTrue();
});

test('outlet code auto-generates if omitted', function (): void {
    $shop = Shop::factory()->create(['name' => 'Metro Cellular']);
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    Outlet::create([
        'shop_id' => $shop->id,
        'name' => 'Main Outlet',
        'code' => 'OUT-01',
        'address' => 'Colombo 03',
        'is_main' => true,
        'is_active' => true,
    ]);

    $response = $this->actingAs($owner, 'web')
        ->post(route('shop.outlets.store'), [
            'name' => 'Galle Fort Outlet',
            'code' => '',
            'address' => '45 Church Street',
            'city' => 'Galle',
        ]);

    $response->assertRedirect(route('shop.outlets.index'));

    $outlet = Outlet::withoutGlobalScopes()
        ->where('shop_id', $shop->id)
        ->where('name', 'Galle Fort Outlet')
        ->first();

    expect($outlet)->not->toBeNull();
    expect($outlet->code)->toBe('OUT-02');
});

test('duplicate outlet code within same shop is rejected', function (): void {
    $shop = Shop::factory()->create(['name' => 'Metro Cellular']);
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    Outlet::create([
        'shop_id' => $shop->id,
        'name' => 'Main Outlet',
        'code' => 'OUT-01',
        'address' => 'Colombo 03',
        'is_main' => true,
        'is_active' => true,
    ]);

    $response = $this->actingAs($owner, 'web')
        ->from(route('shop.outlets.index'))
        ->post(route('shop.outlets.store'), [
            'name' => 'Duplicate Code Branch',
            'code' => 'OUT-01',
            'address' => 'Kandy Rd',
        ]);

    $response->assertRedirect(route('shop.outlets.index'));
    $response->assertSessionHas('error');

    expect(Outlet::withoutGlobalScopes()->where('shop_id', $shop->id)->count())->toBe(1);
});

test('shop cannot exceed subscription plan branch limit', function (): void {
    $shop = Shop::factory()->create(['name' => 'Single Branch Shop']);
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    // Create a 1-branch plan
    $plan = SubscriptionPlan::create([
        'name' => 'Starter Single Branch',
        'slug' => 'starter-single',
        'monthly_price' => 2500,
        'annual_price' => 25000,
        'max_branches' => 1,
        'has_multi_branch' => false,
        'is_active' => true,
    ]);

    Subscription::create([
        'shop_id' => $shop->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'billing_cycle' => 'monthly',
        'recurring_amount' => 2500,
        'current_period_starts_at' => now()->subDay(),
        'current_period_ends_at' => now()->addMonth(),
        'trial_ends_at' => null,
    ]);

    // Create existing 1 outlet
    Outlet::create([
        'shop_id' => $shop->id,
        'name' => 'Existing Branch',
        'code' => 'OUT-01',
        'address' => 'Main St',
        'is_main' => true,
        'is_active' => true,
    ]);

    // Attempt to add a 2nd outlet
    $response = $this->actingAs($owner, 'web')
        ->from(route('shop.outlets.index'))
        ->post(route('shop.outlets.store'), [
            'name' => 'Second Branch',
            'code' => 'OUT-02',
            'address' => 'Second St',
        ]);

    $response->assertRedirect(route('shop.outlets.index'));
    $response->assertSessionHas('error');

    expect(Outlet::withoutGlobalScopes()->where('shop_id', $shop->id)->count())->toBe(1);
});

test('shop owner can update outlet details', function (): void {
    $shop = Shop::factory()->create(['name' => 'Metro Cellular']);
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    $outlet = Outlet::create([
        'shop_id' => $shop->id,
        'name' => 'Old Branch Name',
        'code' => 'OUT-02',
        'address' => 'Old Address',
        'phone' => '0112223334',
        'is_main' => false,
        'is_active' => true,
    ]);

    $response = $this->actingAs($owner, 'web')
        ->put(route('shop.outlets.update', $outlet), [
            'name' => 'New Branch Name',
            'code' => 'OUT-02',
            'address' => 'Updated Address 99',
            'phone' => '0771234567',
            'city' => 'Negombo',
            'manager_name' => 'Kasun Silva',
            'is_active' => '1',
        ]);

    $response->assertRedirect(route('shop.outlets.index'));
    $response->assertSessionHas('success');

    $outlet->refresh();
    expect($outlet->name)->toBe('New Branch Name');
    expect($outlet->address)->toBe('Updated Address 99');
    expect($outlet->city)->toBe('Negombo');
    expect($outlet->manager_name)->toBe('Kasun Silva');
});

test('shop owner can switch active outlet in session', function (): void {
    $shop = Shop::factory()->create(['name' => 'Metro Cellular']);
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    $outlet = Outlet::create([
        'shop_id' => $shop->id,
        'name' => 'Kurunegala Branch',
        'code' => 'KRN-01',
        'address' => 'Circular Rd',
        'is_main' => false,
        'is_active' => true,
    ]);

    $response = $this->actingAs($owner, 'web')
        ->post(route('shop.outlets.switch', $outlet));

    $response->assertRedirect();
    $response->assertSessionHas('active_outlet_id', $outlet->id);
    $response->assertSessionHas('active_outlet_name', 'Kurunegala Branch');
    $response->assertSessionHas('active_outlet_code', 'KRN-01');
});

test('primary main branch cannot be deleted', function (): void {
    $shop = Shop::factory()->create(['name' => 'Metro Cellular']);
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    $mainOutlet = Outlet::create([
        'shop_id' => $shop->id,
        'name' => 'Primary Main Branch',
        'code' => 'OUT-01',
        'address' => 'Colombo 03',
        'is_main' => true,
        'is_active' => true,
    ]);

    $response = $this->actingAs($owner, 'web')
        ->from(route('shop.outlets.index'))
        ->delete(route('shop.outlets.destroy', $mainOutlet));

    $response->assertRedirect(route('shop.outlets.index'));
    $response->assertSessionHas('error');

    expect(Outlet::withoutGlobalScopes()->where('id', $mainOutlet->id)->exists())->toBeTrue();
});

test('non-main outlet can be deleted', function (): void {
    $shop = Shop::factory()->create(['name' => 'Metro Cellular']);
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    $secondaryOutlet = Outlet::create([
        'shop_id' => $shop->id,
        'name' => 'Secondary Branch',
        'code' => 'OUT-02',
        'address' => 'Moratuwa',
        'is_main' => false,
        'is_active' => true,
    ]);

    $response = $this->actingAs($owner, 'web')
        ->delete(route('shop.outlets.destroy', $secondaryOutlet));

    $response->assertRedirect(route('shop.outlets.index'));
    $response->assertSessionHas('success');

    expect(Outlet::withoutGlobalScopes()->where('id', $secondaryOutlet->id)->exists())->toBeFalse();
});

test('shop owner can set another outlet as main branch', function (): void {
    $shop = Shop::factory()->create(['name' => 'Metro Cellular']);
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    $oldMain = Outlet::create([
        'shop_id' => $shop->id,
        'name' => 'Old Main',
        'code' => 'OUT-01',
        'address' => 'Colombo',
        'is_main' => true,
        'is_active' => true,
    ]);

    $newMain = Outlet::create([
        'shop_id' => $shop->id,
        'name' => 'New Main',
        'code' => 'OUT-02',
        'address' => 'Kandy',
        'is_main' => false,
        'is_active' => true,
    ]);

    $response = $this->actingAs($owner, 'web')
        ->post(route('shop.outlets.toggle-main', $newMain));

    $response->assertRedirect(route('shop.outlets.index'));

    $oldMain->refresh();
    $newMain->refresh();

    expect($oldMain->is_main)->toBeFalse();
    expect($newMain->is_main)->toBeTrue();
});

test('cross-tenant isolation prevents Shop A from modifying or deleting Shop B outlet', function (): void {
    $shopA = Shop::factory()->create(['name' => 'Shop A']);
    $ownerA = User::factory()->create(['shop_id' => $shopA->id]);
    $ownerA->assignRole('Shop Owner');

    $shopB = Shop::factory()->create(['name' => 'Shop B']);
    $outletB = Outlet::create([
        'shop_id' => $shopB->id,
        'name' => 'Shop B Outlet',
        'code' => 'B-01',
        'address' => 'Galle Rd',
        'is_main' => false,
        'is_active' => true,
    ]);

    // Shop A owner tries to update Shop B's outlet
    $responseUpdate = $this->actingAs($ownerA, 'web')
        ->put(route('shop.outlets.update', $outletB), [
            'name' => 'Hacked Name',
            'code' => 'B-01',
            'address' => 'Hacked Address',
        ]);

    $responseUpdate->assertNotFound();

    // Shop A owner tries to delete Shop B's outlet
    $responseDelete = $this->actingAs($ownerA, 'web')
        ->delete(route('shop.outlets.destroy', $outletB));

    $responseDelete->assertNotFound();

    // Outlet B still untouched
    $outletB->refresh();
    expect($outletB->name)->toBe('Shop B Outlet');
});

