<?php

namespace Tests\Feature\Shop;

use App\Models\Customer;
use App\Models\Outlet;
use App\Models\PhoneDevice;
use App\Models\Platform\Shop;
use App\Models\Platform\Subscription;
use App\Models\Platform\SubscriptionPayment;
use App\Models\Platform\SubscriptionPlan;
use App\Models\Product;
use App\Models\RepairJob;
use App\Models\SmsLog;
use App\Models\User;
use App\Services\BarcodeGeneratorService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediumPriorityFeaturesTest extends TestCase
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
            'name' => 'Cellular Point Colombo',
            'sms_enabled' => true,
            'sms_provider' => 'mock',
            'sms_on_repair_ready' => true,
            'sms_on_sale' => true,
        ]);

        $manager = User::factory()->create(['shop_id' => $shop->id]);
        $manager->assignRole('Shop Manager');

        $outlet = Outlet::create([
            'shop_id' => $shop->id,
            'name' => 'Colombo Main Branch',
            'code' => 'CMB-01',
            'address' => 'Galle Road, Colombo 03',
            'is_main' => true,
        ]);

        app()->instance('shop.id', $shop->id);

        return [$shop, $manager, $outlet];
    }

    public function test_barcode_generator_service_generates_valid_code128_svg(): void
    {
        $service = new BarcodeGeneratorService();

        // 1. Numeric IMEI
        $imeiSvg = $service->generateCode128Svg('356987123456789', 40, 1.5);
        $this->assertStringContainsString('<svg', $imeiSvg);
        $this->assertStringContainsString('xmlns="http://www.w3.org/2000/svg"', $imeiSvg);
        $this->assertStringContainsString('<rect', $imeiSvg);

        // 2. Alphanumeric SKU
        $skuSvg = $service->generateCode128Svg('ACC-APP-AIRPOD-01', 35, 1.2);
        $this->assertStringContainsString('<svg', $skuSvg);
        $this->assertStringContainsString('<rect', $skuSvg);
    }

    public function test_barcode_label_views_render_properly(): void
    {
        [$shop, $manager, $outlet] = $this->createShopEnvironment();

        $phone = PhoneDevice::create([
            'shop_id' => $shop->id,
            'outlet_id' => $outlet->id,
            'brand' => 'Apple',
            'model' => 'iPhone 15 Pro',
            'imei_1' => '359876543210123',
            'imei_2' => '359876543210124',
            'purchase_cost' => 240000.00,
            'selling_price' => 295000.00,
            'condition' => 'Brand New',
            'status' => 'in_stock',
        ]);

        // Access barcode dashboard
        $response = $this->actingAs($manager)->get("/shop/labels?type=phone&id={$phone->id}");
        $response->assertStatus(200);
        $response->assertSee('iPhone 15 Pro');
        $response->assertSee('359876543210123');

        // Access printable label view
        $printResponse = $this->actingAs($manager)->get("/shop/labels/print?type=phone&id={$phone->id}&quantity=2&template=thermal_50x30");
        $printResponse->assertStatus(200);
        $printResponse->assertSee('iPhone 15 Pro');
        $printResponse->assertSee('Cellular Point Colombo');
        $printResponse->assertSee('window.print()', false);
    }

    public function test_custom_sms_can_be_dispatched_and_logged(): void
    {
        [$shop, $manager, $outlet] = $this->createShopEnvironment();

        $response = $this->actingAs($manager)->post('/shop/notifications/send', [
            'recipient_phone' => '0771234567',
            'message' => 'Hello Customer, special weekend discount 10% on all Apple accessories!',
        ]);

        $response->assertRedirect('/shop/notifications');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('sms_logs', [
            'shop_id' => $shop->id,
            'recipient_phone' => '94771234567',
            'type' => 'custom',
            'status' => 'simulated',
        ]);
    }

    public function test_updating_sms_gateway_settings(): void
    {
        [$shop, $manager] = $this->createShopEnvironment();

        $response = $this->actingAs($manager)->post('/shop/notifications/settings', [
            'sms_enabled' => '1',
            'sms_provider' => 'notifylk',
            'sms_api_key' => 'NOTIFY_SECRET_KEY_123',
            'sms_sender_id' => 'NotifyDEMO',
            'sms_on_repair_ready' => '1',
            'sms_on_sale' => '1',
        ]);

        $response->assertRedirect('/shop/notifications');
        $response->assertSessionHas('success');

        $shop->refresh();
        $this->assertTrue($shop->sms_enabled);
        $this->assertEquals('notifylk', $shop->sms_provider);
        $this->assertEquals('NOTIFY_SECRET_KEY_123', $shop->sms_api_key);
        $this->assertEquals('NotifyDEMO', $shop->sms_sender_id);
    }

    public function test_repair_ready_for_collection_triggers_sms_alert(): void
    {
        [$shop, $manager] = $this->createShopEnvironment();

        $customer = Customer::create([
            'shop_id' => $shop->id,
            'name' => 'Saman Kumara',
            'phone' => '0714567890',
            'nic' => '199201020304',
        ]);

        $repair = RepairJob::create([
            'shop_id' => $shop->id,
            'customer_id' => $customer->id,
            'technician_id' => $manager->id,
            'job_number' => 'REP-TEST-001',
            'brand' => 'Samsung',
            'model' => 'Galaxy S23 Ultra',
            'imei' => '351234567890123',
            'fault_description' => 'Broken AMOLED Display',
            'status' => 'in_repair',
            'estimated_cost' => 45000.00,
            'advance_amount' => 10000.00,
        ]);

        // Transition status to ready_for_collection
        $response = $this->actingAs($manager)->patch("/shop/repairs/{$repair->id}/status", [
            'status' => 'ready_for_collection',
            'final_cost' => 48000.00,
            'technician_notes' => 'Original display replaced and touch calibrated.',
        ]);

        $response->assertRedirect('/shop/repairs');

        // Check that an SMS log was created for ready_for_collection
        $this->assertDatabaseHas('sms_logs', [
            'shop_id' => $shop->id,
            'recipient_phone' => '94714567890',
            'type' => 'repair_ready',
            'status' => 'simulated',
        ]);
    }

    public function test_shop_instant_card_subscription_checkout(): void
    {
        [$shop, $manager] = $this->createShopEnvironment();

        $plan = SubscriptionPlan::create([
            'name' => 'Enterprise Pro',
            'slug' => 'enterprise-pro',
            'description' => 'Unlimited outlets and staff',
            'monthly_price' => 12000.00,
            'annual_price' => 120000.00,
            'trial_days' => 14,
            'max_branches' => 10,
            'max_staff' => 50,
            'max_products' => 10000,
            'has_imei_tracking' => true,
            'has_repair_management' => true,
            'has_warranty_management' => true,
            'has_accounting_reports' => true,
            'has_multi_branch' => true,
            'has_customer_portal' => true,
            'is_active' => true,
        ]);

        $response = $this->actingAs($manager)->post('/shop/subscription/checkout', [
            'plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'cardholder_name' => 'K. D. Perera',
            'card_number' => '4242424242424242',
            'card_expiry' => '12/28',
            'card_cvv' => '123',
        ]);

        $response->assertRedirect('/shop/subscription');
        $response->assertSessionHas('success');

        // Verify active subscription
        $this->assertDatabaseHas('subscriptions', [
            'shop_id' => $shop->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'billing_cycle' => 'monthly',
            'recurring_amount' => 12000.00,
        ]);

        // Verify payment record
        $this->assertDatabaseHas('subscription_payments', [
            'shop_id' => $shop->id,
            'plan_id' => $plan->id,
            'amount' => 12000.00,
            'payment_method' => 'card',
            'status' => 'approved',
            'reference_number' => 'CARD-4242',
        ]);
    }

    public function test_shop_bank_transfer_slip_upload(): void
    {
        Storage::fake('public');
        [$shop, $manager] = $this->createShopEnvironment();

        $plan = SubscriptionPlan::create([
            'name' => 'Growth Plan',
            'slug' => 'growth-plan',
            'monthly_price' => 6000.00,
            'annual_price' => 60000.00,
            'trial_days' => 14,
            'max_branches' => 3,
            'max_staff' => 10,
            'max_products' => 2000,
            'has_imei_tracking' => true,
            'has_repair_management' => true,
            'has_warranty_management' => true,
            'has_accounting_reports' => true,
            'has_multi_branch' => true,
            'has_customer_portal' => true,
            'is_active' => true,
        ]);

        $fakeSlip = UploadedFile::fake()->create('deposit_slip.pdf', 100, 'application/pdf');

        $response = $this->actingAs($manager)->post('/shop/subscription/bank-transfer', [
            'plan_id' => $plan->id,
            'billing_cycle' => 'annual',
            'reference_number' => 'DEP-SLIP-998811',
            'receipt' => $fakeSlip,
        ]);

        $response->assertRedirect('/shop/subscription');
        $response->assertSessionHas('success');

        // Verify pending payment awaiting super admin approval
        $this->assertDatabaseHas('subscription_payments', [
            'shop_id' => $shop->id,
            'plan_id' => $plan->id,
            'amount' => 60000.00,
            'payment_method' => 'bank_transfer',
            'billing_cycle' => 'annual',
            'status' => 'pending',
            'reference_number' => 'DEP-SLIP-998811',
        ]);
    }
}
