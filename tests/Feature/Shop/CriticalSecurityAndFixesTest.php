<?php

namespace Tests\Feature\Shop;

use App\Models\Customer;
use App\Models\CustomerDebtPayment;
use App\Models\Outlet;
use App\Models\Platform\SubscriptionPlan;
use App\Models\Platform\Shop;
use App\Models\Platform\Subscription;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CriticalSecurityAndFixesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function createShopEnvironment(string $subscriptionStatus = 'active'): array
    {
        $shop = Shop::factory()->create(['name' => 'Apex Mobile']);

        $plan = SubscriptionPlan::first() ?? SubscriptionPlan::factory()->create();
        $subscription = Subscription::create([
            'shop_id' => $shop->id,
            'plan_id' => $plan->id,
            'status' => $subscriptionStatus,
            'billing_cycle' => 'monthly',
            'recurring_amount' => 4500.00,
            'current_period_starts_at' => now()->subDays(10),
            'current_period_ends_at' => now()->addDays(20),
        ]);

        $manager = User::factory()->create(['shop_id' => $shop->id]);
        $manager->assignRole('Shop Manager');

        $outlet = Outlet::create([
            'shop_id' => $shop->id,
            'name' => 'Main Outlet',
            'code' => 'MAIN-01',
            'address' => 'Galle Road, Colombo',
            'is_main' => true,
        ]);

        return [$shop, $manager, $outlet, $subscription];
    }

    public function test_suspended_shop_cannot_access_operational_views_and_redirects_to_subscription(): void
    {
        [$shop, $manager] = $this->createShopEnvironment('suspended');

        $response = $this->actingAs($manager)->get('/shop/pos');

        $response->assertRedirect(route('shop.subscription.index'));
        $response->assertSessionHas('error', 'Your shop workspace has been suspended. Please renew your subscription plan or contact platform administration to reactivate your workspace.');
    }

    public function test_suspended_shop_customer_portal_returns_403_forbidden(): void
    {
        [$shop] = $this->createShopEnvironment('suspended');

        $customerUser = User::factory()->create([
            'shop_id' => $shop->id,
            'email' => 'customer_test@example.com',
        ]);
        $customerUser->assignRole('Customer');

        $response = $this->actingAs($customerUser)->get('/portal');

        $response->assertStatus(403);
    }

    public function test_suspended_shop_api_request_returns_403_forbidden(): void
    {
        [$shop, $manager] = $this->createShopEnvironment('suspended');

        Sanctum::actingAs($manager);

        $response = $this->getJson('/api/v1/customers');

        $response->assertStatus(403);
        $response->assertJsonPath('message', 'Your shop workspace has been suspended by platform administration.');
    }

    public function test_customer_debt_settlement_workflow_decreases_balance_and_records_payment(): void
    {
        [$shop, $manager] = $this->createShopEnvironment('active');
        app()->instance('shop.id', $shop->id);

        $customer = Customer::create([
            'shop_id' => $shop->id,
            'name' => 'Sunil Shantha',
            'phone' => '0712345678',
            'balance' => 15000.00,
            'credit_limit' => 25000.00,
        ]);

        app()->forgetInstance('shop.id');

        $response = $this->actingAs($manager)->post("/shop/customers/{$customer->id}/settle-debt", [
            'amount' => 5000.00,
            'payment_method' => 'cash',
            'reference' => 'RCPT-001',
            'notes' => 'Partial cash settlement',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $customer->refresh();
        $this->assertEquals(10000.00, (float) $customer->balance);

        $this->assertDatabaseHas('customer_debt_payments', [
            'shop_id' => $shop->id,
            'customer_id' => $customer->id,
            'amount' => 5000.00,
            'payment_method' => 'cash',
        ]);
    }

    public function test_customer_debt_settlement_rejects_amount_greater_than_outstanding_debt(): void
    {
        [$shop, $manager] = $this->createShopEnvironment('active');
        app()->instance('shop.id', $shop->id);

        $customer = Customer::create([
            'shop_id' => $shop->id,
            'name' => 'Kamal Silva',
            'phone' => '0771234567',
            'balance' => 4000.00,
            'credit_limit' => 10000.00,
        ]);

        app()->forgetInstance('shop.id');

        $response = $this->actingAs($manager)->post("/shop/customers/{$customer->id}/settle-debt", [
            'amount' => 5000.00,
            'payment_method' => 'cash',
        ]);

        $response->assertSessionHasErrors('amount');
        $customer->refresh();
        $this->assertEquals(4000.00, (float) $customer->balance);
    }

    public function test_pos_checkout_rejects_insufficient_stock_for_standard_products(): void
    {
        [$shop, $manager] = $this->createShopEnvironment('active');
        app()->instance('shop.id', $shop->id);

        $product = Product::create([
            'shop_id' => $shop->id,
            'name' => 'Fast Charger 20W',
            'code' => 'ACC-CHG-20W',
            'type' => 'standard',
            'cost_price' => 1200.00,
            'retail_price' => 2500.00,
            'stock_quantity' => 2,
        ]);

        app()->forgetInstance('shop.id');

        Sanctum::actingAs($manager);

        $payload = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'item_name' => 'Fast Charger 20W',
                    'quantity' => 5, // exceeds 2
                    'unit_price' => 2500.00,
                ],
            ],
            'payments' => [
                ['payment_method' => 'cash', 'amount' => 12500.00],
            ],
        ];

        $response = $this->postJson('/api/v1/pos/checkout', $payload);

        $response->assertStatus(422);
        $response->assertJsonFragment([
            'message' => "Insufficient stock for product 'Fast Charger 20W'. Available: 2, Requested: 5.",
        ]);
    }

    public function test_pos_checkout_requires_customer_for_credit_or_partial_sale(): void
    {
        [$shop, $manager] = $this->createShopEnvironment('active');
        app()->instance('shop.id', $shop->id);

        $product = Product::create([
            'shop_id' => $shop->id,
            'name' => 'Bluetooth Earbuds',
            'code' => 'ACC-EAR-01',
            'type' => 'standard',
            'cost_price' => 2000.00,
            'retail_price' => 4000.00,
            'stock_quantity' => 10,
        ]);

        app()->forgetInstance('shop.id');

        Sanctum::actingAs($manager);

        // Total: 4,000, Paid: 2,000, Customer: null
        $payload = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'item_name' => 'Bluetooth Earbuds',
                    'quantity' => 1,
                    'unit_price' => 4000.00,
                ],
            ],
            'payments' => [
                ['payment_method' => 'cash', 'amount' => 2000.00],
            ],
        ];

        $response = $this->postJson('/api/v1/pos/checkout', $payload);

        $response->assertStatus(422);
        $response->assertJsonFragment([
            'message' => 'Credit / Partial payment sale requires a registered customer to record the outstanding balance. Please select or register a customer profile.',
        ]);
    }

    public function test_pos_checkout_rejects_sale_exceeding_customer_credit_limit(): void
    {
        [$shop, $manager] = $this->createShopEnvironment('active');
        app()->instance('shop.id', $shop->id);

        $product = Product::create([
            'shop_id' => $shop->id,
            'name' => 'Smart Watch',
            'code' => 'ACC-SW-01',
            'type' => 'standard',
            'cost_price' => 8000.00,
            'retail_price' => 12000.00,
            'stock_quantity' => 5,
        ]);

        $customer = Customer::create([
            'shop_id' => $shop->id,
            'name' => 'Ruwan Priyantha',
            'phone' => '0781234567',
            'balance' => 3000.00,
            'credit_limit' => 5000.00, // Max additional debt: 2000
        ]);

        app()->forgetInstance('shop.id');

        Sanctum::actingAs($manager);

        // Total: 12,000, Paid: 4,000 -> Due: 8,000. Balance (3,000) + Due (8,000) = 11,000 > 5,000 limit
        $payload = [
            'customer_id' => $customer->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'item_name' => 'Smart Watch',
                    'quantity' => 1,
                    'unit_price' => 12000.00,
                ],
            ],
            'payments' => [
                ['payment_method' => 'cash', 'amount' => 4000.00],
            ],
        ];

        $response = $this->postJson('/api/v1/pos/checkout', $payload);

        $response->assertStatus(422);
        $response->assertJsonFragment([
            'message' => 'Customer credit limit exceeded! Limit: LKR 5,000.00, Current Debt: LKR 3,000.00, New Debt: LKR 8,000.00. Max allowed new credit: LKR 2,000.00.',
        ]);
    }

    public function test_shop_a_cannot_assign_shop_b_category_to_product(): void
    {
        [$shopA, $managerA] = $this->createShopEnvironment('active');
        $shopB = Shop::factory()->create(['name' => 'Shop B']);

        app()->instance('shop.id', $shopB->id);
        $categoryB = \App\Models\Category::create([
            'shop_id' => $shopB->id,
            'name' => 'Shop B Only Category',
            'slug' => 'shop-b-category',
        ]);
        app()->forgetInstance('shop.id');

        $response = $this->actingAs($managerA)->post('/shop/products', [
            'name' => 'Test Cable',
            'category_id' => $categoryB->id, // belongs to Shop B!
            'cost_price' => 500,
            'selling_price' => 1000,
            'stock_quantity' => 10,
        ]);

        $response->assertSessionHasErrors('category_id');
    }

    public function test_shop_a_cannot_assign_shop_b_customer_to_repair(): void
    {
        [$shopA, $managerA] = $this->createShopEnvironment('active');
        $shopB = Shop::factory()->create(['name' => 'Shop B']);

        app()->instance('shop.id', $shopB->id);
        $customerB = Customer::create([
            'shop_id' => $shopB->id,
            'name' => 'Shop B Customer',
            'phone' => '0779998877',
        ]);
        app()->forgetInstance('shop.id');

        $response = $this->actingAs($managerA)->post('/shop/repairs', [
            'customer_id' => $customerB->id, // belongs to Shop B!
            'brand' => 'Apple',
            'model' => 'iPhone 13',
            'fault_description' => 'Broken screen',
            'estimated_cost' => 15000,
        ]);

        $response->assertSessionHasErrors('customer_id');
    }

    public function test_pos_checkout_excess_cash_tender_caps_paid_amount_to_total(): void
    {
        [$shop, $manager] = $this->createShopEnvironment('active');
        app()->instance('shop.id', $shop->id);

        $product = Product::create([
            'shop_id' => $shop->id,
            'name' => 'Tempered Glass',
            'code' => 'ACC-TG-01',
            'type' => 'standard',
            'cost_price' => 500.00,
            'retail_price' => 1500.00,
            'stock_quantity' => 20,
        ]);

        app()->forgetInstance('shop.id');

        Sanctum::actingAs($manager);

        // Total: 1,500.00, Tendered cash: 5,000.00 (Customer pays with a 5000 note)
        $payload = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'item_name' => 'Tempered Glass',
                    'quantity' => 1,
                    'unit_price' => 1500.00,
                ],
            ],
            'payments' => [
                ['payment_method' => 'cash', 'amount' => 5000.00],
            ],
        ];

        $response = $this->postJson('/api/v1/pos/checkout', $payload);

        $response->assertCreated();
        $saleId = $response->json('sale.id');

        $sale = \App\Models\Sale::withoutGlobalScopes()->find($saleId);
        $this->assertEquals(1500.00, (float) $sale->total_amount);
        $this->assertEquals(1500.00, (float) $sale->paid_amount); // Capped at total, not 5000
        $this->assertEquals('completed', $sale->status);
    }
}
