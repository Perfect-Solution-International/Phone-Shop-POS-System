<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Super Admin') — {{ config('app.name', 'SaaS Phone Shop POS System') }} Platform</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <style>
        :root {
            --brand-navy-950: #060913;
            --brand-navy-900: #0b1120;
            --brand-navy-800: #1e293b;
            --brand-indigo-600: #4f46e5;
            --brand-indigo-700: #4338ca;
            --brand-violet-600: #7c3aed;
            --surface-card: #ffffff;
            --surface-bg: #f8fafc;
            --surface-border: #e2e8f0;
            --text-heading: #0f172a;
            --text-body: #334155;
            --text-muted: #64748b;
            --color-success: #10b981;
            --color-warning: #f59e0b;
            --color-danger: #ef4444;
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--surface-bg);
            color: var(--text-body);
            min-height: 100vh;
            display: flex;
        }

        /* Sidebar */
        .saas-sidebar {
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

        .saas-brand {
            padding: 1.5rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            flex-shrink: 0;
        }

        .saas-brand-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--brand-indigo-600) 0%, var(--brand-violet-600) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);
        }

        .saas-brand-text {
            display: flex;
            flex-direction: column;
        }

        .saas-brand-title {
            font-size: 0.9375rem;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.01em;
        }

        .saas-brand-subtitle {
            font-size: 0.6875rem;
            color: #a5b4fc;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-weight: 700;
        }

        .saas-nav {
            padding: 1rem 0.75rem;
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
            flex-grow: 1;
            overflow-y: auto;
        }

        .saas-nav-header {
            font-size: 0.6875rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
            font-weight: 750;
            padding: 0.75rem 0.65rem 0.35rem;
        }

        .saas-nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 0.85rem;
            border-radius: 8px;
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 550;
            transition: all 0.15s ease;
        }

        .saas-nav-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.06);
        }

        .saas-nav-link.active {
            color: #ffffff;
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.3) 0%, rgba(124, 58, 237, 0.2) 100%);
            border: 1px solid rgba(99, 102, 241, 0.3);
            font-weight: 650;
        }

        .saas-badge-pill {
            margin-left: auto;
            background: #ef4444;
            color: #ffffff;
            font-size: 0.6875rem;
            font-weight: 700;
            padding: 0.15rem 0.5rem;
            border-radius: 9999px;
        }

        /* Sidebar User Profile & Sign Out Footer */
        .saas-user-footer {
            flex-shrink: 0;
            padding: 0.85rem 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(0, 0, 0, 0.32);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.65rem;
        }

        .saas-user-meta {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            overflow: hidden;
            min-width: 0;
        }

        .saas-user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 0.8125rem;
            font-weight: 750;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
        }

        .saas-user-details {
            display: flex;
            flex-direction: column;
            overflow: hidden;
            min-width: 0;
        }

        .saas-user-name {
            font-size: 0.8125rem;
            font-weight: 700;
            color: #f1f5f9;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.25;
        }

        .saas-user-role {
            font-size: 0.6875rem;
            color: #a5b4fc;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
            margin-top: 1px;
        }

        .saas-logout-btn {
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

        .saas-logout-btn:hover {
            background: #ef4444;
            color: #ffffff;
            border-color: #ef4444;
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(239, 68, 68, 0.35);
        }

        /* Main Content */
        .saas-main {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .saas-topbar {
            height: 54px;
            background: #ffffff;
            border-bottom: 1px solid var(--surface-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .saas-topbar-title {
            font-size: 1.1rem;
            font-weight: 750;
            color: var(--text-heading);
            letter-spacing: -0.01em;
        }

        .saas-topbar-right {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }

        .saas-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.65rem;
            border-radius: 9999px;
        }

        .status-dot-pulse {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background-color: #10b981;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25);
        }

        .saas-topbar-link {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
            font-size: 0.775rem;
            font-weight: 600;
            text-decoration: none;
            color: var(--brand-indigo-600);
            background: #eef2ff;
            border: 1px solid #c7d2fe;
            transition: all 0.15s ease;
        }

        .saas-topbar-link:hover {
            background: #e0e7ff;
            color: var(--brand-indigo-700);
        }

        .saas-body {
            padding: 1.25rem 1.5rem;
            flex-grow: 1;
        }

        /* Metric Grid */
        .metric-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 0.75rem;
            margin-bottom: 1.25rem;
        }

        @media (max-width: 1240px) {
            .metric-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 0.75rem;
            }
        }

        @media (max-width: 768px) {
            .metric-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.65rem;
            }
        }

        @media (max-width: 480px) {
            .metric-grid {
                grid-template-columns: 1fr;
            }
        }

        .metric-card {
            background: #ffffff;
            border-radius: 10px;
            padding: 0.85rem 0.95rem;
            border: 1px solid var(--surface-border);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 0.25rem;
            min-height: 108px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 14px -2px rgba(0, 0, 0, 0.08);
            border-color: #cbd5e1;
        }

        .metric-label {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: var(--text-muted);
            line-height: 1.2;
        }

        .metric-value {
            font-size: 1.35rem;
            font-weight: 850;
            color: var(--text-heading);
            letter-spacing: -0.01em;
            line-height: 1.2;
            margin: 0.15rem 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .metric-sub {
            font-size: 0.7rem;
            color: var(--color-success);
            font-weight: 600;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Data Card */
        .data-card {
            background: #ffffff;
            border-radius: 10px;
            border: 1px solid var(--surface-border);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            margin-bottom: 1.25rem;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .data-card-header {
            padding: 0.85rem 1.25rem;
            border-bottom: 1px solid var(--surface-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .data-card-title {
            font-size: 0.95rem;
            font-weight: 750;
            color: var(--text-heading);
        }

        /* Table */
        .saas-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.875rem;
        }

        .saas-table th {
            background: #f8fafc;
            padding: 0.75rem 1.25rem;
            font-weight: 650;
            color: #475569;
            border-bottom: 1px solid var(--surface-border);
            font-size: 0.775rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .saas-table td {
            padding: 0.95rem 1.25rem;
            border-bottom: 1px solid var(--surface-border);
            color: #334155;
            vertical-align: middle;
        }

        .saas-table tr:hover td {
            background: #fbfcfe;
        }

        /* Status Badge */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.2rem 0.65rem;
            border-radius: 9999px;
            font-size: 0.725rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .status-active { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .status-trial { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
        .status-expired, .status-suspended, .status-rejected { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .status-pending, .status-under_review { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.45rem 0.85rem;
            border-radius: 7px;
            font-size: 0.8125rem;
            font-weight: 650;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.15s ease;
        }

        .btn-primary { background: var(--brand-indigo-600); color: #ffffff; }
        .btn-primary:hover { background: var(--brand-indigo-700); color: #ffffff; }
        .btn-outline { background: transparent; border-color: var(--surface-border); color: #334155; }
        .btn-outline:hover { background: #f1f5f9; color: #0f172a; }
        .btn-success { background: var(--color-success); color: #ffffff; }
        .btn-danger { background: var(--color-danger); color: #ffffff; }

        .alert-bar {
            padding: 0.85rem 1.25rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        /* Mobile Off-Canvas Drawer & Overlay */
        .saas-sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(6, 9, 19, 0.65);
            backdrop-filter: blur(2px);
            z-index: 1040;
            opacity: 0;
            transition: opacity 0.25s ease;
        }

        .saas-sidebar-overlay.active {
            display: block;
            opacity: 1;
        }

        .saas-mobile-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--text-heading);
            padding: 0.35rem;
            border-radius: 6px;
            cursor: pointer;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .saas-mobile-toggle:hover {
            background: #f1f5f9;
        }

        .saas-close-btn {
            display: none;
            background: none;
            border: none;
            color: #94a3b8;
            font-size: 1.5rem;
            cursor: pointer;
            margin-left: auto;
            line-height: 1;
            padding: 0.2rem 0.45rem;
            border-radius: 4px;
        }

        .saas-close-btn:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.12);
        }

        @media (max-width: 992px) {
            body {
                position: relative;
                overflow-x: hidden;
            }

            .saas-mobile-toggle {
                display: inline-flex;
            }

            .saas-close-btn {
                display: block;
            }

            .saas-sidebar {
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                width: 270px;
                max-width: 85vw;
                height: 100vh;
                z-index: 1050;
                transform: translateX(-100%);
                transition: transform 0.28s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: none;
            }

            .saas-sidebar.open {
                transform: translateX(0);
                box-shadow: 12px 0 35px -5px rgba(0, 0, 0, 0.5);
            }

            .saas-main {
                width: 100%;
                min-width: 0;
            }

            .saas-topbar {
                height: 54px;
                padding: 0 1rem;
                gap: 0.5rem;
            }

            .saas-body {
                padding: 1rem;
            }
        }

        @media (max-width: 640px) {
            .saas-topbar-title {
                font-size: 0.95rem;
            }

            .saas-status-badge {
                padding: 0.2rem 0.45rem;
                font-size: 0.7rem;
            }

            .data-card-header {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 0.75rem;
            }

            .data-card-header form {
                flex-direction: column;
                align-items: stretch !important;
                width: 100%;
            }

            .data-card-header form input,
            .data-card-header form select {
                width: 100% !important;
                max-width: 100% !important;
            }

            .saas-body {
                padding: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Off-Canvas Backdrop -->
    <div class="saas-sidebar-overlay" id="saasSidebarOverlay" onclick="closeSaasSidebar()"></div>

    <!-- Sidebar -->
    <aside class="saas-sidebar">
        <div class="saas-brand">
            <div class="saas-brand-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
            </div>
            <div class="saas-brand-text">
                <span class="saas-brand-title">{{ config('app.name') === 'Laravel' ? 'SaaS Phone Shop POS System' : config('app.name', 'SaaS Phone Shop POS System') }}</span>
                <span class="saas-brand-subtitle">Super Admin Portal</span>
            </div>
            <button type="button" class="saas-close-btn" onclick="closeSaasSidebar()" aria-label="Close menu">&times;</button>
        </div>

        <nav class="saas-nav">
            <div class="saas-nav-header">Platform Core</div>
            <a href="{{ route('superadmin.dashboard') }}" class="saas-nav-link {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('superadmin.shops.index') }}" class="saas-nav-link {{ request()->routeIs('superadmin.shops.*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg>
                <span>Registered Shops</span>
            </a>
            <a href="{{ route('superadmin.owners.index') }}" class="saas-nav-link {{ request()->routeIs('superadmin.owners.*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                <span>Shop Owners</span>
            </a>
            <a href="{{ route('superadmin.plans.index') }}" class="saas-nav-link {{ request()->routeIs('superadmin.plans.*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
                <span>Subscription Plans</span>
            </a>

            <div class="saas-nav-header">Billing &amp; Revenue</div>
            <a href="{{ route('superadmin.payments.approvals') }}" class="saas-nav-link {{ request()->routeIs('superadmin.payments.approvals') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                <span>Bank Approvals</span>
                @php
                    $pendingApprovalsCount = \App\Models\Platform\SubscriptionPayment::where('payment_method', 'bank_transfer')->whereIn('status', ['pending', 'under_review'])->count();
                @endphp
                @if ($pendingApprovalsCount > 0)
                    <span class="saas-badge-pill">{{ $pendingApprovalsCount }}</span>
                @endif
            </a>
            <a href="{{ route('superadmin.payments.index') }}" class="saas-nav-link {{ request()->routeIs('superadmin.payments.index') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                <span>All Payments</span>
            </a>



        </nav>

        <!-- Sidebar User Profile & Sign Out Footer -->
        <div class="saas-user-footer">
            <div class="saas-user-meta">
                <div class="saas-user-avatar">
                    {{ strtoupper(substr(auth()->user()->name ?? 'S', 0, 1)) }}
                </div>
                <div class="saas-user-details">
                    <span class="saas-user-name" title="{{ auth()->user()->name ?? 'System Super Admin' }}">
                        {{ auth()->user()->name ?? 'System Super Admin' }}
                    </span>
                    <span class="saas-user-role">Super Admin</span>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                @csrf
                <button type="submit" class="saas-logout-btn" title="Sign out of Super Admin">
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

    <!-- Main View Area -->
    <div class="saas-main">
        <header class="saas-topbar">
            <button type="button" class="saas-mobile-toggle" onclick="openSaasSidebar()" aria-label="Open navigation menu">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
            <h1 class="saas-topbar-title">@yield('page_title', 'SaaS Platform Management')</h1>
            <div class="saas-topbar-right">
                <span class="saas-status-badge">
                    <span class="status-dot-pulse"></span>
                    <span>System Operational</span>
                </span>
            </div>
        </header>

        <main class="saas-body">
            @if (session('status'))
                <div class="alert-bar" role="status">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        function openSaasSidebar() {
            var sidebar = document.querySelector('.saas-sidebar');
            var overlay = document.getElementById('saasSidebarOverlay');
            if (sidebar) sidebar.classList.add('open');
            if (overlay) overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeSaasSidebar() {
            var sidebar = document.querySelector('.saas-sidebar');
            var overlay = document.getElementById('saasSidebarOverlay');
            if (sidebar) sidebar.classList.remove('open');
            if (overlay) overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.saas-nav-link').forEach(function(link) {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 992) {
                        closeSaasSidebar();
                    }
                });
            });
        });
    </script>
    @stack('scripts')
</body>
</html>

