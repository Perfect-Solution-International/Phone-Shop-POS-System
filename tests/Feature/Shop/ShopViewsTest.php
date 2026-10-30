<?php

use App\Models\Category;
use App\Models\Customer;
use App\Models\PhoneDevice;
use App\Models\Platform\Shop;
use App\Models\Product;
use App\Models\RepairJob;
use App\Models\SmsLog;
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
    $response->assertSee('Sales &amp; Sold Invoices', false);
    $response->assertSee('Sold Phones (IMEI Archive)', false);
    $response->assertSee('shop-user-footer', false);
    $response->assertSee('shop-logout-btn', false);
    $response->assertSee('Sign out');
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

test('shop staff can register phone device with supplier and view relationship on phones page', function (): void {
    $shop = Shop::factory()->create();
    $user = User::factory()->create(['shop_id' => $shop->id]);
    $user->assignRole('Shop Manager');

    app()->instance('shop.id', $shop->id);
    $supplier = \App\Models\Supplier::create([
        'shop_id' => $shop->id,
        'name' => 'Metro Distributors',
        'phone' => '0771234567',
    ]);
    app()->forgetInstance('shop.id');

    $payload = [
        'brand' => 'Samsung',
        'model' => 'Galaxy S24',
        'storage' => '256GB',
        'ram' => '8GB',
        'color' => 'Onyx Black',
        'condition' => 'new',
        'imei_1' => '359876543210987',
        'imei_2' => '359876543210988',
        'purchase_cost' => 220000.00,
        'selling_price' => 265000.00,
        'supplier_id' => $supplier->id,
    ];

    $response = $this->actingAs($user)->post('/shop/phones', $payload);
    $response->assertRedirect('/shop/phones');

    $indexRes = $this->actingAs($user)->get('/shop/phones');
    $indexRes->assertOk();
    $indexRes->assertSee('Metro Distributors');
    $indexRes->assertSee('Galaxy S24');
});

test('shop staff can update phone device details and pricing', function (): void {
    $shop = Shop::factory()->create();
    $user = User::factory()->create(['shop_id' => $shop->id]);
    $user->assignRole('Shop Manager');

    app()->instance('shop.id', $shop->id);
    $device = \App\Models\PhoneDevice::create([
        'shop_id' => $shop->id,
        'brand' => 'Apple',
        'model' => 'iPhone 13',
        'storage' => '128GB',
        'ram' => '4GB',
        'color' => 'Midnight Blue',
        'condition' => 'used',
        'imei_1' => '354455667788990',
        'imei_2' => null,
        'purchase_cost' => 140000.00,
        'selling_price' => 172000.00,
        'status' => 'in_stock',
    ]);
    app()->forgetInstance('shop.id');

    $updatePayload = [
        'brand' => 'Apple',
        'model' => 'iPhone 13 Pro',
        'storage' => '256GB',
        'ram' => '6GB',
        'color' => 'Graphite',
        'condition' => 'refurbished',
        'status' => 'in_stock',
        'imei_1' => '354455667788990',
        'imei_2' => '354455667788991',
        'serial_number' => 'SN-IPHONE13P',
        'purchase_cost' => 155000.00,
        'selling_price' => 189000.00,
        'notes' => 'Battery replaced 100% health',
    ];

    $response = $this->actingAs($user)->put("/shop/phones/{$device->id}", $updatePayload);
    $response->assertRedirect('/shop/phones');

    $device->refresh();
    expect($device->model)->toBe('iPhone 13 Pro')
        ->and($device->storage)->toBe('256GB')
        ->and($device->condition)->toBe('refurbished')
        ->and((float) $device->selling_price)->toBe(189000.00)
        ->and($device->imei_2)->toBe('354455667788991');
});

test('shop staff can delete an unsold phone device', function (): void {
    $shop = Shop::factory()->create();
    $user = User::factory()->create(['shop_id' => $shop->id]);
    $user->assignRole('Shop Manager');

    app()->instance('shop.id', $shop->id);
    $device = \App\Models\PhoneDevice::create([
        'shop_id' => $shop->id,
        'brand' => 'Xiaomi',
        'model' => 'Redmi Note 13',
        'imei_1' => '860123456789012',
        'purchase_cost' => 50000.00,
        'selling_price' => 62000.00,
        'status' => 'in_stock',
    ]);
    app()->forgetInstance('shop.id');

    $response = $this->actingAs($user)->delete("/shop/phones/{$device->id}");
    $response->assertRedirect('/shop/phones');

    $this->assertDatabaseMissing('phone_devices', [
        'id' => $device->id,
    ]);
});

test('shop staff cannot delete an already sold phone device', function (): void {
    $shop = Shop::factory()->create();
    $user = User::factory()->create(['shop_id' => $shop->id]);
    $user->assignRole('Shop Manager');

    app()->instance('shop.id', $shop->id);
    $device = \App\Models\PhoneDevice::create([
        'shop_id' => $shop->id,
        'brand' => 'Apple',
        'model' => 'iPhone 15 Pro Max',
        'imei_1' => '358912345678901',
        'purchase_cost' => 370000.00,
        'selling_price' => 425000.00,
        'status' => 'sold',
        'sold_at' => now(),
    ]);
    app()->forgetInstance('shop.id');

    $response = $this->actingAs($user)->delete("/shop/phones/{$device->id}");
    $response->assertSessionHas('error');

    $this->assertDatabaseHas('phone_devices', [
        'id' => $device->id,
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

    // Update customer
    $cust = Customer::withoutGlobalScopes()->where('phone', '0765556677')->first();
    $this->actingAs($user)->put("/shop/customers/{$cust->id}", [
        'name' => 'Mahesh Fernando Updated',
        'phone' => '0765556688',
        'email' => 'mahesh.updated@example.com',
        'nic' => '199012345678',
        'credit_limit' => 25000.00,
        'address' => '123 Main Road, Kandy',
    ])->assertRedirect('/shop/customers');

    $this->assertDatabaseHas('customers', [
        'id' => $cust->id,
        'name' => 'Mahesh Fernando Updated',
        'phone' => '0765556688',
        'address' => '123 Main Road, Kandy',
    ]);

    // Delete customer
    $this->actingAs($user)->delete("/shop/customers/{$cust->id}")
        ->assertRedirect('/shop/customers');

    $this->assertDatabaseMissing('customers', [
        'id' => $cust->id,
    ]);
});

test('customer profile can be created with password and gains customer portal login credentials', function (): void {
    $shop = Shop::factory()->create();
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    $response = $this->actingAs($owner)->post('/shop/customers', [
        'name' => 'Portal Customer',
        'phone' => '0779998877',
        'email' => 'portal.customer@test.com',
        'nic' => '200012345678',
        'password' => 'SecurePass123',
        'password_confirmation' => 'SecurePass123',
    ]);

    $response->assertRedirect('/shop/customers');

    $customer = Customer::withoutGlobalScopes()->where('email', 'portal.customer@test.com')->first();
    expect($customer)->not->toBeNull();
    expect(\Illuminate\Support\Facades\Hash::check('SecurePass123', $customer->password))->toBeTrue();

    // Verify user account created
    $user = User::where('email', 'portal.customer@test.com')->first();
    expect($user)->not->toBeNull();
    expect($user->hasRole('Customer'))->toBeTrue();

    // Verify customer can login and gets redirected to portal
    $loginResp = $this->postJson('/login', [
        'email' => 'portal.customer@test.com',
        'password' => 'SecurePass123',
    ]);
    $loginResp->assertOk();
    $loginResp->assertJsonPath('redirect', route('customer.portal'));
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

    // Query by Ticket ID number
    $response3 = $this->get('/track-repair?tracking_id=' . $repair->id);
    $response3->assertOk();
    $response3->assertSee('REP-PUB-TEST1');

    // Query by Customer Phone
    $response4 = $this->get('/track-repair?tracking_id=0778889900');
    $response4->assertOk();
    $response4->assertSee('REP-PUB-TEST1');
});

test('shop staff can view sales directory and inspect completed sale details', function (): void {
    $shop = Shop::factory()->create();
    $user = User::factory()->create(['shop_id' => $shop->id]);
    $user->assignRole('Shop Manager');

    app()->instance('shop.id', $shop->id);
    $customer = Customer::create([
        'shop_id' => $shop->id,
        'name' => 'Kasun Chamara',
        'phone' => '0771234567',
    ]);

    $phone = PhoneDevice::create([
        'shop_id' => $shop->id,
        'brand' => 'Apple',
        'model' => 'iPhone 15 Pro Max',
        'imei_1' => '358912345678901',
        'purchase_cost' => 370000.00,
        'selling_price' => 425000.00,
        'status' => 'sold',
        'sold_at' => now(),
    ]);

    $sale = \App\Models\Sale::create([
        'shop_id' => $shop->id,
        'customer_id' => $customer->id,
        'user_id' => $user->id,
        'invoice_number' => 'INV-20261023-TEST1',
        'sale_date' => now()->toDateString(),
        'subtotal' => 425000.00,
        'discount_amount' => 5000.00,
        'total_amount' => 420000.00,
        'paid_amount' => 420000.00,
        'payment_method' => 'cash',
        'status' => 'completed',
    ]);

    \App\Models\SaleItem::create([
        'shop_id' => $shop->id,
        'sale_id' => $sale->id,
        'phone_device_id' => $phone->id,
        'item_name' => 'iPhone 15 Pro Max',
        'quantity' => 1,
        'unit_price' => 425000.00,
        'discount' => 5000.00,
        'subtotal' => 420000.00,
    ]);

    \App\Models\SalePayment::create([
        'shop_id' => $shop->id,
        'sale_id' => $sale->id,
        'payment_method' => 'cash',
        'amount' => 420000.00,
    ]);

    $phone->update(['sale_id' => $sale->id]);
    app()->forgetInstance('shop.id');

    // 1. Visit sales list
    $listResponse = $this->actingAs($user)->get('/shop/sales');
    $listResponse->assertOk()->assertViewIs('shop.sales.index');
    $listResponse->assertSee('INV-20261023-TEST1');
    $listResponse->assertSee('Kasun Chamara');
    $listResponse->assertSee('358912345678901');

    // 2. Visit invoice show page
    $showResponse = $this->actingAs($user)->get("/shop/sales/{$sale->id}");
    $showResponse->assertOk()->assertViewIs('shop.sales.show');
    $showResponse->assertSee('INV-20261023-TEST1');
    $showResponse->assertSee('iPhone 15 Pro Max');
    $showResponse->assertSee('358912345678901');
    $showResponse->assertSee('420,000.00');

    // 3. JSON endpoint for modal
    $jsonResponse = $this->actingAs($user)->getJson("/shop/sales/{$sale->id}");
    $jsonResponse->assertOk();
    $jsonResponse->assertJsonPath('invoice_number', 'INV-20261023-TEST1');
    $jsonResponse->assertJsonPath('items.0.phone_device.imei_1', '358912345678901');
});

test('shop staff can register phone device with uploaded photo and manage image', function (): void {
    \Illuminate\Support\Facades\Storage::fake('public');

    $shop = Shop::factory()->create();
    $user = User::factory()->create(['shop_id' => $shop->id]);
    $user->assignRole('Shop Manager');

    $image = \Illuminate\Http\UploadedFile::fake()->create('iphone-15.jpg', 200, 'image/jpeg');

    // 1. Register with image
    $response = $this->actingAs($user)->post('/shop/phones', [
        'brand' => 'Apple',
        'model' => 'iPhone 15 Pro',
        'storage' => '256GB',
        'ram' => '8GB',
        'color' => 'Natural Titanium',
        'condition' => 'new',
        'imei_1' => '359123456789991',
        'imei_2' => '359123456789992',
        'purchase_cost' => 310000.00,
        'selling_price' => 365000.00,
        'image' => $image,
    ]);

    $response->assertRedirect('/shop/phones');
    $phone = PhoneDevice::withoutGlobalScopes()->where('imei_1', '359123456789991')->firstOrFail();
    expect($phone->image_path)->not->toBeNull();
    expect(\Illuminate\Support\Facades\Storage::disk('public')->exists($phone->image_path))->toBeTrue();
    expect($phone->image_url)->toContain('storage/' . $phone->image_path);

    // 2. View in phones index
    $indexResponse = $this->actingAs($user)->get('/shop/phones');
    $indexResponse->assertOk();
    $indexResponse->assertSee($phone->image_url);

    // 3. Update with new image
    $newImage = \Illuminate\Http\UploadedFile::fake()->create('iphone-15-alt.png', 200, 'image/png');
    $oldPath = $phone->image_path;

    $updateResponse = $this->actingAs($user)->put("/shop/phones/{$phone->id}", [
        'brand' => 'Apple',
        'model' => 'iPhone 15 Pro Max',
        'storage' => '256GB',
        'ram' => '8GB',
        'color' => 'Natural Titanium',
        'condition' => 'new',
        'status' => 'in_stock',
        'imei_1' => '359123456789991',
        'purchase_cost' => 310000.00,
        'selling_price' => 380000.00,
        'image' => $newImage,
    ]);

    $updateResponse->assertRedirect('/shop/phones');
    $phone = PhoneDevice::withoutGlobalScopes()->find($phone->id);
    expect($phone->model)->toBe('iPhone 15 Pro Max');
    expect(\Illuminate\Support\Facades\Storage::disk('public')->exists($oldPath))->toBeFalse();
    expect(\Illuminate\Support\Facades\Storage::disk('public')->exists($phone->image_path))->toBeTrue();

    // 4. Delete image via endpoint
    $deleteImgResponse = $this->actingAs($user)->delete("/shop/phones/{$phone->id}/image");
    $deleteImgResponse->assertRedirect();
    $phone = PhoneDevice::withoutGlobalScopes()->find($phone->id);
    expect($phone->image_path)->toBeNull();
    expect($phone->image_url)->toBeNull();
});

test('shop staff can view notification history and send quick sms', function (): void {
    $shop = Shop::factory()->create();
    $user = User::factory()->create(['shop_id' => $shop->id]);
    $user->assignRole('Shop Manager');

    SmsLog::create([
        'shop_id' => $shop->id,
        'recipient_phone' => '0771234567',
        'message' => 'Your phone repair is ready for pickup!',
        'type' => 'repair_ready',
        'status' => 'simulated',
        'provider' => 'mock',
    ]);

    $response = $this->actingAs($user)->get('/shop/notifications');
    $response->assertOk();
    $response->assertViewIs('shop.notifications.index');
    $response->assertSee('Notification Log History');
    $response->assertSee('0771234567');
    $response->assertSee('Your phone repair is ready for pickup!');
    $response->assertSee('Simulated');
    $response->assertSee('MOCK');
});

