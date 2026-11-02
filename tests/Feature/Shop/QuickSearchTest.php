<?php

namespace Tests\Feature\Shop;

use App\Models\Customer;
use App\Models\PhoneDevice;
use App\Models\Platform\Shop;
use App\Models\RepairJob;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuickSearchTest extends TestCase
{
    use RefreshDatabase;

    private Shop $shop;
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->shop = Shop::factory()->create([
            'name' => 'Apex Mobile Hub',
        ]);

        $this->owner = User::factory()->create([
            'shop_id' => $this->shop->id,
            'email' => 'owner@apex.test',
        ]);
        $this->owner->assignRole('Shop Owner');

        app()->instance('shop.id', $this->shop->id);
    }

    public function test_quick_search_returns_empty_when_query_too_short(): void
    {
        $response = $this->actingAs($this->owner)
            ->getJson(route('shop.quick-search', ['q' => 'a']));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'total_count' => 0,
            ]);
    }

    public function test_quick_search_finds_repairs_phones_sales_and_customers(): void
    {
        $customer = Customer::create([
            'shop_id' => $this->shop->id,
            'name' => 'Nimal Siriwardena',
            'phone' => '0779988776',
            'nic' => '199012345678',
        ]);

        $phone = PhoneDevice::create([
            'shop_id' => $this->shop->id,
            'brand' => 'Apple',
            'model' => 'iPhone 14 Pro',
            'storage' => '128GB',
            'imei_1' => '359876543210987',
            'selling_price' => 245000.00,
            'status' => 'in_stock',
        ]);

        $repair = RepairJob::create([
            'shop_id' => $this->shop->id,
            'customer_id' => $customer->id,
            'job_number' => 'REP-9988',
            'brand' => 'Apple',
            'model' => 'iPhone 13',
            'imei' => '351234567890123',
            'customer_nic' => '199012345678',
            'fault_description' => 'Battery replacement required',
            'status' => 'in_repair',
            'estimated_cost' => 12500.00,
            'advance_amount' => 5000.00,
        ]);

        $sale = Sale::create([
            'shop_id' => $this->shop->id,
            'user_id' => $this->owner->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-2026-999',
            'sale_date' => now()->toDateString(),
            'total_amount' => 15000.00,
            'payment_method' => 'cash',
        ]);

        // Search by customer phone
        $resPhone = $this->actingAs($this->owner)
            ->getJson(route('shop.quick-search', ['q' => '0779988776']));

        $resPhone->assertOk()
            ->assertJsonPath('success', true);

        // Search by IMEI
        $resImei = $this->actingAs($this->owner)
            ->getJson(route('shop.quick-search', ['q' => '359876543210987']));

        $resImei->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.phones.0.title', 'Apple iPhone 14 Pro');

        // Search by Job #
        $resJob = $this->actingAs($this->owner)
            ->getJson(route('shop.quick-search', ['q' => 'REP-9988']));

        $resJob->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.repairs.0.job_number', 'REP-9988');
    }

    public function test_quick_track_repair_returns_live_tracking_details(): void
    {
        $customer = Customer::create([
            'shop_id' => $this->shop->id,
            'name' => 'Sunil Perera',
            'phone' => '0712345678',
            'nic' => '198522334455',
        ]);

        $repair = RepairJob::create([
            'shop_id' => $this->shop->id,
            'customer_id' => $customer->id,
            'job_number' => 'REP-TRACK-01',
            'brand' => 'Samsung',
            'model' => 'Galaxy S23',
            'imei' => '355554444333221',
            'fault_description' => 'Screen cracked, lines on display',
            'status' => 'ready_for_collection',
            'estimated_cost' => 35000.00,
            'advance_amount' => 10000.00,
            'technician_notes' => 'Original AMOLED replaced and tested.',
        ]);

        $res = $this->actingAs($this->owner)
            ->getJson(route('shop.quick-search.track', ['tracking_id' => 'REP-TRACK-01']));

        $res->assertOk()
            ->assertJsonPath('found', true)
            ->assertJsonPath('job.job_number', 'REP-TRACK-01')
            ->assertJsonPath('job.device', 'Samsung Galaxy S23')
            ->assertJsonPath('job.status', 'ready_for_collection')
            ->assertJsonPath('job.status_label', 'Ready for Pickup')
            ->assertJsonPath('job.balance_due', '25,000.00');
    }

    public function test_shop_layout_renders_quick_search_modal_trigger(): void
    {
        $response = $this->actingAs($this->owner)
            ->get(route('shop.dashboard'));

        $response->assertOk()
            ->assertSee('openGlobalSearchModal')
            ->assertSee('quickSearchModal')
            ->assertSee('globalSearchInput');
    }
}
