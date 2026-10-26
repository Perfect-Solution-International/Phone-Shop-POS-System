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

            // 6. Record System Activity Log
            \App\Models\Platform\SystemActivity::log(
                'shop.registered',
                "New shop '{$shop->name}' registered with owner '{$user->name}' ({$user->email})",
                $shop->id,
                $user->id,
                ['shop_name' => $shop->name, 'owner_email' => $user->email]
            );

            return [
                'shop' => $shop,
                'user' => $user,
            ];
        });
    }
}

