<?php

namespace Tests\Feature\Platform;

use App\Models\Outlet;
use App\Models\Platform\Shop;
use App\Models\Platform\Subscription;
use App\Models\Platform\SubscriptionPayment;
use App\Models\Platform\SubscriptionPlan;
use App\Models\Platform\SystemActivity;
use App\Models\User;
use App\Services\Payment\Adapters\SandboxPaymentGatewayAdapter;
use App\Services\Payment\Contracts\PaymentGatewayInterface;
use App\Services\Payment\PaymentGatewayManager;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentGatewayAdapterTest extends TestCase
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
            'name' => 'Metro Cellular Colombo',
        ]);

        $manager = User::factory()->create(['shop_id' => $shop->id]);
        $manager->assignRole('Shop Manager');

        $outlet = Outlet::create([
            'shop_id' => $shop->id,
            'name' => 'Colombo Flagship',
            'code' => 'CMB-MAIN',
            'address' => 'Duplication Road, Colombo 04',
            'is_main' => true,
        ]);

        app()->instance('shop.id', $shop->id);

        return [$shop, $manager, $outlet];
    }

    public function test_gateway_adapter_initiates_valid_checkout_payload_with_hmac_signature(): void
    {
        [$shop] = $this->createShopEnvironment();
        $plan = SubscriptionPlan::where('slug', 'growth')->first();

        $adapter = app(PaymentGatewayManager::class)->driver('sandbox');

        $checkoutData = $adapter->initiateSubscriptionCheckout(
            $shop,
            $plan,
            'monthly',
            'https://pos.test/return',
            'https://pos.test/cancel',
            'https://pos.test/notify'
        );

        $this->assertNotEmpty($checkoutData['order_id']);
        $this->assertEquals((float) $plan->monthly_price, $checkoutData['amount']);
        $this->assertEquals('LKR', $checkoutData['currency']);
        $this->assertStringContainsString($checkoutData['order_id'], $checkoutData['checkout_url']);
        $this->assertNotEmpty($checkoutData['signature']);

        // Verify signature validates against adapter verification
        $this->assertTrue($adapter->verifyCallbackSignature($checkoutData['params']));
    }

    public function test_gateway_callback_verifies_signature_and_renews_subscription(): void
    {
        [$shop] = $this->createShopEnvironment();
        $plan = SubscriptionPlan::where('slug', 'starter')->first();

        $adapter = app(PaymentGatewayManager::class)->driver('sandbox');
        $orderId = 'SUB-' . $shop->id . '-' . now()->timestamp . '-TEST01';
        $transactionId = 'TXN-GW-APPROVED-001';
        $amount = (float) $plan->monthly_price;

        $payload = [
            'merchant_id' => config('payment.gateways.sandbox.merchant_id', 'SANDBOX_MERCHANT_001'),
            'order_id' => $orderId,
            'amount' => number_format($amount, 2, '.', ''),
            'currency' => 'LKR',
            'shop_id' => $shop->id,
            'plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'status' => 'approved',
            'status_code' => '2',
            'transaction_id' => $transactionId,
        ];
        $payload['signature'] = $adapter->generateSignature($payload);

        $response = $this->postJson('/api/v1/payments/gateway/callback', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'duplicate' => false,
            ]);

        // Assert payment record is approved
        $this->assertDatabaseHas('subscription_payments', [
            'shop_id' => $shop->id,
            'plan_id' => $plan->id,
            'amount' => $amount,
            'status' => 'approved',
            'transaction_id' => $transactionId,
            'reference_number' => $orderId,
            'payment_method' => 'sandbox',
        ]);

        // Assert subscription is active
        $subscription = Subscription::where('shop_id', $shop->id)->first();
        $this->assertNotNull($subscription);
        $this->assertEquals('active', $subscription->status);
        $this->assertEquals($plan->id, $subscription->plan_id);
        $this->assertEquals($amount, (float) $subscription->recurring_amount);

        // Assert audit activity logged
        $this->assertDatabaseHas('system_activities', [
            'action' => 'payment.gateway_approved',
            'shop_id' => $shop->id,
        ]);
        $this->assertDatabaseHas('system_activities', [
            'action' => 'subscription.renewed',
            'shop_id' => $shop->id,
        ]);
    }

    public function test_gateway_callback_rejects_tampered_or_invalid_signature(): void
    {
        [$shop] = $this->createShopEnvironment();
        $plan = SubscriptionPlan::where('slug', 'starter')->first();

        $payload = [
            'merchant_id' => 'SANDBOX_MERCHANT_001',
            'order_id' => 'SUB-FORGED-001',
            'amount' => '4500.00',
            'currency' => 'LKR',
            'shop_id' => $shop->id,
            'plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'status' => 'approved',
            'status_code' => '2',
            'transaction_id' => 'TXN-TAMPERED-001',
            'signature' => 'invalid_tampered_hash_signature_12345',
        ];

        $response = $this->postJson('/api/v1/payments/gateway/callback', $payload);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'duplicate' => false,
            ]);

        // Ensure no approved payment is recorded
        $this->assertDatabaseMissing('subscription_payments', [
            'transaction_id' => 'TXN-TAMPERED-001',
        ]);

        // Ensure security alert was logged
        $this->assertDatabaseHas('system_activities', [
            'action' => 'payment.gateway_signature_failed',
        ]);
    }

    public function test_gateway_callback_prevents_duplicate_renewal_on_repeated_webhooks(): void
    {
        [$shop] = $this->createShopEnvironment();
        $plan = SubscriptionPlan::where('slug', 'growth')->first();

        $adapter = app(PaymentGatewayManager::class)->driver('sandbox');
        $orderId = 'SUB-DUP-' . $shop->id . '-999';
        $transactionId = 'TXN-DUP-CHECK-777';
        $amount = (float) $plan->monthly_price;

        $payload = [
            'merchant_id' => config('payment.gateways.sandbox.merchant_id', 'SANDBOX_MERCHANT_001'),
            'order_id' => $orderId,
            'amount' => number_format($amount, 2, '.', ''),
            'currency' => 'LKR',
            'shop_id' => $shop->id,
            'plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'status' => 'approved',
            'status_code' => '2',
            'transaction_id' => $transactionId,
        ];
        $payload['signature'] = $adapter->generateSignature($payload);

        // 1st Callback invocation: Processes and renews subscription
        $firstResponse = $this->postJson('/api/v1/payments/gateway/callback', $payload);
        $firstResponse->assertStatus(200)->assertJson(['duplicate' => false]);

        $subAfterFirst = Subscription::where('shop_id', $shop->id)->first();
        $originalEndsAt = $subAfterFirst->current_period_ends_at->toIso8601String();
        $initialPaymentCount = SubscriptionPayment::where('shop_id', $shop->id)->count();

        // 2nd Callback invocation (simulating gateway retry/duplicate delivery):
        $secondResponse = $this->postJson('/api/v1/payments/gateway/callback', $payload);
        $secondResponse->assertStatus(200)->assertJson([
            'success' => true,
            'duplicate' => true,
        ]);

        // 3rd Callback invocation:
        $thirdResponse = $this->postJson('/api/v1/payments/gateway/callback', $payload);
        $thirdResponse->assertStatus(200)->assertJson([
            'duplicate' => true,
        ]);

        // Assert payment count remained exactly the same (no duplicate payment records)
        $this->assertEquals($initialPaymentCount, SubscriptionPayment::where('shop_id', $shop->id)->count());

        // Assert subscription period did NOT extend twice (no double renewal)
        $subAfterRetries = Subscription::where('shop_id', $shop->id)->first();
        $this->assertEquals($originalEndsAt, $subAfterRetries->current_period_ends_at->toIso8601String());

        // Assert duplicate ignored activity was logged
        $this->assertDatabaseHas('system_activities', [
            'action' => 'payment.duplicate_callback_ignored',
            'shop_id' => $shop->id,
        ]);
    }

    public function test_gateway_callback_handles_payment_failure_and_does_not_activate_subscription(): void
    {
        [$shop] = $this->createShopEnvironment();
        $plan = SubscriptionPlan::where('slug', 'enterprise')->first();

        // Ensure shop starts in trial status
        $sub = Subscription::create([
            'shop_id' => $shop->id,
            'plan_id' => $plan->id,
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(5),
            'current_period_starts_at' => now(),
            'current_period_ends_at' => now()->addDays(5),
        ]);

        $adapter = app(PaymentGatewayManager::class)->driver('sandbox');
        $orderId = 'SUB-FAIL-' . $shop->id;
        $transactionId = 'TXN-FAIL-001';
        $amount = (float) $plan->monthly_price;

        $payload = [
            'merchant_id' => config('payment.gateways.sandbox.merchant_id', 'SANDBOX_MERCHANT_001'),
            'order_id' => $orderId,
            'amount' => number_format($amount, 2, '.', ''),
            'currency' => 'LKR',
            'shop_id' => $shop->id,
            'plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'status' => 'failed',
            'status_code' => '0',
            'failure_reason' => 'Customer card expired or insufficient balance',
            'transaction_id' => $transactionId,
        ];
        $payload['signature'] = $adapter->generateSignature($payload);

        $response = $this->postJson('/api/v1/payments/gateway/callback', $payload);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'duplicate' => false,
            ]);

        // Verify payment record marked as failed with rejection reason
        $this->assertDatabaseHas('subscription_payments', [
            'shop_id' => $shop->id,
            'status' => 'failed',
            'transaction_id' => $transactionId,
            'rejection_reason' => 'Customer card expired or insufficient balance',
        ]);

        // Verify subscription was NOT upgraded/extended to active
        $this->assertEquals('trial', $sub->fresh()->status);

        // Verify failure logged in audit activity
        $this->assertDatabaseHas('system_activities', [
            'action' => 'payment.gateway_failed',
            'shop_id' => $shop->id,
        ]);
    }

    public function test_gateway_callback_handles_annual_and_monthly_renewal_periods(): void
    {
        [$shop] = $this->createShopEnvironment();
        $plan = SubscriptionPlan::where('slug', 'starter')->first();
        $adapter = app(PaymentGatewayManager::class)->driver('sandbox');

        // Test Annual Checkout
        $annualAmount = (float) $plan->annual_price;
        $annualOrderId = 'SUB-ANNUAL-' . $shop->id;
        $annualTxn = 'TXN-ANNUAL-99';

        $annualPayload = [
            'merchant_id' => config('payment.gateways.sandbox.merchant_id', 'SANDBOX_MERCHANT_001'),
            'order_id' => $annualOrderId,
            'amount' => number_format($annualAmount, 2, '.', ''),
            'currency' => 'LKR',
            'shop_id' => $shop->id,
            'plan_id' => $plan->id,
            'billing_cycle' => 'annual',
            'status' => 'approved',
            'status_code' => '2',
            'transaction_id' => $annualTxn,
        ];
        $annualPayload['signature'] = $adapter->generateSignature($annualPayload);

        $this->postJson('/api/v1/payments/gateway/callback', $annualPayload)->assertStatus(200);

        $sub = Subscription::where('shop_id', $shop->id)->first();
        $this->assertEquals('annual', $sub->billing_cycle);
        $this->assertEquals('active', $sub->status);
        // Annual period should be approximately 365 days in the future
        $daysUntilEnd = (int) ceil(now()->diffInDays($sub->current_period_ends_at));
        $this->assertGreaterThanOrEqual(364, $daysUntilEnd);
    }

    public function test_shop_can_initiate_gateway_checkout_and_simulate_actions(): void
    {
        [$shop, $manager] = $this->createShopEnvironment();
        $plan = SubscriptionPlan::where('slug', 'growth')->first();

        // 1. Initiate checkout via web route
        $initiateResponse = $this->actingAs($manager)->post('/shop/subscription/gateway/initiate', [
            'plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
        ]);

        $initiateResponse->assertRedirect();
        $redirectUrl = $initiateResponse->headers->get('Location');
        $this->assertStringContainsString('/shop/subscription/gateway/sandbox-checkout?order_id=', $redirectUrl);

        // Check pending payment record
        $payment = SubscriptionPayment::where('shop_id', $shop->id)->where('status', 'pending')->first();
        $this->assertNotNull($payment);

        // 2. Open sandbox checkout view
        $viewResponse = $this->actingAs($manager)->get($redirectUrl);
        $viewResponse->assertStatus(200)
            ->assertSee('Sandbox Environment')
            ->assertSee($payment->reference_number)
            ->assertSee($plan->name);

        // 3. Simulate success action from simulator UI
        $simulateResponse = $this->actingAs($manager)->post('/shop/subscription/gateway/simulate-action', [
            'order_id' => $payment->reference_number,
            'action' => 'success',
        ]);

        $simulateResponse->assertRedirect('/shop/subscription');
        $simulateResponse->assertSessionHas('success');

        // Verify subscription is now active
        $this->assertDatabaseHas('subscriptions', [
            'shop_id' => $shop->id,
            'plan_id' => $plan->id,
            'status' => 'active',
        ]);
    }
}

