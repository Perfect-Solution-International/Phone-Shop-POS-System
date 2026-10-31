@extends('layouts.shop')

@section('title', 'Staff HRM & Payroll')
@section('page-title', 'Staff HRM, Attendance & Payroll')

@section('content')
<!-- Store Management Unified Sub-Navigation Tabs -->
<div class="shop-subnav-tabs no-print" style="display: flex; gap: 0.45rem; margin-bottom: 1.1rem; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.75rem; overflow-x: auto; align-items: center;">
    <a href="{{ route('shop.outlets.index') }}" class="btn btn-sm {{ request()->routeIs('shop.outlets*') ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: 8px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.45rem 0.85rem; text-decoration: none; white-space: nowrap;">
        🏢 Outlets &amp; Branches
    </a>
    <a href="{{ route('shop.transfers.index') }}" class="btn btn-sm {{ request()->routeIs('shop.transfers*') ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: 8px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.45rem 0.85rem; text-decoration: none; white-space: nowrap;">
        🔄 Branch Transfers
    </a>
    <a href="{{ route('shop.staff') }}" class="btn btn-sm {{ request()->routeIs('shop.staff*') ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: 8px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.45rem 0.85rem; text-decoration: none; white-space: nowrap;">
        👔 Staff &amp; Payroll
    </a>
    <a href="{{ route('shop.subscription.index') }}" class="btn btn-sm {{ request()->routeIs('shop.subscription*') ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: 8px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.45rem 0.85rem; text-decoration: none; white-space: nowrap;">
        💳 Subscription &amp; Billing
    </a>
    <a href="{{ route('shop.settings') }}" class="btn btn-sm {{ request()->routeIs('shop.settings*') ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: 8px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.45rem 0.85rem; text-decoration: none; white-space: nowrap;">
        ⚙️ Store Settings
    </a>
</div>

<!-- Metric Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">Total Staff Members</span>
        <div style="font-size: 1.6rem; font-weight: 800; color: #0f172a; margin-top: 0.35rem;">
            {{ number_format($totalStaff) }}
        </div>
        <div style="font-size: 0.75rem; color: #166534; margin-top: 0.25rem;">{{ $activeStaff }} active employees</div>
    </div>

    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">On Leave Today</span>
        <div style="font-size: 1.6rem; font-weight: 800; color: #7c3aed; margin-top: 0.35rem;">
            {{ number_format($onLeaveTodayCount) }}
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">Approved leave applications</div>
    </div>

    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">Monthly Base Payroll</span>
        <div style="font-size: 1.6rem; font-weight: 800; color: #166534; margin-top: 0.35rem;">
            LKR {{ number_format($totalBasicPayroll, 2) }}
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">Fixed basic salary liability</div>
    </div>

    <div style="display: flex; align-items: center; justify-content: flex-end;">
        <button type="button" class="btn btn-primary" onclick="openAddEmployeeModal()" style="width: 100%; height: 100%; max-height: 52px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Register Staff Member
        </button>
    </div>
</div>

<!-- Tabs Navigation Header -->
<div style="display: flex; gap: 0.5rem; border-bottom: 2px solid #e2e8f0; margin-bottom: 1.5rem; overflow-x: auto; -webkit-overflow-scrolling: touch; white-space: nowrap; padding-bottom: 2px;">
    <a href="{{ route('shop.staff', ['tab' => 'employees']) }}" style="flex-shrink: 0; padding: 0.75rem 1.25rem; font-size: 0.875rem; font-weight: 700; text-decoration: none; color: {{ $tab === 'employees' ? '#4f46e5' : '#64748b' }}; border-bottom: 3px solid {{ $tab === 'employees' ? '#4f46e5' : 'transparent' }}; margin-bottom: -2px; display: inline-flex; align-items: center; gap: 0.4rem;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
        Staff Directory ({{ $employees->count() }})
    </a>
    <a href="{{ route('shop.staff', ['tab' => 'attendance']) }}" style="flex-shrink: 0; padding: 0.75rem 1.25rem; font-size: 0.875rem; font-weight: 700; text-decoration: none; color: {{ $tab === 'attendance' ? '#4f46e5' : '#64748b' }}; border-bottom: 3px solid {{ $tab === 'attendance' ? '#4f46e5' : 'transparent' }}; margin-bottom: -2px; display: inline-flex; align-items: center; gap: 0.4rem;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
        Attendance Tracking
    </a>
    <a href="{{ route('shop.staff', ['tab' => 'leaves']) }}" style="flex-shrink: 0; padding: 0.75rem 1.25rem; font-size: 0.875rem; font-weight: 700; text-decoration: none; color: {{ $tab === 'leaves' ? '#4f46e5' : '#64748b' }}; border-bottom: 3px solid {{ $tab === 'leaves' ? '#4f46e5' : 'transparent' }}; margin-bottom: -2px; display: inline-flex; align-items: center; gap: 0.4rem;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
        Leave Applications ({{ $leaves->count() }})
    </a>
    <a href="{{ route('shop.staff', ['tab' => 'payroll']) }}" style="flex-shrink: 0; padding: 0.75rem 1.25rem; font-size: 0.875rem; font-weight: 700; text-decoration: none; color: {{ $tab === 'payroll' ? '#4f46e5' : '#64748b' }}; border-bottom: 3px solid {{ $tab === 'payroll' ? '#4f46e5' : 'transparent' }}; margin-bottom: -2px; display: inline-flex; align-items: center; gap: 0.4rem;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"></rect><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
        Monthly Payroll &amp; Payslips
    </a>
    <a href="{{ route('shop.staff', ['tab' => 'roles']) }}" style="flex-shrink: 0; padding: 0.75rem 1.25rem; font-size: 0.875rem; font-weight: 700; text-decoration: none; color: {{ $tab === 'roles' ? '#4f46e5' : '#64748b' }}; border-bottom: 3px solid {{ $tab === 'roles' ? '#4f46e5' : 'transparent' }}; margin-bottom: -2px; display: inline-flex; align-items: center; gap: 0.4rem;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
        System Users &amp; Roles
    </a>
</div>

<!-- ========================================== -->
<!-- TAB 1: EMPLOYEES DIRECTORY                 -->
<!-- ========================================== -->
@if($tab === 'employees')
<div class="card">
    <div class="card-header">
        <strong style="color: #0f172a; font-size: 1rem;">Phone Shop Staff Directory</strong>
        <span class="badge badge-info">{{ $activeStaff }} Active Staff</span>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Emp Code</th>
                    <th>Staff Name &amp; Contact</th>
                    <th>Role / Designation</th>
                    <th>Assigned Branch</th>
                    <th>Base Salary</th>
                    <th>Status</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                <tr>
                    <td>
                        <strong style="color: #4f46e5; font-family: monospace; font-size: 0.85rem;">
                            {{ $emp->employee_code }}
                        </strong>
                    </td>
                    <td>
                        <strong style="color: #0f172a; font-size: 0.9375rem;">{{ $emp->name }}</strong>
                        <div style="font-size: 0.75rem; color: #64748b;">
                            {{ $emp->email }} @if($emp->phone) | Tel: {{ $emp->phone }} @endif
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-purple" style="font-weight: 700;">{{ $emp->position }}</span>
                        @if($emp->user)
                            <div style="font-size: 0.7rem; color: #0284c7; margin-top: 2px;">
                                System Account Linked ({{ $emp->user->roles->first()->name ?? 'User' }})
                            </div>
                        @endif
                    </td>
                    <td>
                        <span style="font-size: 0.8125rem; color: #475569;">
                            {{ $emp->outlet->name ?? 'Main Branch' }}
                        </span>
                    </td>
                    <td>
                        <strong style="color: #166534; font-size: 0.875rem;">
                            LKR {{ number_format($emp->basic_salary, 2) }}
                        </strong>
                    </td>
                    <td>
                        @if($emp->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-danger">Inactive</span>
                        @endif
                    </td>
                    <td style="text-align: right; white-space: nowrap;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick='openEditEmployeeModal(@json($emp))' style="padding: 0.3rem 0.6rem;">Edit</button>
                        <form action="{{ route('shop.staff.employees.destroy', $emp) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Archive staff member {{ $emp->name }}?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm" style="background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; padding: 0.3rem 0.6rem;">Archive</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 3rem; color: #94a3b8;">
                        No staff members registered. Click "+ Register Staff Member" to add cashiers, technicians, and managers.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- ========================================== -->
<!-- TAB 2: ATTENDANCE TRACKING                 -->
<!-- ========================================== -->
@if($tab === 'attendance')
<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <form method="GET" action="{{ route('shop.staff') }}" style="display: flex; gap: 0.75rem; align-items: center;">
            <input type="hidden" name="tab" value="attendance">
            <label style="font-size: 0.8125rem; font-weight: 700; color: #475569; margin: 0;">Date:</label>
            <input type="date" name="attendance_date" class="form-control" style="max-width: 170px;" value="{{ $attendanceDate }}" onchange="this.form.submit()">
            <button type="submit" class="btn btn-secondary btn-sm">Load</button>
        </form>

        <button type="button" class="btn btn-primary" onclick="openModal('attendanceModal')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            Clock Attendance Entry
        </button>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Staff Name &amp; Code</th>
                    <th>Designation</th>
                    <th>Work Date</th>
                    <th>Clock In</th>
                    <th>Clock Out</th>
                    <th>Attendance Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $att)
                <tr>
                    <td>
                        <strong style="color: #0f172a; font-size: 0.875rem;">{{ $att->employee->name ?? 'Staff' }}</strong>
                        <div style="font-size: 0.72rem; color: #64748b; font-family: monospace;">{{ $att->employee->employee_code ?? '' }}</div>
                    </td>
                    <td>
                        <span class="badge badge-purple">{{ $att->employee->position ?? 'Staff' }}</span>
                    </td>
                    <td style="font-size: 0.8125rem; color: #64748b;">
                        {{ $att->work_date->format('M d, Y') }}
                    </td>
                    <td>
                        <strong style="color: #166534; font-family: monospace; font-size: 0.875rem;">
                            {{ $att->clocked_in_at ? \Carbon\Carbon::parse($att->clocked_in_at)->format('h:i A') : '—' }}
                        </strong>
                    </td>
                    <td>
                        @if($att->clocked_out_at)
                            <strong style="color: #475569; font-family: monospace; font-size: 0.875rem;">
                                {{ \Carbon\Carbon::parse($att->clocked_out_at)->format('h:i A') }}
                            </strong>
                        @else
                            <span class="badge badge-warning">On Shift (Working)</span>
                        @endif
                    </td>
                    <td>
                        @if($att->clocked_out_at)
                            <span class="badge badge-success">Completed Shift</span>
                        @else
                            <span class="badge badge-info">Active Clock-in</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 3rem; color: #94a3b8;">
                        No attendance records logged for {{ \Carbon\Carbon::parse($attendanceDate)->format('M d, Y') }}. Click "+ Clock Attendance Entry" to record shifts.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- ========================================== -->
<!-- TAB 3: LEAVE APPLICATIONS                  -->
<!-- ========================================== -->
@if($tab === 'leaves')
<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <strong style="color: #0f172a; font-size: 1rem;">Leave Requests &amp; Approvals</strong>
        <button type="button" class="btn btn-primary" onclick="openModal('leaveModal')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Apply for Staff Leave
        </button>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Staff Name</th>
                    <th>Leave Type</th>
                    <th>From Date</th>
                    <th>To Date</th>
                    <th>Reason / Notes</th>
                    <th>Status</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaves as $leave)
                <tr>
                    <td>
                        <strong style="color: #0f172a; font-size: 0.875rem;">{{ $leave->employee->name ?? 'Staff' }}</strong>
                        <div style="font-size: 0.72rem; color: #64748b;">{{ $leave->employee->position ?? '' }}</div>
                    </td>
                    <td>
                        <span class="badge" style="background: #f1f5f9; color: #334155; text-transform: uppercase; font-size: 0.7rem; font-weight: 700;">
                            {{ $leave->type }}
                        </span>
                    </td>
                    <td style="font-size: 0.8125rem; color: #475569; white-space: nowrap;">
                        {{ $leave->starts_on->format('M d, Y') }}
                    </td>
                    <td style="font-size: 0.8125rem; color: #475569; white-space: nowrap;">
                        {{ $leave->ends_on->format('M d, Y') }}
                    </td>
                    <td style="font-size: 0.8125rem; color: #64748b;">
                        {{ $leave->reason ?? '—' }}
                    </td>
                    <td>
                        @if($leave->status === 'approved')
                            <span class="badge badge-success">Approved</span>
                        @elseif($leave->status === 'rejected')
                            <span class="badge badge-danger">Rejected</span>
                        @else
                            <span class="badge badge-warning">Pending Review</span>
                        @endif
                    </td>
                    <td style="text-align: right; white-space: nowrap;">
                        @if($leave->status === 'pending')
                            <form action="{{ route('shop.staff.leaves.status', $leave) }}" method="POST" style="display: inline-block;">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="approved">
                                <button type="submit" class="btn btn-sm" style="background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; padding: 0.25rem 0.6rem;">Approve</button>
                            </form>
                            <form action="{{ route('shop.staff.leaves.status', $leave) }}" method="POST" style="display: inline-block;">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="btn btn-sm" style="background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; padding: 0.25rem 0.6rem;">Reject</button>
                            </form>
                        @else
                            <span style="font-size: 0.75rem; color: #94a3b8;">Resolved</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 3rem; color: #94a3b8;">
                        No leave applications submitted yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- ========================================== -->
<!-- TAB 4: PAYROLL & SALARY SLIPS              -->
<!-- ========================================== -->
@if($tab === 'payroll')
<style>
    @media (max-width: 768px) {
        .payroll-desktop-table-wrap {
            display: none !important;
        }
        .payroll-mobile-cards-wrap {
            display: flex !important;
            flex-direction: column;
            gap: 0.85rem;
            padding: 1rem;
        }
    }
    @media (min-width: 769px) {
        .payroll-mobile-cards-wrap {
            display: none !important;
        }
    }
    .payroll-card-item {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
    }
</style>
<!-- Mini summary cards for selected payroll month -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1rem 1.25rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">Period Payout</span>
        <strong style="font-size: 1.35rem; color: #0f172a; font-weight: 800; display: block; margin-top: 0.25rem;">LKR {{ number_format($payrollTotalNet, 2) }}</strong>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.2rem;">For {{ \Carbon\Carbon::parse($payrollMonth . '-01')->format('F Y') }}</div>
    </div>
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1rem 1.25rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">Paid Payslips</span>
        <strong style="font-size: 1.35rem; color: #166534; font-weight: 800; display: block; margin-top: 0.25rem;">{{ $payrollPaidCount }} Paid</strong>
        <div style="font-size: 0.75rem; color: #166534; margin-top: 0.2rem;">Settled and completed</div>
    </div>
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1rem 1.25rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">Draft Payslips</span>
        <strong style="font-size: 1.35rem; color: #d97706; font-weight: 800; display: block; margin-top: 0.25rem;">{{ $payrollDraftCount }} Draft</strong>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.2rem;">Awaiting manager review & pay</div>
    </div>
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1rem 1.25rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">Eligible Staff Pool</span>
        <strong style="font-size: 1.35rem; color: #4f46e5; font-weight: 800; display: block; margin-top: 0.25rem;">{{ $eligibleStaffCount }} Employees</strong>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.2rem;">Ready for auto/manual payroll</div>
    </div>
</div>

<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem; align-items: center; justify-content: space-between;">
        <form method="GET" action="{{ route('shop.staff') }}" style="display: flex; gap: 0.75rem; align-items: center;">
            <input type="hidden" name="tab" value="payroll">
            <label style="font-size: 0.8125rem; font-weight: 700; color: #475569; margin: 0;">Period:</label>
            <input type="month" name="payroll_month" class="form-control" style="max-width: 170px;" value="{{ $payrollMonth }}" onchange="this.form.submit()">
            <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
        </form>

        <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            <!-- Auto-Generate Button -->
            <button type="button" class="btn" onclick="openAutoPayrollModal()" style="background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); color: #ffffff; border: none; font-weight: 700; box-shadow: 0 2px 4px rgba(79, 70, 229, 0.25); display: inline-flex; align-items: center; gap: 0.4rem;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                Auto-Generate Monthly Payroll
            </button>

            <!-- Manual Generate Button -->
            <button type="button" class="btn btn-secondary" onclick="openManualPayrollModal()" style="font-weight: 700; border: 1px solid #cbd5e1; background: #ffffff; display: inline-flex; align-items: center; gap: 0.4rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Manual Payslip
            </button>
        </div>
    </div>

    <div class="table-responsive payroll-desktop-table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Staff Member</th>
                    <th>Salary Month</th>
                    <th>Basic Salary</th>
                    <th>Allowances</th>
                    <th>Deductions</th>
                    <th>Net Take-Home Pay</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payrolls as $pay)
                <tr>
                    <td>
                        <strong style="color: #0f172a; font-size: 0.875rem;">{{ $pay->employee->name ?? 'Staff' }}</strong>
                        <div style="font-size: 0.72rem; color: #64748b;">
                            {{ $pay->employee->employee_code ?? 'EMP' }} &bull; {{ $pay->employee->position ?? 'Staff' }}
                        </div>
                    </td>
                    <td style="font-size: 0.8125rem; color: #475569; font-family: monospace;">
                        {{ \Carbon\Carbon::parse($pay->period)->format('F Y') }}
                    </td>
                    <td style="font-size: 0.875rem; color: #475569;">
                        LKR {{ number_format($pay->basic_salary, 2) }}
                    </td>
                    <td style="font-size: 0.875rem; color: #166534;">
                        +LKR {{ number_format($pay->allowances, 2) }}
                    </td>
                    <td style="font-size: 0.875rem; color: #b91c1c;">
                        -LKR {{ number_format($pay->deductions, 2) }}
                    </td>
                    <td>
                        <strong style="color: #166534; font-size: 0.9375rem;">
                            LKR {{ number_format($pay->net_salary, 2) }}
                        </strong>
                    </td>
                    <td>
                        @if($pay->generation_type === 'auto')
                            <span class="badge" style="background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; font-size: 0.72rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.25rem;">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                                Auto
                            </span>
                        @else
                            <span class="badge" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; font-size: 0.72rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.25rem;">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                Manual
                            </span>
                        @endif
                    </td>
                    <td>
                        @if($pay->status === 'paid')
                            <span class="badge badge-success" title="Paid via {{ strtoupper($pay->payment_method ?? 'CASH') }}">
                                ✓ Paid ({{ ucfirst($pay->payment_method ?? 'cash') }})
                            </span>
                        @else
                            <span class="badge badge-warning">Draft</span>
                        @endif
                    </td>
                    <td style="text-align: right; white-space: nowrap;">
                        <div style="display: inline-flex; gap: 0.35rem; align-items: center;">
                            @if($pay->status !== 'paid')
                                <button type="button" class="btn btn-sm btn-primary" onclick='openMarkPaidModal(@json($pay))' style="padding: 0.25rem 0.6rem; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.2rem;">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    Pay
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary" onclick='openEditPayrollModal(@json($pay))' style="padding: 0.25rem 0.6rem; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.2rem;" title="Adjust Basic, Allowances & Deductions">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                    Edit
                                </button>
                                <form action="{{ route('shop.staff.payroll.destroy', $pay) }}" method="POST" onsubmit="return confirm('Delete this draft payslip for {{ $pay->employee->name }}?')" style="display: inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;" title="Delete Draft">✕</button>
                                </form>
                            @endif
                            <button type="button" class="btn btn-secondary btn-sm" onclick='viewPayslipModal(@json($pay))' style="padding: 0.25rem 0.6rem; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.2rem;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                                Slip
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align: center; padding: 3.5rem 1rem; color: #94a3b8;">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" style="margin-bottom: 0.75rem;"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                        <div style="font-weight: 600; color: #64748b; font-size: 0.9375rem;">No payslips generated for {{ \Carbon\Carbon::parse($payrollMonth . '-01')->format('F Y') }}</div>
                        <div style="font-size: 0.8125rem; color: #94a3b8; margin-top: 0.25rem;">Click <strong>Auto-Generate Monthly Payroll</strong> to process all staff at once, or <strong>+ Manual Payslip</strong> for a single employee.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Stacked Card View (<= 768px) -->
    <div class="payroll-mobile-cards-wrap">
        @forelse($payrolls as $pay)
            <div class="payroll-card-item">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem;">
                    <div>
                        <strong style="color: #0f172a; font-size: 0.95rem;">{{ $pay->employee->name ?? 'Staff' }}</strong>
                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;">
                            {{ $pay->employee->employee_code ?? 'EMP' }} &bull; {{ $pay->employee->position ?? 'Staff' }}
                        </div>
                    </div>
                    <div>
                        @if($pay->status === 'paid')
                            <span class="badge badge-success" style="font-size: 0.72rem;">✓ Paid ({{ ucfirst($pay->payment_method ?? 'cash') }})</span>
                        @else
                            <span class="badge badge-warning" style="font-size: 0.72rem;">Draft</span>
                        @endif
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; background: #f8fafc; padding: 0.65rem 0.75rem; border-radius: 8px; font-size: 0.78rem;">
                    <div>
                        <span style="color: #64748b; display: block; font-size: 0.7rem;">Period:</span>
                        <strong>{{ \Carbon\Carbon::parse($pay->period)->format('F Y') }}</strong>
                    </div>
                    <div>
                        <span style="color: #64748b; display: block; font-size: 0.7rem;">Basic:</span>
                        <span>LKR {{ number_format($pay->basic_salary, 2) }}</span>
                    </div>
                    <div>
                        <span style="color: #166534; display: block; font-size: 0.7rem;">Allowances:</span>
                        <span style="color: #166534; font-weight: 600;">+LKR {{ number_format($pay->allowances, 2) }}</span>
                    </div>
                    <div>
                        <span style="color: #b91c1c; display: block; font-size: 0.7rem;">Deductions:</span>
                        <span style="color: #b91c1c; font-weight: 600;">-LKR {{ number_format($pay->deductions, 2) }}</span>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.4rem; border-top: 1px dashed #e2e8f0;">
                    <div>
                        <span style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; font-weight: 700; display: block;">Net Take-Home</span>
                        <strong style="color: #166534; font-size: 1.05rem;">LKR {{ number_format($pay->net_salary, 2) }}</strong>
                    </div>
                    <div style="display: flex; gap: 0.35rem; align-items: center;">
                        @if($pay->status !== 'paid')
                            <button type="button" class="btn btn-sm btn-primary" onclick='openMarkPaidModal(@json($pay))' style="padding: 0.3rem 0.6rem; font-size: 0.75rem;">
                                Pay
                            </button>
                            <button type="button" class="btn btn-sm btn-secondary" onclick='openEditPayrollModal(@json($pay))' style="padding: 0.3rem 0.5rem; font-size: 0.75rem;">
                                Edit
                            </button>
                            <form action="{{ route('shop.staff.payroll.destroy', $pay) }}" method="POST" onsubmit="return confirm('Delete this draft payslip for {{ $pay->employee->name }}?')" style="display: inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" style="padding: 0.3rem 0.5rem; font-size: 0.75rem;">✕</button>
                            </form>
                        @endif
                        <button type="button" class="btn btn-secondary btn-sm" onclick='viewPayslipModal(@json($pay))' style="padding: 0.3rem 0.6rem; font-size: 0.75rem;">
                            Slip
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div style="text-align: center; padding: 2.5rem 1rem; color: #94a3b8;">
                <div style="font-weight: 600; color: #64748b; font-size: 0.9rem;">No payslips generated for this period</div>
            </div>
        @endforelse
    </div>
</div>
@endif

<!-- ========================================== -->
<!-- TAB 5: USERS & ROLES                       -->
<!-- ========================================== -->
@if($tab === 'roles')
<div class="card">
    <div class="card-header">
        <div>
            <strong style="color: #0f172a; font-size: 1rem;">Shop System Users &amp; Permissions</strong>
            <div style="font-size: 0.8125rem; color: #64748b;">Staff members with POS and management panel login access.</div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>User Name</th>
                    <th>Login Email</th>
                    <th>Phone</th>
                    <th>Assigned Role</th>
                    <th>Account Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                <tr>
                    <td>
                        <strong style="color: #0f172a; font-size: 0.9375rem;">{{ $u->name }}</strong>
                    </td>
                    <td style="font-size: 0.8125rem; color: #475569; font-family: monospace;">
                        {{ $u->email }}
                    </td>
                    <td style="font-size: 0.8125rem; color: #64748b;">
                        {{ $u->phone ?? '—' }}
                    </td>
                    <td>
                        @foreach($u->roles as $r)
                            <span class="badge badge-purple">{{ $r->name }}</span>
                        @endforeach
                    </td>
                    <td>
                        <span class="badge badge-success">Active Login</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Modal: Register / Edit Employee -->
<div class="modal-backdrop" id="employeeModal">
    <div class="modal-box" style="max-width: 620px;">
        <div class="modal-header">
            <h3 id="empModalTitle">Register Staff Member</h3>
            <button type="button" class="modal-close" onclick="closeModal('employeeModal')">&times;</button>
        </div>
        <form id="employeeForm" action="{{ route('shop.staff.employees.store') }}" method="POST">
            @csrf
            <div id="empMethodPut"></div>
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Staff Full Name *</label>
                        <input type="text" name="name" id="inpEmpName" class="form-control" required placeholder="e.g. Ruwan Jayawardena">
                    </div>
                    <div class="form-group">
                        <label>Email Address *</label>
                        <input type="email" name="email" id="inpEmpEmail" class="form-control" required placeholder="ruwan@example.com">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" id="inpEmpPhone" class="form-control" placeholder="0771234567">
                    </div>
                    <div class="form-group">
                        <label>Designation / Role *</label>
                        <select name="position" id="inpEmpPosition" class="form-control" required>
                            <option value="Cashier">Cashier</option>
                            <option value="Phone Repair Technician">Phone Repair Technician</option>
                            <option value="Shop Manager">Shop Manager</option>
                            <option value="Stock Keeper">Stock Keeper</option>
                            <option value="Sales Assistant">Sales Assistant</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Assigned Outlet / Branch</label>
                        <select name="outlet_id" id="inpEmpOutlet" class="form-control">
                            <option value="">Main Branch</option>
                            @foreach($outlets as $o)
                                <option value="{{ $o->id }}">{{ $o->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Basic Monthly Salary (LKR)</label>
                        <input type="number" step="0.01" name="basic_salary" id="inpEmpSalary" class="form-control" placeholder="50000.00" value="0.00">
                    </div>
                </div>
                <div class="form-group">
                    <label>Joined Date *</label>
                    <input type="date" name="joined_on" id="inpEmpJoined" class="form-control" required value="{{ date('Y-m-d') }}">
                </div>

                <!-- Create Login User checkbox (for new employees) -->
                <div id="createUserSection" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem; margin-top: 1rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                        <input type="checkbox" name="create_user_account" value="1" id="chkCreateUser" onchange="toggleUserAccountFields(this.checked)">
                        <label for="chkCreateUser" style="font-weight: 700; margin: 0; cursor: pointer; color: #0f172a;">
                            Create POS / Panel Login Account for this Staff Member
                        </label>
                    </div>
                    <div id="userAccountFields" style="display: none;">
                        <div class="form-row">
                            <div class="form-group">
                                <label>System Permission Role *</label>
                                <select name="user_role" class="form-control">
                                    <option value="Cashier">Cashier (POS &amp; Invoices only)</option>
                                    <option value="Technician">Technician (Repairs only)</option>
                                    <option value="Shop Manager">Shop Manager (Operations &amp; Reports)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Initial Password *</label>
                                <input type="password" name="password" class="form-control" placeholder="Password123!">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('employeeModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Staff Member</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Clock Attendance -->
<div class="modal-backdrop" id="attendanceModal">
    <div class="modal-box" style="max-width: 480px;">
        <div class="modal-header">
            <h3>Clock Staff Attendance</h3>
            <button type="button" class="modal-close" onclick="closeModal('attendanceModal')">&times;</button>
        </div>
        <form action="{{ route('shop.staff.attendance.clock') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Select Staff Member *</label>
                    <select name="employee_id" class="form-control" required>
                        <option value="">— Select Employee —</option>
                        @foreach($employees as $e)
                            <option value="{{ $e->id }}">{{ $e->name }} ({{ $e->position }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Work Date *</label>
                    <input type="date" name="work_date" class="form-control" required value="{{ date('Y-m-d') }}">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Clock-In Time *</label>
                        <input type="time" name="clocked_in_at" class="form-control" required value="09:00">
                    </div>
                    <div class="form-group">
                        <label>Clock-Out Time (Optional)</label>
                        <input type="time" name="clocked_out_at" class="form-control">
                    </div>
                </div>
            </div>
            <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('attendanceModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Shift Entry</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Apply for Leave -->
<div class="modal-backdrop" id="leaveModal">
    <div class="modal-box" style="max-width: 480px;">
        <div class="modal-header">
            <h3>Staff Leave Application</h3>
            <button type="button" class="modal-close" onclick="closeModal('leaveModal')">&times;</button>
        </div>
        <form action="{{ route('shop.staff.leaves.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Staff Member *</label>
                    <select name="employee_id" class="form-control" required>
                        <option value="">— Select Employee —</option>
                        @foreach($employees as $e)
                            <option value="{{ $e->id }}">{{ $e->name }} ({{ $e->position }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Leave Classification *</label>
                    <select name="type" class="form-control" required>
                        <option value="casual">Casual Leave</option>
                        <option value="medical">Medical / Sick Leave</option>
                        <option value="annual">Annual Leave</option>
                        <option value="other">Other Reason</option>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>From Date *</label>
                        <input type="date" name="starts_on" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-group">
                        <label>To Date *</label>
                        <input type="date" name="ends_on" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Reason / Notes</label>
                    <textarea name="reason" class="form-control" rows="2" placeholder="e.g. Family function in Kandy"></textarea>
                </div>
            </div>
            <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('leaveModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Submit Leave Application</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Auto-Generate Payroll -->
<div class="modal-backdrop" id="autoPayrollModal">
    <div class="modal-box" style="max-width: 520px;">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="#4f46e5"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                <h3 style="margin: 0;">Auto-Generate Monthly Payroll</h3>
            </div>
            <button type="button" class="modal-close" onclick="closeModal('autoPayrollModal')">&times;</button>
        </div>
        <form action="{{ route('shop.staff.payroll.auto') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div style="background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%); border: 1px solid #c7d2fe; border-radius: 8px; padding: 1rem; margin-bottom: 1.25rem;">
                    <div style="font-weight: 700; color: #3730a3; font-size: 0.875rem; margin-bottom: 0.25rem;">Automated Bulk Salary Processing</div>
                    <div style="font-size: 0.8125rem; color: #4338ca; line-height: 1.4;">
                        This will automatically generate draft payslips for all active employees (<strong>{{ $eligibleStaffCount }} eligible staff</strong>) based on their configured basic salary.
                    </div>
                </div>

                <div class="form-group">
                    <label>Payroll Period (Month) *</label>
                    <input type="month" name="period" id="autoPayPeriod" class="form-control" required value="{{ $payrollMonth }}">
                </div>

                <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1rem; background: #fafafa;">
                    <label style="display: flex; align-items: flex-start; gap: 0.6rem; cursor: pointer; margin-bottom: 0.6rem; font-size: 0.8125rem; font-weight: 600; color: #334155;">
                        <input type="checkbox" name="overwrite" value="1" style="margin-top: 0.2rem;">
                        <div>
                            <span>Update / Overwrite existing Unpaid Drafts</span>
                            <div style="font-size: 0.72rem; color: #64748b; font-weight: 400;">If unchecked, employees who already have a payslip for this month will be skipped safely.</div>
                        </div>
                    </label>

                    <label style="display: flex; align-items: flex-start; gap: 0.6rem; cursor: pointer; margin-bottom: 0; font-size: 0.8125rem; font-weight: 600; color: #334155;">
                        <input type="checkbox" name="include_leave_deductions" value="1" checked style="margin-top: 0.2rem;">
                        <div>
                            <span>Automatically deduct Approved Unpaid Leaves</span>
                            <div style="font-size: 0.72rem; color: #64748b; font-weight: 400;">Calculates per-day salary deduction for approved unpaid/other leaves during the month.</div>
                        </div>
                    </label>
                </div>
            </div>
            <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('autoPayrollModal')">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); border: none; display: inline-flex; align-items: center; gap: 0.35rem;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                    Run Auto-Generation
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Manual Generate Payroll -->
<div class="modal-backdrop" id="payrollModal">
    <div class="modal-box" style="max-width: 520px;">
        <div class="modal-header">
            <h3>Manual Payslip Generator</h3>
            <button type="button" class="modal-close" onclick="closeModal('payrollModal')">&times;</button>
        </div>
        <form action="{{ route('shop.staff.payroll.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Staff Member *</label>
                    <select name="employee_id" id="selPayEmployee" class="form-control" required onchange="onPayrollEmployeeSelect(this)">
                        <option value="">— Select Employee —</option>
                        @foreach($employees as $e)
                            <option value="{{ $e->id }}" data-salary="{{ $e->basic_salary }}">{{ $e->name }} ({{ $e->position }}) - LKR {{ number_format($e->basic_salary, 2) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Payroll Period (Month) *</label>
                    <input type="month" name="period" id="inpManualPeriod" class="form-control" required value="{{ $payrollMonth }}">
                </div>
                <div class="form-group">
                    <label>Basic Salary (LKR) *</label>
                    <input type="number" step="0.01" name="basic_salary" id="inpPayBasic" class="form-control" required placeholder="0.00" oninput="calculateNetSalary()">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Allowances & Commissions (+ LKR)</label>
                        <input type="number" step="0.01" name="allowances" id="inpPayAllowances" class="form-control" placeholder="0.00" value="0.00" oninput="calculateNetSalary()">
                    </div>
                    <div class="form-group">
                        <label>Deductions & Advances (- LKR)</label>
                        <input type="number" step="0.01" name="deductions" id="inpPayDeductions" class="form-control" placeholder="0.00" value="0.00" oninput="calculateNetSalary()">
                    </div>
                </div>
                <div class="form-group">
                    <label>Remarks / Notes (Optional)</label>
                    <input type="text" name="note" class="form-control" placeholder="e.g. 10 hours overtime + performance bonus">
                </div>
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 1rem; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 700; color: #166534; font-size: 0.875rem;">Calculated Net Take-Home Pay:</span>
                    <strong id="lblCalculatedNet" style="color: #166534; font-size: 1.25rem;">LKR 0.00</strong>
                </div>
            </div>
            <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('payrollModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Manual Payslip</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Draft Payslip -->
<div class="modal-backdrop" id="editPayrollModal">
    <div class="modal-box" style="max-width: 520px;">
        <div class="modal-header">
            <h3 id="lblEditPayTitle">Edit Draft Payslip</h3>
            <button type="button" class="modal-close" onclick="closeModal('editPayrollModal')">&times;</button>
        </div>
        <form id="editPayrollForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label>Staff Member</label>
                    <input type="text" id="editPayStaffName" class="form-control" readonly style="background: #f1f5f9; cursor: not-allowed;">
                </div>
                <div class="form-group">
                    <label>Basic Salary (LKR) *</label>
                    <input type="number" step="0.01" name="basic_salary" id="editPayBasic" class="form-control" required oninput="calculateEditNetSalary()">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Allowances & OT (+ LKR)</label>
                        <input type="number" step="0.01" name="allowances" id="editPayAllowances" class="form-control" oninput="calculateEditNetSalary()">
                    </div>
                    <div class="form-group">
                        <label>Deductions & Advances (- LKR)</label>
                        <input type="number" step="0.01" name="deductions" id="editPayDeductions" class="form-control" oninput="calculateEditNetSalary()">
                    </div>
                </div>
                <div class="form-group">
                    <label>Remarks / Notes</label>
                    <input type="text" name="note" id="editPayNote" class="form-control" placeholder="e.g. Adjusted manual allowance">
                </div>
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 1rem; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 700; color: #166534; font-size: 0.875rem;">Adjusted Net Pay:</span>
                    <strong id="lblEditCalculatedNet" style="color: #166534; font-size: 1.25rem;">LKR 0.00</strong>
                </div>
            </div>
            <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editPayrollModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Payslip</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Mark as Paid -->
<div class="modal-backdrop" id="markPaidModal">
    <div class="modal-box" style="max-width: 460px;">
        <div class="modal-header">
            <h3>Confirm Salary Payment</h3>
            <button type="button" class="modal-close" onclick="closeModal('markPaidModal')">&times;</button>
        </div>
        <form id="markPaidForm" method="POST">
            @csrf
            @method('PATCH')
            <div class="modal-body">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem; margin-bottom: 1.25rem;">
                    <div style="font-size: 0.8125rem; color: #64748b;">Staff Member:</div>
                    <strong id="lblPaidStaffName" style="font-size: 1rem; color: #0f172a; display: block;">—</strong>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px dashed #cbd5e1;">
                        <span style="font-size: 0.875rem; color: #475569;">Net Amount:</span>
                        <strong id="lblPaidAmount" style="font-size: 1.15rem; color: #166534;">LKR 0.00</strong>
                    </div>
                </div>

                <div class="form-group">
                    <label>Payment Method *</label>
                    <select name="payment_method" class="form-control" required>
                        <option value="cash">Cash in Hand</option>
                        <option value="bank_transfer" selected>Bank Transfer (Direct Deposit)</option>
                        <option value="cheque">Cheque</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label>Payment Date *</label>
                    <input type="date" name="paid_at" class="form-control" required value="{{ date('Y-m-d') }}">
                </div>
            </div>
            <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('markPaidModal')">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background: #166534; border-color: #166534;">✓ Confirm &amp; Mark Paid</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: View Payslip Receipt -->
<div class="modal-backdrop" id="payslipModal">
    <div class="modal-box" style="max-width: 520px; font-family: monospace;">
        <div class="modal-header">
            <h3>Employee Salary Slip</h3>
            <button type="button" class="modal-close" onclick="closeModal('payslipModal')">&times;</button>
        </div>
        <div class="modal-body" id="payslipContent">
            <!-- Dynamically populated -->
        </div>
        <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.5rem;">
            <button type="button" class="btn btn-secondary" onclick="closeModal('payslipModal')">Close</button>
            <button type="button" class="btn btn-primary" onclick="window.print()" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                Print Payslip
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openAddEmployeeModal() {
        document.getElementById('empModalTitle').innerText = 'Register Staff Member';
        const form = document.getElementById('employeeForm');
        form.action = "{{ route('shop.staff.employees.store') }}";
        document.getElementById('empMethodPut').innerHTML = '';
        form.reset();
        document.getElementById('createUserSection').style.display = 'block';
        openModal('employeeModal');
    }

    function openEditEmployeeModal(emp) {
        document.getElementById('empModalTitle').innerText = 'Edit Staff: ' + emp.name;
        const form = document.getElementById('employeeForm');
        form.action = `/shop/staff/employees/${emp.id}`;
        document.getElementById('empMethodPut').innerHTML = '@method("PUT")';
        document.getElementById('inpEmpName').value = emp.name || '';
        document.getElementById('inpEmpEmail').value = emp.email || '';
        document.getElementById('inpEmpPhone').value = emp.phone || '';
        document.getElementById('inpEmpPosition').value = emp.position || 'Cashier';
        document.getElementById('inpEmpOutlet').value = emp.outlet_id || '';
        document.getElementById('inpEmpSalary').value = emp.basic_salary || '0.00';
        document.getElementById('inpEmpJoined').value = emp.joined_on || '';
        document.getElementById('createUserSection').style.display = 'none';
        openModal('employeeModal');
    }

    function toggleUserAccountFields(checked) {
        document.getElementById('userAccountFields').style.display = checked ? 'block' : 'none';
    }

    function openAutoPayrollModal() {
        openModal('autoPayrollModal');
    }

    function openManualPayrollModal() {
        document.getElementById('selPayEmployee').value = '';
        document.getElementById('inpPayBasic').value = '0.00';
        document.getElementById('inpPayAllowances').value = '0.00';
        document.getElementById('inpPayDeductions').value = '0.00';
        calculateNetSalary();
        openModal('payrollModal');
    }

    function onPayrollEmployeeSelect(sel) {
        const opt = sel.options[sel.selectedIndex];
        if (opt && opt.value) {
            document.getElementById('inpPayBasic').value = parseFloat(opt.getAttribute('data-salary') || 0).toFixed(2);
            calculateNetSalary();
        }
    }

    function calculateNetSalary() {
        const basic = parseFloat(document.getElementById('inpPayBasic').value || 0);
        const allowances = parseFloat(document.getElementById('inpPayAllowances').value || 0);
        const deductions = parseFloat(document.getElementById('inpPayDeductions').value || 0);
        const net = Math.max(0, basic + allowances - deductions);
        document.getElementById('lblCalculatedNet').innerText = 'LKR ' + net.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function openEditPayrollModal(pay) {
        document.getElementById('lblEditPayTitle').innerText = 'Edit Payslip: ' + (pay.employee ? pay.employee.name : 'Staff');
        document.getElementById('editPayStaffName').value = (pay.employee ? pay.employee.name : 'Staff') + ' (' + (pay.employee ? pay.employee.position : '') + ')';
        document.getElementById('editPayBasic').value = parseFloat(pay.basic_salary || 0).toFixed(2);
        document.getElementById('editPayAllowances').value = parseFloat(pay.allowances || 0).toFixed(2);
        document.getElementById('editPayDeductions').value = parseFloat(pay.deductions || 0).toFixed(2);
        document.getElementById('editPayNote').value = pay.note || '';
        document.getElementById('editPayrollForm').action = `/shop/staff/payroll/${pay.id}`;
        calculateEditNetSalary();
        openModal('editPayrollModal');
    }

    function calculateEditNetSalary() {
        const basic = parseFloat(document.getElementById('editPayBasic').value || 0);
        const allowances = parseFloat(document.getElementById('editPayAllowances').value || 0);
        const deductions = parseFloat(document.getElementById('editPayDeductions').value || 0);
        const net = Math.max(0, basic + allowances - deductions);
        document.getElementById('lblEditCalculatedNet').innerText = 'LKR ' + net.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function openMarkPaidModal(pay) {
        document.getElementById('lblPaidStaffName').innerText = (pay.employee ? pay.employee.name : 'Staff') + ' (' + (pay.employee ? pay.employee.position : '') + ')';
        document.getElementById('lblPaidAmount').innerText = 'LKR ' + parseFloat(pay.net_salary || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('markPaidForm').action = `/shop/staff/payroll/${pay.id}/paid`;
        openModal('markPaidModal');
    }

    function viewPayslipModal(pay) {
        const c = document.getElementById('payslipContent');
        const isPaid = pay.status === 'paid';
        const method = pay.payment_method ? pay.payment_method.toUpperCase().replace('_', ' ') : 'CASH';
        const genType = pay.generation_type === 'auto' ? 'System Auto-Generated' : 'Manual Configured';

        c.innerHTML = `
            <div id="printablePayslip" style="padding: 10px;">
                <div style="text-align: center; border-bottom: 2px dashed #0f172a; padding-bottom: 0.75rem; margin-bottom: 1rem;">
                    <h2 style="margin: 0; font-size: 1.3rem; font-weight: 800; color: #0f172a;">{{ auth()->user()->shop->name ?? 'Phone Shop' }}</h2>
                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.2rem;">Official Employee Salary Slip</div>
                    <div style="font-size: 0.8125rem; font-weight: 700; color: #4f46e5; margin-top: 0.25rem;">Salary Period: ${pay.period}</div>
                </div>

                <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; font-size: 0.8125rem; background: #f8fafc; padding: 0.75rem; border-radius: 6px;">
                    <div>
                        <strong>Employee:</strong> ${pay.employee ? pay.employee.name : 'Staff'}<br>
                        <strong>Designation:</strong> ${pay.employee ? pay.employee.position : 'Staff'}<br>
                        <strong>Employee Code:</strong> ${pay.employee ? pay.employee.employee_code : '—'}
                    </div>
                    <div style="text-align: right;">
                        <strong>Method:</strong> ${genType}<br>
                        <strong>Status:</strong> <span style="font-weight: 800; color: ${isPaid ? '#166534' : '#d97706'};">${pay.status.toUpperCase()}</span><br>
                        ${isPaid ? `<strong>Paid via:</strong> ${method}` : ''}
                    </div>
                </div>

                <table style="width: 100%; border-collapse: collapse; font-size: 0.8125rem; margin-bottom: 1rem;">
                    <thead>
                        <tr style="border-bottom: 1px solid #cbd5e1; text-align: left; background: #f1f5f9;">
                            <th style="padding: 6px 8px;">Description</th>
                            <th style="padding: 6px 8px; text-align: right;">Amount (LKR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom: 1px dashed #e2e8f0;">
                            <td style="padding: 6px 8px;">Basic Salary</td>
                            <td style="padding: 6px 8px; text-align: right; font-weight: 600;">${parseFloat(pay.basic_salary).toFixed(2)}</td>
                        </tr>
                        <tr style="border-bottom: 1px dashed #e2e8f0; color: #166534;">
                            <td style="padding: 6px 8px;">(+) Allowances, OT &amp; Commissions</td>
                            <td style="padding: 6px 8px; text-align: right; font-weight: 600;">+${parseFloat(pay.allowances).toFixed(2)}</td>
                        </tr>
                        <tr style="border-bottom: 1px dashed #e2e8f0; color: #b91c1c;">
                            <td style="padding: 6px 8px;">(-) Deductions, Advances &amp; Leaves</td>
                            <td style="padding: 6px 8px; text-align: right; font-weight: 600;">-${parseFloat(pay.deductions).toFixed(2)}</td>
                        </tr>
                        <tr style="border-top: 2px solid #0f172a; font-weight: 800; font-size: 0.95rem; background: #f0fdf4;">
                            <td style="padding: 8px; color: #166534;">NET TAKE-HOME PAY</td>
                            <td style="padding: 8px; text-align: right; color: #166534;">LKR ${parseFloat(pay.net_salary).toFixed(2)}</td>
                        </tr>
                    </tbody>
                </table>

                ${pay.note ? `
                    <div style="font-size: 0.75rem; color: #64748b; font-style: italic; margin-bottom: 1.25rem; background: #f8fafc; padding: 0.5rem; border-radius: 4px;">
                        <strong>Notes:</strong> ${pay.note}
                    </div>
                ` : ''}

                <div style="display: flex; justify-content: space-between; margin-top: 2.5rem; padding-top: 0.75rem; font-size: 0.75rem;">
                    <div style="text-align: center; border-top: 1px solid #94a3b8; width: 140px; padding-top: 4px;">
                        Authorized Signature
                    </div>
                    <div style="text-align: center; border-top: 1px solid #94a3b8; width: 140px; padding-top: 4px;">
                        Employee Signature
                    </div>
                </div>
            </div>
        `;
        openModal('payslipModal');
    }
</script>
@endpush
@endsection

