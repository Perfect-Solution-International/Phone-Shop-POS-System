<?php

use App\Models\Category;
use App\Models\Customer;
use App\Models\PhoneDevice;
use App\Models\Platform\Shop;
use App\Models\Product;
use App\Models\RepairJob;
use App\Models\SparePart;
use App\Models\User;
use App\Models\Warranty;
use Database\Seeders\DatabaseSeeder;

beforeEach(function (): void {
    $this->seed(DatabaseSeeder::class);
});

test('shop staff can access shop dashboard view with metrics', function (): void {
    $shop = Shop::factory()->create();
    $user = User::factory()->create(['shop_id' => $shop->id]);
    $user->assignRole('Shop Manager');

    $response = $this->actingAs($user)->get('/shop/dashboard');

    $response->assertOk();
    $response->assertViewIs('shop.dashboard.index');
    $response->assertViewHas('metrics');
});

test('shop cashier can access pos counter and complete sale with split tender', function (): void {
    $shop = Shop::factory()->create();
    $cashier = User::factory()->create(['shop_id' => $shop->id]);
    $cashier->assignRole('Shop Manager');

    app()->instance('shop.id', $shop->id);
    $category = Category::create(['shop_id' => $shop->id, 'name' => 'Audio', 'slug' => 'audio']);
    $product = Product::create([
        'shop_id' => $shop->id,
        'category_id' => $category->id,
        'name' => 'AirPods Pro 2',
        'code' => 'APP2-001',
        'cost_price' => 50000.00,
        'retail_price' => 65000.00,
        'stock_quantity' => 5,
        'is_active' => true,
    ]);
    app()->forgetInstance('shop.id');

    $this->actingAs($cashier)->get('/shop/pos')->assertOk()->assertViewIs('shop.pos.index');

    $checkoutPayload = [
        'discount_amount' => 1000.00,
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 1,
                'unit_price' => 65000.00,
            ],
        ],
        'payments' => [
            ['payment_method' => 'cash', 'amount' => 40000.00],
            ['payment_method' => 'card', 'amount' => 24000.00],
        ],
    ];

    $response = $this->actingAs($cashier)->postJson('/shop/pos/checkout', $checkoutPayload);

    $response->assertCreated();
    $response->assertJsonPath('success', true);
    expect($response->json('sale.invoice_number'))->not->toBeNull();

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'stock_quantity' => 4,
    ]);
});

test('shop staff can register serialized phone with dual IMEI from web view', function (): void {
    $shop = Shop::factory()->create();
    $user = User::factory()->create(['shop_id' => $shop->id]);
    $user->assignRole('Shop Manager');

    $this->actingAs($user)->get('/shop/phones')->assertOk()->assertViewIs('shop.phones.index');

    $payload = [
        'brand' => 'Google',
        'model' => 'Pixel 8a',
        'storage' => '128GB',
        'ram' => '8GB',
        'color' => 'Bay Blue',
        'condition' => 'new',
        'imei_1' => '356789012345671',
        'imei_2' => '356789012345672',
        'purchase_cost' => 140000.00,
        'selling_price' => 175000.00,
    ];

    $response = $this->actingAs($user)->post('/shop/phones', $payload);

    $response->assertRedirect('/shop/phones');
    $this->assertDatabaseHas('phone_devices', [
        'shop_id' => $shop->id,
        'imei_1' => '356789012345671',
        'status' => 'in_stock',
    ]);
});

test('shop staff can book a repair ticket and update stages to delivered', function (): void {
    $shop = Shop::factory()->create();
    $tech = User::factory()->create(['shop_id' => $shop->id]);
    $tech->assignRole('Shop Manager');

    app()->instance('shop.id', $shop->id);
    $customer = Customer::create([
        'shop_id' => $shop->id,
        'name' => 'Saman Perera',
        'phone' => '0772223344',
    ]);
    app()->forgetInstance('shop.id');

    $this->actingAs($tech)->get('/shop/repairs')->assertOk()->assertViewIs('shop.repairs.index');

    $bookingPayload = [
        'customer_id' => $customer->id,
        'brand' => 'OnePlus',
        'model' => '11R',
        'imei' => '351234567890123',
        'fault_description' => 'Green vertical line on screen',
        'estimated_cost' => 25000.00,
        'advance_amount' => 5000.00,
    ];

    $response = $this->actingAs($tech)->post('/shop/repairs', $bookingPayload);
    $response->assertRedirect('/shop/repairs');

    $repair = RepairJob::withoutGlobalScopes()->where('imei', '351234567890123')->first();
    expect($repair)->not->toBeNull();
    expect($repair->status)->toBe('received');

    // Update status to in_repair
    $this->actingAs($tech)->patch("/shop/repairs/{$repair->id}/status", [
        'status' => 'in_repair',
        'technician_notes' => 'Awaiting screen replacement',
    ])->assertRedirect('/shop/repairs');

    // Update status to delivered
    $this->actingAs($tech)->patch("/shop/repairs/{$repair->id}/status", [
        'status' => 'delivered',
        'final_cost' => 25000.00,
    ])->assertRedirect('/shop/repairs');

    $freshRepair = RepairJob::withoutGlobalScopes()->find($repair->id);
    expect($freshRepair->status)->toBe('delivered');
    expect($freshRepair->delivered_at)->not->toBeNull();
});

test('shop staff can issue warranty certificate and file claim', function (): void {
    $shop = Shop::factory()->create();
    $user = User::factory()->create(['shop_id' => $shop->id]);
    $user->assignRole('Shop Manager');

    app()->instance('shop.id', $shop->id);
    $customer = Customer::create([
        'shop_id' => $shop->id,
        'name' => 'Nalin Dias',
        'phone' => '0714445566',
    ]);
    app()->forgetInstance('shop.id');

    $this->actingAs($user)->get('/shop/warranties')->assertOk()->assertViewIs('shop.warranties.index');

    $warrantyPayload = [
        'customer_id' => $customer->id,
        'warranty_type' => 'shop',
        'imei' => '359999000111222',
        'duration_months' => 6,
        'terms' => '6 Months warranty covering parts and labor',
    ];

    $res = $this->actingAs($user)->post('/shop/warranties', $warrantyPayload);
    $res->assertRedirect('/shop/warranties');

    $warranty = Warranty::withoutGlobalScopes()->where('imei', '359999000111222')->first();
    expect($warranty)->not->toBeNull();
    expect($warranty->isValid())->toBeTrue();

    // File claim
    $claimPayload = [
        'issue_description' => 'Volume button stuck',
        'settlement_type' => 'repair',
    ];

    $claimRes = $this->actingAs($user)->post("/shop/warranties/{$warranty->id}/claim", $claimPayload);
    $claimRes->assertRedirect('/shop/warranties');

    $this->assertDatabaseHas('warranty_claims', [
        'warranty_id' => $warranty->id,
        'settlement_type' => 'repair',
    ]);
});

test('spare parts and customer directory endpoints function properly', function (): void {
    $shop = Shop::factory()->create();
    $user = User::factory()->create(['shop_id' => $shop->id]);
    $user->assignRole('Shop Manager');

    // Spare parts
    $this->actingAs($user)->get('/shop/spare-parts')->assertOk()->assertViewIs('shop.spare-parts.index');
    $this->actingAs($user)->post('/shop/spare-parts', [
        'name' => 'Galaxy S21 Ultra AMOLED Screen',
        'part_number' => 'DISP-S21U',
        'quality_type' => 'original',
        'cost_price' => 38000.00,
        'selling_price' => 48000.00,
        'stock_quantity' => 3,
        'alert_quantity' => 1,
    ])->assertRedirect('/shop/spare-parts');

    $this->assertDatabaseHas('spare_parts', [
        'shop_id' => $shop->id,
        'part_number' => 'DISP-S21U',
    ]);

    // Customers
    $this->actingAs($user)->get('/shop/customers')->assertOk()->assertViewIs('shop.customers.index');
    $this->actingAs($user)->post('/shop/customers', [
        'name' => 'Mahesh Fernando',
        'phone' => '0765556677',
        'email' => 'mahesh@example.com',
        'nic' => '199012345678',
        'credit_limit' => 20000.00,
    ])->assertRedirect('/shop/customers');

    $this->assertDatabaseHas('customers', [
        'shop_id' => $shop->id,
        'phone' => '0765556677',
    ]);
});

test('public customer repair tracking page is publicly accessible and displays repair status', function (): void {
    $shop = Shop::factory()->create();

    app()->instance('shop.id', $shop->id);
    $customer = Customer::create([
        'shop_id' => $shop->id,
        'name' => 'Dilshan Silva',
        'phone' => '0778889900',
    ]);
    $repair = RepairJob::create([
        'shop_id' => $shop->id,
        'customer_id' => $customer->id,
        'job_number' => 'REP-PUB-TEST1',
        'brand' => 'Sony',
        'model' => 'Xperia 1 V',
        'imei' => '354321098765432',
        'fault_description' => 'Microphone not functioning',
        'estimated_cost' => 8500.00,
        'status' => 'ready_for_collection',
    ]);
    app()->forgetInstance('shop.id');

    // Open public page
    $this->get('/track-repair')->assertOk()->assertViewIs('public.track-repair');

    // Query by job number
    $response = $this->get('/track-repair?tracking_id=REP-PUB-TEST1');
    $response->assertOk();
    $response->assertSee('REP-PUB-TEST1');
    $response->assertSee('Xperia 1 V');
    $response->assertSee('Ready for Pickup');

    // Query by IMEI
    $response2 = $this->get('/track-repair?tracking_id=354321098765432');
    $response2->assertOk();
    $response2->assertSee('REP-PUB-TEST1');
});
