@extends('layouts.super-admin')

@section('title', 'Registered Shops')
@section('page_title', 'Registered Shop Workspaces')

@section('content')
    <div class="data-card">
        <div class="data-card-header" style="flex-wrap: wrap; gap: 1rem;">
            <h2 class="data-card-title">All Shops ({{ $shops->total() }})</h2>

            <!-- Filter & Search Form -->
            <form method="GET" action="{{ route('superadmin.shops.index') }}" style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                <input
                    type="text"
                    name="search"
                    placeholder="Search shop or owner..."
                    value="{{ request('search') }}"
                    style="padding: 0.45rem 0.85rem; border: 1.5px solid var(--surface-border); border-radius: 8px; font-size: 0.875rem;"
                >
                <select name="status" style="padding: 0.45rem 0.85rem; border: 1.5px solid var(--surface-border); border-radius: 8px; font-size: 0.875rem;">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Paid</option>
                    <option value="trial" {{ request('status') === 'trial' ? 'selected' : '' }}>Free Trial</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
                <button type="submit" class="btn-action btn-primary">Filter</button>
                @if (request()->hasAny(['search', 'status']))
                    <a href="{{ route('superadmin.shops.index') }}" class="btn-action btn-outline">Clear</a>
                @endif
            </form>
        </div>

        <table class="saas-table">
            <thead>
                <tr>
                    <th>Shop / Business</th>
                    <th>Primary Owner</th>
                    <th>Plan</th>
                    <th>Subscription Status</th>
                    <th>Current Period Ends</th>
                    <th style="min-width: 290px; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($shops as $shop)
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
                    <tr>
                        <td>
                            <strong style="font-size: 0.9375rem; color: var(--text-heading);">{{ $shop->name }}</strong>
                            <div style="font-size: 0.75rem; color: #64748b;">ID #{{ $shop->id }} • Joined {{ $shop->created_at->format('M d, Y') }}</div>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: #1e293b;">{{ $primaryUser->name ?? 'N/A' }}</div>
                            <div style="font-size: 0.75rem; color: #64748b;">{{ $primaryUser->email ?? '' }}</div>
                        </td>
                        <td>
                            <span style="font-weight: 600; color: #334155;">{{ $shop->subscription->plan->name ?? 'Standard Trial' }}</span>
                        </td>
                        <td>
                            <span class="status-pill status-{{ $shop->subscription->status ?? 'trial' }}">
                                {{ ucfirst($shop->subscription->status ?? 'trial') }}
                            </span>
                        </td>
                        <td>
                            @if ($shop->subscription?->current_period_ends_at)
                                <div style="font-weight: 550; color: #334155;">{{ $shop->subscription->current_period_ends_at->format('Y-m-d') }}</div>
                                <div style="font-size: 0.75rem; color: #64748b;">{{ $shop->subscription->current_period_ends_at->diffForHumans() }}</div>
                            @else
                                <span style="color: #94a3b8;">N/A</span>
                            @endif
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <div class="shop-action-toolbar">
                                <!-- Quick Action: Access -->
                                <form method="POST" action="{{ route('superadmin.shops.switch', $shop->id) }}" style="display: inline; margin: 0;">
                                    @csrf
                                    <button type="submit" class="shop-btn-action shop-btn-primary" title="Access shop workspace as Super Admin">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
                                        <span>Access</span>
                                    </button>
                                </form>

                                <!-- Quick Action: Details -->
                                <a href="{{ route('superadmin.shops.show', $shop->id) }}" class="shop-btn-action shop-btn-secondary" title="View shop workspace details">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    <span>Details</span>
                                </a>

                                <!-- Quick Action: Edit -->
                                <button type="button" class="shop-btn-action shop-btn-secondary" onclick='openEditShopModal(@json($shopData))' title="Edit shop profile and settings">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    <span>Edit</span>
                                </button>

                                <!-- More Options Dropdown -->
                                <div class="shop-action-dropdown">
                                    <button type="button" class="shop-btn-action shop-btn-more" onclick="toggleShopActionMenu(event, 'shop-menu-{{ $shop->id }}')" title="More workspace options">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="1.5"></circle><circle cx="12" cy="5" r="1.5"></circle><circle cx="12" cy="19" r="1.5"></circle></svg>
                                    </button>
                                    <div id="shop-menu-{{ $shop->id }}" class="shop-dropdown-menu">
                                        <div class="shop-dropdown-header">Workspace Controls</div>

                                        <form method="POST" action="{{ route('superadmin.shops.toggle-suspension', $shop->id) }}" onsubmit="return confirm('Change access state for shop \'{{ addslashes($shop->name) }}\'?');" style="margin: 0;">
                                            @csrf
                                            @if (($shop->subscription->status ?? '') === 'suspended')
                                                <button type="submit" class="shop-dropdown-item item-activate">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                                                    <span>Activate Workspace</span>
                                                </button>
                                            @else
                                                <button type="submit" class="shop-dropdown-item item-suspend">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect></svg>
                                                    <span>Suspend Workspace</span>
                                                </button>
                                            @endif
                                        </form>

                                        <div class="shop-dropdown-divider"></div>

                                        <form method="POST" action="{{ route('superadmin.shops.destroy', $shop->id) }}" onsubmit="return confirm('⚠️ WARNING: Are you sure you want to PERMANENTLY DELETE shop \'{{ addslashes($shop->name) }}\'? All related data, inventory, sales, and accounts will be completely erased. This action CANNOT be undone!');" style="margin: 0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="shop-dropdown-item item-delete">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                                <span>Delete Workspace</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #94a3b8; padding: 2.5rem;">No shops match the search criteria.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="padding: 1.25rem 1.5rem; border-top: 1px solid var(--surface-border);">
            {{ $shops->links() }}
        </div>
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

        /* Action Toolbar & Sleek Buttons */
        .shop-action-toolbar {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            white-space: nowrap;
        }

        .shop-btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            padding: 0.32rem 0.65rem;
            height: 30px;
            border-radius: 7px;
            font-size: 0.775rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
            box-sizing: border-box;
            border: 1px solid transparent;
            line-height: 1;
        }

        .shop-btn-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            color: #ffffff;
            border-color: rgba(79, 70, 229, 0.3);
            box-shadow: 0 1px 2px rgba(79, 70, 229, 0.2);
        }
        .shop-btn-primary:hover {
            background: linear-gradient(135deg, #4338ca 0%, #4f46e5 100%);
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(79, 70, 229, 0.35);
        }

        .shop-btn-secondary {
            background: #ffffff;
            border-color: #cbd5e1;
            color: #334155;
        }
        .shop-btn-secondary:hover {
            background: #f8fafc;
            border-color: #94a3b8;
            color: #0f172a;
            transform: translateY(-1px);
        }

        .shop-btn-more {
            width: 30px;
            padding: 0;
            background: #ffffff;
            border-color: #cbd5e1;
            color: #64748b;
        }
        .shop-btn-more:hover, .shop-btn-more.active {
            background: #f1f5f9;
            border-color: #94a3b8;
            color: #0f172a;
        }

        /* Action Dropdown Menu */
        .shop-action-dropdown {
            position: relative;
            display: inline-block;
        }

        .shop-dropdown-menu {
            position: absolute;
            right: 0;
            top: calc(100% + 5px);
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.12), 0 8px 10px -6px rgba(0, 0, 0, 0.08);
            min-width: 195px;
            padding: 0.4rem;
            z-index: 1050;
            display: none;
            text-align: left;
            animation: shopMenuFadeIn 0.15s ease-out;
        }

        .shop-dropdown-menu.show {
            display: block;
        }

        .shop-dropdown-header {
            font-size: 0.6875rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-weight: 750;
            color: #94a3b8;
            padding: 0.35rem 0.65rem 0.25rem;
        }

        .shop-dropdown-divider {
            height: 1px;
            background: #f1f5f9;
            margin: 0.35rem 0;
        }

        .shop-dropdown-item {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            font-size: 0.8125rem;
            font-weight: 600;
            background: transparent;
            border: none;
            cursor: pointer;
            text-align: left;
            transition: all 0.12s ease;
        }

        .shop-dropdown-item.item-activate {
            color: #059669;
        }
        .shop-dropdown-item.item-activate:hover {
            background: #ecfdf5;
        }

        .shop-dropdown-item.item-suspend {
            color: #d97706;
        }
        .shop-dropdown-item.item-suspend:hover {
            background: #fffbeb;
        }

        .shop-dropdown-item.item-delete {
            color: #dc2626;
        }
        .shop-dropdown-item.item-delete:hover {
            background: #fef2f2;
        }

        @keyframes shopMenuFadeIn {
            from { opacity: 0; transform: translateY(-4px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

    <script>
        function toggleShopActionMenu(event, menuId) {
            event.stopPropagation();
            const targetMenu = document.getElementById(menuId);
            if (!targetMenu) return;

            const isShown = targetMenu.classList.contains('show');
            
            // Close any currently open menus and remove active states
            document.querySelectorAll('.shop-dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
            });
            document.querySelectorAll('.shop-btn-more.active').forEach(btn => {
                btn.classList.remove('active');
            });

            if (!isShown) {
                targetMenu.classList.add('show');
                const triggerBtn = event.currentTarget;
                if (triggerBtn) triggerBtn.classList.add('active');

                // Smart flip upwards if menu extends past screen bottom
                const rect = targetMenu.getBoundingClientRect();
                if (rect.bottom > window.innerHeight - 20) {
                    targetMenu.style.top = 'auto';
                    targetMenu.style.bottom = 'calc(100% + 5px)';
                } else {
                    targetMenu.style.top = 'calc(100% + 5px)';
                    targetMenu.style.bottom = 'auto';
                }
            }
        }

        // Close dropdowns on click outside
        window.addEventListener('click', function(e) {
            if (!e.target.closest('.shop-action-dropdown')) {
                document.querySelectorAll('.shop-dropdown-menu.show').forEach(menu => {
                    menu.classList.remove('show');
                });
                document.querySelectorAll('.shop-btn-more.active').forEach(btn => {
                    btn.classList.remove('active');
                });
            }
        });
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
