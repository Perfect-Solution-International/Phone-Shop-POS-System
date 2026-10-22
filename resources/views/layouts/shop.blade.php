<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Shop Console') — {{ auth()->user()->shop->name ?? 'Phone Shop POS' }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <style>
        :root {
            --brand-navy-950: #060913;
            --brand-navy-900: #0b1120;
            --brand-navy-800: #1e293b;
            --brand-indigo-600: #4f46e5;
            --brand-indigo-700: #4338ca;
            --brand-cyan-500: #06b6d4;
            --surface-card: #ffffff;
            --surface-bg: #f8fafc;
            --surface-border: #e2e8f0;
            --text-heading: #0f172a;
            --text-body: #334155;
            --text-muted: #64748b;
            --color-success: #10b981;
            --color-warning: #f59e0b;
            --color-danger: #ef4444;
            --color-info: #3b82f6;
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: var(--surface-bg);
            color: var(--text-body);
            min-height: 100vh;
            display: flex;
        }

        /* Sidebar */
        .shop-sidebar {
            width: 260px;
            background-color: var(--brand-navy-950);
            color: #ffffff;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            position: sticky;
            top: 0;
            height: 100vh;
            overflow: hidden;
        }

        .shop-brand {
            padding: 1.25rem 1.15rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .shop-brand-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--brand-cyan-500) 0%, var(--brand-indigo-600) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(6, 182, 212, 0.35);
        }

        .shop-brand-text {
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .shop-brand-title {
            font-size: 0.9375rem;
            font-weight: 800;
            color: #ffffff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .shop-brand-subtitle {
            font-size: 0.6875rem;
            color: #38bdf8;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-weight: 700;
        }

        .shop-nav {
            padding: 1rem 0.75rem;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            flex: 1;
            overflow-y: auto;
        }

        .shop-nav-header {
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
            padding: 0.75rem 0.5rem 0.35rem;
        }

        .shop-nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.625rem 0.75rem;
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.15s ease;
        }

        .shop-nav-item:hover {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.06);
        }

        .shop-nav-item.active {
            color: #ffffff;
            background: linear-gradient(90deg, rgba(79, 70, 229, 0.35) 0%, rgba(6, 182, 212, 0.2) 100%);
            border-left: 3px solid var(--brand-cyan-500);
            font-weight: 600;
        }

        .shop-nav-badge {
            margin-left: auto;
            padding: 0.15rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.7rem;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.1);
            color: #e2e8f0;
        }

        .shop-user-footer {
            flex-shrink: 0;
            padding: 0.85rem 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(0, 0, 0, 0.32);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.65rem;
        }

        .shop-user-meta {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            overflow: hidden;
            min-width: 0;
        }

        .shop-user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--brand-cyan-500) 0%, var(--brand-indigo-600) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 0.8125rem;
            font-weight: 750;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
        }

        .shop-user-details {
            display: flex;
            flex-direction: column;
            overflow: hidden;
            min-width: 0;
        }

        .shop-user-name {
            font-size: 0.8125rem;
            font-weight: 700;
            color: #f1f5f9;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.25;
        }

        .shop-user-role {
            font-size: 0.6875rem;
            color: #38bdf8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
            margin-top: 1px;
        }

        .shop-logout-btn {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.28);
            color: #fca5a5;
            padding: 0.38rem 0.65rem;
            border-radius: 7px;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            transition: all 0.18s ease;
            text-decoration: none;
            flex-shrink: 0;
        }

        .shop-logout-btn:hover {
            background: #ef4444;
            color: #ffffff;
            border-color: #ef4444;
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(239, 68, 68, 0.35);
        }

        /* Main Workspace Container */
        .shop-workspace {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            overflow-x: hidden;
        }

        .shop-topbar {
            height: 64px;
            background-color: #ffffff;
            border-bottom: 1px solid var(--surface-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 20;
        }

        .shop-topbar-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text-heading);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .shop-plan-pill {
            font-size: 0.6875rem;
            padding: 0.2rem 0.6rem;
            border-radius: 9999px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: #e0f2fe;
            color: #0369a1;
        }

        .shop-content {
            padding: 2rem;
            flex: 1;
        }

        /* Generic Utilities */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.55rem 1.1rem;
            font-size: 0.875rem;
            font-weight: 600;
            border-radius: 8px;
            border: 1px solid transparent;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--brand-indigo-600) 0%, var(--brand-indigo-700) 100%);
            color: #ffffff;
        }
        .btn-primary:hover {
            opacity: 0.95;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.35);
        }

        .btn-secondary {
            background-color: #ffffff;
            color: var(--text-heading);
            border-color: var(--surface-border);
        }
        .btn-secondary:hover {
            background-color: #f1f5f9;
        }

        .btn-success {
            background-color: var(--color-success);
            color: #ffffff;
        }

        .btn-danger {
            background-color: var(--color-danger);
            color: #ffffff;
        }

        .btn-sm {
            padding: 0.35rem 0.75rem;
            font-size: 0.8125rem;
        }

        .card {
            background-color: var(--surface-card);
            border: 1px solid var(--surface-border);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .card-header {
            padding: 1.15rem 1.5rem;
            border-bottom: 1px solid var(--surface-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-heading);
        }

        .card-body {
            padding: 1.5rem;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.875rem;
        }

        table.data-table th {
            background-color: #f8fafc;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.6875rem;
            letter-spacing: 0.05em;
            padding: 0.875rem 1.25rem;
            border-bottom: 1px solid var(--surface-border);
        }

        table.data-table td {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--surface-border);
            color: var(--text-body);
            vertical-align: middle;
        }

        table.data-table tr:hover td {
            background-color: #f8fafc;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.2rem 0.6rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-success { background: #dcfce7; color: #15803d; }
        .badge-warning { background: #fef3c7; color: #b45309; }
        .badge-danger { background: #fee2e2; color: #b91c1c; }
        .badge-info { background: #e0f2fe; color: #0369a1; }
        .badge-purple { background: #f3e8ff; color: #7e22ce; }

        /* Modal system */
        .modal-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(2px);
            z-index: 100;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .modal-backdrop.open {
            display: flex;
        }

        .modal-box {
            background: #ffffff;
            border-radius: 14px;
            max-width: 650px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--surface-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-header h3 {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text-heading);
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.25rem;
            cursor: pointer;
            color: var(--text-muted);
        }

        .modal-body {
            padding: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.15rem;
        }

        .form-group label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 600;
            margin-bottom: 0.4rem;
            color: var(--text-heading);
        }

        .form-control {
            width: 100%;
            padding: 0.6rem 0.85rem;
            font-size: 0.875rem;
            border: 1.5px solid var(--surface-border);
            border-radius: 8px;
            color: var(--text-heading);
            background: #ffffff;
            transition: border-color 0.15s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--brand-indigo-600);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .alert {
            padding: 0.875rem 1.15rem;
            border-radius: 8px;
            margin-bottom: 1.25rem;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        @media (max-width: 900px) {
            body { flex-direction: column; }
            .shop-sidebar { width: 100%; height: auto; position: static; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="shop-sidebar">
        <div class="shop-brand">
            <div class="shop-brand-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <rect x="5" y="2" width="14" height="20" rx="3" ry="3"></rect>
                    <line x1="12" y1="18" x2="12.01" y2="18"></line>
                </svg>
            </div>
            <div class="shop-brand-text">
                <span class="shop-brand-title">{{ auth()->user()->shop->name ?? 'Phone Shop' }}</span>
                <span class="shop-brand-subtitle">Branch: Main Outlet</span>
            </div>
        </div>

        <nav class="shop-nav">
            <span class="shop-nav-header">Counter & Sales</span>
            <a href="{{ route('shop.dashboard') }}" class="shop-nav-item {{ request()->routeIs('shop.dashboard') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                Dashboard
            </a>
            <a href="{{ route('shop.pos') }}" class="shop-nav-item {{ request()->routeIs('shop.pos*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 9h20"></path><rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="M6 16h4"></path><path d="M14 16h4"></path></svg>
                POS Counter
                <span class="shop-nav-badge" style="background:#06b6d4; color:#fff;">Live</span>
            </a>

            <span class="shop-nav-header">Phone Inventory</span>
            <a href="{{ route('shop.phones') }}" class="shop-nav-item {{ request()->routeIs('shop.phones*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                Dual IMEI Phones
            </a>
            <a href="{{ route('shop.products') }}" class="shop-nav-item {{ request()->routeIs('shop.products*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                Products & Accessories
            </a>
            <a href="{{ route('shop.spare-parts') }}" class="shop-nav-item {{ request()->routeIs('shop.spare-parts*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>
                Spare Parts Stock
            </a>

            <span class="shop-nav-header">Service & Warranties</span>
            <a href="{{ route('shop.repairs') }}" class="shop-nav-item {{ request()->routeIs('shop.repairs*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>
                Repair Jobs (8-Stages)
            </a>
            <a href="{{ route('shop.warranties') }}" class="shop-nav-item {{ request()->routeIs('shop.warranties*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                Warranty & Claims
            </a>

            <span class="shop-nav-header">Directory & Administration</span>
            <a href="{{ route('shop.customers') }}" class="shop-nav-item {{ request()->routeIs('shop.customers*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                Customers & Credit
            </a>
            @can('hrm.view')
            <a href="{{ route('app') }}" class="shop-nav-item">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                Staff HRM & Payroll
            </a>
            @endcan

            @if(auth()->user()->hasRole('Super Administrator') || auth()->user()->can('superadmin.view'))
            <a href="{{ route('superadmin.dashboard') }}" class="shop-nav-item" style="color: #a5b4fc; margin-top: 1rem; border: 1px dashed rgba(165,180,252,0.3);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon></svg>
                Switch to SaaS Super Admin
            </a>
            @endif
        </nav>

        <div class="shop-user-footer">
            <div class="shop-user-meta">
                <div class="shop-user-avatar">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                </div>
                <div class="shop-user-details">
                    <span class="shop-user-name" title="{{ auth()->user()->name }}">{{ auth()->user()->name }}</span>
                    <span class="shop-user-role">{{ auth()->user()->roles->first()->name ?? 'Staff' }}</span>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" class="shop-logout-btn" title="Sign out of Shop Panel">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    <span>Sign out</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Workspace -->
    <div class="shop-workspace">
        <header class="shop-topbar">
            <div class="shop-topbar-title">
                @yield('page-title', 'Overview')
                <span class="shop-plan-pill">
                    {{ auth()->user()->shop->activeSubscription->plan->name ?? 'Free Trial' }}
                </span>
            </div>

            <div style="display: flex; align-items: center; gap: 1rem;">
                <a href="{{ route('shop.pos') }}" class="btn btn-sm btn-primary">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    New POS Sale
                </a>
                <a href="{{ route('public.repair-track') }}" target="_blank" class="btn btn-sm btn-secondary" title="Open Customer Repair Tracking Portal">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    Customer Tracking Portal
                </a>
            </div>
        </header>

        <main class="shop-content">
            @if(session('success'))
                <div class="alert alert-success">
                    <span>{{ session('success') }}</span>
                    <button type="button" onclick="this.parentElement.style.display='none'" style="background:none;border:none;cursor:pointer;">&times;</button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    <span>{{ session('error') }}</span>
                    <button type="button" onclick="this.parentElement.style.display='none'" style="background:none;border:none;cursor:pointer;">&times;</button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Modal helper script -->
    <script>
        function openModal(id) {
            const el = document.getElementById(id);
            if (el) el.classList.add('open');
        }
        function closeModal(id) {
            const el = document.getElementById(id);
            if (el) el.classList.remove('open');
        }
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-backdrop')) {
                e.target.classList.remove('open');
            }
        });
    </script>
    @stack('scripts')
</body>
</html>

