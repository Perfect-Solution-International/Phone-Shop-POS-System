<?php

use App\Models\Outlet;
use App\Models\Platform\Shop;
use App\Models\Platform\Subscription;
use App\Models\Platform\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function (): void {
    $this->seed(DatabaseSeeder::class);
});

test('shop owner visiting outlets page auto-provisions main branch if none exists', function (): void {
    $shop = Shop::factory()->create(['name' => 'Metro Cellular', 'address' => '100 Galle Rd']);
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    expect(Outlet::withoutGlobalScopes()->where('shop_id', $shop->id)->count())->toBe(0);

    $response = $this->actingAs($owner, 'web')
        ->get(route('shop.outlets.index'));

    $response->assertOk();
    $response->assertViewIs('shop.outlets.index');
    $response->assertViewHas('outlets');
    $response->assertSee('Registered Outlets &amp; Showrooms', false);

    $outlets = Outlet::withoutGlobalScopes()->where('shop_id', $shop->id)->get();
    expect($outlets)->toHaveCount(1);
    expect($outlets->first()->is_main)->toBeTrue();
    expect($outlets->first()->code)->toBe('OUT-01');
});

test('shop owner can add a new outlet branch', function (): void {
    $shop = Shop::factory()->create(['name' => 'Metro Cellular']);
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    // Auto-create initial main branch
    Outlet::create([
        'shop_id' => $shop->id,
        'name' => 'Main Outlet',
        'code' => 'OUT-01',
        'address' => 'Colombo 03',
        'is_main' => true,
        'is_active' => true,
    ]);

    $response = $this->actingAs($owner, 'web')
        ->post(route('shop.outlets.store'), [
            'name' => 'Kandy City Showroom',
            'code' => 'KND-01',
            'phone' => '0812233445',
            'email' => 'kandy@metro.com',
            'address' => 'Level 2, KCC, Dalada Veediya',
            'city' => 'Kandy',
            'manager_name' => 'Nuwan Perera',
            'is_active' => '1',
        ]);

    $response->assertRedirect(route('shop.outlets.index'));
    $response->assertSessionHas('success');

    $outlet = Outlet::withoutGlobalScopes()
        ->where('shop_id', $shop->id)
        ->where('code', 'KND-01')
        ->first();

    expect($outlet)->not->toBeNull();
    expect($outlet->name)->toBe('Kandy City Showroom');
    expect($outlet->city)->toBe('Kandy');
    expect($outlet->manager_name)->toBe('Nuwan Perera');
    expect($outlet->is_main)->toBeFalse();
    expect($outlet->approval_status)->toBe('pending');
});

test('outlet code auto-generates if omitted', function (): void {
    $shop = Shop::factory()->create(['name' => 'Metro Cellular']);
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    Outlet::create([
        'shop_id' => $shop->id,
        'name' => 'Main Outlet',
        'code' => 'OUT-01',
        'address' => 'Colombo 03',
        'is_main' => true,
        'is_active' => true,
    ]);

    $response = $this->actingAs($owner, 'web')
        ->post(route('shop.outlets.store'), [
            'name' => 'Galle Fort Outlet',
            'code' => '',
            'address' => '45 Church Street',
            'city' => 'Galle',
        ]);

    $response->assertRedirect(route('shop.outlets.index'));

    $outlet = Outlet::withoutGlobalScopes()
        ->where('shop_id', $shop->id)
        ->where('name', 'Galle Fort Outlet')
        ->first();

    expect($outlet)->not->toBeNull();
    expect($outlet->code)->toBe('OUT-02');
});

test('duplicate outlet code within same shop is rejected', function (): void {
    $shop = Shop::factory()->create(['name' => 'Metro Cellular']);
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    Outlet::create([
        'shop_id' => $shop->id,
        'name' => 'Main Outlet',
        'code' => 'OUT-01',
        'address' => 'Colombo 03',
        'is_main' => true,
        'is_active' => true,
    ]);

    $response = $this->actingAs($owner, 'web')
        ->from(route('shop.outlets.index'))
        ->post(route('shop.outlets.store'), [
            'name' => 'Duplicate Code Branch',
            'code' => 'OUT-01',
            'address' => 'Kandy Rd',
        ]);

    $response->assertRedirect(route('shop.outlets.index'));
    $response->assertSessionHas('error');

    expect(Outlet::withoutGlobalScopes()->where('shop_id', $shop->id)->count())->toBe(1);
});

test('shop owner can add additional branch beyond plan quota marked as additional and unpaid', function (): void {
    $shop = Shop::factory()->create(['name' => 'Single Branch Shop']);
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    // Create a 1-branch plan
    $plan = SubscriptionPlan::create([
        'name' => 'Starter Single Branch',
        'slug' => 'starter-single',
        'monthly_price' => 2500,
        'annual_price' => 25000,
        'max_branches' => 1,
        'has_multi_branch' => false,
        'is_active' => true,
    ]);

    Subscription::create([
        'shop_id' => $shop->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'billing_cycle' => 'monthly',
        'recurring_amount' => 2500,
        'current_period_starts_at' => now()->subDay(),
        'current_period_ends_at' => now()->addMonth(),
        'trial_ends_at' => null,
    ]);

    // Create existing 1 approved outlet (exhausting the 1-branch plan quota)
    Outlet::create([
        'shop_id' => $shop->id,
        'name' => 'Existing Branch',
        'code' => 'OUT-01',
        'address' => 'Main St',
        'is_main' => true,
        'is_active' => true,
        'approval_status' => 'approved',
    ]);

    // Attempt to add a 2nd outlet
    $response = $this->actingAs($owner, 'web')
        ->from(route('shop.outlets.index'))
        ->post(route('shop.outlets.store'), [
            'name' => 'Second Branch',
            'code' => 'OUT-02',
            'address' => 'Second St',
        ]);

    $response->assertRedirect(route('shop.outlets.index'));
    $response->assertSessionHas('success');

    $secondOutlet = Outlet::withoutGlobalScopes()
        ->where('shop_id', $shop->id)
        ->where('code', 'OUT-02')
        ->first();

    expect($secondOutlet)->not->toBeNull();
    expect($secondOutlet->billing_type)->toBe('additional');
    expect($secondOutlet->payment_status)->toBe('unpaid');
    expect((float) $secondOutlet->additional_fee)->toBe(2500.00);
    expect($secondOutlet->approval_status)->toBe('pending');
});

test('shop owner can update outlet details', function (): void {
    $shop = Shop::factory()->create(['name' => 'Metro Cellular']);
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    $outlet = Outlet::create([
        'shop_id' => $shop->id,
        'name' => 'Old Branch Name',
        'code' => 'OUT-02',
        'address' => 'Old Address',
        'phone' => '0112223334',
        'is_main' => false,
        'is_active' => true,
    ]);

    $response = $this->actingAs($owner, 'web')
        ->put(route('shop.outlets.update', $outlet), [
            'name' => 'New Branch Name',
            'code' => 'OUT-02',
            'address' => 'Updated Address 99',
            'phone' => '0771234567',
            'city' => 'Negombo',
            'manager_name' => 'Kasun Silva',
            'is_active' => '1',
        ]);

    $response->assertRedirect(route('shop.outlets.index'));
    $response->assertSessionHas('success');

    $outlet->refresh();
    expect($outlet->name)->toBe('New Branch Name');
    expect($outlet->address)->toBe('Updated Address 99');
    expect($outlet->city)->toBe('Negombo');
    expect($outlet->manager_name)->toBe('Kasun Silva');
});

test('shop owner can switch active outlet in session', function (): void {
    $shop = Shop::factory()->create(['name' => 'Metro Cellular']);
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    $outlet = Outlet::create([
        'shop_id' => $shop->id,
        'name' => 'Kurunegala Branch',
        'code' => 'KRN-01',
        'address' => 'Circular Rd',
        'is_main' => false,
        'is_active' => true,
    ]);

    $response = $this->actingAs($owner, 'web')
        ->post(route('shop.outlets.switch', $outlet));

    $response->assertRedirect();
    $response->assertSessionHas('active_outlet_id', $outlet->id);
    $response->assertSessionHas('active_outlet_name', 'Kurunegala Branch');
    $response->assertSessionHas('active_outlet_code', 'KRN-01');
});

test('primary main branch cannot be deleted', function (): void {
    $shop = Shop::factory()->create(['name' => 'Metro Cellular']);
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    $mainOutlet = Outlet::create([
        'shop_id' => $shop->id,
        'name' => 'Primary Main Branch',
        'code' => 'OUT-01',
        'address' => 'Colombo 03',
        'is_main' => true,
        'is_active' => true,
    ]);

    $response = $this->actingAs($owner, 'web')
        ->from(route('shop.outlets.index'))
        ->delete(route('shop.outlets.destroy', $mainOutlet));

    $response->assertRedirect(route('shop.outlets.index'));
    $response->assertSessionHas('error');

    expect(Outlet::withoutGlobalScopes()->where('id', $mainOutlet->id)->exists())->toBeTrue();
});

test('non-main outlet can be deleted', function (): void {
    $shop = Shop::factory()->create(['name' => 'Metro Cellular']);
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    $secondaryOutlet = Outlet::create([
        'shop_id' => $shop->id,
        'name' => 'Secondary Branch',
        'code' => 'OUT-02',
        'address' => 'Moratuwa',
        'is_main' => false,
        'is_active' => true,
    ]);

    $response = $this->actingAs($owner, 'web')
        ->delete(route('shop.outlets.destroy', $secondaryOutlet));

    $response->assertRedirect(route('shop.outlets.index'));
    $response->assertSessionHas('success');

    expect(Outlet::withoutGlobalScopes()->where('id', $secondaryOutlet->id)->exists())->toBeFalse();
});

test('shop owner can set another outlet as main branch', function (): void {
    $shop = Shop::factory()->create(['name' => 'Metro Cellular']);
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    $oldMain = Outlet::create([
        'shop_id' => $shop->id,
        'name' => 'Old Main',
        'code' => 'OUT-01',
        'address' => 'Colombo',
        'is_main' => true,
        'is_active' => true,
    ]);

    $newMain = Outlet::create([
        'shop_id' => $shop->id,
        'name' => 'New Main',
        'code' => 'OUT-02',
        'address' => 'Kandy',
        'is_main' => false,
        'is_active' => true,
    ]);

    $response = $this->actingAs($owner, 'web')
        ->post(route('shop.outlets.toggle-main', $newMain));

    $response->assertRedirect(route('shop.outlets.index'));

    $oldMain->refresh();
    $newMain->refresh();

    expect($oldMain->is_main)->toBeFalse();
    expect($newMain->is_main)->toBeTrue();
});

test('cross-tenant isolation prevents Shop A from modifying or deleting Shop B outlet', function (): void {
    $shopA = Shop::factory()->create(['name' => 'Shop A']);
    $ownerA = User::factory()->create(['shop_id' => $shopA->id]);
    $ownerA->assignRole('Shop Owner');

    $shopB = Shop::factory()->create(['name' => 'Shop B']);
    $outletB = Outlet::create([
        'shop_id' => $shopB->id,
        'name' => 'Shop B Outlet',
        'code' => 'B-01',
        'address' => 'Galle Rd',
        'is_main' => false,
        'is_active' => true,
    ]);

    // Shop A owner tries to update Shop B's outlet
    $responseUpdate = $this->actingAs($ownerA, 'web')
        ->put(route('shop.outlets.update', $outletB), [
            'name' => 'Hacked Name',
            'code' => 'B-01',
            'address' => 'Hacked Address',
        ]);

    $responseUpdate->assertNotFound();

    // Shop A owner tries to delete Shop B's outlet
    $responseDelete = $this->actingAs($ownerA, 'web')
        ->delete(route('shop.outlets.destroy', $outletB));

    $responseDelete->assertNotFound();

    // Outlet B still untouched
    $outletB->refresh();
    expect($outletB->name)->toBe('Shop B Outlet');
});

test('shop owner on trial can create multiple branches up to 5 without quota block', function (): void {
    $shop = Shop::factory()->create();
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    $starterPlan = SubscriptionPlan::where('slug', 'starter')->first();
    Subscription::create([
        'shop_id' => $shop->id,
        'plan_id' => $starterPlan?->id ?? 1,
        'status' => 'trial',
        'trial_ends_at' => now()->addDays(7),
        'current_period_starts_at' => now(),
        'current_period_ends_at' => now()->addDays(7),
    ]);

    // Initial main branch
    Outlet::create([
        'shop_id' => $shop->id,
        'name' => 'Main Branch',
        'code' => 'OUT-01',
        'address' => 'Colombo',
        'is_main' => true,
        'is_active' => true,
    ]);

    // Owner creates 2nd branch
    $res2 = $this->actingAs($owner, 'web')
        ->post(route('shop.outlets.store'), [
            'name' => 'Kandy Branch',
            'code' => 'OUT-02',
            'address' => 'Dalada Veediya',
        ]);
    $res2->assertRedirect(route('shop.outlets.index'));
    $res2->assertSessionHas('success');

    // Owner creates 3rd branch
    $res3 = $this->actingAs($owner, 'web')
        ->post(route('shop.outlets.store'), [
            'name' => 'Galle Branch',
            'code' => 'OUT-03',
            'address' => 'Fort',
        ]);
    $res3->assertRedirect(route('shop.outlets.index'));
    $res3->assertSessionHas('success');

    expect(Outlet::withoutGlobalScopes()->where('shop_id', $shop->id)->count())->toBe(3);
});

test('shop owner cannot switch to branch while it is pending super admin approval', function (): void {
    $shop = Shop::factory()->create(['name' => 'Metro Cellular']);
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    $pendingOutlet = Outlet::create([
        'shop_id' => $shop->id,
        'name' => 'Jaffna Branch',
        'code' => 'JFN-01',
        'address' => 'Hospital Rd',
        'is_main' => false,
        'is_active' => false,
        'approval_status' => 'pending',
    ]);

    $response = $this->actingAs($owner, 'web')
        ->post(route('shop.outlets.switch', $pendingOutlet));

    $response->assertRedirect();
    $response->assertSessionHas('error');
    expect(session('active_outlet_id'))->toBeNull();
});

test('super admin can view branch approvals list and approve a pending branch', function (): void {
    $shop = Shop::factory()->create(['name' => 'Apex Mobile']);
    $superadmin = User::factory()->create(['shop_id' => null]);
    $superadmin->assignRole('Super Administrator');

    $pendingBranch = Outlet::create([
        'shop_id' => $shop->id,
        'name' => 'Matara Showroom',
        'code' => 'MTR-01',
        'address' => 'Anagarika Dharmapala Mawatha',
        'is_main' => false,
        'is_active' => false,
        'approval_status' => 'pending',
    ]);

    // Super Admin views branch approvals page
    $viewResp = $this->actingAs($superadmin, 'web')
        ->get(route('superadmin.outlets.approvals'));
    $viewResp->assertOk();
    $viewResp->assertSee('Matara Showroom');
    $viewResp->assertSee('Apex Mobile');

    // Super Admin approves the branch
    $approveResp = $this->actingAs($superadmin, 'web')
        ->post(route('superadmin.outlets.approve', $pendingBranch));
    $approveResp->assertRedirect();
    $approveResp->assertSessionHas('success');

    $pendingBranch->refresh();
    expect($pendingBranch->approval_status)->toBe('approved');
    expect($pendingBranch->is_active)->toBeTrue();
    expect($pendingBranch->approved_at)->not->toBeNull();
    expect($pendingBranch->approved_by)->toBe($superadmin->id);
});

test('super admin can reject a pending branch with reason', function (): void {
    $shop = Shop::factory()->create(['name' => 'Apex Mobile']);
    $superadmin = User::factory()->create(['shop_id' => null]);
    $superadmin->assignRole('Super Administrator');

    $pendingBranch = Outlet::create([
        'shop_id' => $shop->id,
        'name' => 'Negombo Branch',
        'code' => 'NGB-01',
        'address' => 'Beach Rd',
        'is_main' => false,
        'is_active' => false,
        'approval_status' => 'pending',
    ]);

    $rejectResp = $this->actingAs($superadmin, 'web')
        ->post(route('superadmin.outlets.reject', $pendingBranch), [
            'reason' => 'Invalid commercial branch address provided.',
        ]);

    $rejectResp->assertRedirect();
    $rejectResp->assertSessionHas('success');

    $pendingBranch->refresh();
    expect($pendingBranch->approval_status)->toBe('rejected');
    expect($pendingBranch->is_active)->toBeFalse();
    expect($pendingBranch->rejection_reason)->toBe('Invalid commercial branch address provided.');
});

test('shop owner can provision branch manager login credentials when creating outlet', function (): void {
    $shop = Shop::factory()->create(['name' => 'Metro Cellular']);
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    // Create main outlet first
    Outlet::create([
        'shop_id' => $shop->id,
        'name' => 'Main Outlet',
        'code' => 'OUT-01',
        'address' => 'Colombo 03',
        'is_main' => true,
        'is_active' => true,
        'approval_status' => 'approved',
    ]);

    $response = $this->actingAs($owner, 'web')
        ->post(route('shop.outlets.store'), [
            'name' => 'Kurunegala Branch',
            'code' => 'KRN-01',
            'phone' => '0372223334',
            'address' => 'Circular Road, Kurunegala',
            'city' => 'Kurunegala',
            'manager_name' => 'Sunil Shantha',
            'manager_email' => 'sunil.krn@metro.com',
            'manager_password' => 'ManagerSecret123!',
        ]);

    $response->assertRedirect(route('shop.outlets.index'));

    $outlet = Outlet::withoutGlobalScopes()
        ->where('shop_id', $shop->id)
        ->where('code', 'KRN-01')
        ->first();

    expect($outlet)->not->toBeNull();
    expect($outlet->manager_name)->toBe('Sunil Shantha');
    expect($outlet->manager_email)->toBe('sunil.krn@metro.com');
    expect($outlet->manager_user_id)->not->toBeNull();

    // Verify user account created for Branch Manager
    $managerUser = User::where('email', 'sunil.krn@metro.com')->first();
    expect($managerUser)->not->toBeNull();
    expect($managerUser->name)->toBe('Sunil Shantha');
    expect($managerUser->shop_id)->toBe($shop->id);
    expect($managerUser->outlet_id)->toBe($outlet->id);
    expect($managerUser->hasRole('Shop Manager'))->toBeTrue();
    expect(\Illuminate\Support\Facades\Hash::check('ManagerSecret123!', $managerUser->password))->toBeTrue();
});

test('branch manager can log in and session automatically initializes with their assigned branch', function (): void {
    $shop = Shop::factory()->create(['name' => 'Metro Cellular']);

    $outlet = Outlet::create([
        'shop_id' => $shop->id,
        'name' => 'Galle City Branch',
        'code' => 'GLE-01',
        'address' => 'Galle Fort',
        'city' => 'Galle',
        'manager_name' => 'Amal Silva',
        'manager_email' => 'amal.gle@metro.com',
        'is_main' => false,
        'is_active' => true,
        'approval_status' => 'approved',
    ]);

    $manager = User::factory()->create([
        'shop_id' => $shop->id,
        'outlet_id' => $outlet->id,
        'name' => 'Amal Silva',
        'email' => 'amal.gle@metro.com',
        'password' => bcrypt('AmalSecret123!'),
    ]);
    $manager->assignRole('Shop Manager');
    $outlet->update(['manager_user_id' => $manager->id]);

    $response = $this->post('/login', [
        'email' => 'amal.gle@metro.com',
        'password' => 'AmalSecret123!',
    ]);

    $response->assertRedirect(route('shop.dashboard'));
    $this->assertAuthenticatedAs($manager, 'web');

    // Verify session binds to their branch
    expect(session('active_outlet_id'))->toBe($outlet->id);
    expect(session('active_outlet_name'))->toBe('Galle City Branch');
    expect(session('active_outlet_code'))->toBe('GLE-01');
});

test('super admin can approve a branch covered by subscription plan quota without fee', function (): void {
    $shop = Shop::factory()->create(['name' => 'Growth Plan Shop']);
    $growthPlan = SubscriptionPlan::where('slug', 'growth')->first(); // Growth plan has 3 branches

    Subscription::create([
        'shop_id' => $shop->id,
        'plan_id' => $growthPlan->id,
        'status' => 'active',
        'billing_cycle' => 'monthly',
        'recurring_amount' => 8500,
        'current_period_starts_at' => now()->subDay(),
        'current_period_ends_at' => now()->addMonth(),
    ]);

    // Shop has only 1 approved branch (out of 3 allowed)
    Outlet::create([
        'shop_id' => $shop->id,
        'name' => 'Main Outlet',
        'code' => 'OUT-01',
        'address' => 'Colombo',
        'is_main' => true,
        'is_active' => true,
        'approval_status' => 'approved',
    ]);

    // Pending 2nd branch (covered by quota)
    $secondBranch = Outlet::create([
        'shop_id' => $shop->id,
        'name' => 'Galle Showroom',
        'code' => 'GLE-01',
        'address' => 'Galle Fort',
        'is_main' => false,
        'is_active' => false,
        'approval_status' => 'pending',
        'billing_type' => 'included',
        'payment_status' => 'paid',
    ]);

    expect($secondBranch->isCoveredByPlan())->toBeTrue();

    $superadmin = User::factory()->create(['shop_id' => null]);
    $superadmin->assignRole('Super Administrator');

    $response = $this->actingAs($superadmin, 'web')
        ->post(route('superadmin.outlets.approve', $secondBranch->id));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $secondBranch->refresh();
    expect($secondBranch->approval_status)->toBe('approved');
    expect($secondBranch->is_active)->toBeTrue();
});

test('super admin cannot approve unpaid additional branch without override', function (): void {
    $shop = Shop::factory()->create(['name' => 'Starter Plan Shop']);
    $starterPlan = SubscriptionPlan::where('slug', 'starter')->first(); // 1 branch limit

    Subscription::create([
        'shop_id' => $shop->id,
        'plan_id' => $starterPlan->id,
        'status' => 'active',
        'billing_cycle' => 'monthly',
        'recurring_amount' => 4500,
        'current_period_starts_at' => now()->subDay(),
        'current_period_ends_at' => now()->addMonth(),
    ]);

    // Shop already has 1 approved branch (quota full)
    Outlet::create([
        'shop_id' => $shop->id,
        'name' => 'Main Outlet',
        'code' => 'OUT-01',
        'address' => 'Colombo',
        'is_main' => true,
        'is_active' => true,
        'approval_status' => 'approved',
    ]);

    // Additional branch created beyond quota
    $extraBranch = Outlet::create([
        'shop_id' => $shop->id,
        'name' => 'Extra Branch',
        'code' => 'EXT-01',
        'address' => 'Moratuwa',
        'is_main' => false,
        'is_active' => false,
        'approval_status' => 'pending',
        'billing_type' => 'additional',
        'payment_status' => 'unpaid',
        'additional_fee' => 2500.00,
    ]);

    expect($extraBranch->isCoveredByPlan())->toBeFalse();

    $superadmin = User::factory()->create(['shop_id' => null]);
    $superadmin->assignRole('Super Administrator');

    // Attempt approval without payment/override
    $response = $this->actingAs($superadmin, 'web')
        ->post(route('superadmin.outlets.approve', $extraBranch->id));

    $response->assertRedirect();
    $response->assertSessionHas('error');

    $extraBranch->refresh();
    expect($extraBranch->approval_status)->toBe('pending');

    // With confirm_unpaid override
    $overrideResp = $this->actingAs($superadmin, 'web')
        ->post(route('superadmin.outlets.approve', $extraBranch->id), [
            'confirm_unpaid' => '1',
        ]);

    $overrideResp->assertRedirect();
    $overrideResp->assertSessionHas('success');

    $extraBranch->refresh();
    expect($extraBranch->approval_status)->toBe('approved');
    expect($extraBranch->payment_status)->toBe('paid');
});

test('shop owner can pay additional branch fee via card and super admin can approve it', function (): void {
    $shop = Shop::factory()->create(['name' => 'Starter Plan Shop']);
    $owner = User::factory()->create(['shop_id' => $shop->id]);
    $owner->assignRole('Shop Owner');

    $starterPlan = SubscriptionPlan::where('slug', 'starter')->first();
    Subscription::create([
        'shop_id' => $shop->id,
        'plan_id' => $starterPlan->id,
        'status' => 'active',
        'billing_cycle' => 'monthly',
        'recurring_amount' => 4500,
        'current_period_starts_at' => now()->subDay(),
        'current_period_ends_at' => now()->addMonth(),
    ]);

    // 1st approved branch
    Outlet::create([
        'shop_id' => $shop->id,
        'name' => 'Main Outlet',
        'code' => 'OUT-01',
        'address' => 'Colombo',
        'is_main' => true,
        'is_active' => true,
        'approval_status' => 'approved',
    ]);

    // 2nd unpaid branch
    $extraBranch = Outlet::create([
        'shop_id' => $shop->id,
        'name' => 'Panadura Branch',
        'code' => 'PAN-01',
        'address' => 'Panadura Town',
        'is_main' => false,
        'is_active' => false,
        'approval_status' => 'pending',
        'billing_type' => 'additional',
        'payment_status' => 'unpaid',
        'additional_fee' => 2500.00,
    ]);

    // Owner pays via card
    $payResp = $this->actingAs($owner, 'web')
        ->post(route('shop.outlets.pay', $extraBranch), [
            'payment_method' => 'card',
            'cardholder_name' => 'Owner Name',
            'card_number' => '4242424242424242',
            'card_expiry' => '12/28',
            'card_cvv' => '123',
        ]);

    $payResp->assertRedirect(route('shop.outlets.index'));
    $payResp->assertSessionHas('success');

    $extraBranch->refresh();
    expect($extraBranch->payment_status)->toBe('paid');
    expect($extraBranch->payment_method)->toBe('card');
    expect($extraBranch->paid_at)->not->toBeNull();

    // Now Super Admin can approve without override
    $superadmin = User::factory()->create(['shop_id' => null]);
    $superadmin->assignRole('Super Administrator');

    $approveResp = $this->actingAs($superadmin, 'web')
        ->post(route('superadmin.outlets.approve', $extraBranch->id));

    $approveResp->assertRedirect();
    $approveResp->assertSessionHas('success');

    $extraBranch->refresh();
    expect($extraBranch->approval_status)->toBe('approved');
    expect($extraBranch->is_active)->toBeTrue();
});

