<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\Outlet;
use App\Models\Payroll;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class StaffViewController extends Controller
{
    public function index(Request $request): View
    {
        $shopId = auth()->user()->shop_id ?? session('superadmin_active_shop_id');
        $tab = $request->input('tab', 'employees');

        // General Shop Resources
        $outlets = Outlet::orderBy('name')->get();
        $roles = Role::whereIn('name', ['Shop Manager', 'Cashier', 'Technician'])->get();
        $employees = Employee::with(['outlet', 'user'])->latest()->get();

        // 1. Employee Tab Metrics
        $totalStaff = $employees->count();
        $activeStaff = $employees->where('is_active', true)->count();
        $totalBasicPayroll = (float) $employees->where('is_active', true)->sum('basic_salary');

        // 2. Attendance Tab
        $attendanceDate = $request->input('attendance_date', now()->toDateString());
        $attendances = Attendance::with('employee')
            ->whereDate('work_date', $attendanceDate)
            ->latest()
            ->get();

        // 3. Leaves Tab
        $leaves = Leave::with('employee')
            ->latest()
            ->get();
        $onLeaveTodayCount = Leave::where('status', 'approved')
            ->whereDate('starts_on', '<=', now()->toDateString())
            ->whereDate('ends_on', '>=', now()->toDateString())
            ->count();

        // 4. Payroll Tab
        $payrollMonth = $request->input('payroll_month', now()->format('Y-m'));
        $payrolls = Payroll::with('employee')
            ->where('period', 'like', "{$payrollMonth}%")
            ->latest()
            ->get();
        $payrollTotalNet = $payrolls->sum('net_salary');
        $payrollPaidCount = $payrolls->where('status', 'paid')->count();
        $payrollDraftCount = $payrolls->where('status', 'draft')->count();
        $eligibleStaffCount = $employees->where('is_active', true)->where('basic_salary', '>', 0)->count();

        // 5. System Users
        $users = User::with('roles')
            ->where('shop_id', $shopId)
            ->latest()
            ->get();

        return view('shop.staff.index', compact(
            'tab',
            'outlets',
            'roles',
            'employees',
            'totalStaff',
            'activeStaff',
            'totalBasicPayroll',
            'attendanceDate',
            'attendances',
            'leaves',
            'onLeaveTodayCount',
            'payrollMonth',
            'payrolls',
            'payrollTotalNet',
            'payrollPaidCount',
            'payrollDraftCount',
            'eligibleStaffCount',
            'users'
        ));
    }

    public function storeEmployee(Request $request): RedirectResponse
    {
        $shopId = auth()->user()->shop_id ?? session('superadmin_active_shop_id');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('employees')->where(fn ($query) => $query->where('shop_id', $shopId)),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'position' => ['required', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:100'],
            'basic_salary' => ['nullable', 'numeric', 'min:0'],
            'outlet_id' => ['nullable', 'exists:outlets,id'],
            'joined_on' => ['required', 'date'],
            'create_user_account' => ['nullable', 'boolean'],
            'user_role' => ['nullable', 'string', 'in:Shop Manager,Cashier,Technician'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $employeeCode = 'EMP-' . strtoupper(substr(uniqid(), -5));

        $userId = null;
        if (! empty($data['create_user_account'])) {
            $user = User::create([
                'shop_id' => $shopId,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password'] ?? 'Password123!'),
                'email_verified_at' => now(),
            ]);

            if (! empty($data['user_role'])) {
                $user->assignRole($data['user_role']);
            }
            $userId = $user->id;
        }

        Employee::create([
            'shop_id' => $shopId,
            'employee_code' => $employeeCode,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'position' => $data['position'],
            'department' => $data['department'] ?? 'Retail Sales',
            'basic_salary' => $data['basic_salary'] ?? 0.00,
            'outlet_id' => $data['outlet_id'] ?? null,
            'user_id' => $userId,
            'joined_on' => $data['joined_on'],
            'is_active' => true,
        ]);

        return redirect()->route('shop.staff', ['tab' => 'employees'])
            ->with('success', "Employee '{$data['name']}' registered successfully with code {$employeeCode}.");
    }

    public function updateEmployee(Request $request, Employee $employee): RedirectResponse
    {
        $shopId = auth()->user()->shop_id ?? session('superadmin_active_shop_id');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('employees')->where(fn ($query) => $query->where('shop_id', $shopId))->ignore($employee->id),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'position' => ['required', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:100'],
            'basic_salary' => ['nullable', 'numeric', 'min:0'],
            'outlet_id' => ['nullable', 'exists:outlets,id'],
            'joined_on' => ['required', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $employee->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'position' => $data['position'],
            'department' => $data['department'] ?? 'Retail Sales',
            'basic_salary' => $data['basic_salary'] ?? 0.00,
            'outlet_id' => $data['outlet_id'] ?? null,
            'joined_on' => $data['joined_on'],
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('shop.staff', ['tab' => 'employees'])
            ->with('success', "Employee '{$employee->name}' updated successfully.");
    }

    public function destroyEmployee(Employee $employee): RedirectResponse
    {
        $name = $employee->name;
        $employee->delete();

        return redirect()->route('shop.staff', ['tab' => 'employees'])
            ->with('success', "Employee '{$name}' archived.");
    }

    public function clockAttendance(Request $request): RedirectResponse
    {
        $shopId = auth()->user()->shop_id ?? session('superadmin_active_shop_id');

        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'work_date' => ['required', 'date'],
            'clocked_in_at' => ['required', 'string'],
            'clocked_out_at' => ['nullable', 'string'],
        ]);

        $inDateTime = "{$data['work_date']} {$data['clocked_in_at']}:00";
        $outDateTime = $data['clocked_out_at'] ? "{$data['work_date']} {$data['clocked_out_at']}:00" : null;

        Attendance::updateOrCreate(
            [
                'shop_id' => $shopId,
                'employee_id' => $data['employee_id'],
                'work_date' => $data['work_date'],
            ],
            [
                'clocked_in_at' => $inDateTime,
                'clocked_out_at' => $outDateTime,
            ]
        );

        return redirect()->route('shop.staff', ['tab' => 'attendance', 'attendance_date' => $data['work_date']])
            ->with('success', 'Attendance record saved successfully.');
    }

    public function storeLeave(Request $request): RedirectResponse
    {
        $shopId = auth()->user()->shop_id ?? session('superadmin_active_shop_id');

        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after_or_equal:starts_on'],
            'type' => ['required', 'string', 'in:casual,medical,annual,other'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        Leave::create([
            'shop_id' => $shopId,
            'employee_id' => $data['employee_id'],
            'starts_on' => $data['starts_on'],
            'ends_on' => $data['ends_on'],
            'type' => $data['type'],
            'reason' => $data['reason'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('shop.staff', ['tab' => 'leaves'])
            ->with('success', 'Leave application submitted.');
    }

    public function updateLeaveStatus(Request $request, Leave $leave): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:approved,rejected,pending'],
        ]);

        $leave->update(['status' => $data['status']]);

        return redirect()->route('shop.staff', ['tab' => 'leaves'])
            ->with('success', "Leave status updated to {$data['status']}.");
    }

    public function storePayroll(Request $request): RedirectResponse
    {
        $shopId = auth()->user()->shop_id ?? session('superadmin_active_shop_id');

        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'period' => ['required', 'date_format:Y-m'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'allowances' => ['nullable', 'numeric', 'min:0'],
            'deductions' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $periodDate = "{$data['period']}-01";

        Payroll::updateOrCreate(
            [
                'shop_id' => $shopId,
                'employee_id' => $data['employee_id'],
                'period' => $periodDate,
            ],
            [
                'basic_salary' => $data['basic_salary'],
                'allowances' => $data['allowances'] ?? 0.00,
                'deductions' => $data['deductions'] ?? 0.00,
                'generation_type' => 'manual',
                'status' => 'draft',
                'note' => $data['note'] ?? null,
            ]
        );

        return redirect()->route('shop.staff', ['tab' => 'payroll', 'payroll_month' => $data['period']])
            ->with('success', 'Manual payslip calculated and draft saved successfully.');
    }

    public function updatePayroll(Request $request, Payroll $payroll): RedirectResponse
    {
        $data = $request->validate([
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'allowances' => ['nullable', 'numeric', 'min:0'],
            'deductions' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $payroll->update([
            'basic_salary' => $data['basic_salary'],
            'allowances' => $data['allowances'] ?? 0.00,
            'deductions' => $data['deductions'] ?? 0.00,
            'note' => $data['note'] ?? $payroll->note,
            'generation_type' => 'manual',
        ]);

        $periodMonth = Carbon::parse($payroll->period)->format('Y-m');

        return redirect()->route('shop.staff', ['tab' => 'payroll', 'payroll_month' => $periodMonth])
            ->with('success', "Payslip for {$payroll->employee->name} updated successfully.");
    }

    public function autoGeneratePayroll(Request $request): RedirectResponse
    {
        $shopId = auth()->user()->shop_id ?? session('superadmin_active_shop_id');

        $data = $request->validate([
            'period' => ['required', 'date_format:Y-m'],
            'overwrite' => ['nullable', 'boolean'],
            'include_leave_deductions' => ['nullable', 'boolean'],
        ]);

        $periodDate = "{$data['period']}-01";
        $periodCarbon = Carbon::createFromFormat('Y-m', $data['period']);
        $periodLabel = $periodCarbon->format('F Y');
        $startOfMonth = $periodCarbon->copy()->startOfMonth()->toDateString();
        $endOfMonth = $periodCarbon->copy()->endOfMonth()->toDateString();
        $overwrite = ! empty($data['overwrite']);
        $includeLeaveDeductions = ! empty($data['include_leave_deductions']);

        $employees = Employee::where('shop_id', $shopId)
            ->where('is_active', true)
            ->where('basic_salary', '>', 0)
            ->get();

        if ($employees->isEmpty()) {
            return redirect()->route('shop.staff', ['tab' => 'payroll', 'payroll_month' => $data['period']])
                ->with('error', 'No active employees with basic salary found to generate payroll.');
        }

        $generatedCount = 0;
        $skippedCount = 0;

        foreach ($employees as $employee) {
            $existing = Payroll::where('shop_id', $shopId)
                ->where('employee_id', $employee->id)
                ->where('period', $periodDate)
                ->first();

            if ($existing) {
                if ($existing->status === 'paid' || ! $overwrite) {
                    $skippedCount++;
                    continue;
                }
            }

            $deductions = 0.00;
            $note = 'Auto-generated monthly payslip';

            if ($includeLeaveDeductions) {
                $unpaidLeaves = Leave::where('shop_id', $shopId)
                    ->where('employee_id', $employee->id)
                    ->where('status', 'approved')
                    ->whereIn('type', ['other', 'casual'])
                    ->where('starts_on', '<=', $endOfMonth)
                    ->where('ends_on', '>=', $startOfMonth)
                    ->get();

                $unpaidDays = 0;
                foreach ($unpaidLeaves as $leave) {
                    $from = Carbon::parse(max($leave->starts_on, $startOfMonth));
                    $to = Carbon::parse(min($leave->ends_on, $endOfMonth));
                    $unpaidDays += $from->diffInDays($to) + 1;
                }

                if ($unpaidDays > 0) {
                    $dailyRate = $employee->basic_salary / 30;
                    $deductions = round($dailyRate * $unpaidDays, 2);
                    $note .= " (Includes {$unpaidDays} days unpaid leave deduction: LKR {$deductions})";
                }
            }

            Payroll::updateOrCreate(
                [
                    'shop_id' => $shopId,
                    'employee_id' => $employee->id,
                    'period' => $periodDate,
                ],
                [
                    'basic_salary' => $employee->basic_salary,
                    'allowances' => 0.00,
                    'deductions' => $deductions,
                    'generation_type' => 'auto',
                    'status' => 'draft',
                    'note' => $note,
                ]
            );

            $generatedCount++;
        }

        $msg = "⚡ Auto-generation complete: {$generatedCount} payslips generated for {$periodLabel}.";
        if ($skippedCount > 0) {
            $msg .= " ({$skippedCount} employees already had payslips and were skipped).";
        }

        return redirect()->route('shop.staff', ['tab' => 'payroll', 'payroll_month' => $data['period']])
            ->with('success', $msg);
    }

    public function markPayrollPaid(Request $request, Payroll $payroll): RedirectResponse
    {
        $data = $request->validate([
            'payment_method' => ['nullable', 'string', 'in:cash,bank_transfer,cheque,card,other'],
            'paid_at' => ['nullable', 'date'],
        ]);

        $payroll->update([
            'status' => 'paid',
            'payment_method' => $data['payment_method'] ?? 'cash',
            'paid_at' => $data['paid_at'] ?? now(),
        ]);

        return redirect()->route('shop.staff', ['tab' => 'payroll', 'payroll_month' => Carbon::parse($payroll->period)->format('Y-m')])
            ->with('success', "Payroll for {$payroll->employee->name} marked as PAID.");
    }

    public function destroyPayroll(Payroll $payroll): RedirectResponse
    {
        $periodMonth = Carbon::parse($payroll->period)->format('Y-m');
        $employeeName = $payroll->employee->name ?? 'Staff';

        if ($payroll->status === 'paid') {
            return redirect()->route('shop.staff', ['tab' => 'payroll', 'payroll_month' => $periodMonth])
                ->with('error', "Cannot delete an already paid salary slip for {$employeeName}.");
        }

        $payroll->delete();

        return redirect()->route('shop.staff', ['tab' => 'payroll', 'payroll_month' => $periodMonth])
            ->with('success', "Draft payslip for {$employeeName} deleted successfully.");
    }
}

