<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'My Account') — Customer Portal</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <style>
        :root {
            --brand-primary: #4f46e5;
            --brand-primary-dark: #4338ca;
            --brand-navy: #0f172a;
            --surface-bg: #f8fafc;
            --surface-card: #ffffff;
            --border-color: #e2e8f0;
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
            flex-direction: column;
        }

        /* Top Header */
        .portal-header {
            background-color: #ffffff;
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 30;
        }

        .portal-header-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0.85rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .portal-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: var(--text-heading);
        }

        .portal-brand-icon {
            width: 36px;
            height: 36px;
            border-radius: 9px;
            background: linear-gradient(135deg, var(--brand-primary) 0%, #06b6d4 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
        }

        .portal-brand-text {
            display: flex;
            flex-direction: column;
        }

        .portal-brand-title {
            font-size: 0.9375rem;
            font-weight: 800;
            color: var(--text-heading);
            line-height: 1.2;
        }

        .portal-brand-subtitle {
            font-size: 0.7rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
        }

        .portal-user-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-greeting {
            display: flex;
            flex-direction: column;
            text-align: right;
            font-size: 0.8125rem;
        }

        .user-name {
            font-weight: 700;
            color: var(--text-heading);
        }

        .user-email {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.45rem 0.95rem;
            font-size: 0.8125rem;
            font-weight: 600;
            border-radius: 7px;
            border: 1px solid transparent;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .btn-outline {
            background-color: #ffffff;
            border-color: var(--border-color);
            color: var(--text-body);
        }

        .btn-outline:hover {
            background-color: #f1f5f9;
        }

        .btn-primary {
            background-color: var(--brand-primary);
            color: #ffffff;
        }

        .btn-primary:hover {
            background-color: var(--brand-primary-dark);
        }

        /* Container */
        .portal-main {
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            padding: 2rem 1.5rem;
            flex: 1;
        }

        .card {
            background: var(--surface-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .card-header {
            padding: 1.15rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
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

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
            text-align: left;
        }

        table.data-table th {
            background: #f8fafc;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.6875rem;
            letter-spacing: 0.05em;
            padding: 0.85rem 1.25rem;
            border-bottom: 1px solid var(--border-color);
        }

        table.data-table td {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-body);
            vertical-align: middle;
        }

        table.data-table tr:hover td {
            background-color: #f8fafc;
        }

        .footer {
            border-top: 1px solid var(--border-color);
            padding: 1.5rem;
            text-align: center;
            font-size: 0.8125rem;
            color: var(--text-muted);
            background: #ffffff;
        }
    </style>
</head>
<body>
    <header class="portal-header">
        <div class="portal-header-inner">
            <a href="{{ route('customer.portal') }}" class="portal-brand">
                <div class="portal-brand-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <div class="portal-brand-text">
                    <span class="portal-brand-title">{{ auth()->user()->shop->name ?? 'Phone Shop' }}</span>
                    <span class="portal-brand-subtitle">Customer Care Portal</span>
                </div>
            </a>

            <div class="portal-user-meta">
                <div class="user-greeting">
                    <span class="user-name">{{ auth()->user()->name }}</span>
                    <span class="user-email">{{ auth()->user()->email }}</span>
                </div>
                <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn btn-outline" title="Sign out" style="display: inline-flex; align-items: center; gap: 0.35rem; color: #dc2626; border-color: #fca5a5; background: #fef2f2;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        <span>Sign Out</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="portal-main">
        @yield('content')
    </main>

    <footer class="footer">
        {{ auth()->user()->shop->name ?? 'Phone Shop' }} Customer Care & Warranty Services • Powered by {{ config('app.name', 'SaaS Phone Shop POS System') }}
    </footer>
    @stack('scripts')
</body>
</html>

