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
     *     timezone?: string,
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
            ]);

            // 2. Initialize default shop settings under the shop context
            app()->instance('shop.id', (int) $shop->id);
            try {
                $setting = new Setting([
                    'timezone' => $data['timezone'] ?? config('app.business_timezone', 'Asia/Colombo'),
                    'currency' => $data['currency'] ?? 'LKR',
                ]);
                $setting->shop_id = (int) $shop->id;
                $setting->save();
            } finally {
                app()->forgetInstance('shop.id');
            }

            // 3. Initialize 7-Day Free Trial Subscription
            $defaultPlan = \App\Models\Platform\SubscriptionPlan::where('slug', 'starter')->first();
            if ($defaultPlan) {
                $now = now();
                \App\Models\Platform\Subscription::create([
                    'shop_id' => $shop->id,
                    'plan_id' => $defaultPlan->id,
                    'status' => 'trial',
                    'trial_ends_at' => $now->copy()->addDays($defaultPlan->trial_days),
                    'current_period_starts_at' => $now,
                    'current_period_ends_at' => $now->copy()->addDays($defaultPlan->trial_days),
                ]);
            }

            // 4. Create the owner user account strictly bound to this shop
            $user = new User([
                'name' => trim($data['name']),
                'email' => strtolower(trim($data['email'])),
                'phone' => isset($data['phone']) ? trim($data['phone']) : null,
                'password' => $data['password'],
            ]);

            // Enforce server-side tenant assignment (never trust client-supplied IDs)
            $user->shop()->associate($shop);
            $user->save();

            // 5. Grant shop administrator & owner privileges
            $user->assignRole(['Shop Owner', 'Administrator']);

            // 5. Fire Registered event for email verification notification
            event(new Registered($user));

            return [
                'shop' => $shop,
                'user' => $user,
            ];
        });
    }
}

