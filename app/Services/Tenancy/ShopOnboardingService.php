<?php

namespace App\Services\Tenancy;

use App\Models\Platform\Shop;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class ShopOnboardingService
{
    /**
     * Atomically onboard a new shop tenant workspace and its primary owner account.
     *
     * @param array{
     *     shop_name: string,
     *     name: string,
     *     email: string,
     *     phone?: string|null,
     *     password: string,
     *     business_address?: string|null,
     *     country?: string|null,
     *     timezone?: string|null,
     *     business_reg_number?: string|null,
     *     currency?: string
     * } $data
     * @return array{shop: Shop, user: User}
     */
    public function onboard(array $data): array
    {
        return DB::transaction(function () use ($data): array {
            // Ensure core roles and permissions exist
            if (! Role::where('name', 'Administrator')->where('guard_name', 'web')->exists()) {
                (new DatabaseSeeder)->run();
            }

            // 1. Create the independent shop workspace
            $shop = Shop::create([
                'name' => trim($data['shop_name']),
                'address' => isset($data['business_address']) && filled($data['business_address']) ? trim($data['business_address']) : null,
                'country' => isset($data['country']) && filled($data['country']) ? trim($data['country']) : 'Sri Lanka',
                'business_reg_number' => isset($data['business_reg_number']) && filled($data['business_reg_number']) ? trim($data['business_reg_number']) : null,
            ]);

            // 2. Initialize default shop settings under the shop context
            app()->instance('shop.id', (int) $shop->id);
            try {
                $setting = new Setting([
                    'timezone' => isset($data['timezone']) && filled($data['timezone']) ? $data['timezone'] : config('app.business_timezone', 'Asia/Colombo'),
                    'currency' => $data['currency'] ?? 'LKR',
                ]);
                $setting->shop_id = (int) $shop->id;
                $setting->save();

                // Initialize standard phone shop categories & brands
                $defaultCategories = [
                    'Smartphones',
                    'Feature Phones',
                    'Chargers & Cables',
                    'Earphones & Audio',
                    'Cases & Covers',
                    'Screen Protectors',
                    'Power Banks & Batteries',
                    'Spare Parts & Displays',
                ];
                foreach ($defaultCategories as $catName) {
                    \App\Models\Category::firstOrCreate(
                        ['slug' => \Illuminate\Support\Str::slug($catName)],
                        ['name' => $catName]
                    );
                }

                $defaultBrands = [
                    'Apple',
                    'Samsung',
                    'Xiaomi',
                    'Vivo',
                    'Oppo',
                    'Realme',
                    'OnePlus',
                    'Huawei',
                    'Google Pixel',
                    'Anker',
                ];
                foreach ($defaultBrands as $brandName) {
                    \App\Models\Brand::firstOrCreate(
                        ['slug' => \Illuminate\Support\Str::slug($brandName)],
                        ['name' => $brandName]
                    );
                }

                // Initialize default Main Branch outlet
                \App\Models\Outlet::firstOrCreate(
                    ['shop_id' => $shop->id, 'code' => 'OUT-01'],
                    [
                        'name' => 'Main Branch',
                        'phone' => isset($data['phone']) ? trim($data['phone']) : null,
                        'email' => strtolower(trim($data['email'])),
                        'address' => isset($data['business_address']) && filled($data['business_address']) ? trim($data['business_address']) : 'Main Street',
                        'city' => 'Colombo',
                        'manager_name' => trim($data['name']),
                        'is_main' => true,
                        'is_active' => true,
                    ]
                );
            } finally {
                app()->forgetInstance('shop.id');
            }

            // 3. Initialize Subscription with Plan & Payment Card details
            $plan = null;
            if (!empty($data['plan_id'])) {
                $plan = \App\Models\Platform\SubscriptionPlan::find($data['plan_id']);
            }
            if (!$plan) {
                $plan = \App\Models\Platform\SubscriptionPlan::where('slug', 'starter')->first()
                    ?? \App\Models\Platform\SubscriptionPlan::first();
            }

            $cleanCardNumber = preg_replace('/[^0-9]/', '', (string) ($data['card_number'] ?? '4242'));
            $last4 = substr($cleanCardNumber, -4) ?: '4242';
            $brand = match (true) {
                str_starts_with($cleanCardNumber, '4') => 'visa',
                str_starts_with($cleanCardNumber, '5') => 'mastercard',
                str_starts_with($cleanCardNumber, '3') => 'amex',
                default => 'card',
            };

            $now = now();
            $trialDays = (int) ($plan?->trial_days ?: 7);
            $trialEndsAt = $now->copy()->addDays($trialDays);
            $billingCycle = ($data['billing_cycle'] ?? 'monthly') === 'annual' ? 'annual' : 'monthly';
            $recurringAmount = $billingCycle === 'annual' ? ($plan?->annual_price ?? 48000.00) : ($plan?->monthly_price ?? 4500.00);

            $subscription = null;
            $paymentMethod = $data['payment_method'] ?? 'card';

            // 4. Create the owner user account strictly bound to this shop
            $user = new User([
                'name' => trim($data['name']),
                'email' => strtolower(trim($data['email'])),
                'phone' => isset($data['phone']) ? trim($data['phone']) : null,
                'password' => $data['password'],
                'email_verified_at' => ! config('auth.verification.enabled', false) ? now() : null,
            ]);

            // Enforce server-side tenant assignment (never trust client-supplied IDs)
            $user->shop()->associate($shop);
            $user->save();

            // 5. Initialize Subscription & Payment based on chosen method
            if ($plan) {
                if ($paymentMethod === 'bank_transfer') {
                    // Upload payment receipt slip if provided
                    $receiptUrl = null;
                    if (isset($data['receipt']) && $data['receipt'] instanceof \Illuminate\Http\UploadedFile) {
                        $path = $data['receipt']->store('receipts', 'public');
                        $receiptUrl = '/storage/' . $path;
                    } elseif (isset($data['receipt_url'])) {
                        $receiptUrl = $data['receipt_url'];
                    }

                    $subscription = \App\Models\Platform\Subscription::create([
                        'shop_id' => $shop->id,
                        'plan_id' => $plan->id,
                        'status' => 'trial',
                        'billing_cycle' => $billingCycle,
                        'recurring_amount' => $recurringAmount,
                        'trial_ends_at' => $trialEndsAt,
                        'current_period_starts_at' => $now,
                        'current_period_ends_at' => $trialEndsAt,
                        'payment_method' => 'bank_transfer',
                    ]);

                    \App\Models\Platform\SubscriptionPayment::create([
                        'shop_id' => $shop->id,
                        'plan_id' => $plan->id,
                        'subscription_id' => $subscription->id,
                        'amount' => $recurringAmount,
                        'discount_amount' => 0.00,
                        'refunded_amount' => 0.00,
                        'payment_method' => 'bank_transfer',
                        'billing_cycle' => $billingCycle,
                        'status' => 'pending',
                        'transaction_id' => 'TXN-BANK-' . strtoupper(\Illuminate\Support\Str::random(10)),
                        'reference_number' => $data['reference_number'] ?? ('DEP-' . strtoupper(\Illuminate\Support\Str::random(6))),
                        'receipt_url' => $receiptUrl,
                    ]);
                } elseif ($paymentMethod === 'card' && auth()->check() && auth()->user()->hasRole('Super Administrator') && ! empty($data['card_number'])) {
                    // Super Admin live card registration
                    $subscription = \App\Models\Platform\Subscription::create([
                        'shop_id' => $shop->id,
                        'plan_id' => $plan->id,
                        'status' => 'active',
                        'billing_cycle' => $billingCycle,
                        'recurring_amount' => $recurringAmount,
                        'trial_ends_at' => $trialEndsAt,
                        'current_period_starts_at' => $now,
                        'current_period_ends_at' => $now->copy()->addDays(30),
                        'last_paid_at' => $now,
                        'card_last4' => $last4,
                        'card_brand' => $brand,
                        'cardholder_name' => trim($data['cardholder_name'] ?? $data['name']),
                        'card_expiry' => trim($data['card_expiry'] ?? '12/28'),
                        'payment_method' => 'card',
                    ]);

                    \App\Models\Platform\SubscriptionPayment::create([
                        'shop_id' => $shop->id,
                        'plan_id' => $plan->id,
                        'subscription_id' => $subscription->id,
                        'amount' => $recurringAmount,
                        'discount_amount' => 0.00,
                        'refunded_amount' => 0.00,
                        'payment_method' => 'card',
                        'billing_cycle' => $billingCycle,
                        'status' => 'approved',
                        'transaction_id' => 'CARD-' . strtoupper(\Illuminate\Support\Str::random(10)),
                        'reference_number' => 'CARD-••••' . $last4,
                        'verified_at' => $now,
                        'verified_by' => auth()->id(),
                    ]);
                } else {
                    // Default / Free Trial Onboarding
                    $subscription = \App\Models\Platform\Subscription::create([
                        'shop_id' => $shop->id,
                        'plan_id' => $plan->id,
                        'status' => 'trial',
                        'billing_cycle' => $billingCycle,
                        'recurring_amount' => $recurringAmount,
                        'trial_ends_at' => $trialEndsAt,
                        'current_period_starts_at' => $now,
                        'current_period_ends_at' => $trialEndsAt,
                        'card_last4' => $last4,
                        'card_brand' => $brand,
                        'cardholder_name' => trim($data['cardholder_name'] ?? $data['name']),
                        'card_expiry' => trim($data['card_expiry'] ?? '12/28'),
                        'payment_method' => 'card',
                    ]);

                    \App\Models\Platform\SubscriptionPayment::create([
                        'shop_id' => $shop->id,
                        'plan_id' => $plan->id,
                        'subscription_id' => $subscription->id,
                        'amount' => 0.00,
                        'discount_amount' => 0.00,
                        'refunded_amount' => 0.00,
                        'payment_method' => 'card',
                        'billing_cycle' => $billingCycle,
                        'status' => 'approved',
                        'transaction_id' => 'AUTH-' . strtoupper(\Illuminate\Support\Str::random(10)),
                        'reference_number' => 'CARD-VERIFIED (•••• ' . $last4 . ')',
                        'verified_at' => $now,
                        'verified_by' => auth()->id() ?? $user->id,
                    ]);
                }
            }

            // 5. Grant shop administrator & owner privileges
            $user->assignRole(['Shop Owner', 'Administrator']);

            // 5. Fire Registered event for email verification notification
            event(new Registered($user));

            // 6. Record System Activity Log
            \App\Models\Platform\SystemActivity::log(
                'shop.registered',
                "New shop '{$shop->name}' registered with owner '{$user->name}' on '{$plan?->name}' plan (Card •••• {$last4})",
                $shop->id,
                $user->id,
                ['shop_name' => $shop->name, 'owner_email' => $user->email, 'plan' => $plan?->name, 'card_last4' => $last4]
            );

            return [
                'shop' => $shop,
                'user' => $user,
            ];
        });
    }
}

