<?php

use App\Models\Customer;
use App\Models\Platform\Shop;
use App\Models\RepairJob;
use App\Models\User;
use App\Models\Warranty;
use Database\Seeders\DatabaseSeeder;

beforeEach(function (): void {
    $this->seed(DatabaseSeeder::class);
});

test('customer logging in with credentials is automatically redirected to customer portal', function (): void {
    $shop = Shop::factory()->create();
    $customerUser = User::factory()->create([
        'shop_id' => $shop->id,
        'email' => 'customer.test@example.com',
        'password' => bcrypt('password'),
    ]);
    $customerUser->assignRole('Customer');

    $response = $this->postJson('/login', [
        'email' => 'customer.test@example.com',
        'password' => 'password',
    ]);

    $response->assertOk();
    $response->assertJsonPath('redirect', route('customer.portal'));
});

test('customer can view personal repairs and warranties on customer portal', function (): void {
    $shop = Shop::factory()->create();
    $customerUser = User::factory()->create([
        'shop_id' => $shop->id,
        'name' => 'Kavindu Customer',
        'email' => 'kavindu.cust@example.com',
    ]);
    $customerUser->assignRole('Customer');

    app()->instance('shop.id', $shop->id);
    $customer = Customer::create([
        'shop_id' => $shop->id,
        'name' => 'Kavindu Customer',
        'email' => 'kavindu.cust@example.com',
        'phone' => '0775551122',
    ]);

    $repair = RepairJob::create([
        'shop_id' => $shop->id,
        'customer_id' => $customer->id,
        'job_number' => 'REP-CUST-PORTAL1',
        'brand' => 'Apple',
        'model' => 'iPhone 15',
        'imei' => '358123456789012',
        'fault_description' => 'Rear camera blur',
        'estimated_cost' => 14000.00,
        'status' => 'in_repair',
    ]);

    $warranty = Warranty::create([
        'shop_id' => $shop->id,
        'customer_id' => $customer->id,
        'warranty_number' => 'WAR-CUST-PORTAL1',
        'warranty_type' => 'shop',
        'imei' => '358123456789012',
        'duration_months' => 12,
        'starts_on' => now()->toDateString(),
        'ends_on' => now()->addYear()->toDateString(),
        'status' => 'active',
    ]);
    app()->forgetInstance('shop.id');

    $response = $this->actingAs($customerUser)->get('/portal');

    $response->assertOk();
    $response->assertViewIs('customer-portal.index');
    $response->assertSee('REP-CUST-PORTAL1');
    $response->assertSee('WAR-CUST-PORTAL1');
    $response->assertSee('Rear camera blur');
});

test('guest cannot access customer portal without authentication', function (): void {
    $this->get('/portal')->assertRedirect(route('login'));
});

