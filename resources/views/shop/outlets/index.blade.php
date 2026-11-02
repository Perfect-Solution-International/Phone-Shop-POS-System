@extends('layouts.shop')

@section('title', 'Outlets & Branches')
@section('page-title', 'Shop Outlets & Multi-Branch Management')

@section('content')
<style>
    .outlets-grid {
        padding: 1.5rem;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(min(100%, 310px), 1fr));
        gap: 1.25rem;
    }
    .outlet-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 2px 6px rgba(0,0,0,0.03);
        position: relative;
        transition: all 0.2s ease;
    }
    .outlet-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
    }
    @media (max-width: 640px) {
        .outlets-grid {
            padding: 1rem;
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        .outlet-card {
            padding: 1rem;
        }
    }
</style>

<!-- Store Management Unified Sub-Navigation Tabs -->
<div class="shop-subnav-tabs no-print" style="display: flex; gap: 0.45rem; margin-bottom: 1.1rem; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.75rem; overflow-x: auto; align-items: center;">
    <a href="{{ route('shop.outlets.index') }}" class="btn btn-sm {{ request()->routeIs('shop.outlets*') ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: 8px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.45rem 0.85rem; text-decoration: none; white-space: nowrap;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 21h18"></path><path d="M5 21V7l8-4v18"></path><path d="M19 21V11l-6-3"></path><line x1="9" y1="9" x2="9" y2="9.01"></line><line x1="9" y1="13" x2="9" y2="13.01"></line><line x1="9" y1="17" x2="9" y2="17.01"></line></svg>
        <span>Outlets &amp; Branches</span>
    </a>
    <a href="{{ route('shop.transfers.index') }}" class="btn btn-sm {{ request()->routeIs('shop.transfers*') ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: 8px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.45rem 0.85rem; text-decoration: none; white-space: nowrap;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M16 3h5v5"></path><path d="M4 20L21 3"></path><path d="M21 16v5h-5"></path><path d="M15 15l6 6"></path><path d="M4 4l5 5"></path></svg>
        <span>Branch Transfers</span>
    </a>
    <a href="{{ route('shop.staff') }}" class="btn btn-sm {{ request()->routeIs('shop.staff*') ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: 8px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.45rem 0.85rem; text-decoration: none; white-space: nowrap;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
        <span>Staff &amp; Payroll</span>
    </a>
    <a href="{{ route('shop.subscription.index') }}" class="btn btn-sm {{ request()->routeIs('shop.subscription*') ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: 8px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.45rem 0.85rem; text-decoration: none; white-space: nowrap;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
        <span>Subscription &amp; Billing</span>
    </a>
    <a href="{{ route('shop.settings') }}" class="btn btn-sm {{ request()->routeIs('shop.settings*') ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: 8px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.45rem 0.85rem; text-decoration: none; white-space: nowrap;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
        <span>Store Settings</span>
    </a>
</div>

<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem; justify-content: space-between; align-items: center;">
        <div>
            <h3 style="margin: 0; font-size: 1.125rem; font-weight: 700; color: var(--text-heading); display: flex; align-items: center; gap: 0.5rem;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2.2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                Registered Outlets &amp; Showrooms
            </h3>
            <p style="margin: 0.25rem 0 0; font-size: 0.8125rem; color: var(--text-muted);">
                Configure your retail branches, contact information, and physical locations.
            </p>
        </div>

        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            <!-- Quota & Billing Status -->
            <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.4rem 0.85rem; font-size: 0.8125rem; font-weight: 600; color: #334155; display: inline-flex; align-items: center; gap: 0.5rem;">
                <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: {{ $remainingPlanSlots > 0 ? '#10b981' : '#f59e0b' }};"></span>
                <span>
                    Plan Limit: <strong>{{ $approvedCount }} / {{ $maxBranches }}</strong> Approved
                    @if($remainingPlanSlots > 0)
                        <span style="color: #059669; font-weight: 700;">({{ $remainingPlanSlots }} free remaining)</span>
                    @else
                        <span style="color: #d97706; font-weight: 700;">(Plan full — Extra: LKR {{ number_format($additionalBranchFee, 0) }}/mo)</span>
                    @endif
                </span>
            </div>

            <button type="button" class="btn btn-primary" onclick="openModal('addOutletModal')">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Add New Outlet
            </button>
        </div>
    </div>

    <!-- Outlets Grid -->
    <div class="outlets-grid">
        @forelse($outlets as $outlet)
            <div class="outlet-card" style="border: 1.5px solid {{ $outlet->is_main ? '#818cf8' : 'var(--surface-border)' }};">
                <div>
                    <!-- Card Top Header -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.75rem;">
                        <div style="min-width: 0;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                <h4 style="margin: 0; font-size: 1rem; font-weight: 700; color: var(--text-heading); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ $outlet->name }}
                                </h4>
                                <span style="font-family: monospace; font-size: 0.72rem; font-weight: 700; background: #e0e7ff; color: #4338ca; padding: 0.15rem 0.45rem; border-radius: 4px;">
                                    {{ $outlet->code }}
                                </span>
                            </div>
                            @if($outlet->city)
                                <span style="font-size: 0.75rem; color: #64748b; font-weight: 500; display: inline-flex; align-items: center; gap: 0.25rem;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                    {{ $outlet->city }}
                                </span>
                            @endif
                        </div>

                        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.25rem;">
                            @if($outlet->is_main)
                                <span class="badge" style="background: #4f46e5; color: #ffffff; font-size: 0.68rem; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.25rem;">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                    Main Branch
                                </span>
                            @endif

                            @if(($outlet->approval_status ?? 'approved') === 'pending')
                                <span class="badge" style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a; font-size: 0.68rem; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.25rem;" title="Awaiting Super Admin approval">
                                    ⏳ Pending Approval
                                </span>
                            @elseif(($outlet->approval_status ?? 'approved') === 'rejected')
                                <span class="badge" style="background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; font-size: 0.68rem; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.25rem;" title="Rejected by Super Admin">
                                    ✕ Rejected
                                </span>
                            @elseif($outlet->is_active)
                                <span class="badge badge-success" style="font-size: 0.65rem;">Active</span>
                            @else
                                <span class="badge badge-secondary" style="font-size: 0.65rem;">Inactive</span>
                            @endif

                            {{-- Billing Status Badge --}}
                            @if($outlet->isCoveredByPlan())
                                <span class="badge" style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; font-size: 0.68rem; font-weight: 600; padding: 0.15rem 0.45rem; border-radius: 5px;" title="Covered under your subscription quota">
                                    ✓ Plan Quota
                                </span>
                            @elseif($outlet->payment_status === 'paid')
                                <span class="badge" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-size: 0.68rem; font-weight: 600; padding: 0.15rem 0.45rem; border-radius: 5px;" title="Add-on branch fee paid">
                                    ✓ Add-on Paid
                                </span>
                            @elseif($outlet->payment_status === 'pending_verification')
                                <span class="badge" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 0.68rem; font-weight: 600; padding: 0.15rem 0.45rem; border-radius: 5px;" title="Bank slip uploaded; Super Admin verifying">
                                    ⏳ Slip Uploaded
                                </span>
                            @else
                                <span class="badge" style="background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; font-size: 0.68rem; font-weight: 700; padding: 0.15rem 0.45rem; border-radius: 5px;" title="Payment required for Super Admin approval">
                                    ⚠️ Fee Unpaid
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Outlet Meta Details -->
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 0.85rem; font-size: 0.8125rem; color: #334155; margin-bottom: 1rem; display: flex; flex-direction: column; gap: 0.4rem;">
                        <div style="display: flex; align-items: flex-start; gap: 0.4rem;">
                            <span style="color: #64748b; min-width: 65px; font-weight: 600; flex-shrink: 0;">Address:</span>
                            <span style="font-weight: 500; color: #1e293b; word-break: break-word;">{{ $outlet->address }}</span>
                        </div>
                        @if($outlet->phone)
                            <div style="display: flex; align-items: center; gap: 0.4rem;">
                                <span style="color: #64748b; min-width: 65px; font-weight: 600; flex-shrink: 0;">Phone:</span>
                                <span style="font-weight: 500; color: #0284c7;">{{ $outlet->phone }}</span>
                            </div>
                        @endif
                        @if($outlet->email)
                            <div style="display: flex; align-items: center; gap: 0.4rem; overflow: hidden;">
                                <span style="color: #64748b; min-width: 65px; font-weight: 600; flex-shrink: 0;">Email:</span>
                                <span style="font-weight: 500; color: #475569; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $outlet->email }}">{{ $outlet->email }}</span>
                            </div>
                        @endif
                        @if($outlet->manager_name || $outlet->manager_email)
                            <div style="display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                                <span style="color: #64748b; min-width: 65px; font-weight: 600; flex-shrink: 0;">Manager:</span>
                                <span style="font-weight: 600; color: #1e293b;">{{ $outlet->manager_name ?: 'Branch Manager' }}</span>
                                @if($outlet->manager_email)
                                    <span class="badge" style="background: #e0e7ff; color: #4338ca; font-size: 0.68rem; padding: 0.15rem 0.45rem; border-radius: 4px; display: inline-flex; align-items: center; gap: 0.25rem;" title="Manager Login Email">
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                        {{ $outlet->manager_email }}
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Additional Branch Payment Alert / Action --}}
                    @if(! $outlet->isCoveredByPlan() && $outlet->payment_status === 'unpaid' && ($outlet->approval_status ?? 'approved') === 'pending')
                        <div style="background: #fffbeb; border: 1.5px dashed #f59e0b; border-radius: 8px; padding: 0.75rem 0.85rem; margin-bottom: 1rem;">
                            <div style="font-size: 0.78rem; font-weight: 700; color: #92400e; display: inline-flex; align-items: center; gap: 0.35rem; margin-bottom: 0.25rem;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                                <span>Additional Branch Fee Required</span>
                            </div>
                            <div style="font-size: 0.74rem; color: #78350f; line-height: 1.4; margin-bottom: 0.5rem;">
                                This branch exceeds your current plan quota. Pay the add-on fee of <strong>LKR {{ number_format($outlet->additional_fee ?: 2500, 2) }}</strong> to enable Super Admin approval.
                            </div>
                            <button type="button" class="btn btn-sm btn-primary" onclick="openPaymentModal({{ $outlet->id }}, '{{ addslashes($outlet->name) }}', {{ $outlet->additional_fee ?: 2500 }})" style="font-size: 0.75rem; padding: 0.3rem 0.75rem; display: inline-flex; align-items: center; gap: 0.4rem;">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                                <span>Pay Fee (LKR {{ number_format($outlet->additional_fee ?: 2500, 0) }})</span>
                            </button>
                        </div>
                    @elseif(! $outlet->isCoveredByPlan() && $outlet->payment_status === 'pending_verification' && ($outlet->approval_status ?? 'approved') === 'pending')
                        <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 0.65rem 0.85rem; margin-bottom: 1rem; font-size: 0.75rem; color: #1e40af; display: flex; align-items: center; gap: 0.4rem;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline></svg>
                            <span>Bank deposit slip submitted (Ref: {{ $outlet->payment_reference }}). Super Admin will review and approve soon.</span>
                        </div>
                    @endif
                </div>

                <!-- Card Actions -->
                <div style="border-top: 1px solid var(--surface-border); padding-top: 0.85rem; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <div>
                        @if(($outlet->approval_status ?? 'approved') === 'pending')
                            <span style="display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.72rem; font-weight: 600; color: #b45309; background: #fffbeb; border: 1px solid #fde68a; padding: 0.25rem 0.55rem; border-radius: 6px;" title="This branch requires Super Admin approval before you can switch or begin sales.">
                                ⏳ Super Admin Review
                            </span>
                        @elseif(($outlet->approval_status ?? 'approved') === 'rejected')
                            <span style="display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.72rem; font-weight: 600; color: #b91c1c; background: #fef2f2; border: 1px solid #fecaca; padding: 0.25rem 0.55rem; border-radius: 6px;" title="{{ $outlet->rejection_reason ?? 'Branch request declined' }}">
                                ✕ Declined
                            </span>
                        @elseif($activeOutletId == $outlet->id)
                            <span style="display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.75rem; font-weight: 700; color: #059669; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 0.25rem 0.55rem; border-radius: 6px;">
                                <span style="width: 6px; height: 6px; border-radius: 50%; background: #10b981;"></span>
                                Active Branch
                            </span>
                        @else
                            <form method="POST" action="{{ route('shop.outlets.switch', $outlet) }}" style="display: inline; margin: 0;">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-secondary" style="font-size: 0.75rem; padding: 0.25rem 0.6rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 2v6h-6"></path><path d="M3 12a9 9 0 0 1 15-6.7L21 8"></path><path d="M3 22v-6h6"></path><path d="M21 12a9 9 0 0 1-15 6.7L3 16"></path></svg>
                                    Switch Branch
                                </button>
                            </form>
                        @endif
                    </div>

                    <div style="display: flex; align-items: center; gap: 0.35rem;">
                        <!-- Edit Button -->
                        <button type="button" 
                                class="btn btn-sm btn-secondary" 
                                onclick='openEditOutletModal(@json($outlet))' 
                                title="Edit outlet details" 
                                style="padding: 0.3rem 0.6rem; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                            Edit
                        </button>

                        @if(! $outlet->is_main)
                            @if(($outlet->approval_status ?? 'approved') === 'approved')
                                <!-- Set as Main Button -->
                                <form method="POST" action="{{ route('shop.outlets.toggle-main', $outlet) }}" style="display: inline; margin: 0;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-secondary" title="Set as primary Main Branch" style="padding: 0.3rem 0.55rem; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                        Make Main
                                    </button>
                                </form>
                            @endif

                            <!-- Delete Button -->
                            <form method="POST" action="{{ route('shop.outlets.destroy', $outlet) }}" onsubmit="return confirm('Are you sure you want to delete outlet \'{{ addslashes($outlet->name) }}\' ({{ $outlet->code }})?');" style="display: inline; margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Delete outlet" style="padding: 0.3rem 0.55rem; font-size: 0.75rem;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: 3rem;">
                No branch outlets found. Click "+ Add New Outlet" to register your first branch.
            </div>
        @endforelse
    </div>
</div>

<!-- Modal: Add Outlet -->
<div class="modal-backdrop" id="addOutletModal">
    <div class="modal-box" style="max-width: 580px;">
        <div class="modal-header">
            <h3>Add New Branch / Outlet</h3>
            <button type="button" class="modal-close" onclick="closeModal('addOutletModal')">&times;</button>
        </div>
        <form method="POST" action="{{ route('shop.outlets.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group" style="flex: 2;">
                        <label>Outlet / Branch Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Kandy City Showroom" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Branch Code</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. KND-01" style="font-family: monospace; text-transform: uppercase;">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Contact Phone</label>
                        <input type="text" name="phone" class="form-control" placeholder="e.g. 081 223 4567">
                    </div>
                    <div class="form-group">
                        <label>Contact Email (Optional)</label>
                        <input type="email" name="email" class="form-control" placeholder="e.g. kandy@store.com">
                    </div>
                </div>

                <div class="form-group">
                    <label>Address *</label>
                    <input type="text" name="address" class="form-control" placeholder="e.g. Level 2, KCC, Dalada Veediya" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>City / District</label>
                        <input type="text" name="city" class="form-control" placeholder="e.g. Kandy">
                    </div>
                    <div class="form-group">
                        <label>Branch Manager / Person in Charge</label>
                        <input type="text" name="manager_name" class="form-control" placeholder="e.g. Nuwan Perera">
                    </div>
                </div>

                <!-- Branch Manager Login Credentials (Optional) -->
                <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.85rem 1rem; margin-top: 0.75rem;">
                    <div style="font-weight: 700; font-size: 0.825rem; color: #1e293b; display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.25rem;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2.2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        Branch Manager Login Account (Optional)
                    </div>
                    <p style="font-size: 0.75rem; color: #64748b; margin: 0 0 0.65rem;">
                        Provide an email and password to automatically create a 'Shop Manager' login account for this branch.
                    </p>
                    <div class="form-row" style="margin-bottom: 0;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label style="font-size: 0.8rem; font-weight: 600;">Manager Login Email</label>
                            <input type="email" name="manager_email" class="form-control" placeholder="manager@branch.com">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label style="font-size: 0.8rem; font-weight: 600;">Manager Login Password</label>
                            <input type="password" name="manager_password" class="form-control" placeholder="Min 6 characters">
                        </div>
                    </div>
                </div>

                <div class="form-row" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 1rem; margin-top: 0.5rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; margin-bottom: 0; font-weight: 600;">
                            <input type="checkbox" name="is_main" value="1">
                            <span>Set as primary Main Branch</span>
                        </label>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; margin-bottom: 0; font-weight: 600;">
                            <input type="checkbox" name="is_active" value="1" checked>
                            <span>Branch is active &amp; open</span>
                        </label>
                    </div>
                </div>
                @if($remainingPlanSlots > 0)
                    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 0.75rem 0.85rem; margin-top: 0.75rem; font-size: 0.8rem; color: #166534; display: flex; align-items: flex-start; gap: 0.5rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="flex-shrink: 0; margin-top: 0.1rem;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <div>
                            <strong>Covered Free by Current Plan:</strong> You have <strong>{{ $remainingPlanSlots }}</strong> remaining included branch slot(s) in your <strong>{{ $plan?->name ?? 'Active' }}</strong> plan. This branch will be submitted for Super Admin free approval without any additional charge!
                        </div>
                    </div>
                @else
                    <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 0.75rem 0.85rem; margin-top: 0.75rem; font-size: 0.8rem; color: #92400e; display: flex; align-items: flex-start; gap: 0.5rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0; margin-top: 0.1rem;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                        <div>
                            <strong>Additional Branch Add-on:</strong> Your plan limit ({{ $maxBranches }} branch{{ $maxBranches > 1 ? 'es' : '' }}) is fully utilized. Adding this new branch requires an add-on fee of <strong>LKR {{ number_format($additionalBranchFee, 2) }}</strong>. You can pay via card or bank slip after submitting to get Super Admin approval.
                        </div>
                    </div>
                @endif
                <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 0.75rem 0.85rem; margin-top: 0.75rem; font-size: 0.8rem; color: #1e40af; display: flex; align-items: flex-start; gap: 0.5rem;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0; margin-top: 0.1rem;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    <div>
                        <strong>Super Admin Approval:</strong> Newly created branches require platform review by the Super Administrator before they can be activated for POS billing and daily operations.
                    </div>
                </div>
            </div>
            <div style="padding: 1.25rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addOutletModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Submit for Approval</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Outlet -->
<div class="modal-backdrop" id="editOutletModal">
    <div class="modal-box" style="max-width: 580px;">
        <div class="modal-header">
            <h3>Edit Outlet / Branch</h3>
            <button type="button" class="modal-close" onclick="closeModal('editOutletModal')">&times;</button>
        </div>
        <form id="editOutletForm" method="POST" action="">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group" style="flex: 2;">
                        <label>Outlet / Branch Name *</label>
                        <input type="text" name="name" id="edit_outlet_name" class="form-control" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Branch Code *</label>
                        <input type="text" name="code" id="edit_outlet_code" class="form-control" required style="font-family: monospace; text-transform: uppercase;">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Contact Phone</label>
                        <input type="text" name="phone" id="edit_outlet_phone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Contact Email (Optional)</label>
                        <input type="email" name="email" id="edit_outlet_email" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label>Address *</label>
                    <input type="text" name="address" id="edit_outlet_address" class="form-control" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>City / District</label>
                        <input type="text" name="city" id="edit_outlet_city" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Branch Manager / Person in Charge</label>
                        <input type="text" name="manager_name" id="edit_outlet_manager_name" class="form-control">
                    </div>
                </div>

                <!-- Branch Manager Login Credentials -->
                <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.85rem 1rem; margin-top: 0.75rem;">
                    <div style="font-weight: 700; font-size: 0.825rem; color: #1e293b; display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.25rem;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2.2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        Branch Manager Login Account (Optional)
                    </div>
                    <p style="font-size: 0.75rem; color: #64748b; margin: 0 0 0.65rem;">
                        Allows the branch manager to log into POS &amp; Shop Dashboard with 'Shop Manager' permissions.
                    </p>
                    <div class="form-row" style="margin-bottom: 0;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label style="font-size: 0.8rem; font-weight: 600;">Manager Login Email</label>
                            <input type="email" name="manager_email" id="edit_outlet_manager_email" class="form-control" placeholder="manager@branch.com">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label style="font-size: 0.8rem; font-weight: 600;">Login Password</label>
                            <input type="password" name="manager_password" id="edit_outlet_manager_password" class="form-control" placeholder="Leave blank to keep unchanged">
                        </div>
                    </div>
                </div>

                <div class="form-row" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 1rem; margin-top: 0.5rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; margin-bottom: 0; font-weight: 600;">
                            <input type="checkbox" name="is_main" id="edit_outlet_is_main" value="1">
                            <span>Primary Main Branch</span>
                        </label>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; margin-bottom: 0; font-weight: 600;">
                            <input type="checkbox" name="is_active" id="edit_outlet_is_active" value="1">
                            <span>Branch is active &amp; open</span>
                        </label>
                    </div>
                </div>
            </div>
            <div style="padding: 1.25rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editOutletModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Pay Additional Branch Fee -->
<div class="modal-backdrop" id="branchPaymentModal">
    <div class="modal-box" style="max-width: 520px;">
        <div class="modal-header">
            <h3>Pay Additional Branch Fee</h3>
            <button type="button" class="modal-close" onclick="closeModal('branchPaymentModal')">&times;</button>
        </div>
        <form id="branchPaymentForm" method="POST" action="" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 1.2rem; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Outlet / Branch</div>
                        <div id="pay_branch_name" style="font-size: 1rem; font-weight: 700; color: #1e293b;">Branch</div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase;">One-time Add-on Fee</div>
                        <div id="pay_branch_fee" style="font-size: 1.15rem; font-weight: 800; color: #4f46e5;">LKR 2,500.00</div>
                    </div>
                </div>

                <!-- Payment Method Toggle -->
                <div style="display: flex; gap: 0.75rem; margin-bottom: 1.25rem;">
                    <label style="flex: 1; border: 1.5px solid #4f46e5; border-radius: 8px; padding: 0.75rem; text-align: center; cursor: pointer; background: #eef2ff;" id="labelMethodCard">
                        <input type="radio" name="payment_method" value="card" checked onchange="switchPayMethod('card')" style="display: none;">
                        <div style="font-weight: 700; font-size: 0.85rem; color: #4338ca; display: inline-flex; align-items: center; justify-content: center; gap: 0.35rem;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                            <span>Credit / Debit Card</span>
                        </div>
                        <div style="font-size: 0.72rem; color: #6366f1;">Instant verification</div>
                    </label>
                    <label style="flex: 1; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 0.75rem; text-align: center; cursor: pointer; background: #ffffff;" id="labelMethodBank">
                        <input type="radio" name="payment_method" value="bank_transfer" onchange="switchPayMethod('bank_transfer')" style="display: none;">
                        <div style="font-weight: 700; font-size: 0.85rem; color: #475569; display: inline-flex; align-items: center; justify-content: center; gap: 0.35rem;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 21h18"></path><path d="M5 21V10l7-5 7 5v11"></path><line x1="9" y1="14" x2="9" y2="17"></line><line x1="15" y1="14" x2="15" y2="17"></line></svg>
                            <span>Bank Transfer</span>
                        </div>
                        <div style="font-size: 0.72rem; color: #64748b;">Manual slip approval</div>
                    </label>
                </div>

                <!-- Card Details Section -->
                <div id="sectionCard">
                    <div class="form-group">
                        <label>Cardholder Name *</label>
                        <input type="text" name="cardholder_name" id="cardholder_name" class="form-control" placeholder="Name on card" value="{{ auth()->user()->name }}">
                    </div>
                    <div class="form-group">
                        <label>Card Number *</label>
                        <input type="text" name="card_number" id="card_number" class="form-control" placeholder="4242 •••• •••• 4242" maxlength="19">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Expiry Date *</label>
                            <input type="text" name="card_expiry" id="card_expiry" class="form-control" placeholder="MM/YY" maxlength="5">
                        </div>
                        <div class="form-group">
                            <label>CVV / CVC *</label>
                            <input type="password" name="card_cvv" id="card_cvv" class="form-control" placeholder="123" maxlength="4">
                        </div>
                    </div>
                </div>

                <!-- Bank Slip Section -->
                <div id="sectionBank" style="display: none;">
                    <div style="background: #f1f5f9; border-radius: 6px; padding: 0.75rem 0.85rem; margin-bottom: 0.85rem; font-size: 0.75rem; color: #334155; line-height: 1.4;">
                        <strong>Bank Details:</strong> Bank of Ceylon (BOC)<br>
                        <strong>Account No:</strong> 0089271638<br>
                        <strong>Branch:</strong> Corporate Branch Colombo
                    </div>
                    <div class="form-group">
                        <label>Deposit / Transfer Reference No. *</label>
                        <input type="text" name="reference_number" id="reference_number" class="form-control" placeholder="e.g. TXN-9823412">
                    </div>
                    <div class="form-group">
                        <label>Upload Payment Receipt / Slip</label>
                        <input type="file" name="receipt" class="form-control" accept="image/*,.pdf">
                    </div>
                </div>
            </div>
            <div style="padding: 1.25rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('branchPaymentModal')">Cancel</button>
                <button type="submit" class="btn btn-primary" id="btnSubmitPayment">Pay Now (Instant)</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openEditOutletModal(outlet) {
        const form = document.getElementById('editOutletForm');
        form.action = `/shop/outlets/${outlet.id}`;

        document.getElementById('edit_outlet_name').value = outlet.name || '';
        document.getElementById('edit_outlet_code').value = outlet.code || '';
        document.getElementById('edit_outlet_phone').value = outlet.phone || '';
        document.getElementById('edit_outlet_email').value = outlet.email || '';
        document.getElementById('edit_outlet_address').value = outlet.address || '';
        document.getElementById('edit_outlet_city').value = outlet.city || '';
        document.getElementById('edit_outlet_manager_name').value = outlet.manager_name || '';
        document.getElementById('edit_outlet_manager_email').value = outlet.manager_email || '';
        document.getElementById('edit_outlet_manager_password').value = '';
        document.getElementById('edit_outlet_is_main').checked = !!outlet.is_main;
        document.getElementById('edit_outlet_is_active').checked = !!outlet.is_active;

        openModal('editOutletModal');
    }

    function openPaymentModal(outletId, outletName, fee) {
        const form = document.getElementById('branchPaymentForm');
        form.action = `/shop/outlets/${outletId}/pay`;
        document.getElementById('pay_branch_name').textContent = outletName;
        document.getElementById('pay_branch_fee').textContent = 'LKR ' + Number(fee).toLocaleString(undefined, {minimumFractionDigits: 2});
        switchPayMethod('card');
        openModal('branchPaymentModal');
    }

    function switchPayMethod(method) {
        const secCard = document.getElementById('sectionCard');
        const secBank = document.getElementById('sectionBank');
        const lblCard = document.getElementById('labelMethodCard');
        const lblBank = document.getElementById('labelMethodBank');
        const btnSubmit = document.getElementById('btnSubmitPayment');

        if (method === 'card') {
            secCard.style.display = 'block';
            secBank.style.display = 'none';
            lblCard.style.border = '1.5px solid #4f46e5';
            lblCard.style.background = '#eef2ff';
            lblBank.style.border = '1.5px solid #cbd5e1';
            lblBank.style.background = '#ffffff';
            btnSubmit.textContent = 'Pay Now (Instant)';
        } else {
            secCard.style.display = 'none';
            secBank.style.display = 'block';
            lblBank.style.border = '1.5px solid #4f46e5';
            lblBank.style.background = '#eef2ff';
            lblCard.style.border = '1.5px solid #cbd5e1';
            lblCard.style.background = '#ffffff';
            btnSubmit.textContent = 'Submit Slip for Approval';
        }
    }
</script>
@endpush
@endsection

