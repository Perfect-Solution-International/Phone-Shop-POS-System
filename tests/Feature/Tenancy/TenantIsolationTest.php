<?php

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\Payroll;
use App\Models\Platform\Shop;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->seed(DatabaseSeeder::class);
});

test('tenant cannot view another tenants employee by manipulating route id', function (): void {
    // Tenant A
    $shopA = Shop::factory()->create(['name' => 'Shop A']);
    $ownerA = User::factory()->create(['shop_id' => $shopA->id]);
    $ownerA->assignRole('Administrator');

    // Tenant B with employee
    $shopB = Shop::factory()->create(['name' => 'Shop B']);
    $employeeB = Employee::factory()->create(['shop_id' => $shopB->id, 'name' => 'Secret Worker B']);

    // Tenant A attempts to read Tenant B's employee directly by ID
    Sanctum::actingAs($ownerA);
    $response = $this->getJson("/api/v1/employees/{$employeeB->id}");

    $response->assertNotFound();
});

test('tenant cannot update another tenants employee by manipulating route id or payload', function (): void {
    $shopA = Shop::factory()->create(['name' => 'Shop A']);
    $ownerA = User::factory()->create(['shop_id' => $shopA->id]);
    $ownerA->assignRole('Administrator');

    $shopB = Shop::factory()->create(['name' => 'Shop B']);
    $employeeB = Employee::factory()->create([
        'shop_id' => $shopB->id,
        'name' => 'Original Name B',
        'email' => 'workerb@shopb.test',
    ]);

    Sanctum::actingAs($ownerA);
    $response = $this->putJson("/api/v1/employees/{$employeeB->id}", [
        'name' => 'Hacked Name',
        'email' => 'hacked@shopb.test',
        'shop_id' => $shopA->id,
    ]);

    $response->assertNotFound();
    expect($employeeB->fresh()->name)->toBe('Original Name B');
    expect($employeeB->fresh()->shop_id)->toBe($shopB->id);
});

test('tenant cannot delete another tenants employee by manipulating route id', function (): void {
    $shopA = Shop::factory()->create(['name' => 'Shop A']);
    $ownerA = User::factory()->create(['shop_id' => $shopA->id]);
    $ownerA->assignRole('Administrator');

    $shopB = Shop::factory()->create(['name' => 'Shop B']);
    $employeeB = Employee::factory()->create(['shop_id' => $shopB->id, 'name' => 'Worker B']);

    Sanctum::actingAs($ownerA);
    $response = $this->deleteJson("/api/v1/employees/{$employeeB->id}");

    $response->assertNotFound();
    expect($employeeB->fresh()->deleted_at)->toBeNull();
});

test('tenant creating an employee is prohibited from supplying client-side shop_id', function (): void {
    $shopA = Shop::factory()->create(['name' => 'Shop A']);
    $ownerA = User::factory()->create(['shop_id' => $shopA->id]);
    $ownerA->assignRole('Administrator');

    $shopB = Shop::factory()->create(['name' => 'Shop B']);

    Sanctum::actingAs($ownerA);
    $response = $this->postJson('/api/v1/employees', [
        'name' => 'New Worker',
        'email' => 'newworker@shopa.test',
        'employee_code' => 'EMP-001',
        'joined_on' => '2026-01-01',
        'is_active' => true,
        'shop_id' => $shopB->id, // Malicious injection attempt
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('shop_id');
});

test('tenant cannot view another tenants shop settings', function (): void {
    $shopA = Shop::factory()->create(['name' => 'Shop A']);
    $ownerA = User::factory()->create(['shop_id' => $shopA->id]);
    $ownerA->assignRole('Administrator');

    // Default setting for Shop A
    app()->instance('shop.id', $shopA->id);
    $settingA = Setting::create(['shop_id' => $shopA->id, 'currency' => 'LKR', 'timezone' => 'Asia/Colombo']);
    app()->forgetInstance('shop.id');

    $shopB = Shop::factory()->create(['name' => 'Shop B']);
    app()->instance('shop.id', $shopB->id);
    $settingB = Setting::create(['shop_id' => $shopB->id, 'currency' => 'USD', 'timezone' => 'America/New_York']);
    app()->forgetInstance('shop.id');

    Sanctum::actingAs($ownerA);
    $response = $this->getJson('/api/v1/settings');

    $response->assertOk();
    $response->assertJsonPath('data.currency', 'LKR');
    $response->assertJsonPath('data.timezone', 'Asia/Colombo');
});

test('unassigned user without shop_id is forbidden from shop resources', function (): void {
    $userWithoutShop = User::factory()->create(['shop_id' => null]);
    $userWithoutShop->assignRole('Administrator');

    Sanctum::actingAs($userWithoutShop);
    $response = $this->getJson('/api/v1/employees');

    $response->assertForbidden();
});

test('tenant models fail closed with zero records outside shop context', function (): void {
    $shop = Shop::factory()->create();
    $employee = Employee::factory()->create(['shop_id' => $shop->id]);

    // Ensure no shop context is bound in the container
    app()->forgetInstance('shop.id');

    // Querying through BelongsToShop global scope outside shop context must return 0 results
    expect(Employee::count())->toBe(0);
    expect(Attendance::count())->toBe(0);
    expect(Leave::count())->toBe(0);
    expect(Payroll::count())->toBe(0);
    expect(Setting::count())->toBe(0);
});
