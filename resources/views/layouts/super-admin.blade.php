<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Super Admin') — {{ config('app.name', 'Phone Shop POS SaaS') }} Platform</title>
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
            overflow-y: auto;
        }

        .saas-brand {
            padding: 1.5rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
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

        /* Main Content */
        .saas-main {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .saas-topbar {
            height: 64px;
            background: #ffffff;
            border-bottom: 1px solid var(--surface-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .saas-topbar-title {
            font-size: 1.15rem;
            font-weight: 750;
            color: var(--text-heading);
            letter-spacing: -0.01em;
        }

        .saas-topbar-user {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .saas-body {
            padding: 2rem;
            flex-grow: 1;
        }

        /* Metric Grid */
        .metric-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .metric-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 1.35rem 1.25rem;
            border: 1px solid var(--surface-border);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }

        .metric-label {
            font-size: 0.775rem;
            font-weight: 650;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--text-muted);
        }

        .metric-value {
            font-size: 1.75rem;
            font-weight: 850;
            color: var(--text-heading);
            letter-spacing: -0.02em;
        }

        .metric-sub {
            font-size: 0.75rem;
            color: var(--color-success);
            font-weight: 600;
        }

        /* Data Card */
        .data-card {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid var(--surface-border);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .data-card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--surface-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .data-card-title {
            font-size: 1.05rem;
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
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="saas-sidebar">
        <div class="saas-brand">
            <div class="saas-brand-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
            </div>
            <div class="saas-brand-text">
                <span class="saas-brand-title">{{ config('app.name', 'Phone Shop POS') }}</span>
                <span class="saas-brand-subtitle">Super Admin Portal</span>
            </div>
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

            <div class="saas-nav-header">Workspaces</div>
            <a href="{{ route('shop.dashboard') }}" class="saas-nav-link" target="_blank">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                <span>Phone Shop Management</span>
            </a>
            <a href="{{ route('customer.portal') }}" class="saas-nav-link" target="_blank">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                <span>Customer Care Portal</span>
            </a>
            <a href="{{ route('app') }}" class="saas-nav-link" target="_blank">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 3 21 3 21 9"></polyline><polyline points="9 21 3 21 3 15"></polyline><line x1="21" y1="3" x2="14" y2="10"></line><line x1="3" y1="21" x2="10" y2="14"></line></svg>
                <span>Staff HRM Workspace</span>
            </a>
        </nav>
    </aside>

    <!-- Main View Area -->
    <div class="saas-main">
        <header class="saas-topbar">
            <h1 class="saas-topbar-title">@yield('page_title', 'SaaS Platform Management')</h1>
            <div class="saas-topbar-user">
                <span style="font-size: 0.875rem; font-weight: 600;">{{ auth()->user()->name ?? 'Super Administrator' }}</span>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn-action btn-outline" style="font-size: 0.775rem;">Sign out</button>
                </form>
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
</body>
</html>

