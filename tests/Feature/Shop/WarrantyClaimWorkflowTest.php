<?php

namespace Tests\Feature\Shop;

use App\Models\Customer;
use App\Models\Outlet;
use App\Models\Platform\Shop;
use App\Models\User;
use App\Models\Warranty;
use App\Models\WarrantyClaim;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WarrantyClaimWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function createShopEnvironment(): array
    {
        $shop = Shop::factory()->create([
            'name' => 'Apex Phone Care',
        ]);

        $manager = User::factory()->create(['shop_id' => $shop->id]);
        $manager->assignRole('Shop Owner');

        $outlet = Outlet::create([
            'shop_id' => $shop->id,
            'name' => 'Main Outlet',
            'code' => 'APX-01',
            'address' => 'Station Road, Galle',
            'is_main' => true,
        ]);

        app()->instance('shop.id', $shop->id);

        $customer = Customer::create([
            'shop_id' => $shop->id,
            'name' => 'Kasun Perera',
            'phone' => '0771234567',
        ]);

        return [$shop, $manager, $outlet, $customer];
    }

    public function test_cannot_file_claim_against_expired_warranty(): void
    {
        [$shop, $manager, $outlet, $customer] = $this->createShopEnvironment();

        // Expired warranty (ended 1 month ago)
        $expiredWarranty = Warranty::create([
            'shop_id' => $shop->id,
            'customer_id' => $customer->id,
            'warranty_number' => 'WAR-EXP-001',
            'warranty_type' => 'shop',
            'imei' => '359999000111222',
            'duration_months' => 6,
            'starts_on' => now()->subMonths(7)->toDateString(),
            'ends_on' => now()->subMonth()->toDateString(),
            'status' => 'active', // Date has passed
            'terms' => '6 Months hardware coverage',
        ]);

        $this->assertFalse($expiredWarranty->isValid());

        // 1. API attempt
        Sanctum::actingAs($manager);
        $apiResponse = $this->postJson("/api/v1/warranties/{$expiredWarranty->id}/claim", [
            'issue_description' => 'Charging port not detecting cable',
            'settlement_type' => 'repair',
        ]);
        $apiResponse->assertStatus(422);

        // 2. Web attempt
        $webResponse = $this->actingAs($manager)->post("/shop/warranties/{$expiredWarranty->id}/claim", [
            'issue_description' => 'Charging port not detecting cable',
            'settlement_type' => 'repair',
        ]);
        $webResponse->assertRedirect();
        $webResponse->assertSessionHas('error');

        // Assert no claim was saved
        $this->assertDatabaseMissing('warranty_claims', [
            'warranty_id' => $expiredWarranty->id,
        ]);
    }

    public function test_cannot_file_claim_against_voided_warranty(): void
    {
        [$shop, $manager, $outlet, $customer] = $this->createShopEnvironment();

        $voidedWarranty = Warranty::create([
            'shop_id' => $shop->id,
            'customer_id' => $customer->id,
            'warranty_number' => 'WAR-VOID-001',
            'warranty_type' => 'shop',
            'imei' => '358888000222333',
            'duration_months' => 12,
            'starts_on' => now()->toDateString(),
            'ends_on' => now()->addMonths(12)->toDateString(),
            'status' => 'voided', // Tampered or cancelled
        ]);

        $this->assertFalse($voidedWarranty->isValid());

        Sanctum::actingAs($manager);
        $response = $this->postJson("/api/v1/warranties/{$voidedWarranty->id}/claim", [
            'issue_description' => 'Display flickering',
        ]);
        $response->assertStatus(422);
    }

    public function test_authorized_staff_can_review_and_approve_claim(): void
    {
        [$shop, $manager, $outlet, $customer] = $this->createShopEnvironment();

        $warranty = Warranty::create([
            'shop_id' => $shop->id,
            'customer_id' => $customer->id,
            'warranty_number' => 'WAR-ACTIVE-001',
            'warranty_type' => 'shop',
            'imei' => '357777000333444',
            'duration_months' => 12,
            'starts_on' => now()->toDateString(),
            'ends_on' => now()->addMonths(12)->toDateString(),
            'status' => 'active',
        ]);

        $claim = WarrantyClaim::create([
            'shop_id' => $shop->id,
            'warranty_id' => $warranty->id,
            'claim_number' => 'CLM-2026-0001',
            'claim_date' => now()->toDateString(),
            'issue_description' => 'Battery draining rapidly within 2 hours',
            'status' => 'pending',
            'settlement_type' => 'repair',
        ]);

        // Web review submission
        $response = $this->actingAs($manager)->post("/shop/warranties/claims/{$claim->id}/review", [
            'status' => 'approved',
            'settlement_type' => 'repair',
            'action_notes' => 'Battery health verified at 68%. Approved for free battery cell replacement under warranty.',
        ]);

        $response->assertRedirect('/shop/warranties');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('warranty_claims', [
            'id' => $claim->id,
            'status' => 'approved',
            'action_notes' => 'Battery health verified at 68%. Approved for free battery cell replacement under warranty.',
        ]);
    }

    public function test_authorized_staff_can_reject_claim_with_inspection_reason(): void
    {
        [$shop, $manager, $outlet, $customer] = $this->createShopEnvironment();

        $warranty = Warranty::create([
            'shop_id' => $shop->id,
            'customer_id' => $customer->id,
            'warranty_number' => 'WAR-REJECT-001',
            'warranty_type' => 'shop',
            'imei' => '356666000444555',
            'duration_months' => 12,
            'starts_on' => now()->toDateString(),
            'ends_on' => now()->addMonths(12)->toDateString(),
            'status' => 'active',
        ]);

        $claim = WarrantyClaim::create([
            'shop_id' => $shop->id,
            'warranty_id' => $warranty->id,
            'claim_number' => 'CLM-2026-0002',
            'claim_date' => now()->toDateString(),
            'issue_description' => 'Touchscreen unresponsive',
            'status' => 'inspecting',
        ]);

        Sanctum::actingAs($manager);
        $response = $this->postJson("/api/v1/warranties/claims/{$claim->id}/review", [
            'status' => 'rejected',
            'settlement_type' => null,
            'action_notes' => 'Inspection revealed red moisture indicator sticker triggered. Liquid ingress violates clause 4 of warranty policy.',
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', 'rejected');

        $this->assertDatabaseHas('warranty_claims', [
            'id' => $claim->id,
            'status' => 'rejected',
        ]);
    }

    public function test_authorized_staff_can_settle_claim_with_replacement(): void
    {
        [$shop, $manager, $outlet, $customer] = $this->createShopEnvironment();

        $warranty = Warranty::create([
            'shop_id' => $shop->id,
            'customer_id' => $customer->id,
            'warranty_number' => 'WAR-SETTLE-001',
            'warranty_type' => 'shop',
            'imei' => '355555000555666',
            'duration_months' => 12,
            'starts_on' => now()->toDateString(),
            'ends_on' => now()->addMonths(12)->toDateString(),
            'status' => 'active',
        ]);

        $claim = WarrantyClaim::create([
            'shop_id' => $shop->id,
            'warranty_id' => $warranty->id,
            'claim_number' => 'CLM-2026-0003',
            'claim_date' => now()->toDateString(),
            'issue_description' => 'Motherboard short circuit defect upon first boot',
            'status' => 'approved',
        ]);

        Sanctum::actingAs($manager);
        $response = $this->postJson("/api/v1/warranties/claims/{$claim->id}/review", [
            'status' => 'settled',
            'settlement_type' => 'replacement',
            'action_notes' => 'Issued brand new replacement handset. Original warranty fulfilled.',
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', 'settled');

        $this->assertEquals('settled', $warranty->fresh()->status);
    }

    public function test_warranty_tracks_full_claims_history_and_lookup(): void
    {
        [$shop, $manager, $outlet, $customer] = $this->createShopEnvironment();

        $warranty = Warranty::create([
            'shop_id' => $shop->id,
            'customer_id' => $customer->id,
            'warranty_number' => 'WAR-HIST-001',
            'warranty_type' => 'shop',
            'imei' => '354444000666777',
            'duration_months' => 12,
            'starts_on' => now()->subMonths(2)->toDateString(),
            'ends_on' => now()->addMonths(10)->toDateString(),
            'status' => 'active',
        ]);

        // File Claim 1 (Resolved)
        WarrantyClaim::create([
            'shop_id' => $shop->id,
            'warranty_id' => $warranty->id,
            'claim_number' => 'CLM-01',
            'claim_date' => now()->subMonth()->toDateString(),
            'issue_description' => 'Speaker crackling sound',
            'status' => 'settled',
            'settlement_type' => 'repair',
            'action_notes' => 'Cleaned speaker grill and adjusted connection',
        ]);

        // File Claim 2 (In progress)
        WarrantyClaim::create([
            'shop_id' => $shop->id,
            'warranty_id' => $warranty->id,
            'claim_number' => 'CLM-02',
            'claim_date' => now()->toDateString(),
            'issue_description' => 'Front camera lens blurry',
            'status' => 'inspecting',
            'settlement_type' => 'repair',
            'action_notes' => 'Under technician inspection',
        ]);

        // Verify warranty model has 2 claims in history
        $this->assertEquals(2, $warranty->claims()->count());

        // Verify API status lookup returns claims count
        Sanctum::actingAs($manager);
        $lookupResponse = $this->getJson('/api/v1/warranties/verify?query=354444000666777');
        $lookupResponse->assertOk()
            ->assertJsonPath('claims_count', 2)
            ->assertJsonPath('status', 'active');
    }
}

