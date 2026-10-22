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
    app()->forgetInstance('tenant.id');
    app()->forgetInstance('tenant');

    // Querying through BelongsToTenant / BelongsToShop global scope outside shop context must return 0 results
    expect(Employee::count())->toBe(0);
    expect(Attendance::count())->toBe(0);
    expect(Leave::count())->toBe(0);
    expect(Payroll::count())->toBe(0);
    expect(Setting::count())->toBe(0);
});

test('tenant resolution strictly ignores query parameter tampering', function (): void {
    $shopA = Shop::factory()->create(['name' => 'Shop A']);
    $ownerA = User::factory()->create(['shop_id' => $shopA->id]);
    $ownerA->assignRole('Administrator');
    $workerA = Employee::factory()->create(['shop_id' => $shopA->id, 'name' => 'Worker Alpha']);

    $shopB = Shop::factory()->create(['name' => 'Shop B']);
    $workerB = Employee::factory()->create(['shop_id' => $shopB->id, 'name' => 'Worker Beta']);

    Sanctum::actingAs($ownerA);

    // Attacker attempts to force Tenant B context via query string parameters
    $response = $this->getJson("/api/v1/employees?tenant_id={$shopB->id}&shop_id={$shopB->id}");

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.name', 'Worker Alpha');
    $response->assertJsonMissing(['name' => 'Worker Beta']);
});

test('tenant resolution strictly ignores header tampering', function (): void {
    $shopA = Shop::factory()->create(['name' => 'Shop A']);
    $ownerA = User::factory()->create(['shop_id' => $shopA->id]);
    $ownerA->assignRole('Administrator');
    $workerA = Employee::factory()->create(['shop_id' => $shopA->id, 'name' => 'Worker Alpha']);

    $shopB = Shop::factory()->create(['name' => 'Shop B']);
    $workerB = Employee::factory()->create(['shop_id' => $shopB->id, 'name' => 'Worker Beta']);

    Sanctum::actingAs($ownerA);

    // Attacker attempts to force Tenant B context via custom headers
    $response = $this->withHeaders([
        'X-Tenant-ID' => (string) $shopB->id,
        'X-Shop-ID' => (string) $shopB->id,
        'Tenant' => (string) $shopB->id,
    ])->getJson('/api/v1/employees');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.name', 'Worker Alpha');
    $response->assertJsonMissing(['name' => 'Worker Beta']);
});

test('tenant middleware binds tenant instance and id globally in container and request', function (): void {
    $shop = Shop::factory()->create(['name' => 'Apex Phones']);
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Administrator');

    $middleware = app(\App\Http\Middleware\ResolveTenantContext::class);

    $request = \Illuminate\Http\Request::create('/api/v1/employees', 'GET');
    $request->setUserResolver(fn () => $owner);

    $executed = false;

    $response = $middleware->handle($request, function ($req) use ($shop, &$executed) {
        $executed = true;

        // Verify globally bound container instances
        expect(app('tenant')->id)->toBe($shop->id);
        expect(app('tenant.id'))->toBe($shop->id);
        expect(app('shop.id'))->toBe($shop->id);
        expect(app(Shop::class)->id)->toBe($shop->id);

        // Verify request attributes
        expect($req->attributes->get('tenant')->id)->toBe($shop->id);
        expect($req->attributes->get('tenant_id'))->toBe($shop->id);

        return response()->json(['status' => 'ok']);
    });

    expect($executed)->toBeTrue();
    expect($response->getStatusCode())->toBe(200);

    // Verify context was cleared after request execution
    expect(app()->bound('tenant'))->toBeFalse();
    expect(app()->bound('tenant.id'))->toBeFalse();
    expect(app()->bound('shop.id'))->toBeFalse();
});

test('user with non-existent tenant workspace is rejected with 403', function (): void {
    $orphanUser = User::factory()->create(['shop_id' => 999999]);
    $orphanUser->assignRole('Administrator');

    Sanctum::actingAs($orphanUser);

    $response = $this->getJson('/api/v1/employees');

    $response->assertForbidden();
    $response->assertJsonPath('message', 'Assigned tenant workspace does not exist or is inaccessible.');
});

test('TenantContext service can execute closures safely in another tenant context', function (): void {
    $shopA = Shop::factory()->create(['name' => 'Shop A']);
    $workerA = Employee::factory()->create(['shop_id' => $shopA->id, 'name' => 'Alpha Worker']);

    $shopB = Shop::factory()->create(['name' => 'Shop B']);
    $workerB = Employee::factory()->create(['shop_id' => $shopB->id, 'name' => 'Beta Worker']);

    $context = app(\App\Services\Tenancy\TenantContext::class);

    // Initial context: Shop A
    $context->set($shopA);
    expect(Employee::count())->toBe(1);
    expect(Employee::first()->name)->toBe('Alpha Worker');

    // Run closure for Shop B
    $result = $context->runForTenant($shopB, function (Shop $tenant) {
        expect($tenant->name)->toBe('Shop B');
        expect(Employee::count())->toBe(1);
        expect(Employee::first()->name)->toBe('Beta Worker');
        return 'executed_b';
    });

    expect($result)->toBe('executed_b');

    // Previous context (Shop A) must be restored
    expect($context->id())->toBe($shopA->id);
    expect(Employee::count())->toBe(1);
    expect(Employee::first()->name)->toBe('Alpha Worker');

    $context->clear();
    expect($context->check())->toBeFalse();
});

test('tenant cannot view another tenants attendance record by id', function (): void {
    $shopA = Shop::factory()->create();
    $ownerA = User::factory()->create(['shop_id' => $shopA->id]);
    $ownerA->assignRole('Administrator');

    $shopB = Shop::factory()->create();
    $workerB = Employee::factory()->create(['shop_id' => $shopB->id]);
    $attendanceB = Attendance::factory()->create([
        'shop_id' => $shopB->id,
        'employee_id' => $workerB->id,
    ]);

    Sanctum::actingAs($ownerA);
    $response = $this->getJson("/api/v1/attendance/{$attendanceB->id}");

    $response->assertNotFound();
});

test('tenant cannot view another tenants leave record by id', function (): void {
    $shopA = Shop::factory()->create();
    $ownerA = User::factory()->create(['shop_id' => $shopA->id]);
    $ownerA->assignRole('Administrator');

    $shopB = Shop::factory()->create();
    $workerB = Employee::factory()->create(['shop_id' => $shopB->id]);
    $leaveB = Leave::factory()->create([
        'shop_id' => $shopB->id,
        'employee_id' => $workerB->id,
    ]);

    Sanctum::actingAs($ownerA);
    $response = $this->getJson("/api/v1/leave/{$leaveB->id}");

    $response->assertNotFound();
});

test('tenant cannot view another tenants payroll record by id', function (): void {
    $shopA = Shop::factory()->create();
    $ownerA = User::factory()->create(['shop_id' => $shopA->id]);
    $ownerA->assignRole('Administrator');

    $shopB = Shop::factory()->create();
    $workerB = Employee::factory()->create(['shop_id' => $shopB->id]);
    $payrollB = Payroll::factory()->create([
        'shop_id' => $shopB->id,
        'employee_id' => $workerB->id,
    ]);

    Sanctum::actingAs($ownerA);
    $response = $this->getJson("/api/v1/payroll/{$payrollB->id}");

    $response->assertNotFound();
});

test('dashboard metrics strictly isolate tenant aggregations', function (): void {
    $shopA = Shop::factory()->create();
    $ownerA = User::factory()->create(['shop_id' => $shopA->id]);
    $ownerA->assignRole('Administrator');
    Employee::factory()->count(2)->create(['shop_id' => $shopA->id]);

    $shopB = Shop::factory()->create();
    Employee::factory()->count(5)->create(['shop_id' => $shopB->id]);

    Sanctum::actingAs($ownerA);
    $response = $this->getJson('/api/v1/dashboard');

    $response->assertOk();
    $response->assertJsonPath('employees', 2);
});

