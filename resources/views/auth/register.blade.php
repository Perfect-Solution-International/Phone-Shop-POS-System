<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>Register Shop Workspace — {{ $applicationName ?? 'SaaS Phone Shop POS System' }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <style>
        :root {
            --brand-navy-950: #060913;
            --brand-navy-900: #0b1120;
            --brand-navy-800: #1e293b;
            --brand-indigo-950: #131138;
            --brand-indigo-700: #4338ca;
            --brand-indigo-600: #4f46e5;
            --brand-indigo-500: #6366f1;
            --brand-indigo-100: #e0e7ff;
            --brand-indigo-50: #eef2ff;
            --brand-violet-600: #7c3aed;
            --brand-violet-500: #8b5cf6;
            --brand-pink-500: #ec4899;
            --surface-white: #ffffff;
            --surface-muted: #f8fafc;
            --surface-border: #e2e8f0;
            --text-heading: #0f172a;
            --text-body: #334155;
            --text-muted: #64748b;
            --text-light: #94a3b8;
            --color-danger: #ef4444;
            --color-danger-bg: #fef2f2;
            --color-danger-border: #fecaca;
            --color-success: #10b981;
            --color-success-bg: #ecfdf5;
            --color-success-border: #a7f3d0;
            --color-warning: #f59e0b;
            --radius-xl: 24px;
            --radius-lg: 14px;
            --radius-md: 12px;
            --radius-sm: 6px;
            --shadow-card: 0 0 0 1px rgba(226, 232, 240, 0.8), 0 25px 60px -15px rgba(0, 0, 0, 0.45), 0 0 50px -10px rgba(99, 102, 241, 0.25);
            --shadow-glow: 0 10px 25px -4px rgba(99, 102, 241, 0.5), 0 4px 10px -2px rgba(124, 58, 237, 0.3);
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            font-size: 16px;
            -webkit-text-size-adjust: 100%;
        }

        /* 
         * STUNNING AURORA & HIGH-TECH MESH BACKGROUND (MATCHING LOGIN DESIGN) 
         */
        body {
            background-color: var(--brand-navy-950);
            background-image: 
                radial-gradient(ellipse 90% 60% at 50% -10%, rgba(99, 102, 241, 0.32), transparent 70%),
                radial-gradient(circle 480px at 85% 75%, rgba(124, 58, 237, 0.2), transparent 70%),
                radial-gradient(circle 480px at 15% 30%, rgba(79, 70, 229, 0.16), transparent 70%),
                radial-gradient(rgba(255, 255, 255, 0.08) 1.2px, transparent 1.2px);
            background-size: 100% 100%, 100% 100%, 100% 100%, 28px 28px;
            background-attachment: fixed;
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: clamp(1.75rem, 4vw, 3.5rem) clamp(1rem, 2.5vw, 1.5rem);
            color: var(--text-body);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            overflow-x: hidden;
            position: relative;
        }

        /* Ambient aurora lighting orb */
        .ambient-aurora {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: clamp(320px, 80vw, 850px);
            height: clamp(320px, 80vw, 850px);
            background: radial-gradient(circle, rgba(99, 102, 241, 0.2) 0%, rgba(124, 58, 237, 0.08) 40%, transparent 70%);
            filter: blur(80px);
            pointer-events: none;
            z-index: 0;
        }

        /* 
         * CENTERED SAAS CONTAINER (IDENTICAL TO LOGIN SHELL) 
         */
        .register-shell {
            width: 100%;
            max-width: 580px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        /* 
         * ULTRA-PREMIUM FLOATING CARD 
         */
        .register-card {
            background: var(--surface-white);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-card);
            border: 1px solid rgba(255, 255, 255, 0.9);
            padding: clamp(2rem, 4.5vw, 2.75rem) clamp(1.35rem, 3.5vw, 2.25rem) 2.25rem;
            width: 100%;
            position: relative;
            overflow: hidden;
            transition: all 0.25s ease;
        }

        /* Iridescent Top Lightbar (Identical to Login) */
        .register-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #4f46e5 0%, #7c3aed 50%, #ec4899 100%);
            border-radius: 24px 24px 0 0;
            z-index: 5;
        }

        /* 
         * HEADER BLOCK WITH ICON & BRANDING (MATCHING LOGIN DESIGN) 
         */
        .card-header-block {
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
        }

        .brand-kicker {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--brand-indigo-600);
            background: var(--brand-indigo-50);
            border: 1px solid var(--brand-indigo-100);
            border-radius: 9999px;
            padding: 0.28rem 0.85rem;
            margin: 0 auto 0.75rem;
        }

        .brand-kicker-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background-color: var(--brand-indigo-600);
            display: inline-block;
            box-shadow: 0 0 8px rgba(79, 70, 229, 0.8);
            animation: pulse-dot 2s infinite ease-in-out;
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(1.2); }
        }

        .brand-emblem-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--brand-indigo-600) 0%, var(--brand-violet-600) 100%);
            border-radius: 15px;
            color: #ffffff;
            margin-bottom: 0.85rem;
            box-shadow: 0 10px 22px -3px rgba(79, 70, 229, 0.45), inset 0 1px 1px rgba(255, 255, 255, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.25);
        }

        .card-title {
            font-size: 1.55rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 0.35rem;
            letter-spacing: -0.025em;
            line-height: 1.25;
        }

        .card-subtitle {
            font-size: 0.875rem;
            color: #64748b;
            font-weight: 500;
            line-height: 1.5;
        }

        /* 
         * ALERTS 
         */
        .alert-box {
            border-radius: var(--radius-md);
            padding: 0.875rem 1rem;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            line-height: 1.45;
        }

        .alert-box-success {
            background-color: var(--color-success-bg);
            border: 1px solid var(--color-success-border);
            color: #065f46;
        }

        .alert-box-danger {
            background-color: var(--color-danger-bg);
            border: 1px solid var(--color-danger-border);
            color: #991b1b;
        }

        /* 
         * FORM CONTROLS & TACTILE INPUTS 
         */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8125rem;
            font-weight: 650;
            color: #1e293b;
            margin-bottom: 0.45rem;
            letter-spacing: -0.01em;
        }

        .required-mark {
            color: var(--color-danger);
            margin-left: 0.2rem;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon-prefix {
            position: absolute;
            left: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            pointer-events: none;
            transition: color 0.2s ease;
            z-index: 2;
        }

        .form-input {
            width: 100%;
            height: 48px;
            padding: 0.75rem 1rem 0.75rem 2.85rem;
            border: 1.5px solid var(--surface-border);
            border-radius: var(--radius-md);
            font-size: 0.9375rem;
            font-weight: 500;
            color: #0f172a;
            background-color: #ffffff;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            outline: none;
        }

        .form-input::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }

        .form-input:hover {
            border-color: #cbd5e1;
            background-color: #fafbfd;
        }

        .form-input:focus {
            border-color: var(--brand-indigo-600);
            background-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.18);
        }

        .form-input:focus + .input-icon-prefix,
        .input-wrapper:focus-within .input-icon-prefix {
            color: var(--brand-indigo-600);
        }

        select.form-input {
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 16px 16px;
            cursor: pointer;
            padding-right: 2.5rem;
        }

        .trial-feature-pill {
            background: linear-gradient(135deg, #eef2ff 0%, #f5f3ff 100%);
            border: 1px solid #c7d2fe;
            border-radius: 9999px;
            padding: 0.55rem 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.8125rem;
            color: #3730a3;
            width: 100%;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .trial-feature-pill svg {
            color: #f59e0b;
            flex-shrink: 0;
        }

        .form-input.is-invalid {
            border-color: var(--color-danger);
            background-color: #fffbfa;
        }

        .form-input.is-invalid:focus {
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.18);
        }

        .form-input.has-toggle {
            padding-right: 4.5rem;
        }

        .form-input-toggle {
            position: absolute;
            right: 0.65rem;
            height: 32px;
            padding: 0 0.65rem;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            color: #475569;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            cursor: pointer;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            user-select: none;
            z-index: 2;
        }

        .form-input-toggle:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        .field-help {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 0.35rem;
            line-height: 1.4;
        }

        .field-error {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.75rem;
            color: var(--color-danger);
            font-weight: 600;
            margin-top: 0.4rem;
        }

        .field-error svg {
            width: 14px;
            height: 14px;
            flex-shrink: 0;
        }

        /* 
         * 2-COLUMN GRID (FOR OWNER NAME & PHONE) 
         */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        @media (max-width: 580px) {
            .form-grid {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }

        /* 
         * LIVE PASSWORD STRENGTH METER 
         */
        .password-strength-box {
            margin-top: 0.65rem;
            background: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: var(--radius-md);
            padding: 0.75rem;
        }

        .strength-meter-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.75rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.4rem;
        }

        .strength-label {
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .strength-bars {
            display: flex;
            gap: 4px;
            height: 5px;
            margin-bottom: 0.65rem;
        }

        .strength-bar {
            flex: 1;
            height: 100%;
            background-color: #e2e8f0;
            border-radius: 9999px;
            transition: background-color 0.25s ease;
        }

        .strength-bar.weak { background-color: var(--color-danger); }
        .strength-bar.fair { background-color: var(--color-warning); }
        .strength-bar.good { background-color: #3b82f6; }
        .strength-bar.strong { background-color: var(--color-success); }

        .rules-list {
            list-style: none;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.35rem 0.5rem;
            font-size: 0.7188rem;
            color: #64748b;
        }

        .rule-item {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            transition: color 0.15s ease;
        }

        .rule-item svg {
            width: 12px;
            height: 12px;
            color: #cbd5e1;
            flex-shrink: 0;
            transition: color 0.15s ease;
        }

        .rule-item.valid {
            color: #059669;
            font-weight: 600;
        }

        .rule-item.valid svg {
            color: var(--color-success);
        }

        /* Match indicator */
        .match-indicator {
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 0.4rem;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .match-indicator.matched { color: var(--color-success); }
        .match-indicator.unmatched { color: var(--color-danger); }

        /* 
         * CUSTOM TERMS CHECKBOX 
         */
        .terms-row {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin: 1.35rem 0 1.5rem;
            cursor: pointer;
            user-select: none;
        }

        .terms-checkbox {
            appearance: none;
            -webkit-appearance: none;
            width: 18px;
            height: 18px;
            border: 1.5px solid #cbd5e1;
            border-radius: 5px;
            background-color: #ffffff;
            cursor: pointer;
            position: relative;
            outline: none;
            flex-shrink: 0;
            margin-top: 0.125rem;
            transition: all 0.15s ease;
        }

        .terms-checkbox:checked {
            background-color: var(--brand-indigo-600);
            border-color: var(--brand-indigo-600);
        }

        .terms-checkbox:checked::after {
            content: "";
            position: absolute;
            left: 5px;
            top: 2px;
            width: 4px;
            height: 9px;
            border: solid #ffffff;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .terms-checkbox:focus-visible {
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25);
        }

        .terms-checkbox.is-invalid {
            border-color: var(--color-danger);
        }

        .terms-label {
            font-size: 0.8125rem;
            color: #475569;
            line-height: 1.45;
        }

        .terms-label a {
            color: var(--brand-indigo-600);
            font-weight: 600;
            text-decoration: underline;
            text-underline-offset: 2px;
        }

        .terms-label a:hover {
            color: var(--brand-indigo-700);
        }

        /* 
         * HERO GRADIENT SUBMIT BUTTON (MATCHING LOGIN DESIGN) 
         */
        .btn-register-submit {
            width: 100%;
            height: 50px;
            background: linear-gradient(135deg, var(--brand-indigo-600) 0%, var(--brand-violet-600) 100%);
            border: none;
            border-radius: var(--radius-md);
            color: #ffffff;
            font-size: 0.9375rem;
            font-weight: 700;
            letter-spacing: -0.01em;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.65rem;
            box-shadow: var(--shadow-glow);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            outline: none;
        }

        .btn-register-submit:hover:not(:disabled) {
            transform: translateY(-1.5px);
            box-shadow: 0 14px 30px -4px rgba(99, 102, 241, 0.6), 0 6px 14px -2px rgba(124, 58, 237, 0.4);
            filter: brightness(1.05);
        }

        .btn-register-submit:active:not(:disabled) {
            transform: translateY(0.5px);
            box-shadow: 0 6px 16px -2px rgba(99, 102, 241, 0.5);
        }

        .btn-register-submit:focus-visible {
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.45);
        }

        .btn-register-submit:disabled {
            opacity: 0.75;
            cursor: not-allowed;
            transform: none;
        }

        .btn-arrow-icon {
            transition: transform 0.2s ease;
        }

        .btn-register-submit:hover:not(:disabled) .btn-arrow-icon {
            transform: translateX(4px);
        }

        .btn-spinner {
            display: none;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* 
         * FOOTER NAVIGATION (MATCHING LOGIN DESIGN) 
         */
        .auth-footer-nav {
            margin-top: 1.5rem;
            text-align: center;
            font-size: 0.875rem;
            color: #94a3b8;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
        }

        .footer-nav-link {
            color: #a5b4fc;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .footer-nav-link:hover {
            color: #ffffff;
            text-decoration: underline;
        }

        /* Responsive Breakpoints */
        @media (max-width: 640px) {
            body {
                padding: 1.25rem 0.875rem;
                justify-content: flex-start;
            }

            .ambient-aurora {
                display: none;
            }

            .register-card {
                padding: 1.85rem 1.25rem 1.75rem;
                border-radius: var(--radius-lg);
            }

            .card-title {
                font-size: 1.35rem;
            }

            .auth-footer-nav {
                flex-direction: column;
                gap: 0.35rem;
            }
        }

        @media (max-width: 380px) {
            body {
                padding: 1rem 0.5rem;
            }

            .register-card {
                padding: 1.35rem 0.875rem;
                border-radius: 14px;
            }

            .card-title {
                font-size: 1.25rem;
            }

            .form-input {
                font-size: 0.9375rem;
                padding-left: 2.5rem;
            }

            .input-icon-prefix {
                left: 0.8rem;
            }

            .btn-register-submit {
                padding: 0.75rem 1rem;
                font-size: 0.875rem;
            }
        }
    </style>
</head>
<body>
    <div class="ambient-aurora" aria-hidden="true"></div>

    <div class="register-shell">
        <main class="register-card" role="main">
            <!-- Header Block with Icon & Branding (Identical to Login) -->
            <header class="card-header-block">
                <div class="brand-kicker">
                    <span class="brand-kicker-dot"></span>
                    <span>Multi-Tenant Platform</span>
                </div>

                <div class="brand-emblem-badge" aria-hidden="true">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
                        <line x1="12" y1="18" x2="12.01" y2="18"></line>
                    </svg>
                </div>

                <h1 class="card-title">Register Shop Workspace</h1>
                <p class="card-subtitle">Create your store account &amp; POS sales counter</p>
            </header>

            @if (isset($currentUser) && $currentUser)
                <div style="background: #eef2ff; border: 1.5px solid #c7d2fe; border-radius: 12px; padding: 0.85rem 1rem; margin-bottom: 1.25rem; font-size: 0.8125rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; flex-wrap: wrap;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #10b981;"></span>
                            <span style="color: #4338ca; font-weight: 600;">Signed in as:</span>
                            <strong style="color: #1e1b4b;">{{ $currentUser->name }} ({{ $currentUser->email }})</strong>
                        </div>
                        <div style="display: flex; gap: 0.75rem; align-items: center;">
                            @if (isset($currentDestination) && $currentDestination)
                                <a href="{{ $currentDestination }}" style="color: #4f46e5; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.25rem;">
                                    Open Dashboard &rarr;
                                </a>
                            @endif
                            <a href="{{ route('logout') }}" style="color: #ef4444; font-weight: 600; text-decoration: underline;">Sign out</a>
                        </div>
                    </div>
                </div>
            @endif

            <div class="trial-feature-pill">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                </svg>
                <span><strong>7-Day Free Trial</strong> included with full POS &amp; Inventory access</span>
            </div>

            @if (session('status'))
                <div class="alert-box alert-box-success" role="status">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <div>{{ session('status') }}</div>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert-box alert-box-danger" role="alert">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <div>
                        <strong style="display: block; margin-bottom: 0.25rem;">Please check the highlighted fields below:</strong>
                        <ul style="padding-left: 1.25rem; margin: 0; font-size: 0.8125rem;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form id="registrationForm" method="POST" action="{{ route('register.store') }}" novalidate>
                @csrf

                <!-- Shop Name -->
                <div class="form-group">
                    <label for="shop_name" class="form-label">
                        <span>Shop / Business Name <span class="required-mark">*</span></span>
                    </label>
                    <div class="input-wrapper">
                        <span class="input-icon-prefix" aria-hidden="true">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                <polyline points="9 22 9 12 15 12 15 22"></polyline>
                            </svg>
                        </span>
                        <input
                            id="shop_name"
                            name="shop_name"
                            type="text"
                            class="form-input @error('shop_name') is-invalid @enderror"
                            value="{{ old('shop_name') }}"
                            required
                            autofocus
                            autocomplete="organization"
                            placeholder="e.g. Apex Cellular Care"
                            aria-required="true"
                            aria-describedby="shop_name_help @error('shop_name') shop_name_error @enderror"
                            @error('shop_name') aria-invalid="true" @enderror
                        >
                    </div>
                    <p id="shop_name_help" class="field-help">Your isolated tenant workspace name. Visible on receipts and employee screens.</p>
                    @error('shop_name')
                        <p id="shop_name_error" class="field-error" role="alert">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                <!-- 2-Column Row: Owner Name & Mobile Number -->
                <div class="form-grid">
                    <!-- Owner Full Name -->
                    <div class="form-group">
                        <label for="name" class="form-label">
                            <span>Owner Full Name <span class="required-mark">*</span></span>
                        </label>
                        <div class="input-wrapper">
                            <span class="input-icon-prefix" aria-hidden="true">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            </span>
                            <input
                                id="name"
                                name="name"
                                type="text"
                                class="form-input @error('name') is-invalid @enderror"
                                value="{{ old('name') }}"
                                required
                                autocomplete="name"
                                placeholder="e.g. Kamal Silva"
                                aria-required="true"
                                aria-describedby="@error('name') name_error @enderror"
                                @error('name') aria-invalid="true" @enderror
                            >
                        </div>
                        @error('name')
                            <p id="name_error" class="field-error" role="alert">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- Mobile Number -->
                    <div class="form-group">
                        <label for="phone" class="form-label">
                            <span>Mobile Number <span class="required-mark">*</span></span>
                        </label>
                        <div class="input-wrapper">
                            <span class="input-icon-prefix" aria-hidden="true">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                </svg>
                            </span>
                            <input
                                id="phone"
                                name="phone"
                                type="tel"
                                class="form-input @error('phone') is-invalid @enderror"
                                value="{{ old('phone') }}"
                                required
                                autocomplete="tel"
                                placeholder="+94 77 123 4567"
                                aria-required="true"
                                aria-describedby="phone_help @error('phone') phone_error @enderror"
                                @error('phone') aria-invalid="true" @enderror
                            >
                        </div>
                        <p id="phone_help" class="field-help">For security alerts &amp; SMS receipts.</p>
                        @error('phone')
                            <p id="phone_error" class="field-error" role="alert">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>
                </div>

                <!-- Owner Email Address -->
                <div class="form-group">
                    <label for="email" class="form-label">
                        <span>Owner Email Address <span class="required-mark">*</span></span>
                    </label>
                    <div class="input-wrapper">
                        <span class="input-icon-prefix" aria-hidden="true">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                        </span>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            class="form-input @error('email') is-invalid @enderror"
                            value="{{ old('email') }}"
                            required
                            autocomplete="username"
                            inputmode="email"
                            placeholder="owner@yourshop.com"
                            aria-required="true"
                            aria-describedby="email_help @error('email') email_error @enderror"
                            @error('email') aria-invalid="true" @enderror
                        >
                    </div>
                    <p id="email_help" class="field-help">We will send a verification link to confirm your ownership.</p>
                    @error('email')
                        <p id="email_error" class="field-error" role="alert">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                <!-- Business Address -->
                <div class="form-group">
                    <label for="business_address" class="form-label">
                        <span>Business Address</span>
                    </label>
                    <div class="input-wrapper">
                        <span class="input-icon-prefix" aria-hidden="true">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                        </span>
                        <input
                            id="business_address"
                            name="business_address"
                            type="text"
                            class="form-input @error('business_address') is-invalid @enderror"
                            value="{{ old('business_address') }}"
                            autocomplete="street-address"
                            placeholder="e.g. No. 142 Galle Road, Colombo 03"
                            aria-describedby="@error('business_address') business_address_error @enderror"
                            @error('business_address') aria-invalid="true" @enderror
                        >
                    </div>
                    @error('business_address')
                        <p id="business_address_error" class="field-error" role="alert">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                <!-- 2-Column Row: Country & Timezone -->
                <div class="form-grid">
                    <!-- Country -->
                    <div class="form-group">
                        <label for="country" class="form-label">
                            <span>Country</span>
                        </label>
                        <div class="input-wrapper">
                            <span class="input-icon-prefix" aria-hidden="true">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="2" y1="12" x2="22" y2="12"></line>
                                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                                </svg>
                            </span>
                            <select
                                id="country"
                                name="country"
                                class="form-input @error('country') is-invalid @enderror"
                                aria-describedby="@error('country') country_error @enderror"
                                @error('country') aria-invalid="true" @enderror
                            >
                                <option value="Sri Lanka" {{ old('country', 'Sri Lanka') === 'Sri Lanka' ? 'selected' : '' }}>Sri Lanka</option>
                                <option value="India" {{ old('country') === 'India' ? 'selected' : '' }}>India</option>
                                <option value="United Arab Emirates" {{ old('country') === 'United Arab Emirates' ? 'selected' : '' }}>United Arab Emirates</option>
                                <option value="Singapore" {{ old('country') === 'Singapore' ? 'selected' : '' }}>Singapore</option>
                                <option value="United Kingdom" {{ old('country') === 'United Kingdom' ? 'selected' : '' }}>United Kingdom</option>
                                <option value="Australia" {{ old('country') === 'Australia' ? 'selected' : '' }}>Australia</option>
                                <option value="United States" {{ old('country') === 'United States' ? 'selected' : '' }}>United States</option>
                                <option value="Canada" {{ old('country') === 'Canada' ? 'selected' : '' }}>Canada</option>
                                <option value="Malaysia" {{ old('country') === 'Malaysia' ? 'selected' : '' }}>Malaysia</option>
                                <option value="Maldives" {{ old('country') === 'Maldives' ? 'selected' : '' }}>Maldives</option>
                            </select>
                        </div>
                        @error('country')
                            <p id="country_error" class="field-error" role="alert">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- Timezone -->
                    <div class="form-group">
                        <label for="timezone" class="form-label">
                            <span>Timezone</span>
                        </label>
                        <div class="input-wrapper">
                            <span class="input-icon-prefix" aria-hidden="true">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                            </span>
                            <select
                                id="timezone"
                                name="timezone"
                                class="form-input @error('timezone') is-invalid @enderror"
                                aria-describedby="@error('timezone') timezone_error @enderror"
                                @error('timezone') aria-invalid="true" @enderror
                            >
                                <option value="Asia/Colombo" {{ old('timezone', 'Asia/Colombo') === 'Asia/Colombo' ? 'selected' : '' }}>Asia/Colombo (+05:30)</option>
                                <option value="Asia/Kolkata" {{ old('timezone') === 'Asia/Kolkata' ? 'selected' : '' }}>Asia/Kolkata (+05:30)</option>
                                <option value="Asia/Dubai" {{ old('timezone') === 'Asia/Dubai' ? 'selected' : '' }}>Asia/Dubai (+04:00)</option>
                                <option value="Asia/Singapore" {{ old('timezone') === 'Asia/Singapore' ? 'selected' : '' }}>Asia/Singapore (+08:00)</option>
                                <option value="UTC" {{ old('timezone') === 'UTC' ? 'selected' : '' }}>UTC (GMT+00:00)</option>
                                <option value="Europe/London" {{ old('timezone') === 'Europe/London' ? 'selected' : '' }}>Europe/London (GMT+00/01)</option>
                                <option value="America/New_York" {{ old('timezone') === 'America/New_York' ? 'selected' : '' }}>America/New_York (EST)</option>
                                <option value="Australia/Sydney" {{ old('timezone') === 'Australia/Sydney' ? 'selected' : '' }}>Australia/Sydney (AEST)</option>
                            </select>
                        </div>
                        @error('timezone')
                            <p id="timezone_error" class="field-error" role="alert">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>
                </div>

                <!-- Business Registration Number (Optional) -->
                <div class="form-group">
                    <label for="business_reg_number" class="form-label">
                        <span>Business Registration Details <span style="font-weight: normal; color: #64748b;">(Optional)</span></span>
                    </label>
                    <div class="input-wrapper">
                        <span class="input-icon-prefix" aria-hidden="true">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 2 2h12a2 2 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                        </span>
                        <input
                            id="business_reg_number"
                            name="business_reg_number"
                            type="text"
                            class="form-input @error('business_reg_number') is-invalid @enderror"
                            value="{{ old('business_reg_number') }}"
                            placeholder="e.g. PV-123456 or TIN 10928374 (if registered)"
                            aria-describedby="business_reg_help @error('business_reg_number') business_reg_error @enderror"
                            @error('business_reg_number') aria-invalid="true" @enderror
                        >
                    </div>
                    <p id="business_reg_help" class="field-help">Optional: Business registration number or tax ID for official receipts and invoices.</p>
                    @error('business_reg_number')
                        <p id="business_reg_error" class="field-error" role="alert">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                <!-- Master Password -->
                <div class="form-group">
                    <label for="password" class="form-label">
                        <span>Password <span class="required-mark">*</span></span>
                    </label>
                    <div class="input-wrapper">
                        <span class="input-icon-prefix" aria-hidden="true">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </span>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            class="form-input has-toggle @error('password') is-invalid @enderror"
                            required
                            autocomplete="new-password"
                            placeholder="••••••••••••"
                            aria-required="true"
                            aria-describedby="password_strength_box @error('password') password_error @enderror"
                            @error('password') aria-invalid="true" @enderror
                        >
                        <button type="button" class="form-input-toggle" onclick="toggleFieldVisibility('password', this)" aria-label="Toggle password visibility">
                            <span class="toggle-text">Show</span>
                        </button>
                    </div>

                    <!-- Live Password Strength Feedback -->
                    <div id="password_strength_box" class="password-strength-box" aria-live="polite">
                        <div class="strength-meter-header">
                            <span>Password Strength:</span>
                            <span id="strengthText" class="strength-label">Enter Password</span>
                        </div>
                        <div class="strength-bars">
                            <div id="bar1" class="strength-bar"></div>
                            <div id="bar2" class="strength-bar"></div>
                            <div id="bar3" class="strength-bar"></div>
                            <div id="bar4" class="strength-bar"></div>
                        </div>
                        <ul class="rules-list">
                            <li id="rule-len" class="rule-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <span>12+ characters</span>
                            </li>
                            <li id="rule-upper" class="rule-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <span>Uppercase (A-Z)</span>
                            </li>
                            <li id="rule-lower" class="rule-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <span>Lowercase (a-z)</span>
                            </li>
                            <li id="rule-num" class="rule-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <span>Number (0-9)</span>
                            </li>
                        </ul>
                    </div>

                    @error('password')
                        <p id="password_error" class="field-error" role="alert">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="password_confirmation" class="form-label">
                        <span>Confirm Password <span class="required-mark">*</span></span>
                    </label>
                    <div class="input-wrapper">
                        <span class="input-icon-prefix" aria-hidden="true">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                        </span>
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            class="form-input has-toggle"
                            required
                            autocomplete="new-password"
                            placeholder="••••••••••••"
                            aria-required="true"
                            aria-describedby="matchIndicator"
                        >
                        <button type="button" class="form-input-toggle" onclick="toggleFieldVisibility('password_confirmation', this)" aria-label="Toggle password confirmation visibility">
                            <span class="toggle-text">Show</span>
                        </button>
                    </div>
                    <div id="matchIndicator" class="match-indicator" style="display: none;"></div>
                </div>

                <!-- Terms & Privacy Policy Acceptance -->
                <label class="terms-row" for="terms">
                    <input
                        type="checkbox"
                        name="terms"
                        id="terms"
                        value="1"
                        class="terms-checkbox @error('terms') is-invalid @enderror"
                        {{ old('terms') ? 'checked' : '' }}
                        required
                        aria-required="true"
                        aria-describedby="@error('terms') terms_error @enderror"
                    >
                    <span class="terms-label">
                        I accept the <a href="javascript:void(0);" onclick="alert('Phone Shop POS Terms: Strict tenant data privacy, SLA uptime, and authorized phone retail workspace use apply.');">Terms of Service</a> and acknowledge the Privacy Policy.
                    </span>
                </label>
                @error('terms')
                    <p id="terms_error" class="field-error" style="margin-top: -1rem; margin-bottom: 1.25rem;" role="alert">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        <span>{{ $message }}</span>
                    </p>
                @enderror

                <!-- Hero Submit Button (Matching Login Design) -->
                <button type="submit" id="submitBtn" class="btn-register-submit">
                    <svg id="submitSpinner" class="btn-spinner" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <circle cx="12" cy="12" r="10" stroke-opacity="0.25"></circle>
                        <path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"></path>
                    </svg>
                    <span id="submitBtnText">Register Shop &amp; Start Free Trial</span>
                    <svg id="submitArrow" class="btn-arrow-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </button>
            </form>
        </main>

        <!-- Centered Footer Navigation -->
        <footer class="auth-footer-nav">
            <span>Already have a workspace?</span>
            <a href="{{ route('login') }}" class="footer-nav-link">Login to your shop &rarr;</a>
        </footer>
    </div>

    <!-- Client-side Interactive Behaviors -->
    <script>
        // Password visibility toggler
        function toggleFieldVisibility(inputId, btn) {
            const input = document.getElementById(inputId);
            const textSpan = btn.querySelector('.toggle-text');
            if (input.type === 'password') {
                input.type = 'text';
                if (textSpan) textSpan.textContent = 'Hide';
                btn.setAttribute('aria-label', 'Hide password');
            } else {
                input.type = 'password';
                if (textSpan) textSpan.textContent = 'Show';
                btn.setAttribute('aria-label', 'Show password');
            }
        }

        // Live Password Strength Meter & Real-time rule checklist
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('password_confirmation');
        const strengthText = document.getElementById('strengthText');
        const bar1 = document.getElementById('bar1');
        const bar2 = document.getElementById('bar2');
        const bar3 = document.getElementById('bar3');
        const bar4 = document.getElementById('bar4');
        const bars = [bar1, bar2, bar3, bar4];

        const ruleLen = document.getElementById('rule-len');
        const ruleUpper = document.getElementById('rule-upper');
        const ruleLower = document.getElementById('rule-lower');
        const ruleNum = document.getElementById('rule-num');

        const matchIndicator = document.getElementById('matchIndicator');

        function updatePasswordStrength() {
            const val = passwordInput.value;
            const hasLen = val.length >= 12;
            const hasUpper = /[A-Z]/.test(val);
            const hasLower = /[a-z]/.test(val);
            const hasNum = /[0-9]/.test(val);

            // Update rule checklist icons
            setRuleState(ruleLen, hasLen);
            setRuleState(ruleUpper, hasUpper);
            setRuleState(ruleLower, hasLower);
            setRuleState(ruleNum, hasNum);

            let score = 0;
            if (hasLen) score++;
            if (hasUpper) score++;
            if (hasLower) score++;
            if (hasNum) score++;

            // Reset bars
            bars.forEach(b => {
                b.className = 'strength-bar';
            });

            if (val.length === 0) {
                strengthText.textContent = 'Enter Password';
                strengthText.style.color = 'var(--text-muted)';
                checkMatch();
                return;
            }

            if (score <= 1) {
                strengthText.textContent = 'Weak';
                strengthText.style.color = 'var(--color-danger)';
                bar1.className = 'strength-bar weak';
            } else if (score === 2) {
                strengthText.textContent = 'Fair';
                strengthText.style.color = 'var(--color-warning)';
                bar1.className = 'strength-bar fair';
                bar2.className = 'strength-bar fair';
            } else if (score === 3) {
                strengthText.textContent = 'Good';
                strengthText.style.color = '#3b82f6';
                bar1.className = 'strength-bar good';
                bar2.className = 'strength-bar good';
                bar3.className = 'strength-bar good';
            } else {
                strengthText.textContent = 'Strong';
                strengthText.style.color = 'var(--color-success)';
                bars.forEach(b => b.className = 'strength-bar strong');
            }

            checkMatch();
        }

        function setRuleState(el, isValid) {
            if (isValid) {
                el.classList.add('valid');
            } else {
                el.classList.remove('valid');
            }
        }

        function checkMatch() {
            const pVal = passwordInput.value;
            const cVal = confirmInput.value;

            if (!cVal) {
                matchIndicator.style.display = 'none';
                return;
            }

            matchIndicator.style.display = 'flex';
            if (pVal === cVal) {
                matchIndicator.className = 'match-indicator matched';
                matchIndicator.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg> Passwords match';
            } else {
                matchIndicator.className = 'match-indicator unmatched';
                matchIndicator.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg> Passwords do not match';
            }
        }

        passwordInput.addEventListener('input', updatePasswordStrength);
        confirmInput.addEventListener('input', checkMatch);

        // Submit button loading animation
        const form = document.getElementById('registrationForm');
        const submitBtn = document.getElementById('submitBtn');
        const submitBtnText = document.getElementById('submitBtnText');
        const submitSpinner = document.getElementById('submitSpinner');
        const submitArrow = document.getElementById('submitArrow');

        if (form && submitBtn) {
            form.addEventListener('submit', function() {
                submitBtn.disabled = true;
                if (submitSpinner) submitSpinner.style.display = 'inline-block';
                if (submitArrow) submitArrow.style.display = 'none';
                submitBtnText.textContent = 'Creating Account...';
                submitBtn.style.cursor = 'wait';
            });
        }
    </script>
</body>
</html>
