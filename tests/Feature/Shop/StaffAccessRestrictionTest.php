<?php

namespace Tests\Feature\Shop;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Outlet;
use App\Models\Platform\Shop;
use App\Models\Platform\Subscription;
use App\Models\Platform\SubscriptionPlan;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffAccessRestrictionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function createShopWithStaffAndOwner(): array
    {
        $shop = Shop::factory()->create(['name' => 'Metro Cellular']);

        $plan = SubscriptionPlan::first() ?? SubscriptionPlan::factory()->create();
        Subscription::create([
            'shop_id' => $shop->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'billing_cycle' => 'monthly',
            'recurring_amount' => 5000.00,
            'current_period_starts_at' => now()->subDays(5),
            'current_period_ends_at' => now()->addDays(25),
        ]);

        $owner = User::factory()->create(['shop_id' => $shop->id]);
        $owner->assignRole('Shop Owner');

        $cashier = User::factory()->create(['shop_id' => $shop->id]);
        $cashier->assignRole('Cashier');

        $technician = User::factory()->create(['shop_id' => $shop->id]);
        $technician->assignRole('Technician');

        $manager = User::factory()->create(['shop_id' => $shop->id]);
        $manager->assignRole('Shop Manager');

        $outlet = Outlet::create([
            'shop_id' => $shop->id,
            'name' => 'Colombo Main Branch',
            'code' => 'CMB-01',
            'address' => 'Galle Road, Colombo',
            'is_main' => true,
        ]);

        app()->instance('shop.id', $shop->id);

        return compact('shop', 'owner', 'cashier', 'technician', 'manager', 'outlet');
    }

    public function test_staff_cannot_create_or_modify_inventory_products(): void
    {
        $data = $this->createShopWithStaffAndOwner();

        // Cashier cannot add product
        $res = $this->actingAs($data['cashier'])->post('/shop/products', [
            'name' => 'Unauthorized Item',
            'cost_price' => 1000,
            'selling_price' => 1500,
            'stock_quantity' => 10,
        ]);
        $res->assertStatus(403);

        // Shop Manager cannot add product
        $mgrRes = $this->actingAs($data['manager'])->post('/shop/products', [
            'name' => 'Unauthorized Item',
            'cost_price' => 1000,
            'selling_price' => 1500,
            'stock_quantity' => 10,
        ]);
        $mgrRes->assertStatus(403);

        // Shop Owner CAN add product
        $ownerRes = $this->actingAs($data['owner'])->post('/shop/products', [
            'name' => 'Authorized Item',
            'cost_price' => 1000,
            'selling_price' => 1500,
            'stock_quantity' => 10,
        ]);
        $ownerRes->assertRedirect('/shop/products');
        $this->assertDatabaseHas('products', ['shop_id' => $data['shop']->id, 'name' => 'Authorized Item']);
    }

    public function test_staff_cannot_create_phones_expenses_suppliers_or_customers(): void
    {
        $data = $this->createShopWithStaffAndOwner();

        // Register phone blocked for staff
        $phoneRes = $this->actingAs($data['technician'])->post('/shop/phones', [
            'brand' => 'Apple',
            'model' => 'iPhone 15',
            'imei_1' => '359123456789123',
            'purchase_cost' => 200000,
            'selling_price' => 250000,
            'condition' => 'new',
        ]);
        $phoneRes->assertStatus(403);

        // Record expense blocked for staff
        $expRes = $this->actingAs($data['cashier'])->post('/shop/expenses', [
            'category' => 'Shop Rent',
            'amount' => 50000,
            'payment_method' => 'cash',
            'expense_date' => now()->toDateString(),
        ]);
        $expRes->assertStatus(403);

        // Register supplier blocked for staff
        $supRes = $this->actingAs($data['cashier'])->post('/shop/suppliers', [
            'name' => 'Test Supplier',
            'phone' => '0771234567',
        ]);
        $supRes->assertStatus(403);

        // Add customer blocked for staff
        $cusRes = $this->actingAs($data['cashier'])->post('/shop/customers', [
            'name' => 'Test Customer',
            'phone' => '0779988776',
        ]);
        $cusRes->assertStatus(403);
    }

    public function test_staff_cannot_update_store_settings_or_manage_staff_payroll(): void
    {
        $data = $this->createShopWithStaffAndOwner();

        // Update settings blocked
        $setRes = $this->actingAs($data['manager'])->put('/shop/settings', [
            'name' => 'Hacked Name',
            'currency' => 'USD',
            'timezone' => 'UTC',
        ]);
        $setRes->assertStatus(403);

        // Register employee blocked
        $empRes = $this->actingAs($data['manager'])->post('/shop/staff/employees', [
            'name' => 'New Staff',
            'email' => 'staff@test.com',
            'position' => 'Cashier',
            'basic_salary' => 45000,
            'joined_on' => now()->toDateString(),
        ]);
        $empRes->assertStatus(403);
    }

    public function test_staff_retains_pos_counter_and_repairs_desk_access(): void
    {
        $data = $this->createShopWithStaffAndOwner();

        // 1. Staff can view POS Counter
        $posView = $this->actingAs($data['cashier'])->get('/shop/pos');
        $posView->assertOk();

        // 2. Staff can complete sales checkout
        $cat = Category::create(['shop_id' => $data['shop']->id, 'name' => 'Accessories', 'slug' => 'accessories']);
        $prod = Product::create([
            'shop_id' => $data['shop']->id,
            'category_id' => $cat->id,
            'name' => 'Tempered Glass',
            'code' => 'TG-01',
            'cost_price' => 500.00,
            'retail_price' => 1200.00,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        $checkoutRes = $this->actingAs($data['cashier'])->postJson('/api/v1/pos/checkout', [
            'items' => [
                [
                    'product_id' => $prod->id,
                    'item_name' => 'Tempered Glass',
                    'quantity' => 1,
                    'unit_price' => 1200.00,
                ],
            ],
            'payments' => [
                ['payment_method' => 'cash', 'amount' => 1200.00],
            ],
        ]);
        $checkoutRes->assertStatus(201);

        // 3. Staff can book repair tickets
        $customer = Customer::create([
            'shop_id' => $data['shop']->id,
            'name' => 'Repair Customer',
            'phone' => '0773344556',
        ]);

        $repairRes = $this->actingAs($data['technician'])->post('/shop/repairs', [
            'customer_id' => $customer->id,
            'brand' => 'Samsung',
            'model' => 'Galaxy A54',
            'fault_description' => 'Display flickering',
            'estimated_cost' => 14000.00,
        ]);
        $repairRes->assertRedirect('/shop/repairs');
    }

    public function test_blade_views_hide_add_buttons_for_staff(): void
    {
        $data = $this->createShopWithStaffAndOwner();

        // 1. When Cashier visits products page: cannot see 'Add New Item', 'Categories & Brands', or 'Cost Price'
        $prodView = $this->actingAs($data['cashier'])->get('/shop/products');
        $prodView->assertOk();
        $prodView->assertDontSee('Add New Item');
        $prodView->assertDontSee('Categories &amp; Brands', false);
        $prodView->assertDontSee('<th>Cost Price</th>', false);
        $prodView->assertDontSee('Store Management');

        // When Owner visits products page: sees 'Add New Item' and 'Cost Price'
        $ownerProdView = $this->actingAs($data['owner'])->get('/shop/products');
        $ownerProdView->assertOk();
        $ownerProdView->assertSee('Add New Item');
        $ownerProdView->assertSee('Categories &amp; Brands', false);
        $ownerProdView->assertSee('<th>Cost Price</th>', false);
        $ownerProdView->assertSee('Store Management');

        // 2. When Cashier visits phones page: cannot see 'Register Phone' or 'Purchase Cost'
        $phoneView = $this->actingAs($data['cashier'])->get('/shop/phones');
        $phoneView->assertOk();
        $phoneView->assertDontSee('Register Phone');
        $phoneView->assertDontSee('Purchase Cost');

        // When Owner visits phones page: sees 'Register Phone' and 'Purchase Cost'
        $ownerPhoneView = $this->actingAs($data['owner'])->get('/shop/phones');
        $ownerPhoneView->assertOk();
        $ownerPhoneView->assertSee('Register Phone');
        $ownerPhoneView->assertSee('Purchase Cost');

        // 3. Financial reports: 403 for staff, 200 for Owner
        $this->actingAs($data['cashier'])->get('/shop/reports')->assertStatus(403);
        $this->actingAs($data['owner'])->get('/shop/reports')->assertOk();

        // 4. Staff & Payroll directory: 403 for staff, 200 for Owner
        $this->actingAs($data['cashier'])->get('/shop/staff')->assertStatus(403);
        $this->actingAs($data['owner'])->get('/shop/staff')->assertOk();

        // 5. Store Settings: 403 for staff, 200 for Owner
        $this->actingAs($data['cashier'])->get('/shop/settings')->assertStatus(403);
        $this->actingAs($data['owner'])->get('/shop/settings')->assertOk();
    }
}
