@extends('layouts.super-admin')

@section('title', 'Subscription Plans')
@section('page_title', 'SaaS Subscription Plans')

@section('content')
    <!-- Create Plan Form Card (collapsible / compact) -->
    <div class="data-card">
        <div class="data-card-header">
            <h2 class="data-card-title">Create New Subscription Plan</h2>
        </div>
        <form method="POST" action="{{ route('superadmin.plans.store') }}" style="padding: 1.5rem;">
            @csrf
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <label style="display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 0.35rem;">Plan Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Pro Repair Plus" style="width: 100%; padding: 0.5rem; border: 1px solid var(--surface-border); border-radius: 7px;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 0.35rem;">Monthly Price (LKR) *</label>
                    <input type="number" step="0.01" name="monthly_price" required placeholder="8500.00" style="width: 100%; padding: 0.5rem; border: 1px solid var(--surface-border); border-radius: 7px;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 0.35rem;">Trial Days *</label>
                    <input type="number" name="trial_days" value="7" required style="width: 100%; padding: 0.5rem; border: 1px solid var(--surface-border); border-radius: 7px;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 0.35rem;">Max Staff *</label>
                    <input type="number" name="max_staff" value="5" required style="width: 100%; padding: 0.5rem; border: 1px solid var(--surface-border); border-radius: 7px;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 0.35rem;">Max Branches *</label>
                    <input type="number" name="max_branches" value="1" required style="width: 100%; padding: 0.5rem; border: 1px solid var(--surface-border); border-radius: 7px;">
                </div>
            </div>

            <div style="display: flex; flex-wrap: wrap; gap: 1.25rem; margin-bottom: 1.25rem; font-size: 0.875rem;">
                <label><input type="checkbox" name="has_imei_tracking" value="1" checked> IMEI Tracking</label>
                <label><input type="checkbox" name="has_repair_management" value="1" checked> Repair Management</label>
                <label><input type="checkbox" name="has_warranty_management" value="1" checked> Warranty Management</label>
                <label><input type="checkbox" name="has_accounting_reports" value="1" checked> Accounting Reports</label>
                <label><input type="checkbox" name="has_multi_branch" value="1"> Multi-Branch</label>
                <label><input type="checkbox" name="has_customer_portal" value="1"> Customer Portal</label>
            </div>

            <button type="submit" class="btn-action btn-primary">Create Plan</button>
        </form>
    </div>

    <!-- Existing Plans Table -->
    <div class="data-card">
        <div class="data-card-header">
            <h2 class="data-card-title">Configured Plans ({{ $plans->count() }})</h2>
        </div>
        <table class="saas-table">
            <thead>
                <tr>
                    <th>Plan Name</th>
                    <th>Monthly Price</th>
                    <th>Branches / Staff</th>
                    <th>Key Feature Flags</th>
                    <th>Active Subscribers</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($plans as $plan)
                    <tr>
                        <td>
                            <strong>{{ $plan->name }}</strong>
                            <div style="font-size: 0.75rem; color: #64748b;">Slug: {{ $plan->slug }}</div>
                        </td>
                        <td>
                            <strong style="color: var(--brand-indigo-600); font-size: 1rem;">LKR {{ number_format($plan->monthly_price, 2) }}</strong>
                            <div style="font-size: 0.75rem; color: #64748b;">{{ $plan->trial_days }} days free trial</div>
                        </td>
                        <td>
                            <div>{{ $plan->max_branches }} branch(es)</div>
                            <div style="font-size: 0.75rem; color: #64748b;">Up to {{ $plan->max_staff }} staff</div>
                        </td>
                        <td style="font-size: 0.775rem;">
                            @if ($plan->has_imei_tracking) <span class="status-pill status-active" style="margin-bottom: 2px;">IMEI</span> @endif
                            @if ($plan->has_repair_management) <span class="status-pill status-active" style="margin-bottom: 2px;">Repairs</span> @endif
                            @if ($plan->has_warranty_management) <span class="status-pill status-active" style="margin-bottom: 2px;">Warranty</span> @endif
                            @if ($plan->has_multi_branch) <span class="status-pill status-trial" style="margin-bottom: 2px;">Multi-Branch</span> @endif
                        </td>
                        <td>
                            <strong>{{ $plan->subscriptions_count }}</strong> shops
                        </td>
                        <td>
                            <span class="status-pill status-{{ $plan->is_active ? 'active' : 'expired' }}">
                                {{ $plan->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('superadmin.plans.toggle-active', $plan->id) }}" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn-action btn-outline" style="font-size: 0.775rem;">
                                    {{ $plan->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

