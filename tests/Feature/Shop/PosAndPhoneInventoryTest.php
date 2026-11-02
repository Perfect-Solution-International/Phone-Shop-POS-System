<?php

use App\Models\Customer;
use App\Models\PhoneDevice;
use App\Models\Platform\Shop;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->seed(DatabaseSeeder::class);
});

test('authorized staff can register phone device with dual IMEI and condition', function (): void {
    $shop = Shop::factory()->create();
    $user = User::factory()->create(['shop_id' => $shop->id]);
    $user->assignRole('Stock Manager');

    Sanctum::actingAs($user);

    $payload = [
        'brand' => 'Samsung',
        'model' => 'Galaxy S24 Ultra',
        'storage' => '512GB',
        'ram' => '12GB',
        'color' => 'Titanium Black',
        'condition' => 'new',
        'imei_1' => '358912345678901',
        'imei_2' => '358912345678902',
        'serial_number' => 'RF9N123456X',
        'purchase_cost' => 280000.00,
        'selling_price' => 345000.00,
        'notes' => 'Direct official warranty stock',
    ];

    $response = $this->postJson('/api/v1/phones', $payload);

    $response->assertCreated();
    $response->assertJsonPath('imei_1', '358912345678901');
    $response->assertJsonPath('status', 'in_stock');

    $this->assertDatabaseHas('phone_devices', [
        'shop_id' => $shop->id,
        'imei_1' => '358912345678901',
        'status' => 'in_stock',
    ]);
});

test('registering duplicate IMEI within the same shop is rejected', function (): void {
    $shop = Shop::factory()->create();
    $user = User::factory()->create(['shop_id' => $shop->id]);
    $user->assignRole('Stock Manager');

    app()->instance('shop.id', $shop->id);
    PhoneDevice::create([
        'shop_id' => $shop->id,
        'brand' => 'Apple',
        'model' => 'iPhone 15 Pro',
        'condition' => 'new',
        'imei_1' => '359998887776665',
        'purchase_cost' => 310000.00,
        'selling_price' => 380000.00,
        'status' => 'in_stock',
    ]);
    app()->forgetInstance('shop.id');

    Sanctum::actingAs($user);

    $payload = [
        'brand' => 'Apple',
        'model' => 'iPhone 15 Pro',
        'condition' => 'new',
        'imei_1' => '359998887776665',
        'purchase_cost' => 310000.00,
        'selling_price' => 380000.00,
    ];

    $response = $this->postJson('/api/v1/phones', $payload);

    $response->assertStatus(422);
    $response->assertJsonPath('message', 'A device with IMEI 359998887776665 is already registered in this workspace.');
});

test('cashier can lookup device by IMEI 1 or IMEI 2 for rapid POS scanning', function (): void {
    $shop = Shop::factory()->create();
    $user = User::factory()->create(['shop_id' => $shop->id]);
    $user->assignRole('Cashier');

    app()->instance('shop.id', $shop->id);
    PhoneDevice::create([
        'shop_id' => $shop->id,
        'brand' => 'Xiaomi',
        'model' => 'Redmi Note 13',
        'condition' => 'new',
        'imei_1' => '861234567890123',
        'imei_2' => '861234567890124',
        'purchase_cost' => 52000.00,
        'selling_price' => 64000.00,
        'status' => 'in_stock',
    ]);
    app()->forgetInstance('shop.id');

    Sanctum::actingAs($user);

    // Lookup via IMEI 2
    $response = $this->getJson('/api/v1/phones/lookup?imei=861234567890124');

    $response->assertOk();
    $response->assertJsonPath('model', 'Redmi Note 13');
    $response->assertJsonPath('imei_1', '861234567890123');
});

test('POS checkout processes split payment, creates invoice, decrements stock and marks phone sold', function (): void {
    $shop = Shop::factory()->create();
    $cashier = User::factory()->create(['shop_id' => $shop->id]);
    $cashier->assignRole('Cashier');

    app()->instance('shop.id', $shop->id);

    // Standard accessory (Case)
    $caseProduct = Product::create([
        'shop_id' => $shop->id,
        'name' => 'Silicone Case',
        'code' => 'ACC-CASE-01',
        'type' => 'standard',
        'cost_price' => 800.00,
        'retail_price' => 1500.00,
        'stock_quantity' => 10,
    ]);

    // Serialized phone (Samsung S23)
    $phone = PhoneDevice::create([
        'shop_id' => $shop->id,
        'brand' => 'Samsung',
        'model' => 'Galaxy S23',
        'condition' => 'new',
        'imei_1' => '355554444333222',
        'purchase_cost' => 180000.00,
        'selling_price' => 220000.00,
        'status' => 'in_stock',
    ]);

    // Customer
    $customer = Customer::create([
        'shop_id' => $shop->id,
        'name' => 'Nadeeka Perera',
        'phone' => '0779988776',
    ]);

    app()->forgetInstance('shop.id');

    Sanctum::actingAs($cashier);

    // Total: Phone (220,000) + Case (1,500) = 221,500
    // Split Payment: LKR 100,000 Cash + LKR 121,500 Card
    $payload = [
        'customer_id' => $customer->id,
        'items' => [
            [
                'phone_device_id' => $phone->id,
                'item_name' => 'Samsung Galaxy S23 (355554444333222)',
                'quantity' => 1,
                'unit_price' => 220000.00,
            ],
            [
                'product_id' => $caseProduct->id,
                'item_name' => 'Silicone Case',
                'quantity' => 1,
                'unit_price' => 1500.00,
            ],
        ],
        'payments' => [
            ['payment_method' => 'cash', 'amount' => 100000.00],
            ['payment_method' => 'card', 'amount' => 121500.00, 'reference' => 'TXN-998877'],
        ],
    ];

    $response = $this->postJson('/api/v1/pos/checkout', $payload);

    $response->assertCreated();
    $response->assertJsonPath('sale.total_amount', '221500.00');
    $response->assertJsonPath('sale.payment_method', 'split');
    $response->assertJsonPath('sale.status', 'completed');

    // Phone status updated to sold
    expect($phone->fresh()->status)->toBe('sold');
    expect($phone->fresh()->sold_at)->not->toBeNull();

    // Standard accessory stock decremented from 10 to 9
    expect($caseProduct->fresh()->stock_quantity)->toBe(9);

    // Stock movements recorded
    $this->assertDatabaseHas('stock_movements', [
        'shop_id' => $shop->id,
        'phone_device_id' => $phone->id,
        'type' => 'sale',
    ]);
});

test('double-selling an already sold phone device is rejected', function (): void {
    $shop = Shop::factory()->create();
    $cashier = User::factory()->create(['shop_id' => $shop->id]);
    $cashier->assignRole('Cashier');

    app()->instance('shop.id', $shop->id);
    $phone = PhoneDevice::create([
        'shop_id' => $shop->id,
        'brand' => 'Apple',
        'model' => 'iPhone 14',
        'condition' => 'new',
        'imei_1' => '351112223334445',
        'purchase_cost' => 190000.00,
        'selling_price' => 240000.00,
        'status' => 'sold', // Already sold!
    ]);
    app()->forgetInstance('shop.id');

    Sanctum::actingAs($cashier);

    $payload = [
        'items' => [
            [
                'phone_device_id' => $phone->id,
                'item_name' => 'iPhone 14',
                'quantity' => 1,
                'unit_price' => 240000.00,
            ],
        ],
        'payments' => [
            ['payment_method' => 'cash', 'amount' => 240000.00],
        ],
    ];

    $response = $this->postJson('/api/v1/pos/checkout', $payload);

    $this->assertTrue(in_array($response->status(), [422, 500]), 'Expected 422 or 500 for aborted transaction.');
});

test('tenant isolation strictly prohibits Shop A from accessing Shop B phone inventory', function (): void {
    $shopA = Shop::factory()->create();
    $userA = User::factory()->create(['shop_id' => $shopA->id]);
    $userA->assignRole('Stock Manager');

    $shopB = Shop::factory()->create();
    app()->instance('shop.id', $shopB->id);
    $deviceB = PhoneDevice::create([
        'shop_id' => $shopB->id,
        'brand' => 'Google',
        'model' => 'Pixel 8',
        'condition' => 'new',
        'imei_1' => '357778889990001',
        'purchase_cost' => 160000.00,
        'selling_price' => 195000.00,
        'status' => 'in_stock',
    ]);
    app()->forgetInstance('shop.id');

    Sanctum::actingAs($userA);

    // Shop A searches for Shop B's IMEI
    $response = $this->getJson("/api/v1/phones/lookup?imei={$deviceB->imei_1}");

    $response->assertNotFound();
});

test('POS direct counter sale captures customer details and immediately deducts stock', function (): void {
    $shop = Shop::factory()->create();
    $cashier = User::factory()->create(['shop_id' => $shop->id]);
    $cashier->assignRole('Cashier');

    app()->instance('shop.id', $shop->id);
    $device = PhoneDevice::create([
        'shop_id' => $shop->id,
        'brand' => 'Google Pixel',
        'model' => 'model 3',
        'storage' => '128GB',
        'color' => 'Black',
        'condition' => 'new',
        'imei_1' => '1111111',
        'purchase_cost' => 60000.00,
        'selling_price' => 80000.00,
        'status' => 'in_stock',
    ]);
    app()->forgetInstance('shop.id');

    $response = $this->actingAs($cashier, 'web')
        ->postJson(route('shop.pos.checkout'), [
            'customer_name' => 'Kasun Jayawardena',
            'customer_phone' => '0714545788',
            'customer_nic' => '200120653157',
            'customer_address' => 'Colombo',
            'items' => [
                [
                    'phone_device_id' => $device->id,
                    'item_name' => 'Google Pixel model 3',
                    'quantity' => 1,
                    'unit_price' => 80000.00,
                ],
            ],
            'payments' => [
                ['payment_method' => 'cash', 'amount' => 80000.00],
            ],
        ]);

    $response->assertCreated();
    $response->assertJsonPath('success', true);

    // Verify phone stock is immediately deducted (status = sold)
    expect($device->fresh()->status)->toBe('sold');
    expect($device->fresh()->sale_id)->not->toBeNull();

    // Verify customer record created and linked
    $customer = Customer::withoutGlobalScopes()->where('shop_id', $shop->id)->where('phone', '0714545788')->first();
    expect($customer)->not->toBeNull();
    expect($customer->name)->toBe('Kasun Jayawardena');
    expect($customer->nic)->toBe('200120653157');

    $sale = Sale::withoutGlobalScopes()->where('shop_id', $shop->id)->latest('id')->first();
    expect($sale->customer_id)->toBe($customer->id);
    expect((float) $sale->total_amount)->toBe(80000.00);
});

