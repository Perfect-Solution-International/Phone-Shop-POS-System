@extends('layouts.super-admin')

@section('title', 'Branch Approvals')
@section('page_title', 'Branch Outlet Verification & Approvals')

@section('content')
    <!-- Status Filter Bar & Search -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <a href="{{ route('superadmin.outlets.approvals', ['status' => 'pending']) }}" 
               class="btn-action {{ $status === 'pending' ? 'btn-primary' : 'btn-outline' }}">
                ⏳ Pending Approvals ({{ $counts['pending'] }})
            </a>
            <a href="{{ route('superadmin.outlets.approvals', ['status' => 'approved']) }}" 
               class="btn-action {{ $status === 'approved' ? 'btn-primary' : 'btn-outline' }}">
                ✓ Approved ({{ $counts['approved'] }})
            </a>
            <a href="{{ route('superadmin.outlets.approvals', ['status' => 'rejected']) }}" 
               class="btn-action {{ $status === 'rejected' ? 'btn-primary' : 'btn-outline' }}">
                ✕ Rejected ({{ $counts['rejected'] }})
            </a>
            <a href="{{ route('superadmin.outlets.approvals', ['status' => 'all']) }}" 
               class="btn-action {{ $status === 'all' ? 'btn-primary' : 'btn-outline' }}">
                All Branches ({{ $counts['all'] }})
            </a>
        </div>

        <form method="GET" action="{{ route('superadmin.outlets.approvals') }}" style="display: flex; gap: 0.5rem; margin: 0;">
            <input type="hidden" name="status" value="{{ $status }}">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search shop or branch..." 
                   style="padding: 0.45rem 0.85rem; font-size: 0.8125rem; border: 1px solid var(--surface-border); border-radius: 8px; min-width: 220px;">
            <button type="submit" class="btn-action btn-outline">Search</button>
        </form>
    </div>

    <div class="data-card">
        <div class="data-card-header">
            <h2 class="data-card-title">
                @if($status === 'pending')
                    Branch Outlets Awaiting Approval ({{ $outlets->total() }})
                @elseif($status === 'approved')
                    Approved Branches ({{ $outlets->total() }})
                @elseif($status === 'rejected')
                    Declined Branch Requests ({{ $outlets->total() }})
                @else
                    All Branch Outlets Across Stores ({{ $outlets->total() }})
                @endif
            </h2>
        </div>

        <table class="saas-table">
            <thead>
                <tr>
                    <th>Branch Details</th>
                    <th>Shop &amp; Subscription Plan</th>
                    <th>Plan Quota &amp; Billing</th>
                    <th>Location &amp; Contact</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($outlets as $outlet)
                    @php
                        $shopPlan = $outlet->shop->activeSubscription?->plan ?? $outlet->shop->subscription?->plan;
                        $planMax = (int) ($shopPlan?->max_branches ?? 1);
                        $approvedInShop = \App\Models\Outlet::withoutGlobalScopes()
                            ->where('shop_id', $outlet->shop_id)
                            ->where('approval_status', 'approved')
                            ->count();
                        $isCovered = $outlet->isCoveredByPlan();
                    @endphp
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <strong>{{ $outlet->name }}</strong>
                                <span style="font-family: monospace; font-size: 0.72rem; font-weight: 700; background: #e0e7ff; color: #4338ca; padding: 0.15rem 0.45rem; border-radius: 4px;">
                                    {{ $outlet->code }}
                                </span>
                            </div>
                            @if($outlet->is_main)
                                <span style="display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.68rem; font-weight: 700; color: #4f46e5; margin-top: 0.2rem;">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                    <span>Primary Main Branch</span>
                                </span>
                            @endif
                            @if($outlet->manager_name || $outlet->manager_email)
                                <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                    <span>Manager: <strong>{{ $outlet->manager_name ?: 'Branch Manager' }}</strong></span>
                                    @if($outlet->manager_email)
                                        <span style="color: #4338ca; display: inline-flex; align-items: center; gap: 0.2rem;">
                                            (<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="7.5" cy="15.5" r="5.5"></circle><path d="m21 2-9.6 9.6"></path><path d="m15.5 7.5 3 3"></path><path d="M18.5 4.5 20 6"></path></svg> {{ $outlet->manager_email }})
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('superadmin.shops.show', $outlet->shop_id) }}" style="color: #4338ca; font-weight: 700; text-decoration: none;">
                                {{ $outlet->shop->name ?? 'Shop #' . $outlet->shop_id }}
                            </a>
                            <div style="font-size: 0.75rem; color: #334155; margin-top: 0.15rem;">
                                Plan: <strong>{{ $shopPlan->name ?? 'Default Starter' }}</strong>
                            </div>
                            <div style="font-size: 0.72rem; color: #64748b;">
                                Tenant #{{ $outlet->shop_id }} • License: {{ ucfirst($outlet->shop->activeSubscription?->status ?? 'active') }}
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 0.8125rem; margin-bottom: 0.25rem;">
                                <strong>{{ $approvedInShop }} / {{ $planMax }}</strong> Plan Branches Used
                            </div>

                            @if($isCovered)
                                <span class="status-pill status-active" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-size: 0.72rem;">
                                    ✓ Covered by Plan (Free Approval)
                                </span>
                            @else
                                @if($outlet->payment_status === 'paid')
                                    <span class="status-pill status-active" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 0.72rem;">
                                        ✓ Add-on Fee Paid (LKR {{ number_format($outlet->additional_fee ?: 2500, 2) }})
                                    </span>
                                    <div style="font-size: 0.7rem; color: #64748b; margin-top: 0.15rem;">{{ $outlet->payment_reference ?? 'Verified' }}</div>
                                @elseif($outlet->payment_status === 'pending_verification')
                                    <span class="status-pill status-trial" style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a; font-size: 0.72rem;">
                                        ⏳ Bank Deposit Slip Uploaded
                                    </span>
                                    <div style="font-size: 0.7rem; color: #64748b; margin-top: 0.15rem;">Ref: {{ $outlet->payment_reference }}</div>
                                    @if($outlet->payment_receipt_url)
                                        <a href="{{ $outlet->payment_receipt_url }}" target="_blank" style="font-size: 0.72rem; color: #2563eb; text-decoration: underline; display: inline-block; margin-top: 0.1rem;">
                                            View Slip Attachment ↗
                                        </a>
                                    @endif
                                @else
                                    <span class="status-pill status-suspended" style="background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; font-size: 0.72rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                        <span>Quota Exceeded (LKR {{ number_format($outlet->additional_fee ?: 2500, 2) }} Unpaid)</span>
                                    </span>
                                @endif
                            @endif
                        </td>
                        <td>
                            <div style="font-size: 0.8125rem; color: #1e293b;">
                                {{ $outlet->address }}
                            </div>
                            <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;">
                                {{ $outlet->city ? $outlet->city . ' • ' : '' }}{{ $outlet->phone ?? 'No phone' }}
                            </div>
                        </td>
                        <td>
                            @if($outlet->approval_status === 'pending')
                                <span class="status-pill status-trial" style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a;">
                                    ⏳ Pending Review
                                </span>
                            @elseif($outlet->approval_status === 'approved')
                                <span class="status-pill status-active" style="background: #dcfce7; color: #166534; border: 1px solid #bbf7d0;">
                                    ✓ Approved
                                </span>
                            @elseif($outlet->approval_status === 'rejected')
                                <span class="status-pill status-suspended" style="background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;" title="{{ $outlet->rejection_reason }}">
                                    ✕ Rejected
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($outlet->approval_status === 'pending')
                                <div style="display: flex; gap: 0.4rem; align-items: center; flex-wrap: wrap;">
                                    @if($isCovered || $outlet->payment_status === 'paid')
                                        <!-- Normal Approval for covered or paid branch -->
                                        <form method="POST" action="{{ route('superadmin.outlets.approve', $outlet->id) }}" onsubmit="return confirm('Approve branch \'{{ addslashes($outlet->name) }}\' for shop \'{{ addslashes($outlet->shop->name ?? '') }}\'?');" style="margin: 0;">
                                            @csrf
                                            <button type="submit" class="btn-action btn-success" style="font-size: 0.75rem; padding: 0.35rem 0.65rem;">
                                                ✓ Approve Branch
                                            </button>
                                        </form>
                                    @elseif($outlet->payment_status === 'pending_verification')
                                        <!-- Bank Slip Verify & Approve -->
                                        <form method="POST" action="{{ route('superadmin.outlets.approve', $outlet->id) }}" onsubmit="return confirm('Verify bank slip and approve additional branch \'{{ addslashes($outlet->name) }}\'?');" style="margin: 0;">
                                            @csrf
                                            <button type="submit" class="btn-action btn-success" style="font-size: 0.75rem; padding: 0.35rem 0.65rem;">
                                                ✓ Verify &amp; Approve
                                            </button>
                                        </form>
                                    @else
                                        <!-- Unpaid Additional Branch: Requires confirmation or payment receipt -->
                                        <form method="POST" action="{{ route('superadmin.outlets.approve', $outlet->id) }}" onsubmit="return confirm('Notice: This shop has exhausted their plan quota ({{ $approvedInShop }}/{{ $planMax }} branches used). Payment of LKR {{ number_format($outlet->additional_fee ?: 2500, 2) }} is currently unpaid. Confirm payment received and approve branch?');" style="margin: 0;">
                                            @csrf
                                            <input type="hidden" name="confirm_unpaid" value="1">
                                            <button type="submit" class="btn-action btn-primary" style="font-size: 0.72rem; padding: 0.35rem 0.6rem; background: #d97706; border-color: #d97706;" title="Mark fee as received and approve branch">
                                                ✓ Mark Paid &amp; Approve
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Reject Toggle -->
                                    <button type="button" class="btn-action btn-danger" style="font-size: 0.75rem; padding: 0.35rem 0.65rem;" onclick="toggleRejectOutletModal({{ $outlet->id }})">
                                        ✕ Reject
                                    </button>
                                </div>

                                <!-- Inline Rejection Form -->
                                <div id="reject-outlet-{{ $outlet->id }}" style="display: none; margin-top: 0.5rem; background: #fef2f2; border: 1px solid #fecaca; padding: 0.6rem; border-radius: 8px;">
                                    <form method="POST" action="{{ route('superadmin.outlets.reject', $outlet->id) }}">
                                        @csrf
                                        <label style="font-size: 0.72rem; font-weight: 700; color: #991b1b; display: block; margin-bottom: 0.25rem;">Rejection Reason:</label>
                                        <input type="text" name="reason" placeholder="e.g. Plan quota exceeded / fee not received" style="width: 100%; padding: 0.35rem; font-size: 0.75rem; border: 1px solid #fca5a5; border-radius: 6px; margin-bottom: 0.4rem;">
                                        <div style="display: flex; gap: 0.3rem;">
                                            <button type="submit" class="btn-action btn-danger" style="font-size: 0.72rem; padding: 0.25rem 0.5rem;">Confirm</button>
                                            <button type="button" class="btn-action btn-outline" style="font-size: 0.72rem; padding: 0.25rem 0.5rem;" onclick="toggleRejectOutletModal({{ $outlet->id }})">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            @elseif($outlet->approval_status === 'approved')
                                <div style="font-size: 0.75rem; color: #166534;">
                                    Approved {{ $outlet->approved_at ? $outlet->approved_at->format('M d, Y') : '' }}
                                    @if($isCovered)
                                        <span style="color: #059669; display: block; font-size: 0.7rem;">(Plan Quota)</span>
                                    @else
                                        <span style="color: #2563eb; display: block; font-size: 0.7rem;">(Additional Branch)</span>
                                    @endif
                                </div>
                            @elseif($outlet->approval_status === 'rejected')
                                <div style="font-size: 0.75rem; color: #991b1b; max-width: 200px; word-break: break-word;">
                                    <em>Reason: {{ $outlet->rejection_reason ?? 'Declined by Admin' }}</em>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #94a3b8; padding: 3rem;">
                            <div style="margin-bottom: 0.5rem;">
                                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="1.75" style="display: inline-block;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            </div>
                            <strong style="color: #475569;">No branch requests matching the selected filter!</strong>
                            <p style="font-size: 0.8125rem; margin-top: 0.25rem;">All branch creation requests have been reviewed.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($outlets->hasPages())
            <div style="padding: 1.25rem 1.5rem; border-top: 1px solid var(--surface-border);">
                {{ $outlets->links() }}
            </div>
        @endif
    </div>

    <script>
        function toggleRejectOutletModal(id) {
            const el = document.getElementById('reject-outlet-' + id);
            if (el) {
                el.style.display = el.style.display === 'none' ? 'block' : 'none';
            }
        }
    </script>
@endsection
