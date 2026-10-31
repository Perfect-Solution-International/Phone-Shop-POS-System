@extends('layouts.super-admin')

@section('title', 'Shop Owners & Stores')
@section('page_title', 'Shop Owners & Stores Directory')

@section('content')
    <!-- Status Notices -->
    @if (session('status'))
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 0.85rem 1.25rem; border-radius: 10px; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; font-weight: 600;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <span>{{ session('status') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.style.display='none'" style="background:none; border:none; color: #065f46; font-size: 1.2rem; cursor: pointer;">&times;</button>
        </div>
    @endif

    @if (session('error'))
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.85rem 1.25rem; border-radius: 10px; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; font-weight: 600;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.style.display='none'" style="background:none; border:none; color: #991b1b; font-size: 1.2rem; cursor: pointer;">&times;</button>
        </div>
    @endif

    @if (isset($errors) && $errors->any())
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.85rem 1.25rem; border-radius: 10px; margin-bottom: 1.5rem;">
            <strong style="display: block; margin-bottom: 0.35rem; font-size: 0.875rem;">Please review the following input errors:</strong>
            <ul style="margin: 0; padding-left: 1.2rem; font-size: 0.8125rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Metric Summary Grid -->
    <div class="metric-grid">
        <div class="metric-card">
            <span class="metric-label">Registered Shop Owners</span>
            <span class="metric-value">{{ $totalOwners }}</span>
            <span class="metric-sub" style="color: #6366f1;">Verified Accounts: {{ $verifiedOwners }}</span>
        </div>
        <div class="metric-card">
            <span class="metric-label">Email Verified Owners</span>
            <span class="metric-value" style="color: #10b981;">{{ $verifiedOwners }}</span>
            <span class="metric-sub" style="color: #059669;">Ready for Live Operations</span>
        </div>
        <div class="metric-card">
            <span class="metric-label">Unverified Owners</span>
            <span class="metric-value" style="color: #f59e0b;">{{ $unverifiedOwners }}</span>
            <span class="metric-sub" style="color: #d97706;">Pending Verification</span>
        </div>
        <div class="metric-card">
            <span class="metric-label">Tenant Store Workspaces</span>
            <span class="metric-value">{{ $totalShops }}</span>
            <span class="metric-sub" style="color: #64748b;">Active Platforms</span>
        </div>
    </div>

    <!-- Main Owners Data Card -->
    <div class="data-card">
        <div class="data-card-header" style="flex-wrap: wrap; gap: 1rem; justify-content: space-between; align-items: center;">
            <div>
                <h2 class="data-card-title" style="display: flex; align-items: center; gap: 0.5rem;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2.2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    <span>Shop Owners Directory &amp; Stores ({{ $owners->total() }})</span>
                </h2>
                <p style="margin: 0.2rem 0 0; font-size: 0.8125rem; color: #64748b;">
                    Manage tenant shop accounts, credentials, their registered stores, and subscription plans.
                </p>
            </div>

            <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                <!-- Filter Form -->
                <form method="GET" action="{{ route('superadmin.owners.index') }}" style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                    <input
                        type="text"
                        name="search"
                        placeholder="Search name, email, phone, shop..."
                        value="{{ request('search') }}"
                        style="padding: 0.45rem 0.85rem; border: 1.5px solid var(--surface-border); border-radius: 8px; font-size: 0.875rem; min-width: 220px;"
                    >
                    <select name="shop_id" style="padding: 0.45rem 0.85rem; border: 1.5px solid var(--surface-border); border-radius: 8px; font-size: 0.875rem;">
                        <option value="">All Stores</option>
                        @foreach($shops as $sh)
                            <option value="{{ $sh->id }}" {{ request('shop_id') == $sh->id ? 'selected' : '' }}>{{ $sh->name }}</option>
                        @endforeach
                    </select>
                    <select name="verification" style="padding: 0.45rem 0.85rem; border: 1.5px solid var(--surface-border); border-radius: 8px; font-size: 0.875rem;">
                        <option value="">All Verification</option>
                        <option value="verified" {{ request('verification') === 'verified' ? 'selected' : '' }}>Verified</option>
                        <option value="unverified" {{ request('verification') === 'unverified' ? 'selected' : '' }}>Unverified</option>
                    </select>
                    <button type="submit" class="btn-action btn-primary" style="padding: 0.45rem 0.85rem;">Filter</button>
                    @if (request()->hasAny(['search', 'shop_id', 'verification']))
                        <a href="{{ route('superadmin.owners.index') }}" class="btn-action btn-outline" style="padding: 0.45rem 0.85rem;">Clear</a>
                    @endif
                </form>

                <!-- Add Owner & Store Button -->
                <button type="button" class="btn-action btn-primary" onclick="openOwnerModal('addOwnerModal')" style="display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 700; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none; padding: 0.45rem 1rem;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span>Register New Owner &amp; Store</span>
                </button>
            </div>
        </div>

        <table class="saas-table">
            <thead>
                <tr>
                    <th>Shop Owner</th>
                    <th>Contact Phone</th>
                    <th>Registered Store Workspace</th>
                    <th>Subscription Plan</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th style="text-align: right; min-width: 240px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($owners as $owner)
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 38px; height: 38px; border-radius: 50%; background: #e0e7ff; color: #4338ca; font-weight: 750; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    {{ strtoupper(substr($owner->name, 0, 1)) }}
                                </div>
                                <div style="min-width: 0;">
                                    <strong style="font-size: 0.9375rem; color: var(--text-heading); display: block;">{{ $owner->name }}</strong>
                                    <span style="font-size: 0.78rem; color: #64748b;">{{ $owner->email }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if ($owner->phone)
                                <span style="font-size: 0.8125rem; font-family: monospace; color: #0284c7; font-weight: 600;">{{ $owner->phone }}</span>
                            @else
                                <span style="font-size: 0.78rem; color: #94a3b8;">None</span>
                            @endif
                        </td>
                        <td>
                            @if ($owner->shop)
                                <div>
                                    <a href="{{ route('superadmin.shops.show', $owner->shop->id) }}" style="font-size: 0.9375rem; font-weight: 700; color: #4338ca; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;" title="View Store Analytics & Details">
                                        <span>{{ $owner->shop->name }}</span>
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                    </a>
                                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;">
                                        <span>ID #{{ $owner->shop->id }}</span>
                                        @if($owner->shop->address)
                                            <span>• {{ \Illuminate\Support\Str::limit($owner->shop->address, 28) }}</span>
                                        @endif
                                    </div>
                                    <div style="margin-top: 0.3rem; display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.75rem; font-weight: 600; color: #0369a1; background: #f0f9ff; border: 1px solid #bae6fd; padding: 0.15rem 0.5rem; border-radius: 6px;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                                        <span>{{ $owner->shop->outlets ? $owner->shop->outlets->count() : 1 }} Branch Outlet(s)</span>
                                    </div>
                                </div>
                            @else
                                <span style="color: #ef4444; font-size: 0.78rem; font-weight: 600;">No Store Assigned</span>
                            @endif
                        </td>
                        <td>
                            @if ($owner->shop?->subscription)
                                <div>
                                    <div style="font-weight: 700; font-size: 0.85rem; color: #1e293b;">
                                        {{ $owner->shop->subscription->plan->name ?? 'Standard Plan' }}
                                    </div>
                                    <div style="font-size: 0.72rem; color: #64748b; margin-top: 0.1rem;">
                                        LKR {{ number_format($owner->shop->subscription->recurring_amount ?? $owner->shop->subscription->plan->monthly_price ?? 0, 0) }}/mo
                                    </div>
                                </div>
                            @else
                                <span style="color: #94a3b8; font-size: 0.75rem;">None</span>
                            @endif
                        </td>
                        <td>
                            @if ($owner->shop?->subscription)
                                <span class="status-pill status-{{ $owner->shop->subscription->status ?? 'trial' }}" style="display: inline-block; margin-bottom: 0.25rem;">
                                    {{ ucfirst($owner->shop->subscription->status ?? 'trial') }}
                                </span>
                            @endif
                            <div>
                                @if ($owner->email_verified_at)
                                    <span style="display: inline-flex; align-items: center; gap: 0.2rem; font-size: 0.72rem; font-weight: 600; color: #059669;">
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                        Verified
                                    </span>
                                @else
                                    <span style="display: inline-flex; align-items: center; gap: 0.2rem; font-size: 0.72rem; font-weight: 600; color: #d97706;">
                                        Unverified
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 0.8125rem; color: #334155;">{{ $owner->created_at->format('Y-m-d') }}</div>
                            <div style="font-size: 0.72rem; color: #64748b;">{{ $owner->created_at->diffForHumans() }}</div>
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <div class="owner-action-toolbar">
                                <!-- Enter Store Action -->
                                @if($owner->shop_id)
                                    <form method="POST" action="{{ route('superadmin.owners.access', $owner) }}" style="display: inline; margin: 0;">
                                        @csrf
                                        <button type="submit" class="owner-btn-action owner-btn-primary" title="Access Store Workspace as Super Admin">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
                                            <span>Access</span>
                                        </button>
                                    </form>

                                    <!-- View Store Details -->
                                    <a href="{{ route('superadmin.shops.show', $owner->shop_id) }}" class="owner-btn-action owner-btn-secondary" title="View Store Analytics & Branches">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                        <span>Store</span>
                                    </a>

                                    <!-- Toggle Store Suspension -->
                                    <form method="POST" action="{{ route('superadmin.shops.toggle-suspension', $owner->shop_id) }}" style="display: inline; margin: 0;">
                                        @csrf
                                        @if($owner->shop?->subscription?->status === 'suspended')
                                            <button type="submit" class="owner-btn-action" style="background: #f0fdf4; border-color: #86efac; color: #166534;" title="Activate Store" onclick="return confirm('Activate store {{ addslashes($owner->shop->name) }}?');">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                                                <span>Activate</span>
                                            </button>
                                        @else
                                            <button type="submit" class="owner-btn-action" style="background: #fffbeb; border-color: #fde68a; color: #b45309;" title="Suspend Store" onclick="return confirm('Suspend store {{ addslashes($owner->shop->name) }}?');">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect></svg>
                                                <span>Suspend</span>
                                            </button>
                                        @endif
                                    </form>
                                @endif

                                <!-- Edit Button -->
                                <button
                                    type="button"
                                    class="owner-btn-action owner-btn-secondary"
                                    onclick='openEditOwnerModal(@json($owner), @json($owner->shop))'
                                    title="Edit owner credentials and store details"
                                >
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    <span>Edit</span>
                                </button>

                                <!-- Delete Button -->
                                <form method="POST" action="{{ route('superadmin.owners.destroy', $owner) }}" onsubmit="return confirm('Are you sure you want to delete owner \'{{ addslashes($owner->name) }}\' ({{ $owner->email }})?');" style="display: inline; margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="owner-btn-action owner-btn-danger" title="Delete owner account">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: #94a3b8; padding: 3rem;">
                            No shop owners found matching your criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($owners->hasPages())
            <div style="padding: 1.25rem 1.5rem; border-top: 1px solid var(--surface-border);">
                {{ $owners->links() }}
            </div>
        @endif
    </div>

    <!-- Modal: Register Shop Owner & Store Workspace -->
    <div class="owner-modal-backdrop" id="addOwnerModal">
        <div class="owner-modal-box" style="max-width: 620px;">
            <div class="owner-modal-header">
                <h3>Register Shop Owner &amp; Store Workspace</h3>
                <button type="button" class="owner-modal-close" onclick="closeOwnerModal('addOwnerModal')">&times;</button>
            </div>

            <!-- 2-Step Wizard Stepper Bar -->
            <div style="display: flex; border-bottom: 1px solid #e2e8f0; background: #f8fafc; padding: 0.65rem 1.5rem; gap: 1rem; align-items: center;">
                <div id="stepPill1" style="display: flex; align-items: center; gap: 0.45rem; font-size: 0.8125rem; font-weight: 700; color: #4338ca; cursor: pointer;" onclick="goToOwnerStep(1)">
                    <span id="stepBadge1" style="display: inline-flex; width: 22px; height: 22px; border-radius: 50%; background: #4f46e5; color: #fff; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700;">1</span>
                    <span>1. Owner &amp; Store Details</span>
                </div>
                <div style="flex: 1; height: 2px; background: #e2e8f0;" id="stepLine"></div>
                <div id="stepPill2" style="display: flex; align-items: center; gap: 0.45rem; font-size: 0.8125rem; font-weight: 600; color: #94a3b8; cursor: pointer;" onclick="goToOwnerStep(2)">
                    <span id="stepBadge2" style="display: inline-flex; width: 22px; height: 22px; border-radius: 50%; background: #e2e8f0; color: #64748b; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700;">2</span>
                    <span>2. Subscription Payment</span>
                </div>
            </div>

            <form method="POST" action="{{ route('superadmin.owners.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="owner-modal-body" id="ownerModalBody" style="max-height: 70vh; overflow-y: auto;">
                    <!-- STEP 1: Details Container -->
                    <div id="ownerStep1">
                        <!-- Section 1: Owner Details -->
                        <div style="margin-bottom: 1.25rem;">
                            <div style="font-weight: 750; font-size: 0.875rem; color: #1e293b; display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.65rem; padding-bottom: 0.4rem; border-bottom: 1.5px solid #e2e8f0;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2.2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                <span>Owner Account Details</span>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 0.75rem;">
                                <div class="owner-form-group">
                                    <label>Owner Full Name *</label>
                                    <input type="text" name="name" id="add_owner_name" class="owner-form-control" placeholder="e.g. Kasun Jayawardena" required>
                                </div>
                                <div class="owner-form-group">
                                    <label>Email Address (Login) *</label>
                                    <input type="email" name="email" id="add_owner_email" class="owner-form-control" placeholder="e.g. kasun@apexcare.com" required>
                                </div>
                            </div>

                            <div class="owner-form-group" style="margin-bottom: 0.75rem;">
                                <label>Contact Phone</label>
                                <input type="text" name="phone" id="add_owner_phone" class="owner-form-control" placeholder="e.g. 077 123 4567">
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 0.75rem;">
                                <div class="owner-form-group">
                                    <label>Password *</label>
                                    <input type="password" name="password" id="add_owner_password" class="owner-form-control" placeholder="Minimum 8 characters" required>
                                </div>
                                <div class="owner-form-group">
                                    <label>Confirm Password *</label>
                                    <input type="password" name="password_confirmation" id="add_owner_password_confirmation" class="owner-form-control" placeholder="Repeat password" required>
                                </div>
                            </div>

                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.5rem;">
                                <input type="checkbox" name="is_verified" id="add_is_verified" value="1" checked style="width: 16px; height: 16px; cursor: pointer;">
                                <label for="add_is_verified" style="margin: 0; font-size: 0.8125rem; font-weight: 600; cursor: pointer; color: #334155;">
                                    Pre-verify owner email (Allows immediate live login without email verification)
                                </label>
                            </div>
                        </div>

                        <!-- Section 2: Store Workspace Under Owner -->
                        <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 1rem; margin-bottom: 0.5rem;">
                            <div style="font-weight: 750; font-size: 0.875rem; color: #1e293b; display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                                <div style="display: flex; align-items: center; gap: 0.4rem;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2.2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                                    <span>Registered Store Workspace</span>
                                </div>
                                <button type="button" onclick="toggleExistingOption()" style="background: none; border: none; font-size: 0.75rem; color: #4f46e5; font-weight: 600; cursor: pointer; text-decoration: underline;">
                                    <span id="toggleText">Assign to existing shop instead?</span>
                                </button>
                            </div>

                            <!-- New Shop Inputs (Default) -->
                            <div id="newShopContainer" style="display: flex; flex-direction: column; gap: 0.75rem;">
                                <input type="hidden" name="shop_assignment_type" id="shop_assignment_type" value="new">

                                <div class="owner-form-group">
                                    <label>Shop / Business Name *</label>
                                    <input type="text" name="shop_name" id="add_new_shop_name" class="owner-form-control" placeholder="e.g. Apex Cellular Care" required>
                                </div>

                                <div class="owner-form-group">
                                    <label>Business Address</label>
                                    <input type="text" name="business_address" id="add_business_address" class="owner-form-control" placeholder="e.g. 150 Main Street, Colombo 03">
                                </div>

                                <div class="owner-form-group">
                                    <label>Choose Subscription Plan *</label>
                                    <select name="plan_id" id="add_plan_id" class="owner-form-control" style="font-weight: 600;">
                                        @foreach($plans as $plan)
                                            <option value="{{ $plan->id }}">
                                                {{ $plan->name }} — LKR {{ number_format($plan->monthly_price, 0) }}/mo ({{ $plan->max_branches }} branch(es), {{ $plan->max_users }} staff)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Existing Shop Dropdown (Hidden by default) -->
                            <div id="existingShopContainer" class="owner-form-group" style="display: none; margin-top: 0.5rem;">
                                <label>Select Existing Store Workspace *</label>
                                <select name="shop_id" id="add_shop_id" class="owner-form-control">
                                    <option value="">-- Choose Existing Shop --</option>
                                    @foreach($shops as $sh)
                                        <option value="{{ $sh->id }}">{{ $sh->name }} (ID #{{ $sh->id }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 2: Subscription Payment & Verification Container -->
                    <div id="ownerStep2" style="display: none;">
                        <!-- Registration Summary Banner -->
                        <div style="background: #eef2ff; border: 1.5px solid #c7d2fe; border-radius: 10px; padding: 0.85rem 1rem; margin-bottom: 1.15rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                            <div>
                                <div style="font-size: 0.72rem; color: #6366f1; text-transform: uppercase; font-weight: 700;">Store &amp; Primary Owner</div>
                                <div style="font-size: 0.95rem; font-weight: 800; color: #1e1b4b;" id="summaryShopName">Apex Cellular Care</div>
                                <div style="font-size: 0.78rem; color: #4338ca;" id="summaryOwnerText">Kasun Jayawardena • kasun@apexcare.com</div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 0.72rem; color: #6366f1; text-transform: uppercase; font-weight: 700;">Selected Plan</div>
                                <div style="font-size: 0.95rem; font-weight: 800; color: #4338ca;" id="summaryPlanName">Starter Plan</div>
                            </div>
                        </div>

                        <!-- Section 3: Plan Payment & Verification -->
                        <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 1rem; margin-bottom: 0.5rem;">
                            <div style="font-weight: 750; font-size: 0.875rem; color: #1e293b; display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.75rem;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2.2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                                <span>Subscription Payment &amp; Verification</span>
                            </div>

                            <!-- Payment Method Toggle Options -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                                <!-- Option 1: Card Payment -->
                                <label id="optMethodCard" style="border: 2px solid #4f46e5; background: #eef2ff; border-radius: 8px; padding: 0.75rem; cursor: pointer; display: flex; align-items: flex-start; gap: 0.5rem; transition: all 0.15s ease;">
                                    <input type="radio" name="payment_method" value="card" checked onchange="switchOwnerPaymentMethod('card')" style="margin-top: 0.2rem; cursor: pointer;">
                                    <div>
                                        <div style="font-weight: 750; font-size: 0.85rem; color: #3730a3;">💳 Credit / Debit Card</div>
                                        <div style="font-size: 0.73rem; color: #6366f1;">Instant verification &amp; active store</div>
                                    </div>
                                </label>

                                <!-- Option 2: Bank Slip Upload -->
                                <label id="optMethodBank" style="border: 1.5px solid #cbd5e1; background: #ffffff; border-radius: 8px; padding: 0.75rem; cursor: pointer; display: flex; align-items: flex-start; gap: 0.5rem; transition: all 0.15s ease;">
                                    <input type="radio" name="payment_method" value="bank_transfer" onchange="switchOwnerPaymentMethod('bank_transfer')" style="margin-top: 0.2rem; cursor: pointer;">
                                    <div>
                                        <div style="font-weight: 750; font-size: 0.85rem; color: #1e293b;">🏦 Bank Transfer Slip</div>
                                        <div style="font-size: 0.73rem; color: #64748b;">Upload deposit slip for Bank Approvals</div>
                                    </div>
                                </label>
                            </div>

                            <!-- Card Payment Fields Container -->
                            <div id="ownerCardFields" style="display: flex; flex-direction: column; gap: 0.75rem;">
                                <div class="owner-form-group" style="margin-bottom: 0;">
                                    <label style="font-size: 0.8125rem;">Cardholder Name</label>
                                    <input type="text" name="cardholder_name" class="owner-form-control" placeholder="Name as printed on card">
                                </div>
                                <div class="owner-form-group" style="margin-bottom: 0;">
                                    <label style="font-size: 0.8125rem;">Card Number</label>
                                    <input type="text" name="card_number" class="owner-form-control" placeholder="4242 •••• •••• 4242" maxlength="19">
                                </div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                                    <div class="owner-form-group" style="margin-bottom: 0;">
                                        <label style="font-size: 0.8125rem;">Expiry Date</label>
                                        <input type="text" name="card_expiry" class="owner-form-control" placeholder="MM/YY" maxlength="5">
                                    </div>
                                    <div class="owner-form-group" style="margin-bottom: 0;">
                                        <label style="font-size: 0.8125rem;">CVV / CVC</label>
                                        <input type="password" name="card_cvv" class="owner-form-control" placeholder="123" maxlength="4">
                                    </div>
                                </div>
                            </div>

                            <!-- Bank Transfer Fields Container -->
                            <div id="ownerBankFields" style="display: none; flex-direction: column; gap: 0.75rem;">
                                <!-- Bank Account Instructions -->
                                <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.75rem 0.85rem; font-size: 0.78rem; color: #334155; line-height: 1.45;">
                                    <strong>Platform Bank Account:</strong><br>
                                    <strong>Bank:</strong> Bank of Ceylon (BOC) • <strong>Account No:</strong> 0089271638<br>
                                    <strong>Branch:</strong> Corporate Branch Colombo • <strong>Name:</strong> SaaS POS Lanka (Pvt) Ltd
                                </div>

                                <div class="owner-form-group" style="margin-bottom: 0;">
                                    <label style="font-size: 0.8125rem;">Bank Deposit / Transfer Reference No. *</label>
                                    <input type="text" name="reference_number" id="owner_bank_reference" class="owner-form-control" placeholder="e.g. BOC-TXN-98421">
                                </div>

                                <div class="owner-form-group" style="margin-bottom: 0;">
                                    <label style="font-size: 0.8125rem;">Upload Payment Receipt / Bank Slip</label>
                                    <input type="file" name="receipt" class="owner-form-control" accept="image/*,.pdf" style="padding: 0.35rem 0.5rem; background: #ffffff;">
                                    <span style="font-size: 0.72rem; color: #64748b; margin-top: 0.2rem; display: block;">Supports JPG, PNG, PDF up to 5MB. Will appear in <strong>Bank Approvals</strong> for verification.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Wizard Modal Footer Buttons -->
                <div class="owner-modal-footer">
                    <div id="footerStep1" style="display: flex; justify-content: flex-end; width: 100%; gap: 0.75rem;">
                        <button type="button" class="btn-action btn-outline" onclick="closeOwnerModal('addOwnerModal')">Cancel</button>
                        <button type="button" class="btn-action btn-primary" onclick="goToOwnerStep(2)" style="padding: 0.5rem 1.35rem; font-weight: 700; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);">
                            Next: Payment &amp; Activation &rarr;
                        </button>
                    </div>
                    <div id="footerStep2" style="display: none; justify-content: space-between; width: 100%; gap: 0.75rem;">
                        <button type="button" class="btn-action btn-outline" onclick="goToOwnerStep(1)">&larr; Back to Details</button>
                        <button type="submit" class="btn-action btn-primary" id="btnSubmitOwnerRegistration" style="padding: 0.5rem 1.35rem; font-weight: 700; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);">
                            Complete Registration &amp; Activate Store
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Edit Shop Owner & Store Details -->
    <div class="owner-modal-backdrop" id="editOwnerModal">
        <div class="owner-modal-box" style="max-width: 600px;">
            <div class="owner-modal-header">
                <h3>Edit Shop Owner &amp; Store</h3>
                <button type="button" class="owner-modal-close" onclick="closeOwnerModal('editOwnerModal')">&times;</button>
            </div>
            <form id="editOwnerForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="owner-modal-body" style="max-height: 75vh; overflow-y: auto;">
                    <!-- Owner info -->
                    <div style="font-weight: 750; font-size: 0.85rem; color: #1e293b; margin-bottom: 0.65rem; padding-bottom: 0.35rem; border-bottom: 1.5px solid #e2e8f0;">
                        Owner Profile &amp; Contact
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 0.85rem;">
                        <div class="owner-form-group">
                            <label>Owner Full Name *</label>
                            <input type="text" name="name" id="edit_owner_name" class="owner-form-control" required>
                        </div>
                        <div class="owner-form-group">
                            <label>Email Address *</label>
                            <input type="email" name="email" id="edit_owner_email" class="owner-form-control" required>
                        </div>
                    </div>

                    <div class="owner-form-group" style="margin-bottom: 0.85rem;">
                        <label>Phone Number</label>
                        <input type="text" name="phone" id="edit_owner_phone" class="owner-form-control">
                    </div>

                    <!-- Store info -->
                    <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 1rem; margin-bottom: 0.85rem;">
                        <div style="font-weight: 750; font-size: 0.85rem; color: #1e293b; margin-bottom: 0.65rem;">
                            Registered Store Workspace
                        </div>
                        <div class="owner-form-group" style="margin-bottom: 0.75rem;">
                            <label>Store / Shop Name</label>
                            <input type="text" name="shop_name" id="edit_owner_shop_name" class="owner-form-control" placeholder="Store Name">
                        </div>
                        <div class="owner-form-group" style="margin-bottom: 0.75rem;">
                            <label>Business Address</label>
                            <input type="text" name="business_address" id="edit_owner_business_address" class="owner-form-control" placeholder="Address">
                        </div>
                        <div class="owner-form-group" style="margin-bottom: 0;">
                            <label>Reassign to Different Store Workspace</label>
                            <select name="shop_id" id="edit_owner_shop_id" class="owner-form-control">
                                @foreach($shops as $sh)
                                    <option value="{{ $sh->id }}">{{ $sh->name }} (ID #{{ $sh->id }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Password Change -->
                    <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 1rem; margin-bottom: 0.85rem;">
                        <label style="font-weight: 700; color: #1e293b; display: block; margin-bottom: 0.25rem;">Change Password (Optional)</label>
                        <p style="font-size: 0.75rem; color: #64748b; margin-bottom: 0.75rem;">Leave password fields blank to retain current password.</p>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="owner-form-group">
                                <label style="font-size: 0.8125rem;">New Password</label>
                                <input type="password" name="password" class="owner-form-control" placeholder="Min 8 characters">
                            </div>
                            <div class="owner-form-group">
                                <label style="font-size: 0.8125rem;">Confirm New Password</label>
                                <input type="password" name="password_confirmation" class="owner-form-control" placeholder="Repeat password">
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="is_verified" id="edit_is_verified" value="1" style="width: 16px; height: 16px; cursor: pointer;">
                        <label for="edit_is_verified" style="margin: 0; font-size: 0.875rem; font-weight: 600; cursor: pointer; color: #1e293b;">
                            Email verified status
                        </label>
                    </div>
                </div>
                <div class="owner-modal-footer">
                    <button type="button" class="btn-action btn-outline" onclick="closeOwnerModal('editOwnerModal')">Cancel</button>
                    <button type="submit" class="btn-action btn-primary" style="padding: 0.5rem 1.25rem;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Styles and Scripts -->
    <style>
        .owner-modal-backdrop {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(4px);
            z-index: 1050;
            align-items: center;
            justify-content: center;
        }
        .owner-modal-backdrop.open {
            display: flex;
        }
        .owner-modal-box {
            background: #ffffff;
            border-radius: 12px;
            width: 100%;
            max-width: 580px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            animation: modalFadeIn 0.2s ease-out;
            margin: 1rem;
        }
        .owner-modal-header {
            padding: 1.15rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8fafc;
        }
        .owner-modal-header h3 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f172a;
        }
        .owner-modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #64748b;
            cursor: pointer;
            line-height: 1;
            padding: 0;
        }
        .owner-modal-close:hover {
            color: #0f172a;
        }
        .owner-modal-body {
            padding: 1.5rem;
        }
        .owner-modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #e2e8f0;
            background: #f8fafc;
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }
        .owner-form-group label {
            display: block;
            margin-bottom: 0.35rem;
            font-size: 0.8125rem;
            font-weight: 650;
            color: #334155;
        }
        .owner-form-control {
            width: 100%;
            padding: 0.5rem 0.85rem;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            font-size: 0.875rem;
            color: #1e293b;
            transition: all 0.15s ease;
        }
        .owner-form-control:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        }
        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.97); }
            to { opacity: 1; transform: scale(1); }
        }

        /* Owner Action Toolbar & Buttons */
        .owner-action-toolbar {
            display: inline-flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.35rem;
            white-space: nowrap;
        }

        .owner-btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            padding: 0.32rem 0.65rem;
            height: 30px;
            border-radius: 7px;
            font-size: 0.775rem;
            font-weight: 600;
            white-space: nowrap;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
            box-sizing: border-box;
            border: 1px solid transparent;
            line-height: 1;
        }

        .owner-btn-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            color: #ffffff;
            border-color: rgba(79, 70, 229, 0.3);
            box-shadow: 0 1px 2px rgba(79, 70, 229, 0.2);
        }
        .owner-btn-primary:hover {
            background: linear-gradient(135deg, #4338ca 0%, #4f46e5 100%);
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(79, 70, 229, 0.35);
        }

        .owner-btn-secondary {
            background: #ffffff;
            border-color: #cbd5e1;
            color: #334155;
        }
        .owner-btn-secondary:hover {
            background: #f8fafc;
            border-color: #94a3b8;
            color: #0f172a;
            transform: translateY(-1px);
        }

        .owner-btn-danger {
            width: 30px;
            padding: 0;
            background: #ffffff;
            border-color: #fecaca;
            color: #dc2626;
        }
        .owner-btn-danger:hover {
            background: #ef4444;
            border-color: #ef4444;
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(239, 68, 68, 0.3);
        }
    </style>

    <script>
        function openOwnerModal(id) {
            const el = document.getElementById(id);
            if (el) {
                if (id === 'addOwnerModal') {
                    goToOwnerStep(1);
                }
                el.classList.add('open');
            }
        }
        function closeOwnerModal(id) {
            const el = document.getElementById(id);
            if (el) {
                if (id === 'addOwnerModal') {
                    goToOwnerStep(1);
                }
                el.classList.remove('open');
            }
        }
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('owner-modal-backdrop')) {
                e.target.classList.remove('open');
            }
        });

        function goToOwnerStep(step) {
            const step1 = document.getElementById('ownerStep1');
            const step2 = document.getElementById('ownerStep2');
            const footer1 = document.getElementById('footerStep1');
            const footer2 = document.getElementById('footerStep2');
            const pill1 = document.getElementById('stepPill1');
            const pill2 = document.getElementById('stepPill2');
            const badge1 = document.getElementById('stepBadge1');
            const badge2 = document.getElementById('stepBadge2');
            const line = document.getElementById('stepLine');
            const modalBody = document.getElementById('ownerModalBody');

            if (step === 2) {
                // Validate Step 1 inputs
                const nameInput = document.getElementById('add_owner_name');
                const emailInput = document.getElementById('add_owner_email');
                const passInput = document.getElementById('add_owner_password');
                const passConfirm = document.getElementById('add_owner_password_confirmation');
                const shopNameInput = document.getElementById('add_new_shop_name');
                const existingShopSelect = document.getElementById('add_shop_id');
                const assignmentType = document.getElementById('shop_assignment_type').value;

                if (nameInput && !nameInput.value.trim()) {
                    nameInput.reportValidity();
                    return;
                }
                if (emailInput && !emailInput.reportValidity()) {
                    return;
                }
                if (passInput && !passInput.value) {
                    passInput.reportValidity();
                    return;
                }
                if (passInput && passInput.value.length < 8) {
                    alert('Password must be at least 8 characters long.');
                    passInput.focus();
                    return;
                }
                if (passConfirm && passInput && passConfirm.value !== passInput.value) {
                    alert('Password confirmation does not match password.');
                    passConfirm.focus();
                    return;
                }

                if (assignmentType === 'new') {
                    if (shopNameInput && !shopNameInput.value.trim()) {
                        shopNameInput.reportValidity();
                        return;
                    }
                } else {
                    if (existingShopSelect && !existingShopSelect.value) {
                        existingShopSelect.reportValidity();
                        return;
                    }
                }

                // Populate Step 2 Summary Banner
                const storeName = assignmentType === 'new'
                    ? (shopNameInput ? shopNameInput.value.trim() : 'Store Workspace')
                    : (existingShopSelect && existingShopSelect.selectedOptions[0] ? existingShopSelect.selectedOptions[0].text : 'Existing Store');

                const ownerName = nameInput ? nameInput.value.trim() : '';
                const ownerEmail = emailInput ? emailInput.value.trim() : '';
                const planSelect = document.getElementById('add_plan_id');
                const planText = planSelect && planSelect.selectedOptions[0] ? planSelect.selectedOptions[0].text : 'Standard Plan';

                const summaryShop = document.getElementById('summaryShopName');
                const summaryOwner = document.getElementById('summaryOwnerText');
                const summaryPlan = document.getElementById('summaryPlanName');

                if (summaryShop) summaryShop.textContent = storeName;
                if (summaryOwner) summaryOwner.textContent = `${ownerName} • ${ownerEmail}`;
                if (summaryPlan) summaryPlan.textContent = planText.split('—')[0] ? planText.split('—')[0].trim() : planText;

                // Toggle visibility
                if (step1) step1.style.display = 'none';
                if (step2) step2.style.display = 'block';
                if (footer1) footer1.style.display = 'none';
                if (footer2) footer2.style.display = 'flex';

                // Update Stepper pills
                if (pill1) {
                    pill1.style.color = '#059669';
                    if (badge1) {
                        badge1.style.background = '#10b981';
                        badge1.innerHTML = '&#10003;';
                    }
                }
                if (pill2) {
                    pill2.style.color = '#4338ca';
                    pill2.style.fontWeight = '700';
                    if (badge2) {
                        badge2.style.background = '#4f46e5';
                        badge2.style.color = '#ffffff';
                    }
                }
                if (line) line.style.background = '#10b981';
                if (modalBody) modalBody.scrollTop = 0;
            } else {
                // Return to Step 1
                if (step1) step1.style.display = 'block';
                if (step2) step2.style.display = 'none';
                if (footer1) footer1.style.display = 'flex';
                if (footer2) footer2.style.display = 'none';

                // Reset Stepper pills
                if (pill1) {
                    pill1.style.color = '#4338ca';
                    if (badge1) {
                        badge1.style.background = '#4f46e5';
                        badge1.textContent = '1';
                    }
                }
                if (pill2) {
                    pill2.style.color = '#94a3b8';
                    pill2.style.fontWeight = '600';
                    if (badge2) {
                        badge2.style.background = '#e2e8f0';
                        badge2.style.color = '#64748b';
                    }
                }
                if (line) line.style.background = '#e2e8f0';
                if (modalBody) modalBody.scrollTop = 0;
            }
        }

        let isExistingMode = false;
        function toggleExistingOption() {
            isExistingMode = !isExistingMode;
            const newWrap = document.getElementById('newShopContainer');
            const existingWrap = document.getElementById('existingShopContainer');
            const toggleText = document.getElementById('toggleText');
            const typeInput = document.getElementById('shop_assignment_type');
            const newShopInput = document.getElementById('add_new_shop_name');
            const existingSelect = document.getElementById('add_shop_id');

            if (isExistingMode) {
                newWrap.style.display = 'none';
                existingWrap.style.display = 'block';
                toggleText.textContent = '← Register brand new shop instead';
                typeInput.value = 'existing';
                if (newShopInput) newShopInput.removeAttribute('required');
                if (existingSelect) existingSelect.setAttribute('required', 'required');
            } else {
                newWrap.style.display = 'flex';
                existingWrap.style.display = 'none';
                toggleText.textContent = 'Assign to existing shop instead?';
                typeInput.value = 'new';
                if (newShopInput) newShopInput.setAttribute('required', 'required');
                if (existingSelect) existingSelect.removeAttribute('required');
            }
        }

        function toggleShopAssignment(type) {
            const existingWrap = document.getElementById('existingShopContainer');
            const newWrap = document.getElementById('newShopContainer');
            const shopSelect = document.getElementById('add_shop_id');
            const newShopInput = document.getElementById('add_new_shop_name');
            const typeInput = document.getElementById('shop_assignment_type');

            if (type === 'new') {
                if (existingWrap) existingWrap.style.display = 'none';
                if (newWrap) newWrap.style.display = 'flex';
                if (typeInput) typeInput.value = 'new';
                if (shopSelect) shopSelect.removeAttribute('required');
                if (newShopInput) newShopInput.setAttribute('required', 'required');
            } else {
                if (existingWrap) existingWrap.style.display = 'block';
                if (newWrap) newWrap.style.display = 'none';
                if (typeInput) typeInput.value = 'existing';
                if (shopSelect) shopSelect.setAttribute('required', 'required');
                if (newShopInput) newShopInput.removeAttribute('required');
            }
        }

        function openEditOwnerModal(owner, shop) {
            const form = document.getElementById('editOwnerForm');
            if (form) form.action = `/super-admin/owners/${owner.id}`;

            const nameEl = document.getElementById('edit_owner_name');
            const emailEl = document.getElementById('edit_owner_email');
            const phoneEl = document.getElementById('edit_owner_phone');
            const shopNameEl = document.getElementById('edit_owner_shop_name');
            const shopAddressEl = document.getElementById('edit_owner_business_address');
            const shopEl = document.getElementById('edit_owner_shop_id');
            const verEl = document.getElementById('edit_is_verified');

            if (nameEl) nameEl.value = owner.name || '';
            if (emailEl) emailEl.value = owner.email || '';
            if (phoneEl) phoneEl.value = owner.phone || '';
            if (shopEl) shopEl.value = owner.shop_id || '';
            if (shopNameEl) shopNameEl.value = (shop && shop.name) ? shop.name : (owner.shop ? owner.shop.name : '');
            if (shopAddressEl) shopAddressEl.value = (shop && shop.address) ? shop.address : (owner.shop ? owner.shop.address : '');
            if (verEl) verEl.checked = !!owner.email_verified_at;

            openOwnerModal('editOwnerModal');
        }

        function switchOwnerPaymentMethod(method) {
            const cardFields = document.getElementById('ownerCardFields');
            const bankFields = document.getElementById('ownerBankFields');
            const optCard = document.getElementById('optMethodCard');
            const optBank = document.getElementById('optMethodBank');
            const bankRef = document.getElementById('owner_bank_reference');
            const submitBtn = document.getElementById('btnSubmitOwnerRegistration');

            if (method === 'bank_transfer') {
                if (cardFields) cardFields.style.display = 'none';
                if (bankFields) bankFields.style.display = 'flex';
                if (optBank) {
                    optBank.style.border = '2px solid #4f46e5';
                    optBank.style.background = '#eef2ff';
                }
                if (optCard) {
                    optCard.style.border = '1.5px solid #cbd5e1';
                    optCard.style.background = '#ffffff';
                }
                if (bankRef) bankRef.setAttribute('required', 'required');
                if (submitBtn) submitBtn.textContent = 'Register Store & Submit Slip for Approval';
            } else {
                if (cardFields) cardFields.style.display = 'flex';
                if (bankFields) bankFields.style.display = 'none';
                if (optCard) {
                    optCard.style.border = '2px solid #4f46e5';
                    optCard.style.background = '#eef2ff';
                }
                if (optBank) {
                    optBank.style.border = '1.5px solid #cbd5e1';
                    optBank.style.background = '#ffffff';
                }
                if (bankRef) bankRef.removeAttribute('required');
                if (submitBtn) submitBtn.textContent = 'Complete Registration & Activate Store';
            }
        }

        window.openOwnerModal = openOwnerModal;
        window.closeOwnerModal = closeOwnerModal;
        window.goToOwnerStep = goToOwnerStep;
        window.toggleExistingOption = toggleExistingOption;
        window.toggleShopAssignment = toggleShopAssignment;
        window.switchOwnerPaymentMethod = switchOwnerPaymentMethod;
        window.openEditOwnerModal = openEditOwnerModal;
    </script>
@endsection
