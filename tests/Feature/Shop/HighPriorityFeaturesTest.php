<?php

namespace Tests\Feature\Shop;

use App\Models\Customer;
use App\Models\Outlet;
use App\Models\PhoneDevice;
use App\Models\PhoneTradeIn;
use App\Models\Platform\Shop;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\Sale;
use App\Models\StockTransfer;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HighPriorityFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function createShopEnvironment(): array
    {
        $shop = Shop::factory()->create(['name' => 'Cellular Point']);
        $manager = User::factory()->create(['shop_id' => $shop->id]);
        $manager->assignRole('Shop Owner');

        $outletA = Outlet::create([
            'shop_id' => $shop->id,
            'name' => 'Colombo Main Branch',
            'code' => 'CMB-01',
            'address' => 'Galle Road, Colombo 03',
            'is_main' => true,
        ]);

        $outletB = Outlet::create([
            'shop_id' => $shop->id,
            'name' => 'Kandy City Branch',
            'code' => 'KDY-01',
            'address' => 'Dalada Veediya, Kandy',
            'is_main' => false,
        ]);

        app()->instance('shop.id', $shop->id);

        return [$shop, $manager, $outletA, $outletB];
    }

    public function test_used_phone_buying_and_trade_in_creates_inventory_and_trade_in_record(): void
    {
        [$shop, $manager, $outletA] = $this->createShopEnvironment();

        $res = $this->actingAs($manager)->post('/shop/trade-ins', [
            'customer_name' => 'Sunil Perera',
            'customer_phone' => '0771234567',
            'customer_nic' => '199012345678',
            'outlet_id' => $outletA->id,
            'brand' => 'Apple',
            'model' => 'iPhone 12 Pro',
            'storage' => '128GB',
            'ram' => '6GB',
            'color' => 'Pacific Blue',
            'imei_1' => '359876543210999',
            'imei_2' => '359876543210998',
            'valuation_amount' => 110000.00,
            'resale_price' => 135000.00,
            'payout_type' => 'cash',
            'screen_condition' => 'Minor Scratches',
            'battery_health' => '84%',
            'cameras_working' => 1,
            'biometrics_working' => 1,
            'icloud_unlocked' => 1,
            'notes' => 'Customer upgrading to iPhone 15',
        ]);

        $res->assertRedirect(route('shop.trade-ins.index'));

        // Assert Phone Device was created in inventory
        $this->assertDatabaseHas('phone_devices', [
            'shop_id' => $shop->id,
            'outlet_id' => $outletA->id,
            'brand' => 'Apple',
            'model' => 'iPhone 12 Pro',
            'imei_1' => '359876543210999',
            'condition' => 'Used',
            'status' => 'in_stock',
            'purchase_cost' => 110000.00,
            'selling_price' => 135000.00,
        ]);

        // Assert Trade-in Record was saved
        $tradeIn = PhoneTradeIn::withoutGlobalScopes()->where('shop_id', $shop->id)->first();
        $this->assertNotNull($tradeIn);
        $this->assertEquals('Sunil Perera', $tradeIn->customer_name);
        $this->assertEquals(110000.00, (float) $tradeIn->valuation_amount);
        $this->assertEquals('cash', $tradeIn->payout_type);
        $this->assertEquals('84%', $tradeIn->condition_checklist['battery_health']);
    }

    public function test_duplicate_imei_trade_in_is_rejected(): void
    {
        [$shop, $manager, $outletA] = $this->createShopEnvironment();

        PhoneDevice::create([
            'shop_id' => $shop->id,
            'outlet_id' => $outletA->id,
            'brand' => 'Apple',
            'model' => 'iPhone 13',
            'color' => 'Black',
            'imei_1' => '359999999999999',
            'condition' => 'New',
            'status' => 'In Stock',
            'purchase_cost' => 150000.00,
            'selling_price' => 180000.00,
        ]);

        $res = $this->actingAs($manager)->post('/shop/trade-ins', [
            'customer_name' => 'Nimal Silva',
            'customer_phone' => '0772223344',
            'brand' => 'Apple',
            'model' => 'iPhone 13',
            'imei_1' => '359999999999999',
            'valuation_amount' => 120000.00,
            'payout_type' => 'cash',
        ]);

        $res->assertSessionHas('error');
    }

    public function test_branch_stock_transfer_dispatch_and_receive(): void
    {
        [$shop, $manager, $outletA, $outletB] = $this->createShopEnvironment();

        $phone = PhoneDevice::create([
            'shop_id' => $shop->id,
            'outlet_id' => $outletA->id,
            'brand' => 'Samsung',
            'model' => 'Galaxy S23',
            'color' => 'Phantom Black',
            'imei_1' => '351111111111111',
            'condition' => 'New',
            'status' => 'In Stock',
            'purchase_cost' => 200000.00,
            'selling_price' => 230000.00,
        ]);

        // 1. Dispatch Transfer from Outlet A to Outlet B
        $res = $this->actingAs($manager)->post('/shop/transfers', [
            'from_outlet_id' => $outletA->id,
            'to_outlet_id' => $outletB->id,
            'item_type' => 'phone',
            'phone_device_id' => $phone->id,
            'notes' => 'Customer reservation transfer to Kandy',
        ]);

        $res->assertRedirect(route('shop.transfers.index'));

        $transfer = StockTransfer::withoutGlobalScopes()->where('shop_id', $shop->id)->first();
        $this->assertEquals('in_transit', $transfer->status);

        // Verify phone status is in_transit
        $phone->refresh();
        $this->assertEquals('in_transit', $phone->status);

        // 2. Receive and confirm Transfer at Outlet B
        $receiveRes = $this->actingAs($manager)->patch("/shop/transfers/{$transfer->id}/receive");
        $receiveRes->assertRedirect(route('shop.transfers.index'));

        $transfer->refresh();
        $this->assertEquals('received', $transfer->status);

        // Verify phone is now in_stock at Outlet B
        $phone->refresh();
        $this->assertEquals('in_stock', $phone->status);
        $this->assertEquals($outletB->id, $phone->outlet_id);
    }

    public function test_stock_transfer_cancel_reverts_item_status(): void
    {
        [$shop, $manager, $outletA, $outletB] = $this->createShopEnvironment();

        $phone = PhoneDevice::create([
            'shop_id' => $shop->id,
            'outlet_id' => $outletA->id,
            'brand' => 'Google',
            'model' => 'Pixel 8',
            'color' => 'Obsidian',
            'imei_1' => '352222222222222',
            'condition' => 'New',
            'status' => 'In Stock',
            'purchase_cost' => 170000.00,
            'selling_price' => 200000.00,
        ]);

        $this->actingAs($manager)->post('/shop/transfers', [
            'from_outlet_id' => $outletA->id,
            'to_outlet_id' => $outletB->id,
            'item_type' => 'phone',
            'phone_device_id' => $phone->id,
        ]);

        $transfer = StockTransfer::withoutGlobalScopes()->where('shop_id', $shop->id)->first();

        // Cancel transfer
        $this->actingAs($manager)->patch("/shop/transfers/{$transfer->id}/cancel");

        $transfer->refresh();
        $this->assertEquals('cancelled', $transfer->status);

        $phone->refresh();
        $this->assertEquals('in_stock', $phone->status);
        $this->assertEquals($outletA->id, $phone->outlet_id);
    }

    public function test_quotation_creation_and_conversion_to_invoice(): void
    {
        [$shop, $manager, $outletA] = $this->createShopEnvironment();

        $customer = Customer::create([
            'shop_id' => $shop->id,
            'name' => 'Nalaka Bandara',
            'phone' => '0775556677',
        ]);

        $product = Product::create([
            'shop_id' => $shop->id,
            'code' => 'CHG-30W',
            'name' => 'Fast USB-C Charger 30W',
            'category_id' => null,
            'brand_id' => null,
            'cost_price' => 2500.00,
            'retail_price' => 4500.00,
            'stock_quantity' => 10,
        ]);

        // 1. Create Quotation for 2 chargers: Subtotal 9000 - Discount 500 = 8500
        $res = $this->actingAs($manager)->post('/shop/quotations', [
            'customer_name' => 'Nalaka Bandara',
            'customer_phone' => '0775556677',
            'customer_id' => $customer->id,
            'valid_until' => now()->addDays(14)->toDateString(),
            'discount' => 500.00,
            'items' => [
                [
                    'item_type' => 'product',
                    'product_id' => $product->id,
                    'item_name' => 'Fast USB-C Charger 30W',
                    'quantity' => 2,
                    'unit_price' => 4500.00,
                ],
            ],
        ]);

        $res->assertRedirect(route('shop.quotations.index'));

        $quotation = Quotation::withoutGlobalScopes()->where('shop_id', $shop->id)->first();
        $this->assertNotNull($quotation);
        $this->assertEquals('pending', $quotation->status);
        $this->assertEquals(8500.00, (float) $quotation->total_amount);

        // Product stock should still be 10 (not decremented while quotation)
        $this->assertEquals(10, Product::withoutGlobalScopes()->find($product->id)->stock_quantity);

        // 2. Convert Quotation to Sale
        $convertRes = $this->actingAs($manager)->post("/shop/quotations/{$quotation->id}/convert", [
            'payment_method' => 'card',
        ]);

        $quotation = Quotation::withoutGlobalScopes()->find($quotation->id);
        $this->assertEquals('converted', $quotation->status);
        $this->assertNotNull($quotation->converted_sale_id);

        // Product stock now decremented by 2 -> 8
        $this->assertEquals(8, Product::withoutGlobalScopes()->find($product->id)->stock_quantity);

        // Invoice created with matching total
        $sale = Sale::withoutGlobalScopes()->find($quotation->converted_sale_id);
        $this->assertEquals(8500.00, (float) $sale->total_amount);
        $this->assertEquals(8500.00, (float) $sale->paid_amount);
        $this->assertEquals('completed', $sale->status);
    }
}
