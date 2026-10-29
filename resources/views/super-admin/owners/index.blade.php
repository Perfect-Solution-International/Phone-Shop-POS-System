@extends('layouts.super-admin')

@section('title', 'Shop Owners Management')
@section('page_title', 'Shop Owners & Account Management')

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
                    <span>Shop Owners Directory ({{ $owners->total() }})</span>
                </h2>
                <p style="margin: 0.2rem 0 0; font-size: 0.8125rem; color: #64748b;">
                    Manage tenant shop accounts, credentials, contact details, and workspace assignments.
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

                <!-- Add Owner Button -->
                <button type="button" class="btn-action btn-primary" onclick="openOwnerModal('addOwnerModal')" style="display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 700; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none; padding: 0.45rem 1rem;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span>Add New Owner</span>
                </button>
            </div>
        </div>

        <table class="saas-table">
            <thead>
                <tr>
                    <th>Owner Details</th>
                    <th>Contact Phone</th>
                    <th>Assigned Shop</th>
                    <th>Subscription</th>
                    <th>Email Status</th>
                    <th>Registered</th>
                    <th style="text-align: right; min-width: 180px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($owners as $owner)
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 36px; height: 36px; border-radius: 50%; background: #e0e7ff; color: #4338ca; font-weight: 750; font-size: 0.875rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
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
                                    <strong style="font-size: 0.875rem; color: #1e293b; display: block;">{{ $owner->shop->name }}</strong>
                                    <span style="font-size: 0.72rem; color: #64748b;">ID #{{ $owner->shop->id }}</span>
                                </div>
                            @else
                                <span style="color: #ef4444; font-size: 0.78rem; font-weight: 600;">No Store Assigned</span>
                            @endif
                        </td>
                        <td>
                            @if ($owner->shop?->subscription)
                                <span class="status-pill status-{{ $owner->shop->subscription->status ?? 'trial' }}">
                                    {{ $owner->shop->subscription->plan->name ?? 'Standard' }} ({{ ucfirst($owner->shop->subscription->status) }})
                                </span>
                            @else
                                <span style="color: #94a3b8; font-size: 0.75rem;">None</span>
                            @endif
                        </td>
                        <td>
                            @if ($owner->email_verified_at)
                                <span style="display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.75rem; font-weight: 700; color: #059669; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 0.2rem 0.55rem; border-radius: 9999px;">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    Verified
                                </span>
                            @else
                                <span style="display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.75rem; font-weight: 700; color: #d97706; background: #fffbeb; border: 1px solid #fde68a; padding: 0.2rem 0.55rem; border-radius: 9999px;">
                                    Unverified
                                </span>
                            @endif
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
                                        <button type="submit" class="owner-btn-action owner-btn-primary" title="Access {{ $owner->shop->name ?? 'Shop Workspace' }}">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
                                            <span>Access</span>
                                        </button>
                                    </form>
                                @endif

                                <!-- Edit Button -->
                                <button
                                    type="button"
                                    class="owner-btn-action owner-btn-secondary"
                                    onclick='openEditOwnerModal(@json($owner))'
                                    title="Edit owner credentials and details"
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

    <!-- Modal: Add Shop Owner -->
    <div class="owner-modal-backdrop" id="addOwnerModal">
        <div class="owner-modal-box">
            <div class="owner-modal-header">
                <h3>Add New Shop Owner</h3>
                <button type="button" class="owner-modal-close" onclick="closeOwnerModal('addOwnerModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('superadmin.owners.store') }}">
                @csrf
                <div class="owner-modal-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div class="owner-form-group">
                            <label>Owner Full Name *</label>
                            <input type="text" name="name" class="owner-form-control" placeholder="e.g. Kasun Jayawardena" required>
                        </div>
                        <div class="owner-form-group">
                            <label>Email Address *</label>
                            <input type="email" name="email" class="owner-form-control" placeholder="e.g. kasun@phoneshop.com" required>
                        </div>
                    </div>

                    <div class="owner-form-group" style="margin-bottom: 1rem;">
                        <label>Phone Number</label>
                        <input type="text" name="phone" class="owner-form-control" placeholder="e.g. 077 123 4567">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div class="owner-form-group">
                            <label>Password *</label>
                            <input type="password" name="password" class="owner-form-control" placeholder="Minimum 8 characters" required>
                        </div>
                        <div class="owner-form-group">
                            <label>Confirm Password *</label>
                            <input type="password" name="password_confirmation" class="owner-form-control" placeholder="Repeat password" required>
                        </div>
                    </div>

                    <!-- Shop Assignment Selector -->
                    <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 1rem; margin-bottom: 1rem;">
                        <label style="font-weight: 700; color: #1e293b; display: block; margin-bottom: 0.5rem;">Store Workspace Assignment</label>
                        
                        <div style="display: flex; gap: 1.5rem; margin-bottom: 0.75rem;">
                            <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer; font-size: 0.875rem; font-weight: 600;">
                                <input type="radio" name="shop_assignment_type" value="existing" checked onchange="toggleShopAssignment(this.value)">
                                <span>Assign to Existing Shop</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer; font-size: 0.875rem; font-weight: 600;">
                                <input type="radio" name="shop_assignment_type" value="new" onchange="toggleShopAssignment(this.value)">
                                <span>Create New Shop for this Owner</span>
                            </label>
                        </div>

                        <!-- Existing Shop Dropdown -->
                        <div id="existingShopContainer" class="owner-form-group">
                            <select name="shop_id" id="add_shop_id" class="owner-form-control" required>
                                <option value="">-- Select an Existing Shop --</option>
                                @foreach($shops as $sh)
                                    <option value="{{ $sh->id }}">{{ $sh->name }} (ID #{{ $sh->id }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- New Shop Inputs -->
                        <div id="newShopContainer" style="display: none; flex-direction: column; gap: 0.75rem;">
                            <div class="owner-form-group">
                                <label style="font-size: 0.8125rem;">New Shop / Business Name *</label>
                                <input type="text" name="new_shop_name" id="add_new_shop_name" class="owner-form-control" placeholder="e.g. Apex Cellular Care">
                            </div>
                            <div class="owner-form-group">
                                <label style="font-size: 0.8125rem;">Business Address (Optional)</label>
                                <input type="text" name="business_address" class="owner-form-control" placeholder="e.g. 150 Main Street, Kandy">
                            </div>
                        </div>
                    </div>

                    <!-- Verified Checkbox -->
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="is_verified" id="add_is_verified" value="1" checked style="width: 16px; height: 16px; cursor: pointer;">
                        <label for="add_is_verified" style="margin: 0; font-size: 0.875rem; font-weight: 600; cursor: pointer; color: #1e293b;">
                            Mark email as pre-verified (allows instant login)
                        </label>
                    </div>
                </div>
                <div class="owner-modal-footer">
                    <button type="button" class="btn-action btn-outline" onclick="closeOwnerModal('addOwnerModal')">Cancel</button>
                    <button type="submit" class="btn-action btn-primary" style="padding: 0.5rem 1.25rem;">Create Shop Owner</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Edit Shop Owner -->
    <div class="owner-modal-backdrop" id="editOwnerModal">
        <div class="owner-modal-box">
            <div class="owner-modal-header">
                <h3>Edit Shop Owner Account</h3>
                <button type="button" class="owner-modal-close" onclick="closeOwnerModal('editOwnerModal')">&times;</button>
            </div>
            <form id="editOwnerForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="owner-modal-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div class="owner-form-group">
                            <label>Owner Full Name *</label>
                            <input type="text" name="name" id="edit_owner_name" class="owner-form-control" required>
                        </div>
                        <div class="owner-form-group">
                            <label>Email Address *</label>
                            <input type="email" name="email" id="edit_owner_email" class="owner-form-control" required>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div class="owner-form-group">
                            <label>Phone Number</label>
                            <input type="text" name="phone" id="edit_owner_phone" class="owner-form-control">
                        </div>
                        <div class="owner-form-group">
                            <label>Assigned Store Workspace *</label>
                            <select name="shop_id" id="edit_owner_shop_id" class="owner-form-control" required>
                                @foreach($shops as $sh)
                                    <option value="{{ $sh->id }}">{{ $sh->name }} (ID #{{ $sh->id }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 1rem; margin-bottom: 1rem;">
                        <label style="font-weight: 700; color: #1e293b; display: block; margin-bottom: 0.25rem;">Change Password (Optional)</label>
                        <p style="font-size: 0.75rem; color: #64748b; margin-bottom: 0.75rem;">Leave password fields blank to retain the current password.</p>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="owner-form-group">
                                <label style="font-size: 0.8125rem;">New Password</label>
                                <input type="password" name="password" class="owner-form-control" placeholder="Minimum 8 characters">
                            </div>
                            <div class="owner-form-group">
                                <label style="font-size: 0.8125rem;">Confirm New Password</label>
                                <input type="password" name="password_confirmation" class="owner-form-control" placeholder="Repeat new password">
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
            padding: 1rem;
        }
        .owner-modal-backdrop.open {
            display: flex;
        }
        .owner-modal-box {
            background: #ffffff;
            border-radius: 14px;
            width: 100%;
            max-width: 620px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            animation: modalFadeIn 0.2s ease-out;
        }
        .owner-modal-header {
            padding: 1.25rem 1.5rem;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .owner-modal-header h3 {
            margin: 0;
            font-size: 1.125rem;
            font-weight: 750;
            color: #0f172a;
        }
        .owner-modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #64748b;
            cursor: pointer;
            line-height: 1;
        }
        .owner-modal-body {
            padding: 1.5rem;
            max-height: 75vh;
            overflow-y: auto;
        }
        .owner-modal-footer {
            padding: 1.25rem 1.5rem;
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

        /* Owner Action Toolbar & Professional Buttons */
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
                el.classList.add('open');
            }
        }
        function closeOwnerModal(id) {
            const el = document.getElementById(id);
            if (el) {
                el.classList.remove('open');
            }
        }
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('owner-modal-backdrop')) {
                e.target.classList.remove('open');
            }
        });

        function toggleShopAssignment(type) {
            const existingWrap = document.getElementById('existingShopContainer');
            const newWrap = document.getElementById('newShopContainer');
            const shopSelect = document.getElementById('add_shop_id');
            const newShopInput = document.getElementById('add_new_shop_name');

            if (type === 'new') {
                if (existingWrap) existingWrap.style.display = 'none';
                if (newWrap) newWrap.style.display = 'flex';
                if (shopSelect) shopSelect.removeAttribute('required');
                if (newShopInput) newShopInput.setAttribute('required', 'required');
            } else {
                if (existingWrap) existingWrap.style.display = 'block';
                if (newWrap) newWrap.style.display = 'none';
                if (shopSelect) shopSelect.setAttribute('required', 'required');
                if (newShopInput) newShopInput.removeAttribute('required');
            }
        }

        function openEditOwnerModal(owner) {
            const form = document.getElementById('editOwnerForm');
            if (form) form.action = `/super-admin/owners/${owner.id}`;

            const nameEl = document.getElementById('edit_owner_name');
            const emailEl = document.getElementById('edit_owner_email');
            const phoneEl = document.getElementById('edit_owner_phone');
            const shopEl = document.getElementById('edit_owner_shop_id');
            const verEl = document.getElementById('edit_is_verified');

            if (nameEl) nameEl.value = owner.name || '';
            if (emailEl) emailEl.value = owner.email || '';
            if (phoneEl) phoneEl.value = owner.phone || '';
            if (shopEl) shopEl.value = owner.shop_id || '';
            if (verEl) verEl.checked = !!owner.email_verified_at;

            openOwnerModal('editOwnerModal');
        }

        window.openOwnerModal = openOwnerModal;
        window.closeOwnerModal = closeOwnerModal;
        window.toggleShopAssignment = toggleShopAssignment;
        window.openEditOwnerModal = openEditOwnerModal;
    </script>
@endsection

