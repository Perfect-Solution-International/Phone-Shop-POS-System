<?php

namespace Tests\Feature\Shop;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\Payroll;
use App\Models\Platform\Shop;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function createShopAndManager(): array
    {
        $shop = Shop::factory()->create(['name' => 'Cellular Point']);
        $manager = User::factory()->create(['shop_id' => $shop->id]);
        $manager->assignRole('Shop Manager');

        app()->instance('shop.id', $shop->id);

        return [$shop, $manager];
    }

    public function test_shop_manager_can_access_staff_hrm_page_and_tabs(): void
    {
        [$shop, $manager] = $this->createShopAndManager();

        $employee = Employee::create([
            'shop_id' => $shop->id,
            'employee_code' => 'EMP-001',
            'name' => 'Nimal Silva',
            'email' => 'nimal@cellularpoint.lk',
            'position' => 'Cashier',
            'basic_salary' => 55000.00,
            'joined_on' => now()->toDateString(),
            'is_active' => true,
        ]);

        // 1. Employee tab
        $res = $this->actingAs($manager)->get('/shop/staff?tab=employees');
        $res->assertOk()->assertViewIs('shop.staff.index');
        $res->assertSee('Nimal Silva');
        $res->assertSee('EMP-001');

        // 2. Attendance tab
        $attRes = $this->actingAs($manager)->get('/shop/staff?tab=attendance');
        $attRes->assertOk()->assertViewIs('shop.staff.index');
        $attRes->assertSee('Attendance Tracking');

        // 3. Leaves tab
        $leaveRes = $this->actingAs($manager)->get('/shop/staff?tab=leaves');
        $leaveRes->assertOk()->assertViewIs('shop.staff.index');
        $leaveRes->assertSee('Leave Requests &amp; Approvals', false);

        // 4. Payroll tab
        $payRes = $this->actingAs($manager)->get('/shop/staff?tab=payroll');
        $payRes->assertOk()->assertViewIs('shop.staff.index');
        $payRes->assertSee('Monthly Payroll &amp; Payslips', false);

        // 5. Roles tab
        $rolesRes = $this->actingAs($manager)->get('/shop/staff?tab=roles');
        $rolesRes->assertOk()->assertViewIs('shop.staff.index');
        $rolesRes->assertSee('Shop System Users &amp; Permissions', false);
    }

    public function test_manager_can_register_new_employee_with_user_account(): void
    {
        [$shop, $manager] = $this->createShopAndManager();

        $res = $this->actingAs($manager)->post('/shop/staff/employees', [
            'name' => 'Kasun Perera',
            'email' => 'kasun@cellularpoint.lk',
            'phone' => '0778899001',
            'position' => 'Phone Repair Technician',
            'basic_salary' => 65000.00,
            'joined_on' => now()->toDateString(),
            'create_user_account' => 1,
            'user_role' => 'Technician',
            'password' => 'TechPass123!',
        ]);

        $res->assertRedirect(route('shop.staff', ['tab' => 'employees']));

        $this->assertDatabaseHas('employees', [
            'shop_id' => $shop->id,
            'name' => 'Kasun Perera',
            'email' => 'kasun@cellularpoint.lk',
            'position' => 'Phone Repair Technician',
            'basic_salary' => 65000.00,
        ]);

        $this->assertDatabaseHas('users', [
            'shop_id' => $shop->id,
            'name' => 'Kasun Perera',
            'email' => 'kasun@cellularpoint.lk',
        ]);
    }

    public function test_manager_can_register_employee_when_user_account_email_already_exists(): void
    {
        [$shop, $manager] = $this->createShopAndManager();

        // Simulate pre-existing user with same email in another shop or orphaned
        $preUser = User::create([
            'shop_id' => 999,
            'name' => 'Existing Staff',
            'email' => 'duplicate_staff@example.com',
            'password' => bcrypt('OldPass123!'),
        ]);
        $preUser->assignRole('Cashier');

        $res = $this->actingAs($manager)->post('/shop/staff/employees', [
            'name' => 'Duplicate Staff Resolved',
            'email' => 'duplicate_staff@example.com',
            'phone' => '0771122334',
            'position' => 'Cashier',
            'basic_salary' => 50000.00,
            'joined_on' => now()->toDateString(),
            'create_user_account' => 1,
            'user_role' => 'Cashier',
            'password' => 'NewPass123!',
        ]);

        $res->assertRedirect(route('shop.staff', ['tab' => 'employees']));

        $this->assertDatabaseHas('employees', [
            'shop_id' => $shop->id,
            'name' => 'Duplicate Staff Resolved',
            'email' => 'duplicate_staff@example.com',
            'user_id' => $preUser->id,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $preUser->id,
            'shop_id' => $shop->id,
            'name' => 'Duplicate Staff Resolved',
            'email' => 'duplicate_staff@example.com',
        ]);
    }

    public function test_attendance_clock_in_and_clock_out(): void
    {
        [$shop, $manager] = $this->createShopAndManager();

        $employee = Employee::create([
            'shop_id' => $shop->id,
            'employee_code' => 'EMP-002',
            'name' => 'Saman Kumara',
            'email' => 'saman@cellularpoint.lk',
            'position' => 'Cashier',
            'joined_on' => now()->toDateString(),
        ]);

        $today = now()->toDateString();

        $res = $this->actingAs($manager)->post('/shop/staff/attendance', [
            'employee_id' => $employee->id,
            'work_date' => $today,
            'clocked_in_at' => '08:45',
            'clocked_out_at' => '17:30',
        ]);

        $res->assertRedirect(route('shop.staff', ['tab' => 'attendance', 'attendance_date' => $today]));

        $this->assertDatabaseHas('attendances', [
            'shop_id' => $shop->id,
            'employee_id' => $employee->id,
            'work_date' => $today,
        ]);
    }

    public function test_leave_application_and_approval(): void
    {
        [$shop, $manager] = $this->createShopAndManager();

        $employee = Employee::create([
            'shop_id' => $shop->id,
            'employee_code' => 'EMP-003',
            'name' => 'Sunil Bandara',
            'email' => 'sunil@cellularpoint.lk',
            'position' => 'Sales Assistant',
            'joined_on' => now()->toDateString(),
        ]);

        // 1. Submit leave
        $res = $this->actingAs($manager)->post('/shop/staff/leaves', [
            'employee_id' => $employee->id,
            'starts_on' => now()->addDays(2)->toDateString(),
            'ends_on' => now()->addDays(4)->toDateString(),
            'type' => 'casual',
            'reason' => 'Family wedding',
        ]);
        $res->assertRedirect(route('shop.staff', ['tab' => 'leaves']));

        $leave = Leave::withoutGlobalScopes()->where('shop_id', $shop->id)->first();
        $this->assertEquals('pending', $leave->status);

        // 2. Approve leave
        $appRes = $this->actingAs($manager)->patch("/shop/staff/leaves/{$leave->id}/status", [
            'status' => 'approved',
        ]);
        $appRes->assertRedirect(route('shop.staff', ['tab' => 'leaves']));

        $leave = Leave::withoutGlobalScopes()->find($leave->id);
        $this->assertEquals('approved', $leave->status);
    }

    public function test_manual_payroll_generation_edit_and_mark_as_paid(): void
    {
        [$shop, $manager] = $this->createShopEnvironment();

        $employee = Employee::create([
            'shop_id' => $shop->id,
            'employee_code' => 'EMP-004',
            'name' => 'Anura Senanayake',
            'email' => 'anura@cellularpoint.lk',
            'position' => 'Shop Manager',
            'basic_salary' => 80000.00,
            'joined_on' => now()->toDateString(),
        ]);

        $period = now()->format('Y-m');

        // 1. Generate manual payroll: Basic 80,000 + Allowance 10,000 - Deduction 5,000 = 85,000 net
        $res = $this->actingAs($manager)->post('/shop/staff/payroll', [
            'employee_id' => $employee->id,
            'period' => $period,
            'basic_salary' => 80000.00,
            'allowances' => 10000.00,
            'deductions' => 5000.00,
            'note' => 'Manual incentive added',
        ]);
        $res->assertRedirect(route('shop.staff', ['tab' => 'payroll', 'payroll_month' => $period]));

        $payroll = Payroll::withoutGlobalScopes()->where('shop_id', $shop->id)->first();
        $this->assertEquals(85000.00, (float) $payroll->net_salary);
        $this->assertEquals('manual', $payroll->generation_type);
        $this->assertEquals('draft', $payroll->status);
        $this->assertEquals('Manual incentive added', $payroll->note);

        // 2. Manually adjust/update draft payroll
        $updateRes = $this->actingAs($manager)->put("/shop/staff/payroll/{$payroll->id}", [
            'basic_salary' => 80000.00,
            'allowances' => 15000.00,
            'deductions' => 5000.00,
            'note' => 'Updated allowance',
        ]);
        $updateRes->assertRedirect(route('shop.staff', ['tab' => 'payroll', 'payroll_month' => $period]));

        $payroll = Payroll::withoutGlobalScopes()->find($payroll->id);
        $this->assertEquals(90000.00, (float) $payroll->net_salary);

        // 3. Mark as paid via Bank Transfer
        $paidRes = $this->actingAs($manager)->patch("/shop/staff/payroll/{$payroll->id}/paid", [
            'payment_method' => 'bank_transfer',
            'paid_at' => now()->toDateString(),
        ]);
        $paidRes->assertRedirect(route('shop.staff', ['tab' => 'payroll', 'payroll_month' => $period]));

        $payroll = Payroll::withoutGlobalScopes()->find($payroll->id);
        $this->assertEquals('paid', $payroll->status);
        $this->assertEquals('bank_transfer', $payroll->payment_method);
    }

    public function test_auto_payroll_bulk_generation(): void
    {
        [$shop, $manager] = $this->createShopEnvironment();

        // Create 2 active employees with basic salaries
        $emp1 = Employee::create([
            'shop_id' => $shop->id,
            'employee_code' => 'EMP-010',
            'name' => 'Ruwan Bandara',
            'email' => 'ruwan@cellularpoint.lk',
            'position' => 'Cashier',
            'basic_salary' => 50000.00,
            'is_active' => true,
            'joined_on' => now()->toDateString(),
        ]);

        $emp2 = Employee::create([
            'shop_id' => $shop->id,
            'employee_code' => 'EMP-011',
            'name' => 'Chathuri Silva',
            'email' => 'chathuri@cellularpoint.lk',
            'position' => 'Sales Assistant',
            'basic_salary' => 45000.00,
            'is_active' => true,
            'joined_on' => now()->toDateString(),
        ]);

        $period = now()->format('Y-m');

        // Run auto-generate
        $res = $this->actingAs($manager)->post('/shop/staff/payroll/auto-generate', [
            'period' => $period,
            'overwrite' => 0,
        ]);
        $res->assertRedirect(route('shop.staff', ['tab' => 'payroll', 'payroll_month' => $period]));

        $payrolls = Payroll::withoutGlobalScopes()->where('shop_id', $shop->id)->get();
        $this->assertCount(2, $payrolls);

        foreach ($payrolls as $pay) {
            $this->assertEquals('auto', $pay->generation_type);
            $this->assertEquals('draft', $pay->status);
            $this->assertTrue(in_array((float) $pay->basic_salary, [50000.00, 45000.00]));
        }

        // Run again without overwrite -> should skip both existing
        $res2 = $this->actingAs($manager)->post('/shop/staff/payroll/auto-generate', [
            'period' => $period,
            'overwrite' => 0,
        ]);
        $res2->assertRedirect(route('shop.staff', ['tab' => 'payroll', 'payroll_month' => $period]));
        $this->assertCount(2, Payroll::withoutGlobalScopes()->where('shop_id', $shop->id)->get());
    }

    public function test_auto_payroll_artisan_command(): void
    {
        [$shop, $manager] = $this->createShopEnvironment();

        Employee::create([
            'shop_id' => $shop->id,
            'employee_code' => 'EMP-020',
            'name' => 'Nimal Jayawardena',
            'email' => 'nimal@cellularpoint.lk',
            'position' => 'Technician',
            'basic_salary' => 60000.00,
            'is_active' => true,
            'joined_on' => now()->toDateString(),
        ]);

        $period = '2026-11';

        $this->artisan("payroll:auto-generate --month={$period} --shop={$shop->id}")
            ->assertExitCode(0);

        $payroll = Payroll::withoutGlobalScopes()
            ->where('shop_id', $shop->id)
            ->where('period', '2026-11-01')
            ->first();

        $this->assertNotNull($payroll);
        $this->assertEquals(60000.00, (float) $payroll->net_salary);
        $this->assertEquals('auto', $payroll->generation_type);
    }

    public function test_draft_payroll_deletion(): void
    {
        [$shop, $manager] = $this->createShopEnvironment();

        $employee = Employee::create([
            'shop_id' => $shop->id,
            'employee_code' => 'EMP-030',
            'name' => 'Kamal Dias',
            'email' => 'kamal@cellularpoint.lk',
            'position' => 'Cashier',
            'basic_salary' => 40000.00,
            'is_active' => true,
            'joined_on' => now()->toDateString(),
        ]);

        $payroll = Payroll::create([
            'shop_id' => $shop->id,
            'employee_id' => $employee->id,
            'period' => now()->startOfMonth()->toDateString(),
            'basic_salary' => 40000.00,
            'allowances' => 0,
            'deductions' => 0,
            'generation_type' => 'manual',
            'status' => 'draft',
        ]);

        $res = $this->actingAs($manager)->delete("/shop/staff/payroll/{$payroll->id}");
        $res->assertRedirect();

        $this->assertNull(Payroll::withoutGlobalScopes()->find($payroll->id));
    }

    private function createShopEnvironment(): array
    {
        return $this->createShopAndManager();
    }
}
