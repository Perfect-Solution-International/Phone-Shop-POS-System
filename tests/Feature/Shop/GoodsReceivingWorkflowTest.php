<?php

namespace Tests\Feature\Shop;

use App\Models\Grn;
use App\Models\GrnItem;
use App\Models\Outlet;
use App\Models\PhoneDevice;
use App\Models\Platform\Shop;
use App\Models\Product;
use App\Models\SparePart;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class GoodsReceivingWorkflowTest extends TestCase
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
            'name' => 'Colombo Tech Hub',
        ]);

        $owner = User::factory()->create(['shop_id' => $shop->id]);
        $owner->assignRole('Shop Owner');

        $outlet = Outlet::create([
            'shop_id' => $shop->id,
            'name' => 'Main Outlet',
            'code' => 'HUB-01',
            'address' => 'High Level Road, Nugegoda',
            'is_main' => true,
        ]);

        app()->instance('shop.id', $shop->id);

        $supplier = Supplier::create([
            'shop_id' => $shop->id,
            'name' => 'Lanka Telecom Supplies',
            'company_name' => 'Lanka Telecom Ltd',
            'balance' => 0.00,
        ]);

        return [$shop, $owner, $outlet, $supplier];
    }

    public function test_can_receive_goods_with_atomic_transaction_and_stock_increment(): void
    {
        [$shop, $owner, $outlet, $supplier] = $this->createShopEnvironment();

        $product = Product::create([
            'shop_id' => $shop->id,
            'name' => 'Original 25W Fast Charger',
            'code' => 'CHG-25W',
            'cost_price' => 2000.00,
            'retail_price' => 3800.00,
            'stock_quantity' => 10,
        ]);

        $sparePart = SparePart::create([
            'shop_id' => $shop->id,
            'name' => 'OLED Screen Assembly A54',
            'part_number' => 'DSP-SAM-A54',
            'cost_price' => 12500.00,
            'selling_price' => 17000.00,
            'stock_quantity' => 4,
        ]);

        $payload = [
            'supplier_id' => $supplier->id,
            'supplier_invoice_ref' => 'INV-LTS-1001',
            'received_date' => now()->toDateString(),
            'notes' => 'Bulk accessories batch delivery',
            'items' => [
                [
                    'item_type' => 'product',
                    'product_id' => $product->id,
                    'item_name' => 'Original 25W Fast Charger',
                    'quantity' => 15,
                    'unit_cost' => 1950.00,
                ],
                [
                    'item_type' => 'spare_part',
                    'spare_part_id' => $sparePart->id,
                    'item_name' => 'OLED Screen Assembly A54',
                    'quantity' => 6,
                    'unit_cost' => 12000.00,
                ],
            ],
        ];

        $response = $this->actingAs($owner)->post('/shop/inventory/grns', $payload);
        $response->assertRedirect();

        app()->instance('shop.id', $shop->id);

        // 1. Assert GRN Header
        $grn = Grn::where('shop_id', $shop->id)->where('supplier_invoice_ref', 'INV-LTS-1001')->first();
        $this->assertNotNull($grn);
        $this->assertStringStartsWith('GRN-', $grn->grn_number);
        $this->assertEquals('completed', $grn->status);
        // Total cost: (15 * 1950) + (6 * 12000) = 29,250 + 72,000 = 101,250
        $this->assertEquals(101250.00, (float) $grn->total_cost);

        // 2. Assert Stock Increments
        $product->refresh();
        $this->assertEquals(25, $product->stock_quantity); // 10 + 15

        $sparePart->refresh();
        $this->assertEquals(10, $sparePart->stock_quantity); // 4 + 6

        // 3. Assert Stock Movement Ledger Audit
        $this->assertDatabaseHas('stock_movements', [
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'type' => 'purchase',
            'quantity' => 15,
            'reference_type' => 'GRN',
            'reference_id' => $grn->id,
        ]);
    }

    public function test_duplicate_supplier_invoice_ref_prevented_from_receiving_twice(): void
    {
        [$shop, $owner, $outlet, $supplier] = $this->createShopEnvironment();

        $product = Product::create([
            'shop_id' => $shop->id,
            'name' => 'Type-C Braided Cable',
            'code' => 'CBL-TC',
            'cost_price' => 400.00,
            'retail_price' => 900.00,
            'stock_quantity' => 20,
        ]);

        $payload = [
            'supplier_id' => $supplier->id,
            'supplier_invoice_ref' => 'DELIVERY-NOTE-9922',
            'received_date' => now()->toDateString(),
            'items' => [
                [
                    'item_type' => 'product',
                    'product_id' => $product->id,
                    'item_name' => 'Type-C Braided Cable',
                    'quantity' => 10,
                    'unit_cost' => 380.00,
                ],
            ],
        ];

        // 1st Receiving: Success
        $firstResponse = $this->actingAs($owner)->post('/shop/inventory/grns', $payload);
        $firstResponse->assertRedirect();

        app()->instance('shop.id', $shop->id);
        $product->refresh();
        $this->assertEquals(30, $product->stock_quantity); // 20 + 10

        // 2nd Receiving with exact same Supplier Invoice Ref: Must be rejected to prevent duplicate stock
        $secondResponse = $this->actingAs($owner)->post('/shop/inventory/grns', $payload);
        $secondResponse->assertSessionHasErrors(['supplier_invoice_ref']);

        app()->instance('shop.id', $shop->id);
        // Assert stock did NOT increment twice
        $product->refresh();
        $this->assertEquals(30, $product->stock_quantity);

        // Assert only 1 GRN was created
        $this->assertEquals(1, Grn::where('shop_id', $shop->id)->where('supplier_invoice_ref', 'DELIVERY-NOTE-9922')->count());
    }

    public function test_receiving_serialized_phones_creates_phone_devices_and_stock_ledger(): void
    {
        [$shop, $owner, $outlet, $supplier] = $this->createShopEnvironment();

        $payload = [
            'supplier_id' => $supplier->id,
            'supplier_invoice_ref' => 'PHONE-DELIVERY-01',
            'received_date' => now()->toDateString(),
            'items' => [
                [
                    'item_type' => 'phone',
                    'item_name' => 'Samsung Galaxy A35',
                    'brand' => 'Samsung',
                    'model' => 'Galaxy A35 5G',
                    'imei_1' => '359876543210123',
                    'imei_2' => '359876543210124',
                    'quantity' => 1,
                    'unit_cost' => 84000.00,
                    'selling_price' => 98000.00,
                ],
            ],
        ];

        $response = $this->actingAs($owner)->post('/shop/inventory/grns', $payload);
        $response->assertRedirect();

        app()->instance('shop.id', $shop->id);

        // Verify PhoneDevice created in inventory
        $phone = PhoneDevice::where('shop_id', $shop->id)->where('imei_1', '359876543210123')->first();
        $this->assertNotNull($phone);
        $this->assertEquals('in_stock', $phone->status);
        $this->assertEquals($supplier->id, $phone->supplier_id);
        $this->assertEquals('359876543210124', $phone->imei_2);

        // Verify Stock Movement recorded for the phone
        $this->assertDatabaseHas('stock_movements', [
            'shop_id' => $shop->id,
            'phone_device_id' => $phone->id,
            'type' => 'purchase',
            'quantity' => 1,
            'reference_type' => 'GRN',
        ]);
    }

    public function test_grn_voucher_viewable_and_printable(): void
    {
        [$shop, $owner, $outlet, $supplier] = $this->createShopEnvironment();

        $grn = Grn::create([
            'shop_id' => $shop->id,
            'supplier_id' => $supplier->id,
            'received_by_id' => $owner->id,
            'grn_number' => 'GRN-20261026-VIEW1',
            'supplier_invoice_ref' => 'INV-LTS-999',
            'received_date' => now()->toDateString(),
            'status' => 'completed',
            'total_cost' => 15000.00,
            'notes' => 'Checked and verified by receiving manager',
        ]);

        GrnItem::create([
            'shop_id' => $shop->id,
            'grn_id' => $grn->id,
            'item_type' => 'product',
            'item_name' => 'Screen Protector 9D',
            'quantity_received' => 50,
            'unit_cost' => 300.00,
            'subtotal' => 15000.00,
        ]);

        $response = $this->actingAs($owner)->get("/shop/inventory/grns/{$grn->id}");
        $response->assertOk()
            ->assertSee('GOODS RECEIVED NOTE (GRN)')
            ->assertSee('GRN-20261026-VIEW1')
            ->assertSee('INV-LTS-999')
            ->assertSee('Screen Protector 9D')
            ->assertSee('15,000.00');
    }

    public function test_tenant_isolation_protects_grn_records(): void
    {
        [$shopA, $ownerA] = $this->createShopEnvironment();

        $shopB = Shop::factory()->create(['name' => 'Kandy Mobile Mart']);
        $ownerB = User::factory()->create(['shop_id' => $shopB->id]);
        $ownerB->assignRole('Shop Owner');

        app()->instance('shop.id', $shopA->id);
        $supplierA = Supplier::create(['shop_id' => $shopA->id, 'name' => 'Supplier Shop A', 'balance' => 0.00]);
        $grnA = Grn::create([
            'shop_id' => $shopA->id,
            'supplier_id' => $supplierA->id,
            'grn_number' => 'GRN-SHOPA-001',
            'received_date' => now()->toDateString(),
            'total_cost' => 5000.00,
        ]);
        app()->forgetInstance('shop.id');

        // Owner B attempts to view Shop A's GRN
        $response = $this->actingAs($ownerB)->get("/shop/inventory/grns/{$grnA->id}");
        $response->assertStatus(404);
    }

    public function test_transaction_rollbacks_cleanly_if_line_item_fails(): void
    {
        [$shop, $owner, $outlet, $supplier] = $this->createShopEnvironment();

        $product = Product::create([
            'shop_id' => $shop->id,
            'name' => 'Original Battery BM4Y',
            'code' => 'BAT-BM4Y',
            'cost_price' => 2500.00,
            'retail_price' => 4500.00,
            'stock_quantity' => 5,
        ]);

        $invalidPayload = [
            'supplier_id' => $supplier->id,
            'supplier_invoice_ref' => 'FAIL-NOTE-01',
            'received_date' => now()->toDateString(),
            'items' => [
                [
                    'item_type' => 'product',
                    'product_id' => $product->id,
                    'item_name' => 'Original Battery BM4Y',
                    'quantity' => 10,
                    'unit_cost' => 2400.00,
                ],
                [
                    'item_type' => 'product',
                    'product_id' => 999999, // Non-existent product ID triggers validation error
                    'item_name' => 'Ghost Product',
                    'quantity' => 2,
                    'unit_cost' => 1000.00,
                ],
            ],
        ];

        $response = $this->actingAs($owner)->post('/shop/inventory/grns', $invalidPayload);
        $response->assertSessionHasErrors();

        app()->instance('shop.id', $shop->id);
        // Verify product stock did NOT increment (rollback successful)
        $product->refresh();
        $this->assertEquals(5, $product->stock_quantity);

        // Verify no orphaned GRN was created
        $this->assertDatabaseMissing('grns', [
            'shop_id' => $shop->id,
            'supplier_invoice_ref' => 'FAIL-NOTE-01',
        ]);
    }
}

