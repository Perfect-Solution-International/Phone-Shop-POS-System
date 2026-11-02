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

        /* Professional Portal Footer */
        .portal-footer {
            background: #0f172a;
            color: #94a3b8;
            border-top: 1px solid #1e293b;
            margin-top: auto;
            font-size: 0.875rem;
        }

        .portal-footer-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 3rem 1.5rem 1.75rem;
        }

        .portal-footer-grid {
            display: grid;
            grid-template-columns: 2fr 1.2fr 1.5fr;
            gap: 2.5rem;
            padding-bottom: 2.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .footer-col {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .footer-brand {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }

        .footer-brand-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: linear-gradient(135deg, #4f46e5 0%, #06b6d4 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        .footer-brand-name {
            font-size: 1.125rem;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.01em;
        }

        .footer-brand-tag {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #38bdf8;
            font-weight: 700;
        }

        .footer-desc {
            font-size: 0.8125rem;
            line-height: 1.6;
            color: #94a3b8;
            max-width: 380px;
            margin: 0;
        }

        .footer-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 0.85rem;
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.25);
            color: #34d399;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            width: fit-content;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #10b981;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.3);
        }

        .footer-heading {
            font-size: 0.8125rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #ffffff;
            margin: 0;
        }

        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .footer-links li a {
            color: #94a3b8;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8125rem;
            transition: color 0.15s ease, transform 0.15s ease;
        }

        .footer-links li a:hover {
            color: #ffffff;
            transform: translateX(3px);
        }

        .footer-contact-info {
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
        }

        .contact-item {
            display: flex;
            align-items: flex-start;
            gap: 0.65rem;
            font-size: 0.8125rem;
            color: #94a3b8;
            line-height: 1.4;
        }

        .contact-item svg {
            flex-shrink: 0;
            color: #38bdf8;
            margin-top: 0.15rem;
        }

        .contact-item a:hover {
            color: #38bdf8 !important;
        }

        .security-badge {
            margin-top: 0.25rem;
            padding: 0.55rem 0.85rem;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 8px;
            color: #cbd5e1;
            font-size: 0.75rem;
        }

        .security-badge svg {
            color: #a78bfa;
            margin-top: 0;
        }

        .portal-footer-bottom {
            padding-top: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1.25rem;
            font-size: 0.75rem;
            color: #64748b;
        }

        .portal-footer-bottom strong {
            color: #cbd5e1;
        }

        .footer-developer {
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
        }

        .developer-label {
            font-size: 0.75rem;
            color: #94a3b8;
            font-weight: 600;
            letter-spacing: 0.03em;
        }

        .developer-brand-badge {
            display: inline-flex;
            align-items: center;
            padding: 0;
            background: transparent;
            border: none;
            box-shadow: none;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .developer-brand-badge:hover {
            opacity: 0.85;
            transform: translateY(-1px);
        }

        .developer-logo-img {
            height: 26px;
            width: auto;
            max-width: 140px;
            object-fit: contain;
            display: block;
        }

        @media (max-width: 868px) {
            .portal-footer-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            .portal-footer-bottom {
                flex-direction: column;
                text-align: center;
                gap: 1.25rem;
            }
            .footer-developer {
                justify-content: center;
            }
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

    <footer class="portal-footer">
        <div class="portal-footer-inner">
            <div class="portal-footer-grid">
                <!-- Col 1: Shop Brand & About -->
                <div class="footer-col brand-col">
                    <div class="footer-brand">
                        <div class="footer-brand-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
                                <line x1="12" y1="18" x2="12.01" y2="18"></line>
                            </svg>
                        </div>
                        <div>
                            <div class="footer-brand-name">{{ auth()->user()->shop->name ?? 'Phone Shop' }}</div>
                            <div class="footer-brand-tag">Customer Care & Support Portal</div>
                        </div>
                    </div>
                    <p class="footer-desc">
                        Your trusted mobile partner for device sales, genuine warranty tracking, and certified repair workshop services.
                    </p>
                    <div class="footer-status-pill">
                        <span class="status-dot"></span>
                        <span>Service Center Active &bull; Mon - Sat</span>
                    </div>
                </div>

                <!-- Col 2: Fast Navigation Links -->
                <div class="footer-col">
                    <h4 class="footer-heading">Customer Services</h4>
                    <ul class="footer-links">
                        <li>
                            <a href="{{ route('customer.portal') }}">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>
                                My Repair Jobs
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('customer.portal') }}">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                Device Warranties
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('customer.portal') }}">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                Purchase Receipts & Invoices
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('public.repair-track') }}" target="_blank">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                Public IMEI & Repair Tracker
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Col 3: Outlet / Store Contact Details -->
                <div class="footer-col">
                    <h4 class="footer-heading">Store Contact & Location</h4>
                    <div class="footer-contact-info">
                        @if(auth()->user()->shop && auth()->user()->shop->address)
                        <div class="contact-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            <span>{{ auth()->user()->shop->address }}</span>
                        </div>
                        @else
                        <div class="contact-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            <span>Main Retail Outlet & Workshop</span>
                        </div>
                        @endif

                        @if(auth()->user()->shop && auth()->user()->shop->phone)
                        <div class="contact-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                            <a href="tel:{{ auth()->user()->shop->phone }}" style="color: inherit; text-decoration: none;">{{ auth()->user()->shop->phone }}</a>
                        </div>
                        @endif

                        @if(auth()->user()->shop && auth()->user()->shop->email)
                        <div class="contact-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            <a href="mailto:{{ auth()->user()->shop->email }}" style="color: inherit; text-decoration: none;">{{ auth()->user()->shop->email }}</a>
                        </div>
                        @endif

                        <div class="contact-item security-badge">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            <span>256-Bit SSL Encrypted & Verified Session</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="portal-footer-bottom">
                <div class="footer-copy">
                    &copy; {{ date('Y') }} <strong>{{ auth()->user()->shop->name ?? 'Phone Shop' }}</strong>. All rights reserved.
                    <div style="margin-top: 0.25rem; font-size: 0.7rem; color: #475569;">Powered by {{ config('app.name', 'SaaS Phone Shop POS System') }}</div>
                </div>
                <div class="footer-developer">
                    <span class="developer-label">Developed by</span>
                    <a href="https://www.perfectsolutioninternational.com/" target="_blank" rel="noopener noreferrer" class="developer-brand-badge" title="Perfect Solution International — Visit Website">
                        <img src="{{ asset('images/perfect-solution-logo.png') }}" alt="Perfect Solution International" class="developer-logo-img">
                    </a>
                </div>
            </div>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>

