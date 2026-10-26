<?php

use App\Models\Platform\Shop;
use App\Models\Platform\SystemActivity;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function (): void {
    $this->seed(DatabaseSeeder::class);
});

test('super admin can view shop owners directory', function (): void {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('Super Administrator');

    $shop = Shop::factory()->create(['name' => 'Metro Cellular']);
    $owner = User::factory()->create(['shop_id' => $shop->id, 'name' => 'Kasun Silva']);
    $owner->assignRole('Shop Owner');

    $response = $this->actingAs($superAdmin, 'web')
        ->get(route('superadmin.owners.index'));

    $response->assertOk()
        ->assertViewIs('super-admin.owners.index')
        ->assertSee('Shop Owners Directory')
        ->assertSee('Kasun Silva')
        ->assertSee('Metro Cellular');
});

test('super admin can add a new shop owner assigned to an existing shop', function (): void {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('Super Administrator');

    $shop = Shop::factory()->create(['name' => 'Lanka Smartphones']);

    $response = $this->actingAs($superAdmin, 'web')
        ->post(route('superadmin.owners.store'), [
            'name' => 'Nuwan Perera',
            'email' => 'nuwan@lankasmart.test',
            'phone' => '0771234567',
            'password' => 'Secret1234!',
            'password_confirmation' => 'Secret1234!',
            'shop_assignment_type' => 'existing',
            'shop_id' => $shop->id,
            'is_verified' => '1',
        ]);

    $response->assertRedirect(route('superadmin.owners.index'));
    $response->assertSessionHas('status');

    $newUser = User::where('email', 'nuwan@lankasmart.test')->first();
    expect($newUser)->not->toBeNull();
    expect($newUser->name)->toBe('Nuwan Perera');
    expect($newUser->phone)->toBe('0771234567');
    expect($newUser->shop_id)->toBe($shop->id);
    expect($newUser->hasRole('Shop Owner'))->toBeTrue();
    expect($newUser->email_verified_at)->not->toBeNull();
    expect(Hash::check('Secret1234!', $newUser->password))->toBeTrue();

    // Verify activity logged
    expect(SystemActivity::where('action', 'owner.created')->exists())->toBeTrue();
});

test('super admin can add a new shop owner and auto-provision a new shop workspace', function (): void {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('Super Administrator');

    $response = $this->actingAs($superAdmin, 'web')
        ->post(route('superadmin.owners.store'), [
            'name' => 'Saman Kumara',
            'email' => 'saman@apexcare.test',
            'phone' => '0812233445',
            'password' => 'Secret999!',
            'password_confirmation' => 'Secret999!',
            'shop_assignment_type' => 'new',
            'new_shop_name' => 'Apex Care Kandy',
            'business_address' => 'Dalada Veediya, Kandy',
            'is_verified' => '1',
        ]);

    $response->assertRedirect(route('superadmin.owners.index'));

    $newShop = Shop::where('name', 'Apex Care Kandy')->first();
    expect($newShop)->not->toBeNull();
    expect($newShop->address)->toBe('Dalada Veediya, Kandy');

    $newOwner = User::where('email', 'saman@apexcare.test')->first();
    expect($newOwner)->not->toBeNull();
    expect($newOwner->shop_id)->toBe($newShop->id);
    expect($newOwner->hasRole('Shop Owner'))->toBeTrue();
});

test('super admin cannot create owner with duplicate email', function (): void {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('Super Administrator');

    $shop = Shop::factory()->create();
    User::factory()->create(['email' => 'existing@owner.test', 'shop_id' => $shop->id]);

    $response = $this->actingAs($superAdmin, 'web')
        ->from(route('superadmin.owners.index'))
        ->post(route('superadmin.owners.store'), [
            'name' => 'Duplicate Person',
            'email' => 'existing@owner.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'shop_assignment_type' => 'existing',
            'shop_id' => $shop->id,
        ]);

    $response->assertRedirect(route('superadmin.owners.index'));
    $response->assertSessionHasErrors('email');
});

test('super admin can update shop owner details and change assigned shop', function (): void {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('Super Administrator');

    $shopA = Shop::factory()->create(['name' => 'Shop A']);
    $shopB = Shop::factory()->create(['name' => 'Shop B']);

    $owner = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@owner.test',
        'phone' => '0112223334',
        'shop_id' => $shopA->id,
    ]);
    $owner->assignRole('Shop Owner');

    $response = $this->actingAs($superAdmin, 'web')
        ->put(route('superadmin.owners.update', $owner), [
            'name' => 'Updated Name',
            'email' => 'updated@owner.test',
            'phone' => '0779998888',
            'shop_id' => $shopB->id,
            'is_verified' => '1',
        ]);

    $response->assertRedirect(route('superadmin.owners.index'));
    $response->assertSessionHas('status');

    $owner->refresh();
    expect($owner->name)->toBe('Updated Name');
    expect($owner->email)->toBe('updated@owner.test');
    expect($owner->phone)->toBe('0779998888');
    expect($owner->shop_id)->toBe($shopB->id);
    expect($owner->email_verified_at)->not->toBeNull();
});

test('super admin can update shop owner password', function (): void {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('Super Administrator');

    $shop = Shop::factory()->create();
    $owner = User::factory()->create([
        'password' => Hash::make('OldPassword123!'),
        'shop_id' => $shop->id,
    ]);
    $owner->assignRole('Shop Owner');

    $response = $this->actingAs($superAdmin, 'web')
        ->put(route('superadmin.owners.update', $owner), [
            'name' => $owner->name,
            'email' => $owner->email,
            'shop_id' => $shop->id,
            'password' => 'NewStrongPass999!',
            'password_confirmation' => 'NewStrongPass999!',
        ]);

    $response->assertRedirect(route('superadmin.owners.index'));

    $owner->refresh();
    expect(Hash::check('NewStrongPass999!', $owner->password))->toBeTrue();
});

test('super admin can delete a shop owner', function (): void {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('Super Administrator');

    $shop = Shop::factory()->create();
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    $response = $this->actingAs($superAdmin, 'web')
        ->delete(route('superadmin.owners.destroy', $owner));

    $response->assertRedirect(route('superadmin.owners.index'));
    $response->assertSessionHas('status');

    expect(User::find($owner->id))->toBeNull();
    expect(SystemActivity::where('action', 'owner.deleted')->exists())->toBeTrue();
});

test('super admin cannot delete own super admin account', function (): void {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('Super Administrator');

    $response = $this->actingAs($superAdmin, 'web')
        ->delete(route('superadmin.owners.destroy', $superAdmin));

    $response->assertRedirect();
    $response->assertSessionHas('error');

    expect(User::find($superAdmin->id))->not->toBeNull();
});

test('super admin can access owner shop directly', function (): void {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('Super Administrator');

    $shop = Shop::factory()->create(['name' => 'Target Shop']);
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    $response = $this->actingAs($superAdmin, 'web')
        ->post(route('superadmin.owners.access', $owner));

    $response->assertRedirect(route('shop.dashboard'));
    $response->assertSessionHas('superadmin_active_shop_id', $shop->id);
});

test('non-superadmin users are denied from owner management endpoints', function (): void {
    $shop = Shop::factory()->create();
    $regularUser = User::factory()->create(['shop_id' => $shop->id]);
    $regularUser->assignRole('Shop Owner');

    // GET index forbidden
    $this->actingAs($regularUser, 'web')
        ->get(route('superadmin.owners.index'))
        ->assertForbidden();

    // POST store forbidden
    $this->actingAs($regularUser, 'web')
        ->post(route('superadmin.owners.store'), [
            'name' => 'Unauthorized User',
            'email' => 'unauth@test.com',
            'password' => 'Pass1234!',
            'password_confirmation' => 'Pass1234!',
            'shop_assignment_type' => 'existing',
            'shop_id' => $shop->id,
        ])
        ->assertForbidden();
});

