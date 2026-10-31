@extends('layouts.super-admin')

@section('title', 'Shop Details — ' . $shop->name)
@section('page_title', 'Shop Workspace: ' . $shop->name)

@section('content')
    @php
        $primaryUser = $shop->users->first();
        $shopData = [
            'id' => $shop->id,
            'name' => $shop->name,
            'phone' => $shop->phone ?? '',
            'email' => $shop->email ?? '',
            'address' => $shop->address ?? '',
            'business_reg_number' => $shop->business_reg_number ?? '',
            'currency' => $shop->setting->currency ?? 'LKR',
            'timezone' => $shop->setting->timezone ?? 'Asia/Colombo',
            'owner_name' => $primaryUser->name ?? '',
            'owner_email' => $primaryUser->email ?? '',
            'owner_phone' => $primaryUser->phone ?? '',
            'plan_id' => $shop->subscription->plan_id ?? ($plans->first()->id ?? ''),
            'subscription_status' => $shop->subscription->status ?? 'trial',
            'current_period_ends_at' => $shop->subscription?->current_period_ends_at ? $shop->subscription->current_period_ends_at->format('Y-m-d') : '',
        ];
    @endphp

    <div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <a href="{{ route('superadmin.shops.index') }}" class="btn-action btn-outline">&larr; Back to Shops</a>

        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            <form method="POST" action="{{ route('superadmin.shops.switch', $shop->id) }}" style="display: inline; margin: 0;">
                @csrf
                <button type="submit" class="btn-action btn-primary" title="Access shop portal as Super Admin">Access Shop Workspace</button>
            </form>
            <button type="button" class="btn-action btn-outline" onclick='openEditShopModal(@json($shopData))' style="color: #4338ca; border-color: #c7d2fe;">
                Edit Shop Details
            </button>
            <form method="POST" action="{{ route('superadmin.shops.destroy', $shop->id) }}" style="display: inline; margin: 0;" onsubmit="return confirm('⚠️ WARNING: Are you sure you want to PERMANENTLY DELETE shop \'{{ addslashes($shop->name) }}\'? All related data, inventory, sales, and accounts will be completely erased. This action CANNOT be undone!');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-action btn-danger" style="background: #991b1b; border-color: #991b1b;">Delete Shop</button>
            </form>
        </div>
    </div>

    <!-- Shop Overview & Settings -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
        <div class="data-card" style="margin-bottom: 0;">
            <div class="data-card-header">
                <h2 class="data-card-title">Shop Information</h2>
                <form method="POST" action="{{ route('superadmin.shops.toggle-suspension', $shop->id) }}" style="margin: 0;">
                    @csrf
                    @if (($shop->subscription->status ?? '') === 'suspended')
                        <button type="submit" class="btn-action btn-success">Activate Shop</button>
                    @else
                        <button type="submit" class="btn-action btn-danger">Suspend Shop</button>
                    @endif
                </form>
            </div>
            <div style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
                <div>
                    <span style="font-size: 0.775rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Shop Name</span>
                    <p style="font-size: 1.15rem; font-weight: 750; color: #0f172a; margin: 0.15rem 0 0;">{{ $shop->name }}</p>
                </div>
                <div>
                    <span style="font-size: 0.775rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Tenant Workspace ID</span>
                    <p style="font-family: monospace; font-size: 1rem; color: #4338ca; margin: 0.15rem 0 0;">#{{ $shop->id }}</p>
                </div>
                <div>
                    <span style="font-size: 0.775rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Contact &amp; Location</span>
                    <p style="margin: 0.15rem 0 0; color: #334155;">
                        Phone: {{ $shop->phone ?? 'N/A' }} • Email: {{ $shop->email ?? 'N/A' }}<br>
                        Address: {{ $shop->address ?? 'N/A' }}<br>
                        BRN: {{ $shop->business_reg_number ?? 'N/A' }}
                    </p>
                </div>
                <div>
                    <span style="font-size: 0.775rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Currency &amp; Timezone</span>
                    <p style="margin: 0.15rem 0 0;">{{ $shop->setting->currency ?? 'LKR' }} • {{ $shop->setting->timezone ?? 'Asia/Colombo' }}</p>
                </div>
                <div>
                    <span style="font-size: 0.775rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Registered On</span>
                    <p style="margin: 0.15rem 0 0;">{{ $shop->created_at->format('M d, Y h:i A') }} ({{ $shop->created_at->diffForHumans() }})</p>
                </div>
            </div>
        </div>

        <div class="data-card" style="margin-bottom: 0;">
            <div class="data-card-header">
                <h2 class="data-card-title">Current Subscription</h2>
                <span class="status-pill status-{{ $shop->subscription->status ?? 'trial' }}">
                    {{ $shop->subscription->status ?? 'Trial' }}
                </span>
            </div>
            <div style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
                <div>
                    <span style="font-size: 0.775rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Plan</span>
                    <p style="font-size: 1.15rem; font-weight: 750; color: #0f172a; margin: 0.15rem 0 0;">{{ $shop->subscription->plan->name ?? 'Standard Trial' }}</p>
                </div>
                <div>
                    <span style="font-size: 0.775rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Billing Period</span>
                    <p style="margin: 0.15rem 0 0;">
                        {{ $shop->subscription?->current_period_starts_at ? $shop->subscription->current_period_starts_at->format('Y-m-d') : 'N/A' }} 
                        to 
                        {{ $shop->subscription?->current_period_ends_at ? $shop->subscription->current_period_ends_at->format('Y-m-d') : 'N/A' }}
                    </p>
                </div>
                @if ($shop->subscription?->trial_ends_at)
                    <div>
                        <span style="font-size: 0.775rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Trial Expiration</span>
                        <p style="color: #2563eb; font-weight: 600; margin: 0.15rem 0 0;">{{ $shop->subscription->trial_ends_at->format('Y-m-d') }} ({{ $shop->subscription->trial_ends_at->diffForHumans() }})</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Shop Users Table -->
    <div class="data-card">
        <div class="data-card-header">
            <h2 class="data-card-title">Assigned Users &amp; Staff ({{ $shop->users->count() }})</h2>
        </div>
        <table class="saas-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($shop->users as $u)
                    <tr>
                        <td><strong>{{ $u->name }}</strong></td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $u->phone ?? 'N/A' }}</td>
                        <td>
                            @foreach ($u->roles as $r)
                                <span class="status-pill status-trial">{{ $r->name }}</span>
                            @endforeach
                        </td>
                        <td>{{ $u->created_at->format('M d, Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Branch Outlets & Approvals -->
    <div class="data-card">
        <div class="data-card-header">
            <h2 class="data-card-title">Registered Branch Outlets ({{ $shop->outlets->count() }})</h2>
        </div>
        <table class="saas-table">
            <thead>
                <tr>
                    <th>Branch</th>
                    <th>Code</th>
                    <th>Billing / Quota</th>
                    <th>Address / City</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($shop->outlets as $outlet)
                    @php
                        $isCovered = $outlet->isCoveredByPlan();
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $outlet->name }}</strong>
                            @if($outlet->is_main)
                                <span style="display: block; font-size: 0.7rem; font-weight: 700; color: #4f46e5;">★ Main Branch</span>
                            @endif
                            @if($outlet->manager_email)
                                <div style="font-size: 0.72rem; color: #64748b;">🔑 {{ $outlet->manager_email }}</div>
                            @endif
                        </td>
                        <td><span style="font-family: monospace; font-size: 0.8125rem; background: #e0e7ff; color: #4338ca; padding: 0.15rem 0.45rem; border-radius: 4px;">{{ $outlet->code }}</span></td>
                        <td>
                            @if($isCovered)
                                <span class="status-pill status-active" style="background: #ecfdf5; color: #047857; font-size: 0.7rem;">✓ Plan Quota (Free)</span>
                            @elseif($outlet->payment_status === 'paid')
                                <span class="status-pill status-active" style="background: #eff6ff; color: #1d4ed8; font-size: 0.7rem;">✓ Paid (LKR {{ number_format($outlet->additional_fee ?: 2500, 0) }})</span>
                            @elseif($outlet->payment_status === 'pending_verification')
                                <span class="status-pill status-trial" style="background: #fffbeb; color: #b45309; font-size: 0.7rem;">⏳ Slip Uploaded</span>
                                @if($outlet->payment_receipt_url)
                                    <a href="{{ $outlet->payment_receipt_url }}" target="_blank" style="font-size: 0.7rem; color: #2563eb; display: block;">View Slip ↗</a>
                                @endif
                            @else
                                <span class="status-pill status-suspended" style="background: #fef2f2; color: #b91c1c; font-size: 0.7rem;">⚠️ LKR {{ number_format($outlet->additional_fee ?: 2500, 0) }} Unpaid</span>
                            @endif
                        </td>
                        <td>{{ $outlet->address }}{{ $outlet->city ? ' (' . $outlet->city . ')' : '' }}</td>
                        <td>
                            @if($outlet->approval_status === 'pending')
                                <span class="status-pill status-trial" style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a;">⏳ Pending Approval</span>
                            @elseif($outlet->approval_status === 'approved')
                                <span class="status-pill status-active" style="background: #dcfce7; color: #166534; border: 1px solid #bbf7d0;">✓ Approved</span>
                            @elseif($outlet->approval_status === 'rejected')
                                <span class="status-pill status-suspended" style="background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;">✕ Rejected</span>
                            @endif
                        </td>
                        <td>
                            @if($outlet->approval_status === 'pending')
                                <div style="display: flex; gap: 0.4rem; align-items: center;">
                                    <form method="POST" action="{{ route('superadmin.outlets.approve', $outlet->id) }}" style="margin: 0;">
                                        @csrf
                                        @if(! $isCovered && $outlet->payment_status === 'unpaid')
                                            <input type="hidden" name="confirm_unpaid" value="1">
                                            <button type="submit" class="btn-action btn-warning" onclick="return confirm('Branch exceeds quota and is UNPAID. Approve anyway?');" style="font-size: 0.72rem; padding: 0.3rem 0.55rem;" title="Override and approve">✓ Force Approve</button>
                                        @else
                                            <button type="submit" class="btn-action btn-success" style="font-size: 0.72rem; padding: 0.3rem 0.55rem;">✓ Approve</button>
                                        @endif
                                    </form>
                                    <form method="POST" action="{{ route('superadmin.outlets.reject', $outlet->id) }}" style="margin: 0;">
                                        @csrf
                                        <button type="submit" class="btn-action btn-danger" style="font-size: 0.72rem; padding: 0.3rem 0.55rem;">✕ Reject</button>
                                    </form>
                                </div>
                            @else
                                <span style="font-size: 0.75rem; color: #64748b;">{{ ucfirst($outlet->approval_status) }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #94a3b8; padding: 2rem;">No branches registered for this store.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Payments Ledger -->
    <div class="data-card">
        <div class="data-card-header">
            <h2 class="data-card-title">Subscription Payment History</h2>
        </div>
        <table class="saas-table">
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th>Reference / Transaction</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($shop->subscriptionPayments as $p)
                    <tr>
                        <td>#{{ $p->id }}</td>
                        <td><strong>LKR {{ number_format($p->amount, 2) }}</strong></td>
                        <td><span style="text-transform: uppercase;">{{ str_replace('_', ' ', $p->payment_method) }}</span></td>
                        <td><span class="status-pill status-{{ $p->status }}">{{ $p->status }}</span></td>
                        <td>{{ $p->reference_number ?? ($p->transaction_id ?? 'N/A') }}</td>
                        <td>{{ $p->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #94a3b8; padding: 2rem;">No payments on record for this workspace.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Modal: Edit Shop Details -->
    <div class="shop-modal-backdrop" id="editShopModal">
        <div class="shop-modal-box">
            <div class="shop-modal-header">
                <h3>Edit Shop Workspace</h3>
                <button type="button" class="shop-modal-close" onclick="closeShopModal('editShopModal')">&times;</button>
            </div>
            <form id="editShopForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="shop-modal-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div class="shop-form-group">
                            <label>Shop / Business Name *</label>
                            <input type="text" name="name" id="edit_shop_name" class="shop-form-control" required>
                        </div>
                        <div class="shop-form-group">
                            <label>Business Reg Number (BRN)</label>
                            <input type="text" name="business_reg_number" id="edit_shop_brn" class="shop-form-control" placeholder="e.g. PV-123456">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div class="shop-form-group">
                            <label>Shop Contact Phone</label>
                            <input type="text" name="phone" id="edit_shop_phone" class="shop-form-control">
                        </div>
                        <div class="shop-form-group">
                            <label>Shop Email Address</label>
                            <input type="email" name="email" id="edit_shop_email" class="shop-form-control">
                        </div>
                    </div>

                    <div class="shop-form-group" style="margin-bottom: 1rem;">
                        <label>Physical Address / Location</label>
                        <input type="text" name="address" id="edit_shop_address" class="shop-form-control" placeholder="Street, City, Province">
                    </div>

                    <!-- Primary Owner Information -->
                    <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 1rem; margin-bottom: 1rem;">
                        <label style="font-weight: 700; color: #1e293b; display: block; margin-bottom: 0.25rem;">Primary Owner Details</label>
                        <p style="font-size: 0.75rem; color: #64748b; margin-bottom: 0.75rem;">Updates the primary user account attached to this store workspace.</p>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.75rem;">
                            <div class="shop-form-group">
                                <label style="font-size: 0.8125rem;">Owner Name</label>
                                <input type="text" name="owner_name" id="edit_owner_name" class="shop-form-control">
                            </div>
                            <div class="shop-form-group">
                                <label style="font-size: 0.8125rem;">Owner Email</label>
                                <input type="email" name="owner_email" id="edit_owner_email" class="shop-form-control">
                            </div>
                            <div class="shop-form-group">
                                <label style="font-size: 0.8125rem;">Owner Phone</label>
                                <input type="text" name="owner_phone" id="edit_owner_phone" class="shop-form-control">
                            </div>
                        </div>
                    </div>

                    <!-- Subscription & License Controls -->
                    <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 1rem; margin-bottom: 1rem;">
                        <label style="font-weight: 700; color: #1e293b; display: block; margin-bottom: 0.25rem;">Subscription &amp; Platform Plan</label>
                        <p style="font-size: 0.75rem; color: #64748b; margin-bottom: 0.75rem;">Directly adjust tenant plan, license status, or expiry date.</p>

                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.75rem;">
                            <div class="shop-form-group">
                                <label style="font-size: 0.8125rem;">Plan</label>
                                <select name="plan_id" id="edit_plan_id" class="shop-form-control">
                                    @foreach($plans as $plan)
                                        <option value="{{ $plan->id }}">{{ $plan->name }} (LKR {{ number_format($plan->monthly_price, 2) }}/mo)</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="shop-form-group">
                                <label style="font-size: 0.8125rem;">License Status</label>
                                <select name="subscription_status" id="edit_sub_status" class="shop-form-control">
                                    <option value="trial">Free Trial</option>
                                    <option value="active">Active Paid</option>
                                    <option value="suspended">Suspended</option>
                                    <option value="expired">Expired</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="shop-form-group">
                                <label style="font-size: 0.8125rem;">Period Ends At</label>
                                <input type="date" name="current_period_ends_at" id="edit_period_ends" class="shop-form-control">
                            </div>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="shop-form-group">
                            <label>Default Currency</label>
                            <input type="text" name="currency" id="edit_currency" class="shop-form-control" value="LKR">
                        </div>
                        <div class="shop-form-group">
                            <label>Timezone</label>
                            <input type="text" name="timezone" id="edit_timezone" class="shop-form-control" value="Asia/Colombo">
                        </div>
                    </div>
                </div>
                <div class="shop-modal-footer">
                    <button type="button" class="btn-action btn-outline" onclick="closeShopModal('editShopModal')">Cancel</button>
                    <button type="submit" class="btn-action btn-primary" style="padding: 0.5rem 1.25rem;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Styles and Scripts -->
    <style>
        .shop-modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .shop-modal-backdrop.open {
            display: flex;
        }
        .shop-modal-box {
            background: #ffffff;
            border-radius: 14px;
            width: 100%;
            max-width: 680px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
            animation: modalFadeIn 0.2s ease-out;
        }
        .shop-modal-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1.5px solid var(--surface-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .shop-modal-header h3 {
            font-size: 1.15rem;
            font-weight: 750;
            color: #0f172a;
            margin: 0;
        }
        .shop-modal-close {
            background: transparent;
            border: none;
            font-size: 1.5rem;
            line-height: 1;
            color: #94a3b8;
            cursor: pointer;
        }
        .shop-modal-close:hover {
            color: #0f172a;
        }
        .shop-modal-body {
            padding: 1.5rem;
        }
        .shop-modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1.5px solid var(--surface-border);
            background: #f8fafc;
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            border-bottom-left-radius: 14px;
            border-bottom-right-radius: 14px;
        }
        .shop-form-group {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }
        .shop-form-group label {
            font-size: 0.8125rem;
            font-weight: 700;
            color: #475569;
        }
        .shop-form-control {
            padding: 0.45rem 0.75rem;
            border: 1.5px solid var(--surface-border);
            border-radius: 8px;
            font-size: 0.875rem;
            color: #1e293b;
            width: 100%;
        }
        .shop-form-control:focus {
            outline: none;
            border-color: #4f46e5;
        }
        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.96); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>

    <script>
        function openShopModal(id) {
            const el = document.getElementById(id);
            if (el) el.classList.add('open');
        }
        function closeShopModal(id) {
            const el = document.getElementById(id);
            if (el) el.classList.remove('open');
        }

        function openEditShopModal(shop) {
            const form = document.getElementById('editShopForm');
            form.action = `/super-admin/shops/${shop.id}`;

            document.getElementById('edit_shop_name').value = shop.name || '';
            document.getElementById('edit_shop_brn').value = shop.business_reg_number || '';
            document.getElementById('edit_shop_phone').value = shop.phone || '';
            document.getElementById('edit_shop_email').value = shop.email || '';
            document.getElementById('edit_shop_address').value = shop.address || '';

            document.getElementById('edit_owner_name').value = shop.owner_name || '';
            document.getElementById('edit_owner_email').value = shop.owner_email || '';
            document.getElementById('edit_owner_phone').value = shop.owner_phone || '';

            if (shop.plan_id) {
                document.getElementById('edit_plan_id').value = shop.plan_id;
            }
            if (shop.subscription_status) {
                document.getElementById('edit_sub_status').value = shop.subscription_status;
            }
            document.getElementById('edit_period_ends').value = shop.current_period_ends_at || '';

            document.getElementById('edit_currency').value = shop.currency || 'LKR';
            document.getElementById('edit_timezone').value = shop.timezone || 'Asia/Colombo';

            openShopModal('editShopModal');
        }

        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('shop-modal-backdrop')) {
                e.target.classList.remove('open');
            }
        });
    </script>
@endsection
