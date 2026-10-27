<?php

use App\Mail\RepairPickupReadyMail;
use App\Models\Customer;
use App\Models\Platform\Shop;
use App\Models\RepairJob;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    $this->seed(DatabaseSeeder::class);
});

test('customer can approve repair estimate from portal transitioning status to in_repair', function (): void {
    $shop = Shop::factory()->create(['name' => 'Cellular Clinic']);
    $user = User::factory()->create([
        'shop_id' => $shop->id,
        'email' => 'kusal.perera@example.com',
    ]);
    $user->assignRole('Customer');

    app()->instance('shop.id', $shop->id);
    $customer = Customer::create([
        'shop_id' => $shop->id,
        'name' => 'Kusal Perera',
        'email' => 'kusal.perera@example.com',
        'phone' => '0773344556',
    ]);

    $repair = RepairJob::create([
        'shop_id' => $shop->id,
        'customer_id' => $customer->id,
        'job_number' => 'REP-EST-001',
        'brand' => 'Apple',
        'model' => 'iPhone 13',
        'fault_description' => 'Water damage diagnosis needed',
        'estimated_cost' => 22000.00,
        'status' => 'waiting_for_approval',
    ]);
    app()->forgetInstance('shop.id');

    $response = $this->actingAs($user)->post("/portal/repairs/{$repair->id}/estimate/approve", [
        'notes' => 'Please proceed with OEM screen replacement.',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $repair->refresh();
    expect($repair->status)->toBe('in_repair');
    expect($repair->technician_notes)->toContain('Customer Approved Estimate');
    expect($repair->technician_notes)->toContain('OEM screen replacement');
});

test('customer cannot approve another customers repair estimate and receives 403', function (): void {
    $shop = Shop::factory()->create();

    $userA = User::factory()->create(['shop_id' => $shop->id, 'email' => 'userA@example.com']);
    $userA->assignRole('Customer');

    app()->instance('shop.id', $shop->id);
    $custA = Customer::create(['shop_id' => $shop->id, 'name' => 'User A', 'email' => 'userA@example.com', 'phone' => '0711111111']);
    $custB = Customer::create(['shop_id' => $shop->id, 'name' => 'User B', 'email' => 'userB@example.com', 'phone' => '0722222222']);

    $repairB = RepairJob::create([
        'shop_id' => $shop->id,
        'customer_id' => $custB->id,
        'job_number' => 'REP-EST-B01',
        'brand' => 'Samsung',
        'model' => 'S22',
        'fault_description' => 'Camera replacement',
        'estimated_cost' => 15000.00,
        'status' => 'waiting_for_approval',
    ]);
    app()->forgetInstance('shop.id');

    // User A attempting to approve User B's repair is rejected with 403 Forbidden
    $response = $this->actingAs($userA)->post("/portal/repairs/{$repairB->id}/estimate/approve");
    $response->assertForbidden();

    expect($repairB->fresh()->status)->toBe('waiting_for_approval');
});

test('public online tracking allows direct customer estimate approval', function (): void {
    $shop = Shop::factory()->create();

    app()->instance('shop.id', $shop->id);
    $customer = Customer::create([
        'shop_id' => $shop->id,
        'name' => 'Online Customer',
        'email' => 'online@example.com',
        'phone' => '0779998877',
    ]);

    $repair = RepairJob::create([
        'shop_id' => $shop->id,
        'customer_id' => $customer->id,
        'job_number' => 'REP-PUB-APP01',
        'brand' => 'Google',
        'model' => 'Pixel 6',
        'fault_description' => 'Charging port repair',
        'estimated_cost' => 6500.00,
        'status' => 'waiting_for_approval',
    ]);
    app()->forgetInstance('shop.id');

    // Customer visits public link and clicks Approve
    $response = $this->post('/track-repair/approve', [
        'tracking_id' => $repair->job_number,
    ]);

    $response->assertRedirect(route('public.repair-track', ['tracking_id' => $repair->job_number]));
    $response->assertSessionHas('success');

    $repair->refresh();
    expect($repair->status)->toBe('in_repair');
    expect($repair->technician_notes)->toContain('Customer Approved Estimate Online');
});

test('transitioning repair to ready_for_collection dispatches email notification to customer', function (): void {
    Mail::fake();

    $shop = Shop::factory()->create(['name' => 'QuickFix Flagship', 'phone' => '0112345678']);
    $tech = User::factory()->create(['shop_id' => $shop->id]);
    $tech->assignRole('Technician');

    app()->instance('shop.id', $shop->id);
    $customer = Customer::create([
        'shop_id' => $shop->id,
        'name' => 'Amila Perera',
        'email' => 'amila.p@example.com',
        'phone' => '0771122334',
    ]);

    $repair = RepairJob::create([
        'shop_id' => $shop->id,
        'customer_id' => $customer->id,
        'job_number' => 'REP-PICKUP-001',
        'brand' => 'Apple',
        'model' => 'iPhone 14 Pro',
        'fault_description' => 'Rear Glass Replacement',
        'estimated_cost' => 19500.00,
        'final_cost' => 19500.00,
        'advance_amount' => 5000.00,
        'status' => 'quality_check',
    ]);
    app()->forgetInstance('shop.id');

    // Technician marks repair as ready_for_collection
    $response = $this->actingAs($tech)->patch("/shop/repairs/{$repair->id}/status", [
        'status' => 'ready_for_collection',
        'technician_notes' => 'Passed full hardware test diagnostics',
        'final_cost' => 19500.00,
    ]);

    $response->assertRedirect('/shop/repairs');

    // Verify email was queued to customer's email address
    Mail::assertQueued(RepairPickupReadyMail::class, function ($mail) use ($customer, $repair) {
        return $mail->hasTo($customer->email) &&
               $mail->repair->id === $repair->id;
    });

    // Verify mail envelope subject contains ticket number and shop name
    $mailable = new RepairPickupReadyMail($repair->fresh());
    expect($mailable->envelope()->subject)->toContain('REP-PICKUP-001');
    expect($mailable->envelope()->subject)->toContain('QuickFix Flagship');
});

