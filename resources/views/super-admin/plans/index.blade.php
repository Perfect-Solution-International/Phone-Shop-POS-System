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
                            <div style="display: flex; gap: 0.35rem; align-items: center;">
                                <button type="button" class="btn-action btn-outline" onclick='openEditPlanModal(@json($plan))' title="Edit plan configuration and pricing" style="font-size: 0.775rem; padding: 0.3rem 0.65rem;">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('superadmin.plans.toggle-active', $plan->id) }}" style="display: inline; margin: 0;">
                                    @csrf
                                    <button type="submit" class="btn-action btn-outline" style="font-size: 0.775rem; padding: 0.3rem 0.65rem; color: {{ $plan->is_active ? '#dc2626' : '#16a34a' }};">
                                        {{ $plan->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal: Edit Subscription Plan -->
    <div class="plan-modal-backdrop" id="editPlanModal">
        <div class="plan-modal-box">
            <div class="plan-modal-header">
                <h3 id="editPlanTitle">Edit Subscription Plan</h3>
                <button type="button" class="plan-modal-close" onclick="closePlanModal('editPlanModal')">&times;</button>
            </div>
            <form id="editPlanForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="plan-modal-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label style="display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 0.35rem;">Plan Name *</label>
                            <input type="text" name="name" id="edit_plan_name" required placeholder="e.g. Pro Repair Plus" class="form-control-saas">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 0.35rem;">Monthly Price (LKR) *</label>
                            <input type="number" step="0.01" name="monthly_price" id="edit_plan_monthly_price" required placeholder="8500.00" class="form-control-saas">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label style="display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 0.35rem;">Trial Days *</label>
                            <input type="number" name="trial_days" id="edit_plan_trial_days" required min="0" max="90" class="form-control-saas">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 0.35rem;">Max Branches *</label>
                            <input type="number" name="max_branches" id="edit_plan_max_branches" required min="1" class="form-control-saas">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 0.35rem;">Max Staff *</label>
                            <input type="number" name="max_staff" id="edit_plan_max_staff" required min="1" class="form-control-saas">
                        </div>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 0.35rem;">Description / Remarks</label>
                        <textarea name="description" id="edit_plan_description" rows="2" placeholder="Brief plan summary..." class="form-control-saas"></textarea>
                    </div>

                    <div style="margin-bottom: 0.75rem;">
                        <label style="display: block; font-size: 0.8125rem; font-weight: 700; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em; color: #475569;">Feature Capabilities</label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 0.85rem;">
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                <input type="checkbox" name="has_imei_tracking" id="edit_has_imei_tracking" value="1">
                                <span>IMEI Tracking</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                <input type="checkbox" name="has_repair_management" id="edit_has_repair_management" value="1">
                                <span>Repair Management</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                <input type="checkbox" name="has_warranty_management" id="edit_has_warranty_management" value="1">
                                <span>Warranty Management</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                <input type="checkbox" name="has_accounting_reports" id="edit_has_accounting_reports" value="1">
                                <span>Accounting Reports</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                <input type="checkbox" name="has_multi_branch" id="edit_has_multi_branch" value="1">
                                <span>Multi-Branch Outlets</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                <input type="checkbox" name="has_customer_portal" id="edit_has_customer_portal" value="1">
                                <span>Customer Portal</span>
                            </label>
                        </div>
                    </div>

                    <div style="margin-top: 1rem; padding: 0.75rem 1rem; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; margin: 0; font-size: 0.85rem; font-weight: 600; color: #166534;">
                            <input type="checkbox" name="is_active" id="edit_is_active" value="1">
                            <span>Plan is Active (Available for shops to purchase)</span>
                        </label>
                    </div>
                </div>
                <div class="plan-modal-footer">
                    <button type="button" class="btn-action btn-outline" onclick="closePlanModal('editPlanModal')">Cancel</button>
                    <button type="submit" class="btn-action btn-primary" style="padding: 0.5rem 1.25rem;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .form-control-saas {
            width: 100%;
            padding: 0.55rem 0.75rem;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            font-size: 0.875rem;
            color: #0f172a;
            box-sizing: border-box;
            outline: none;
            transition: border-color 0.15s ease;
        }
        .form-control-saas:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        }
        .plan-modal-backdrop {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .plan-modal-backdrop.open {
            display: flex;
        }
        .plan-modal-box {
            background: #ffffff;
            border-radius: 14px;
            max-width: 620px;
            width: 100%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            animation: modalFadeIn 0.2s ease-out;
        }
        .plan-modal-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8fafc;
        }
        .plan-modal-header h3 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f172a;
        }
        .plan-modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #64748b;
            line-height: 1;
            padding: 0;
        }
        .plan-modal-close:hover {
            color: #0f172a;
        }
        .plan-modal-body {
            padding: 1.5rem;
            max-height: 75vh;
            overflow-y: auto;
        }
        .plan-modal-footer {
            padding: 1.25rem 1.5rem;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            background: #f8fafc;
        }
        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.97); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>

    <script>
        function openPlanModal(id) {
            const el = document.getElementById(id);
            if (el) el.classList.add('open');
        }

        function closePlanModal(id) {
            const el = document.getElementById(id);
            if (el) el.classList.remove('open');
        }

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('plan-modal-backdrop')) {
                e.target.classList.remove('open');
            }
        });

        function openEditPlanModal(plan) {
            document.getElementById('editPlanTitle').innerText = 'Edit Plan: ' + (plan.name || '');
            const form = document.getElementById('editPlanForm');
            form.action = `/super-admin/plans/${plan.id}`;

            document.getElementById('edit_plan_name').value = plan.name || '';
            document.getElementById('edit_plan_monthly_price').value = plan.monthly_price || '0.00';
            document.getElementById('edit_plan_trial_days').value = plan.trial_days ?? 7;
            document.getElementById('edit_plan_max_branches').value = plan.max_branches ?? 1;
            document.getElementById('edit_plan_max_staff').value = plan.max_staff ?? 5;
            document.getElementById('edit_plan_description').value = plan.description || '';

            document.getElementById('edit_has_imei_tracking').checked = !!plan.has_imei_tracking;
            document.getElementById('edit_has_repair_management').checked = !!plan.has_repair_management;
            document.getElementById('edit_has_warranty_management').checked = !!plan.has_warranty_management;
            document.getElementById('edit_has_accounting_reports').checked = !!plan.has_accounting_reports;
            document.getElementById('edit_has_multi_branch').checked = !!plan.has_multi_branch;
            document.getElementById('edit_has_customer_portal').checked = !!plan.has_customer_portal;
            document.getElementById('edit_is_active').checked = !!plan.is_active;

            openPlanModal('editPlanModal');
        }

        window.openPlanModal = openPlanModal;
        window.closePlanModal = closePlanModal;
        window.openEditPlanModal = openEditPlanModal;
    </script>
@endsection

