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

test('customer can buy a phone device from portal, deducting stock and saving customer details', function (): void {
    $shop = Shop::factory()->create();
    $customerUser = User::factory()->create([
        'shop_id' => $shop->id,
        'name' => 'Kavindu Customer',
        'email' => 'kavindu.buy@example.com',
        'phone' => '0779876543',
    ]);
    $customerUser->assignRole('Customer');

    app()->instance('shop.id', $shop->id);
    $device = \App\Models\PhoneDevice::create([
        'shop_id' => $shop->id,
        'brand' => 'Apple',
        'model' => 'iPhone 14 Pro',
        'storage' => '256GB',
        'color' => 'Space Black',
        'condition' => 'new',
        'imei_1' => '359123456789099',
        'purchase_cost' => 180000.00,
        'selling_price' => 220000.00,
        'status' => 'in_stock',
    ]);
    app()->forgetInstance('shop.id');

    $response = $this->actingAs($customerUser)->post('/portal/buy', [
        'item_type' => 'phone',
        'phone_device_id' => $device->id,
        'payment_method' => 'card',
        'contact_phone' => '0779876543',
        'delivery_address' => '123 Galle Road, Colombo 03',
    ]);

    $response->assertRedirect(route('customer.portal', ['tab' => 'invoices']));
    $response->assertSessionHas('success');

    // Verify phone stock is deducted immediately
    $device->refresh();
    expect($device->status)->toBe('sold');
    expect($device->sale_id)->not->toBeNull();

    // Verify customer details recorded on the sale
    $sale = \App\Models\Sale::withoutGlobalScopes()->where('id', $device->sale_id)->first();
    expect($sale)->not->toBeNull();
    expect((float) $sale->total_amount)->toBe(220000.00);

    $customer = \App\Models\Customer::withoutGlobalScopes()->where('id', $sale->customer_id)->first();
    expect($customer)->not->toBeNull();
    expect($customer->name)->toBe('Kavindu Customer');
    expect($customer->phone)->toBe('0779876543');
});

