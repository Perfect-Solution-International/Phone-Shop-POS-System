<?php

namespace Tests\Feature\Shop;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\PhoneDevice;
use App\Models\Platform\Shop;
use App\Models\Platform\Subscription;
use App\Models\Platform\SubscriptionPlan;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\RepairJob;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\SaleReturn;
use App\Models\SparePart;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FullOperationsSuiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    private function createShopEnvironment(): array
    {
        $shop = Shop::factory()->create([
            'name' => 'TechCity Cellular',
            'address' => '500 Galle Rd, Colombo',
            'phone' => '0112345678',
            'email' => 'sales@techcity.lk',
        ]);

        $owner = User::factory()->create([
            'shop_id' => $shop->id,
            'email' => 'owner@techcity.lk',
        ]);
        $owner->assignRole('Shop Manager');

        app()->instance('shop.id', $shop->id);

        return [$shop, $owner];
    }

    public function test_supplier_management_crud(): void
    {
        [$shop, $owner] = $this->createShopEnvironment();

        // 1. View suppliers
        $res = $this->actingAs($owner)->get('/shop/suppliers');
        $res->assertOk()->assertViewIs('shop.suppliers.index');

        // 2. Create supplier
        $storeRes = $this->actingAs($owner)->post('/shop/suppliers', [
            'name' => 'Apex Distributors',
            'company_name' => 'Apex Cellular Ltd',
            'phone' => '0779988776',
            'email' => 'apex@example.com',
            'address' => '100 Main Street, Colombo 11',
            'balance' => 25000.00,
        ]);
        $storeRes->assertRedirect(route('shop.suppliers'));

        $this->assertDatabaseHas('suppliers', [
            'shop_id' => $shop->id,
            'name' => 'Apex Distributors',
            'company_name' => 'Apex Cellular Ltd',
            'balance' => 25000.00,
        ]);

        $supplier = Supplier::withoutGlobalScopes()->where('shop_id', $shop->id)->first();

        // 3. Update supplier
        $updateRes = $this->actingAs($owner)->put("/shop/suppliers/{$supplier->id}", [
            'name' => 'Apex Distributors Worldwide',
            'company_name' => 'Apex Cellular Ltd',
            'phone' => '0779988776',
            'email' => 'apex@example.com',
            'address' => '100 Main Street, Colombo 11',
            'balance' => 20000.00,
        ]);
        $updateRes->assertRedirect(route('shop.suppliers'));

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => 'Apex Distributors Worldwide',
            'balance' => 20000.00,
        ]);
    }

    public function test_inbound_stock_purchase_increments_inventory_and_logs_movement(): void
    {
        [$shop, $owner] = $this->createShopEnvironment();

        $supplier = Supplier::create([
            'shop_id' => $shop->id,
            'name' => 'Cellular Importers',
            'balance' => 0.00,
        ]);

        $product = Product::create([
            'shop_id' => $shop->id,
            'name' => '20W USB-C Fast Charger',
            'code' => 'CHG-20W',
            'cost_price' => 1500.00,
            'retail_price' => 3000.00,
            'stock_quantity' => 10,
        ]);

        // Record purchase: 15 chargers @ 1400 each = 21000 total. Paid 15000 now.
        $res = $this->actingAs($owner)->post('/shop/purchases', [
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->toDateString(),
            'paid_amount' => 15000.00,
            'notes' => 'Invoice # 99881',
            'items' => [
                [
                    'item_type' => 'product',
                    'product_id' => $product->id,
                    'item_name' => '20W USB-C Fast Charger',
                    'quantity' => 15,
                    'unit_cost' => 1400.00,
                ],
            ],
        ]);

        $res->assertRedirect(route('shop.purchases'));

        // Product stock should increment from 10 to 25
        $product->refresh();
        $this->assertEquals(25, $product->stock_quantity);

        // Supplier balance should reflect unpaid 6,000 (21,000 - 15,000)
        $supplier->refresh();
        $this->assertEquals(6000.00, (float) $supplier->balance);

        // Stock movement audit should exist
        $this->assertDatabaseHas('stock_movements', [
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'type' => 'purchase',
            'quantity' => 15,
        ]);
    }

    public function test_expense_tracking_and_cashbook(): void
    {
        [$shop, $owner] = $this->createShopEnvironment();

        // 1. Record expense
        $res = $this->actingAs($owner)->post('/shop/expenses', [
            'category' => 'electricity',
            'title' => 'CEB Store Bill',
            'amount' => 18500.00,
            'expense_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'notes' => 'October electricity bill',
        ]);
        $res->assertRedirect(route('shop.expenses'));

        $this->assertDatabaseHas('expenses', [
            'shop_id' => $shop->id,
            'title' => 'CEB Store Bill',
            'amount' => 18500.00,
            'category' => 'electricity',
        ]);

        // 2. View Cashbook
        $cashbookRes = $this->actingAs($owner)->get('/shop/cashbook?date=' . now()->toDateString());
        $cashbookRes->assertOk()->assertViewIs('shop.cashbook.index');
        $cashbookRes->assertSee('CEB Store Bill');
        $cashbookRes->assertSee('18,500.00');
    }

    public function test_inventory_movements_and_manual_adjustment(): void
    {
        [$shop, $owner] = $this->createShopEnvironment();

        $product = Product::create([
            'shop_id' => $shop->id,
            'name' => 'Silk Silicone Case iPhone 15',
            'code' => 'CASE-IP15',
            'cost_price' => 800.00,
            'retail_price' => 2200.00,
            'stock_quantity' => 20,
        ]);

        // 1. Visit inventory audit page
        $res = $this->actingAs($owner)->get('/shop/inventory');
        $res->assertOk()->assertViewIs('shop.inventory.index');

        // 2. Perform manual adjustment: -3 damaged
        $adjRes = $this->actingAs($owner)->post('/shop/inventory/adjust', [
            'item_type' => 'product',
            'item_id' => $product->id,
            'quantity' => -3,
            'reason' => 'damaged',
            'notes' => 'Scratched during unboxing',
        ]);
        $adjRes->assertRedirect(route('shop.inventory'));

        $product->refresh();
        $this->assertEquals(17, $product->stock_quantity);

        $this->assertDatabaseHas('stock_movements', [
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'type' => 'adjustment',
            'quantity' => -3,
        ]);
    }

    public function test_sales_returns_and_restock(): void
    {
        [$shop, $owner] = $this->createShopEnvironment();

        $customer = Customer::create([
            'shop_id' => $shop->id,
            'name' => 'Kamal Dias',
            'phone' => '0711122334',
        ]);

        $product = Product::create([
            'shop_id' => $shop->id,
            'name' => 'Anker PowerBank 10000mAh',
            'code' => 'ANK-PB10',
            'cost_price' => 5000.00,
            'retail_price' => 8500.00,
            'stock_quantity' => 5,
        ]);

        $sale = Sale::create([
            'shop_id' => $shop->id,
            'customer_id' => $customer->id,
            'user_id' => $owner->id,
            'invoice_number' => 'INV-TEST-RET-01',
            'sale_date' => now()->toDateString(),
            'subtotal' => 8500.00,
            'total_amount' => 8500.00,
            'paid_amount' => 8500.00,
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);

        $saleItem = SaleItem::create([
            'shop_id' => $shop->id,
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'item_name' => $product->name,
            'quantity' => 1,
            'unit_price' => 8500.00,
            'subtotal' => 8500.00,
        ]);

        // 1. Visit returns directory
        $res = $this->actingAs($owner)->get('/shop/sales-returns');
        $res->assertOk()->assertViewIs('shop.sales.returns');

        // 2. Submit sales return
        $retRes = $this->actingAs($owner)->post('/shop/sales-returns', [
            'sale_id' => $sale->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Customer needed 20000mAh instead',
            'items' => [
                [
                    'sale_item_id' => $saleItem->id,
                    'quantity' => 1,
                    'refund_amount' => 8500.00,
                    'restock' => 1,
                    'condition' => 'resalable',
                ],
            ],
        ]);
        $retRes->assertRedirect(route('shop.sales.returns'));

        // Stock should be restocked back from 5 to 6
        $product->refresh();
        $this->assertEquals(6, $product->stock_quantity);

        // SaleReturn record created
        $this->assertDatabaseHas('sale_returns', [
            'shop_id' => $shop->id,
            'sale_id' => $sale->id,
            'total_refund_amount' => 8500.00,
        ]);

        // Sale status updated to returned
        $sale->refresh();
        $this->assertEquals('returned', $sale->status);
    }

    public function test_shop_settings_profile_update(): void
    {
        [$shop, $owner] = $this->createShopEnvironment();

        // 1. Visit settings page
        $res = $this->actingAs($owner)->get('/shop/settings');
        $res->assertOk()->assertViewIs('shop.settings.index');

        // 2. Update settings
        $upRes = $this->actingAs($owner)->put('/shop/settings', [
            'name' => 'TechCity Flagship Store',
            'phone' => '0112999888',
            'email' => 'info@techcity.lk',
            'address' => '700 Galle Road, Colombo 03',
            'business_reg_number' => 'PV-889900',
            'country' => 'Sri Lanka',
            'currency' => 'LKR',
            'timezone' => 'Asia/Colombo',
            'invoice_header_note' => 'Official Apple & Samsung Reseller',
            'invoice_footer_note' => 'Thank you for your business. Warranty claims valid with invoice.',
            'warranty_terms' => '1 Year standard hardware warranty.',
        ]);
        $upRes->assertRedirect(route('shop.settings'));

        $this->assertDatabaseHas('shops', [
            'id' => $shop->id,
            'name' => 'TechCity Flagship Store',
            'phone' => '0112999888',
            'business_reg_number' => 'PV-889900',
        ]);
    }

    public function test_reports_and_analytics_view(): void
    {
        [$shop, $owner] = $this->createShopEnvironment();

        $res = $this->actingAs($owner)->get('/shop/reports');
        $res->assertOk()->assertViewIs('shop.reports.index');
        $res->assertSee('Sales Revenue');
        $res->assertSee('Operating Expenses');
        $res->assertSee('Estimated Net Operating Profit');
    }
}
