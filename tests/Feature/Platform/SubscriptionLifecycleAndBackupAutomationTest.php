<?php

namespace Tests\Feature\Platform;

use App\Models\Platform\Shop;
use App\Models\Platform\Subscription;
use App\Models\Platform\SubscriptionPlan;
use App\Models\Platform\SystemActivity;
use App\Models\User;
use App\Services\System\DatabaseBackupService;
use App\Services\System\DatabaseRestoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SubscriptionLifecycleAndBackupAutomationTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;
    protected SubscriptionPlan $plan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plan = SubscriptionPlan::create([
            'name' => 'Professional Plan',
            'slug' => 'pro-tier',
            'monthly_price' => 4500.00,
            'annual_price' => 45000.00,
            'trial_days' => 7,
            'is_active' => true,
        ]);

        $this->shop = Shop::create([
            'name' => 'Metro Mobile Hub',
            'email' => 'metro@example.com',
            'phone' => '0771234567',
        ]);
    }

    public function test_trial_expiration_transitions_to_grace_period_and_logs_audit_trail(): void
    {
        config(['subscriptions.grace_period_days' => 3]);

        $subscription = Subscription::create([
            'shop_id' => $this->shop->id,
            'plan_id' => $this->plan->id,
            'status' => 'trial',
            'trial_ends_at' => Carbon::now()->subHour(),
            'current_period_starts_at' => Carbon::now()->subDays(7),
            'current_period_ends_at' => Carbon::now()->subHour(),
        ]);

        $exitCode = Artisan::call('subscriptions:process-lifecycle');
        $this->assertSame(0, $exitCode);

        $subscription->refresh();
        $this->assertSame('grace_period', $subscription->status);
        $this->assertNotNull($subscription->grace_period_ends_at);
        $this->assertTrue($subscription->grace_period_ends_at->isFuture());

        $this->assertDatabaseHas('system_activities', [
            'action' => 'subscription.trial_expired_grace',
            'shop_id' => $this->shop->id,
        ]);
    }

    public function test_expired_grace_period_transitions_to_expired_and_logs_audit_trail(): void
    {
        $subscription = Subscription::create([
            'shop_id' => $this->shop->id,
            'plan_id' => $this->plan->id,
            'status' => 'grace_period',
            'trial_ends_at' => Carbon::now()->subDays(10),
            'grace_period_ends_at' => Carbon::now()->subMinutes(5),
            'current_period_starts_at' => Carbon::now()->subDays(10),
            'current_period_ends_at' => Carbon::now()->subDays(3),
        ]);

        $exitCode = Artisan::call('subscriptions:process-lifecycle');
        $this->assertSame(0, $exitCode);

        $subscription->refresh();
        $this->assertSame('expired', $subscription->status);

        $this->assertDatabaseHas('system_activities', [
            'action' => 'subscription.trial_expired',
            'shop_id' => $this->shop->id,
        ]);
    }

    public function test_active_subscription_past_period_end_transitions_to_grace_period(): void
    {
        config(['subscriptions.grace_period_days' => 5]);

        $subscription = Subscription::create([
            'shop_id' => $this->shop->id,
            'plan_id' => $this->plan->id,
            'status' => 'active',
            'current_period_starts_at' => Carbon::now()->subMonth(),
            'current_period_ends_at' => Carbon::now()->subHour(),
            'last_paid_at' => Carbon::now()->subMonth(),
            'recurring_amount' => 4500.00,
        ]);

        Artisan::call('subscriptions:process-lifecycle');

        $subscription->refresh();
        $this->assertSame('grace_period', $subscription->status);
        $this->assertNotNull($subscription->grace_period_ends_at);

        $this->assertDatabaseHas('system_activities', [
            'action' => 'subscription.renewal_overdue_grace',
            'shop_id' => $this->shop->id,
        ]);
    }

    public function test_backup_database_command_creates_backup_and_records_audit_trail(): void
    {
        $exitCode = Artisan::call('backup:database', ['--prune' => 0]);
        $this->assertSame(0, $exitCode);

        $backupService = app(DatabaseBackupService::class);
        $latest = $backupService->getLatestBackup();

        $this->assertNotNull($latest);
        $this->assertTrue(File::exists($latest['path']));
        $this->assertGreaterThan(0, $latest['size_bytes']);
        $this->assertNotEmpty($latest['checksum']);

        $this->assertDatabaseHas('system_activities', [
            'action' => 'system.backup_created',
        ]);
    }

    public function test_backup_database_command_prunes_old_backups(): void
    {
        $backupService = app(DatabaseBackupService::class);
        $dir = $backupService->getBackupDirectory();

        // Create an artificial old backup file
        $oldFile = $dir . DIRECTORY_SEPARATOR . 'backup-sqlite-old-test.sqlite';
        File::put($oldFile, 'SQLite format 3' . str_repeat("\0", 500));
        touch($oldFile, time() - (15 * 86400)); // 15 days ago

        $this->assertTrue(File::exists($oldFile));

        $exitCode = Artisan::call('backup:database', ['--prune' => 7]);
        $this->assertSame(0, $exitCode);

        $this->assertFalse(File::exists($oldFile), 'Old backup file should have been pruned.');

        $this->assertDatabaseHas('system_activities', [
            'action' => 'system.backup_pruned',
        ]);
    }

    public function test_restore_smoke_test_verifies_backup_integrity_without_altering_live_database(): void
    {
        // First, ensure a valid backup archive is created
        Artisan::call('backup:database');

        $exitCode = Artisan::call('backup:restore', ['--smoke-test' => true]);
        $this->assertSame(0, $exitCode);

        $this->assertDatabaseHas('system_activities', [
            'action' => 'system.restore_smoke_test',
        ]);

        // Live database records should remain untouched
        $this->assertDatabaseHas('shops', ['id' => $this->shop->id]);
    }

    public function test_super_admin_dashboard_displays_lifecycle_backup_and_smoke_test_audit_trail(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $superAdmin = User::factory()->create([
            'email' => 'superadmin@example.com',
            'shop_id' => null,
        ]);
        $superAdmin->assignRole('Super Administrator');

        // Generate backup and smoke test to populate activity log
        Artisan::call('backup:database');
        Artisan::call('backup:restore', ['--smoke-test' => true]);

        $response = $this->actingAs($superAdmin, 'web')->get('/super-admin');

        $response->assertOk();
        $response->assertSee('System &amp; Admin Activity Log', false);
        $response->assertSee('system.backup_created');
        $response->assertSee('system.restore_smoke_test');
    }
}
