<?php

use App\Models\Customer;
use App\Models\Platform\Shop;
use App\Models\Product;
use App\Models\RepairJob;
use App\Models\RepairPartUsed;
use App\Models\SparePart;
use App\Models\User;
use App\Models\Warranty;
use App\Models\WarrantyClaim;
use Database\Seeders\DatabaseSeeder;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->seed(DatabaseSeeder::class);
});

test('technician can register a new phone repair job with auto-generated job number', function (): void {
    $shop = Shop::factory()->create();
    $tech = User::factory()->create(['shop_id' => $shop->id]);
    $tech->assignRole('Technician');

    app()->instance('shop.id', $shop->id);
    $customer = Customer::create([
        'shop_id' => $shop->id,
        'name' => 'Kavindu Perera',
        'phone' => '0771234567',
        'email' => 'kavindu@example.com',
    ]);
    app()->forgetInstance('shop.id');

    Sanctum::actingAs($tech);

    $payload = [
        'customer_id' => $customer->id,
        'brand' => 'Apple',
        'model' => 'iPhone 13',
        'imei' => '354890123456789',
        'fault_description' => 'Display cracked and touch screen not responding on top left',
        'condition_notes' => 'Minor scratch on rear glass',
        'accessories_received' => 'Device only, no SIM or charger',
        'estimated_cost' => 18500.00,
        'advance_amount' => 5000.00,
        'estimated_completion_date' => now()->addDays(2)->toDateString(),
    ];

    $response = $this->postJson('/api/v1/repairs', $payload);

    $response->assertCreated();
    $response->assertJsonPath('status', 'received');
    $response->assertJsonPath('brand', 'Apple');
    $response->assertJsonPath('model', 'iPhone 13');
    expect($response->json('job_number'))->toStartWith('REP-');

    $this->assertDatabaseHas('repair_jobs', [
        'shop_id' => $shop->id,
        'customer_id' => $customer->id,
        'status' => 'received',
        'estimated_cost' => 18500.00,
    ]);
});

test('technician can transition repair job through lifecycle stages up to delivered', function (): void {
    $shop = Shop::factory()->create();
    $tech = User::factory()->create(['shop_id' => $shop->id]);
    $tech->assignRole('Technician');

    app()->instance('shop.id', $shop->id);
    $customer = Customer::create([
        'shop_id' => $shop->id,
        'name' => 'Nimal Silva',
        'phone' => '0719876543',
    ]);
    $repair = RepairJob::create([
        'shop_id' => $shop->id,
        'job_number' => 'REP-20261022-TEST',
        'customer_id' => $customer->id,
        'brand' => 'Samsung',
        'model' => 'Galaxy A54',
        'fault_description' => 'Charging port loose',
        'estimated_cost' => 4500.00,
        'status' => 'received',
    ]);
    app()->forgetInstance('shop.id');

    Sanctum::actingAs($tech);

    // Stage 1: diagnosing
    $r1 = $this->patchJson("/api/v1/repairs/{$repair->id}/status", [
        'status' => 'diagnosing',
        'technician_notes' => 'Examining sub-board pins',
    ]);
    $r1->assertOk();
    $r1->assertJsonPath('status', 'diagnosing');

    // Stage 2: in_repair
    $r2 = $this->patchJson("/api/v1/repairs/{$repair->id}/status", [
        'status' => 'in_repair',
        'technician_notes' => 'Replacing Type-C port flex PCB',
    ]);
    $r2->assertOk();
    $r2->assertJsonPath('status', 'in_repair');

    // Stage 3: delivered
    $r3 = $this->patchJson("/api/v1/repairs/{$repair->id}/status", [
        'status' => 'delivered',
        'final_cost' => 4500.00,
    ]);
    $r3->assertOk();
    $r3->assertJsonPath('status', 'delivered');
    expect($r3->json('delivered_at'))->not->toBeNull();

    $this->assertDatabaseHas('repair_jobs', [
        'id' => $repair->id,
        'status' => 'delivered',
        'final_cost' => 4500.00,
    ]);
});

test('allocating spare part to repair job decrements stock automatically', function (): void {
    $shop = Shop::factory()->create();
    $tech = User::factory()->create(['shop_id' => $shop->id]);
    $tech->assignRole('Technician');

    app()->instance('shop.id', $shop->id);
    $customer = Customer::create([
        'shop_id' => $shop->id,
        'name' => 'Nuwan Gamage',
        'phone' => '0751112233',
    ]);
    $repair = RepairJob::create([
        'shop_id' => $shop->id,
        'job_number' => 'REP-20261022-PART',
        'customer_id' => $customer->id,
        'brand' => 'Apple',
        'model' => 'iPhone 11',
        'fault_description' => 'Battery replacement required',
        'estimated_cost' => 9500.00,
        'status' => 'in_repair',
    ]);
    $part = SparePart::create([
        'shop_id' => $shop->id,
        'name' => 'iPhone 11 OEM Battery 3110mAh',
        'part_number' => 'BAT-IP11-OEM',
        'quality_type' => 'oem',
        'cost_price' => 4200.00,
        'selling_price' => 7500.00,
        'stock_quantity' => 10,
        'alert_quantity' => 2,
    ]);
    app()->forgetInstance('shop.id');

    Sanctum::actingAs($tech);

    $response = $this->postJson("/api/v1/repairs/{$repair->id}/parts", [
        'spare_part_id' => $part->id,
        'quantity' => 1,
        'unit_price' => 7500.00,
    ]);

    $response->assertCreated();
    $response->assertJsonPath('quantity', 1);
    $response->assertJsonPath('unit_price', '7500.00');

    // Verify stock was decremented from 10 to 9
    $this->assertDatabaseHas('spare_parts', [
        'id' => $part->id,
        'stock_quantity' => 9,
    ]);

    // Verify part allocation record was saved
    $this->assertDatabaseHas('repair_parts_used', [
        'repair_job_id' => $repair->id,
        'spare_part_id' => $part->id,
        'quantity' => 1,
    ]);
});

test('allocating spare part with insufficient stock is rejected', function (): void {
    $shop = Shop::factory()->create();
    $tech = User::factory()->create(['shop_id' => $shop->id]);
    $tech->assignRole('Technician');

    app()->instance('shop.id', $shop->id);
    $customer = Customer::create([
        'shop_id' => $shop->id,
        'name' => 'Sanath Fernando',
        'phone' => '0783334455',
    ]);
    $repair = RepairJob::create([
        'shop_id' => $shop->id,
        'job_number' => 'REP-20261022-OUT',
        'customer_id' => $customer->id,
        'brand' => 'Xiaomi',
        'model' => 'Redmi Note 12',
        'fault_description' => 'Display repair',
        'estimated_cost' => 11000.00,
        'status' => 'in_repair',
    ]);
    $part = SparePart::create([
        'shop_id' => $shop->id,
        'name' => 'Redmi Note 12 AMOLED Assembly',
        'part_number' => 'DISP-RN12',
        'quality_type' => 'original',
        'cost_price' => 8000.00,
        'selling_price' => 11000.00,
        'stock_quantity' => 1, // Only 1 in stock
    ]);
    app()->forgetInstance('shop.id');

    Sanctum::actingAs($tech);

    // Attempting to consume 2 units should fail
    $response = $this->postJson("/api/v1/repairs/{$repair->id}/parts", [
        'spare_part_id' => $part->id,
        'quantity' => 2,
    ]);

    $response->assertStatus(422);

    // Stock must remain unchanged
    $this->assertDatabaseHas('spare_parts', [
        'id' => $part->id,
        'stock_quantity' => 1,
    ]);
});

test('authorized staff can register warranty and look up status by IMEI', function (): void {
    $shop = Shop::factory()->create();
    $manager = User::factory()->create(['shop_id' => $shop->id]);
    $manager->assignRole('Shop Manager');

    app()->instance('shop.id', $shop->id);
    $customer = Customer::create([
        'shop_id' => $shop->id,
        'name' => 'Amal Jayasuriya',
        'phone' => '0704445566',
    ]);
    app()->forgetInstance('shop.id');

    Sanctum::actingAs($manager);

    $warrantyPayload = [
        'customer_id' => $customer->id,
        'warranty_type' => 'shop',
        'imei' => '359123456789012',
        'duration_months' => 12,
        'starts_on' => now()->toDateString(),
        'terms' => '1 Year hardware warranty covering motherboard and display defects.',
    ];

    $response = $this->postJson('/api/v1/warranties', $warrantyPayload);
    $response->assertCreated();
    $response->assertJsonPath('status', 'active');
    $response->assertJsonPath('duration_months', 12);
    $warrantyNumber = $response->json('warranty_number');

    // Verify lookup by IMEI
    $lookupResponse = $this->getJson('/api/v1/warranties/verify?query=359123456789012');
    $lookupResponse->assertOk();
    $lookupResponse->assertJsonPath('warranty_number', $warrantyNumber);
    $lookupResponse->assertJsonPath('status', 'active');
    $lookupResponse->assertJsonPath('customer_name', 'Amal Jayasuriya');
});

test('shop owner can file and record a warranty claim against an active warranty', function (): void {
    $shop = Shop::factory()->create();
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    app()->instance('shop.id', $shop->id);
    $warranty = Warranty::create([
        'shop_id' => $shop->id,
        'warranty_number' => 'WAR-2026-CLAIM1',
        'warranty_type' => 'shop',
        'imei' => '357890123456789',
        'duration_months' => 6,
        'starts_on' => now()->subMonth()->toDateString(),
        'ends_on' => now()->addMonths(5)->toDateString(),
        'status' => 'active',
    ]);
    app()->forgetInstance('shop.id');

    Sanctum::actingAs($owner);

    $claimPayload = [
        'issue_description' => 'Ear speaker distorted and low volume after 3 weeks of usage',
        'settlement_type' => 'repair',
        'action_notes' => 'Accepted for warranty inspection and speaker replacement under coverage',
    ];

    $response = $this->postJson("/api/v1/warranties/{$warranty->id}/claim", $claimPayload);

    $response->assertCreated();
    $response->assertJsonPath('warranty_id', $warranty->id);
    $response->assertJsonPath('settlement_type', 'repair');
    expect($response->json('claim_number'))->toStartWith('CLM-');

    $this->assertDatabaseHas('warranty_claims', [
        'warranty_id' => $warranty->id,
        'settlement_type' => 'repair',
        'status' => 'pending',
    ]);
});

test('public customer tracking endpoint allows customers to track repairs without auth', function (): void {
    $shop = Shop::factory()->create();

    app()->instance('shop.id', $shop->id);
    $customer = Customer::create([
        'shop_id' => $shop->id,
        'name' => 'Tracking User',
        'phone' => '0779998877',
    ]);
    $repair = RepairJob::create([
        'shop_id' => $shop->id,
        'customer_id' => $customer->id,
        'job_number' => 'REP-TRACK-9999',
        'brand' => 'Google',
        'model' => 'Pixel 8 Pro',
        'imei' => '352999000111222',
        'fault_description' => 'Camera glass shattered',
        'estimated_cost' => 16000.00,
        'status' => 'quality_check',
    ]);
    app()->forgetInstance('shop.id');

    // Track using Job Number anonymously without logging in
    $res1 = $this->getJson('/api/v1/public/repairs/track?tracking_id=REP-TRACK-9999');
    $res1->assertOk();
    $res1->assertJsonPath('job_number', 'REP-TRACK-9999');
    $res1->assertJsonPath('brand', 'Google');
    $res1->assertJsonPath('status', 'quality_check');

    // Track using IMEI anonymously without logging in
    $res2 = $this->getJson('/api/v1/public/repairs/track?tracking_id=352999000111222');
    $res2->assertOk();
    $res2->assertJsonPath('job_number', 'REP-TRACK-9999');
    $res2->assertJsonPath('model', 'Pixel 8 Pro');

    // Non-existent tracking ID returns 404
    $res3 = $this->getJson('/api/v1/public/repairs/track?tracking_id=INVALID-CODE');
    $res3->assertNotFound();
});

test('tenant isolation prevents Tenant A from viewing Tenant B repair jobs', function (): void {
    $shopA = Shop::factory()->create();
    $shopB = Shop::factory()->create();

    $techA = User::factory()->create(['shop_id' => $shopA->id]);
    $techA->assignRole('Technician');

    app()->instance('shop.id', $shopA->id);
    $customerA = Customer::create([
        'shop_id' => $shopA->id,
        'name' => 'Customer A',
        'phone' => '0770001111',
    ]);
    $repairA = RepairJob::create([
        'shop_id' => $shopA->id,
        'customer_id' => $customerA->id,
        'job_number' => 'REP-SHOP-A01',
        'brand' => 'Apple',
        'model' => 'iPhone 14',
        'fault_description' => 'Shop A repair',
        'estimated_cost' => 12000.00,
        'status' => 'received',
    ]);
    app()->forgetInstance('shop.id');

    app()->instance('shop.id', $shopB->id);
    $customerB = Customer::create([
        'shop_id' => $shopB->id,
        'name' => 'Customer B',
        'phone' => '0770002222',
    ]);
    $repairB = RepairJob::create([
        'shop_id' => $shopB->id,
        'customer_id' => $customerB->id,
        'job_number' => 'REP-SHOP-B01',
        'brand' => 'Samsung',
        'model' => 'Galaxy S23',
        'fault_description' => 'Shop B repair',
        'estimated_cost' => 15000.00,
        'status' => 'received',
    ]);
    app()->forgetInstance('shop.id');

    Sanctum::actingAs($techA);

    // Tech A querying the list should only see Shop A's repairs
    $listResponse = $this->getJson('/api/v1/repairs');
    $listResponse->assertOk();
    $items = collect($listResponse->json('data'));

    expect($items->pluck('id'))->toContain($repairA->id);
    expect($items->pluck('id'))->not->toContain($repairB->id);

    // Tech A trying to fetch Shop B's repair details directly gets 404
    $directResponse = $this->getJson("/api/v1/repairs/{$repairB->id}");
    $directResponse->assertNotFound();
});
