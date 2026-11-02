<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <title>Login | {{ $applicationName }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ mix('css/app.min.css') }}">
    <style>
        :root {
            --brand-navy-950: #050814;
            --brand-navy-900: #0a0f1d;
            --brand-navy-800: #151e32;
            --brand-indigo-900: #1e1b4b;
            --brand-indigo-700: #4338ca;
            --brand-indigo-600: #4f46e5;
            --brand-indigo-500: #6366f1;
            --brand-indigo-100: #e0e7ff;
            --brand-indigo-50: #eef2ff;
            --brand-violet-600: #7c3aed;
            --brand-violet-500: #8b5cf6;
            --brand-emerald-600: #059669;
            --brand-emerald-50: #ecfdf5;
            --brand-sky-600: #0284c7;
            --brand-sky-50: #f0f9ff;
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
            --radius-xl: 22px;
            --radius-lg: 14px;
            --radius-md: 10px;
            --radius-sm: 6px;
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

        body {
            background-color: var(--brand-navy-950) !important;
            background-image: 
                radial-gradient(ellipse 90% 60% at 50% -10%, rgba(99, 102, 241, 0.32), transparent 70%),
                radial-gradient(circle 520px at 85% 75%, rgba(124, 58, 237, 0.18), transparent 70%),
                radial-gradient(circle 480px at 15% 30%, rgba(79, 70, 229, 0.16), transparent 70%),
                radial-gradient(rgba(255, 255, 255, 0.08) 1.2px, transparent 1.2px) !important;
            background-size: 100% 100%, 100% 100%, 100% 100%, 28px 28px !important;
            background-attachment: fixed !important;
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            margin: 0;
            padding: clamp(1.5rem, 3.5vw, 3rem) clamp(1rem, 2.5vw, 1.5rem);
            color: var(--text-body);
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            overflow-x: hidden;
            position: relative;
        }

        .ambient-aurora {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: clamp(320px, 80vw, 680px);
            height: clamp(320px, 80vw, 680px);
            background: radial-gradient(circle, rgba(99, 102, 241, 0.22) 0%, rgba(124, 58, 237, 0.1) 40%, transparent 70%);
            filter: blur(80px);
            pointer-events: none;
            z-index: 0;
        }

        .auth-container-shell,
        .login-shell {
            width: 100%;
            max-width: 480px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0 !important;
        }

        /* 
         * ULTRA-PREMIUM CARD DESIGN
         */
        .fallback-card,
        .login-card {
            background: var(--surface-white) !important;
            border-radius: var(--radius-xl) !important;
            border: 1px solid rgba(255, 255, 255, 0.95) !important;
            box-shadow: 
                0 0 0 1px rgba(226, 232, 240, 0.8),
                0 25px 60px -15px rgba(0, 0, 0, 0.5),
                0 0 50px -10px rgba(99, 102, 241, 0.3) !important;
            padding: clamp(1.85rem, 4vw, 2.5rem) clamp(1.35rem, 3.5vw, 2.25rem) 2rem !important;
            width: 100% !important;
            max-width: 480px !important;
            position: relative !important;
            overflow: hidden !important;
            transition: all 0.25s ease !important;
        }

        .fallback-card::before,
        .login-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #4f46e5 0%, #7c3aed 50%, #06b6d4 100%) !important;
            border-radius: 22px 22px 0 0;
            z-index: 5;
        }

        /* 
         * HEADER BRANDING
         */
        .card-header-block,
        .login-header {
            text-align: center;
            margin-bottom: 1.5rem;
            position: relative;
        }

        .fallback-brand-icon {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 56px !important;
            height: 56px !important;
            background: linear-gradient(135deg, var(--brand-indigo-600) 0%, var(--brand-violet-600) 100%) !important;
            border-radius: 16px !important;
            color: #ffffff !important;
            margin: 0 auto 0.75rem !important;
            box-shadow: 0 10px 20px -3px rgba(79, 70, 229, 0.45), inset 0 1px 1px rgba(255, 255, 255, 0.4) !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
        }

        .fallback-brand-icon:hover {
            transform: scale(1.08) rotate(-2deg) !important;
        }

        .fallback-brand-icon svg {
            width: 28px !important;
            height: 28px !important;
        }

        .brand-kicker-pill {
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.4rem !important;
            font-size: 0.6875rem !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.08em !important;
            color: var(--brand-indigo-600) !important;
            background: var(--brand-indigo-50) !important;
            border: 1px solid var(--brand-indigo-100) !important;
            border-radius: 9999px !important;
            padding: 0.25rem 0.75rem !important;
            margin: 0 auto 0.5rem !important;
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

        .card-title,
        #login-heading {
            font-size: 1.45rem !important;
            font-weight: 800 !important;
            color: #0f172a !important;
            margin-bottom: 0.25rem !important;
            letter-spacing: -0.025em !important;
            line-height: 1.25 !important;
        }

        .card-subtitle {
            font-size: 0.825rem !important;
            color: #64748b !important;
            font-weight: 500 !important;
            line-height: 1.4 !important;
        }

        /* 
         * FORM INPUTS WITH CRISP SVG ICONS
         */
        .form-group {
            margin-bottom: 1.25rem !important;
        }

        .form-label-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.4rem;
        }

        .form-label {
            font-size: 0.8125rem;
            font-weight: 650;
            color: #1e293b;
        }

        .required-star {
            color: #ef4444;
            margin-left: 2px;
        }

        .forgot-link {
            font-size: 0.78rem;
            font-weight: 600;
            color: #4f46e5;
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .forgot-link:hover {
            color: #3730a3;
            text-decoration: underline;
        }

        .input-shell {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .input-icon-prefix {
            position: absolute;
            left: 1rem;
            color: #94a3b8;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            z-index: 2;
            transition: color 0.15s ease;
        }

        .input-shell:focus-within .input-icon-prefix {
            color: #4f46e5;
        }

        .fallback-input {
            width: 100% !important;
            min-height: 48px !important;
            padding: 0.75rem 1rem 0.75rem 2.85rem !important;
            font-size: 0.9375rem !important;
            color: #0f172a !important;
            background-color: #f8fafc !important;
            border: 1.5px solid #cbd5e1 !important;
            border-radius: var(--radius-md) !important;
            outline: none !important;
            transition: all 0.2s ease !important;
        }

        .fallback-input.has-toggle {
            padding-right: 3.25rem !important;
        }

        .fallback-input:focus {
            background-color: #ffffff !important;
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2) !important;
        }

        .input-toggle-btn {
            position: absolute;
            right: 0.75rem;
            background: transparent !important;
            border: none !important;
            color: #64748b !important;
            cursor: pointer;
            padding: 0.35rem !important;
            border-radius: 6px !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s ease;
            z-index: 2;
        }

        .input-toggle-btn:hover {
            color: #4f46e5 !important;
            background-color: #eef2ff !important;
        }

        /* 
         * REMEMBER ME CHECKBOX
         */
        .remember-row {
            display: flex !important;
            align-items: center !important;
            gap: 0.65rem !important;
            margin: 1.15rem 0 1.35rem !important;
            cursor: pointer !important;
        }

        .custom-checkbox {
            appearance: none;
            -webkit-appearance: none;
            width: 18px !important;
            height: 18px !important;
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 5px !important;
            background-color: #ffffff !important;
            cursor: pointer !important;
            position: relative !important;
            outline: none !important;
            flex-shrink: 0 !important;
            transition: all 0.15s ease !important;
            margin: 0 !important;
        }

        .custom-checkbox:checked {
            background-color: #4f46e5 !important;
            border-color: #4f46e5 !important;
        }

        .custom-checkbox:checked::after {
            content: "" !important;
            position: absolute !important;
            left: 5px !important;
            top: 2px !important;
            width: 5px !important;
            height: 9px !important;
            border: solid #ffffff !important;
            border-width: 0 2px 2px 0 !important;
            transform: rotate(45deg) !important;
        }

        .remember-label {
            font-size: 0.8125rem !important;
            font-weight: 550 !important;
            color: #475569 !important;
            cursor: pointer !important;
            user-select: none !important;
        }

        /* 
         * HERO LOGIN BUTTON
         */
        .fallback-btn {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 0.6rem !important;
            width: 100% !important;
            min-height: 50px !important;
            padding: 0.875rem 1.5rem !important;
            font-size: 0.95rem !important;
            font-weight: 750 !important;
            letter-spacing: 0.01em !important;
            color: #ffffff !important;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 50%, #7c3aed 100%) !important;
            border: 1px solid rgba(255, 255, 255, 0.25) !important;
            border-radius: var(--radius-md) !important;
            cursor: pointer !important;
            box-shadow: 0 10px 25px -4px rgba(99, 102, 241, 0.5), 0 4px 10px -2px rgba(124, 58, 237, 0.3) !important;
            transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
            position: relative !important;
            overflow: hidden !important;
        }

        .fallback-btn:hover:not(:disabled) {
            background: linear-gradient(135deg, #4338ca 0%, #4f46e5 50%, #6d28d9 100%) !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 16px 32px -4px rgba(99, 102, 241, 0.6), 0 6px 14px -2px rgba(124, 58, 237, 0.4) !important;
        }

        .fallback-btn:hover:not(:disabled) .btn-arrow-icon {
            transform: translateX(4px) !important;
        }

        .fallback-btn:disabled {
            opacity: 0.75 !important;
            cursor: wait !important;
            transform: none !important;
        }

        .btn-arrow-icon {
            transition: transform 0.2s ease;
        }

        .btn-spinner {
            display: none;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* 
         * REGISTRATION & PUBLIC ACCESS CTA BLOCKS
         */
        .auth-actions-card {
            margin-top: 1.5rem;
            padding-top: 1.25rem;
            border-top: 1px solid #f1f5f9;
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
        }

        .action-cta-pill {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.65rem 0.85rem;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.78rem;
            font-weight: 600;
            transition: all 0.15s ease;
        }

        .action-cta-register {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        .action-cta-register:hover {
            background: #dcfce7;
            border-color: #86efac;
            color: #14532d;
            text-decoration: none;
        }

        .action-cta-track {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            color: #0369a1;
        }

        .action-cta-track:hover {
            background: #e0f2fe;
            border-color: #7dd3fc;
            color: #075985;
            text-decoration: none;
        }

        /* Security Trust Seal */
        .security-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            margin-top: 1.35rem;
            font-size: 0.72rem;
            color: #64748b;
            text-align: center;
        }

        .security-badge svg {
            width: 14px;
            height: 14px;
            color: var(--color-success);
            flex-shrink: 0;
        }
    </style>
</head>
<body data-application-name="{{ $applicationName }}" data-login-preview-url="{{ $loginPreviewUrl }}" data-login-url="{{ route('login', [], false) }}" data-login-store-url="{{ route('login.store', [], false) }}" data-csrf-url="{{ route('sanctum.csrf-cookie', [], false) }}">
    <div class="ambient-aurora" aria-hidden="true"></div>

    <div class="auth-container-shell">
        <div id="login">
            <main class="fallback-card" role="main">
                <!-- Header with Icon & Branding -->
                <header class="card-header-block">
                    <div class="fallback-brand-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
                            <line x1="12" y1="18" x2="12.01" y2="18"></line>
                        </svg>
                    </div>

                    <div class="brand-kicker-pill">
                        <span class="brand-kicker-dot"></span>
                        <span id="brandKickerText">Enterprise POS &amp; SaaS Cloud</span>
                    </div>

                    <h1 class="card-title">{{ $applicationName }}</h1>
                    <p class="card-subtitle" id="cardSubtitle">Retail POS, IMEI Tracking &amp; Repair Center</p>
                </header>

                <!-- Interactive Portal Mode Switcher -->
                <div class="portal-mode-toggle" style="display: flex; background: #f1f5f9; padding: 4px; border-radius: 12px; margin-bottom: 1.35rem; gap: 4px; border: 1px solid #e2e8f0;">
                    <button type="button" id="btnModeOwner" class="mode-tab-btn" onclick="switchLoginMode('owner')" style="flex: 1; padding: 0.65rem 0.5rem; border: none; border-radius: 9px; font-size: 0.8125rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.4rem; transition: all 0.2s ease;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                        <span>Store Owner &amp; Admin</span>
                    </button>
                    <button type="button" id="btnModeStaff" class="mode-tab-btn" onclick="switchLoginMode('staff')" style="flex: 1; padding: 0.65rem 0.5rem; border: none; border-radius: 9px; font-size: 0.8125rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.4rem; transition: all 0.2s ease;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        <span>Staff &amp; Cashier Portal</span>
                    </button>
                </div>

                @auth
                    <div style="background-color: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; padding: 0.75rem 1rem; border-radius: 10px; margin-bottom: 1.25rem; font-size: 0.85rem; display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;">
                        <span>Signed in as: <strong>{{ auth()->user()->email }}</strong></span>
                        <a href="{{ auth()->user()->hasRole('Super Administrator') ? route('superadmin.dashboard') : route('shop.dashboard') }}" style="font-weight: 600; color: #2563eb; text-decoration: underline;">Go to Dashboard &rarr;</a>
                    </div>
                @endauth

                @if (session('status'))
                    <div style="background-color: var(--color-success-bg); border: 1px solid var(--color-success-border); color: #065f46; padding: 0.85rem 1rem; border-radius: 10px; margin-bottom: 1.25rem; font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem;" role="status">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div style="background-color: var(--color-danger-bg); border: 1px solid var(--color-danger-border); color: #991b1b; padding: 0.85rem 1rem; border-radius: 10px; margin-bottom: 1.25rem; font-size: 0.8125rem;" role="alert">
                        <div style="display: flex; align-items: flex-start; gap: 0.5rem;">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="flex-shrink: 0; margin-top: 0.15rem;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            <ul style="margin: 0; padding-left: 1rem; list-style-type: disc;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @if (isset($currentUser) && $currentUser)
                    <div style="background: #eef2ff; border: 1.5px solid #c7d2fe; border-radius: 12px; padding: 0.85rem 1rem; margin-bottom: 1.25rem; font-size: 0.8125rem;">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; flex-wrap: wrap;">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #10b981;"></span>
                                <span style="color: #4338ca; font-weight: 600;">Active session:</span>
                                <strong style="color: #1e1b4b;">{{ $currentUser->name }} ({{ $currentUser->email }})</strong>
                            </div>
                            <div style="display: flex; gap: 0.75rem; align-items: center;">
                                @if (isset($currentDestination) && $currentDestination)
                                    <a href="{{ $currentDestination }}" style="color: #4f46e5; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        Open Workspace &rarr;
                                    </a>
                                @endif
                                <a href="{{ route('logout') }}" style="color: #ef4444; font-weight: 600; text-decoration: underline;">Sign out</a>
                            </div>
                        </div>
                    </div>
                @endif

                <form id="fallbackLoginForm" method="POST" action="{{ route('login.store') }}" novalidate>
                    @csrf

                    <!-- Email Input with Icon Prefix -->
                    <div class="form-group">
                        <div class="form-label-row">
                            <label for="email" class="form-label">
                                <span id="emailLabelText">Email address</span> <span class="required-star">*</span>
                            </label>
                        </div>
                        <div class="input-shell">
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
                                autocomplete="username" 
                                inputmode="email" 
                                class="fallback-input" 
                                value="{{ old('email') }}" 
                                required 
                                autofocus 
                                placeholder="owner@yourshop.com"
                                aria-required="true"
                            >
                        </div>
                    </div>

                    <!-- Password Input with Lock Icon Prefix & Eye Toggle -->
                    <div class="form-group">
                        <div class="form-label-row">
                            <label for="password" class="form-label">
                                Password <span class="required-star">*</span>
                            </label>
                            <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
                        </div>
                        <div class="input-shell">
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
                                autocomplete="current-password" 
                                class="fallback-input has-toggle" 
                                required 
                                placeholder="••••••••••••"
                                aria-required="true"
                            >
                            <button type="button" class="input-toggle-btn" onclick="toggleFallbackPassword('password', this)" aria-label="Toggle password visibility">
                                <svg id="eyeIconOpen" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg id="eyeIconClosed" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me Checkbox -->
                    <label class="remember-row" for="remember">
                        <input type="checkbox" id="remember" name="remember" value="1" class="custom-checkbox">
                        <span class="remember-label">Keep me signed in on this device</span>
                    </label>

                    <!-- Role Routing Information Box (Staff Portal) -->
                    <div id="staffRoleHint" style="display: none; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 9px; padding: 0.75rem 0.85rem; margin-bottom: 1.15rem; font-size: 0.78rem; color: #166534; line-height: 1.45;">
                        <div style="font-weight: 700; margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.35rem;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Direct Role-Based Portal Routing:
                        </div>
                        &bull; <strong>Cashier:</strong> Automatically opens the live <strong>POS Counter</strong>.<br>
                        &bull; <strong>Technician:</strong> Automatically opens the <strong>Repair Pipeline Workbench</strong>.<br>
                        &bull; <strong>Stock Manager:</strong> Opens <strong>Inventory &amp; Phone Stock</strong>.
                    </div>

                    <!-- Submit Hero Button -->
                    <button type="submit" id="loginSubmitBtn" class="fallback-btn">
                        <svg id="loginSpinner" class="btn-spinner" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <circle cx="12" cy="12" r="10" stroke-opacity="0.25"></circle>
                            <path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"></path>
                        </svg>
                        <span id="loginSubmitText">Sign In to Workspace</span>
                        <svg class="btn-arrow-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </button>
                </form>

                <!-- Dedicated Registration / Portal Info Action Card -->
                <div class="auth-actions-card">
                    <div id="ownerCtaBlock">
                        <a href="{{ route('register') }}" class="action-cta-pill action-cta-register">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                                <span>New Store Owner? <strong>Register Shop (7-Day Trial)</strong></span>
                            </div>
                            <span>&rarr;</span>
                        </a>
                    </div>
                    <div id="staffCtaBlock" style="display: none; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 0.75rem 0.85rem; font-size: 0.78rem; color: #64748b; text-align: center;">
                        <span style="font-weight: 600; color: #334155;">Need Staff Login Credentials?</span><br>
                        Contact your Store Owner to create your account under <strong>Store Management &gt; Staff HRM</strong>.
                    </div>
                </div>
            </main>
        </div>

        <!-- Enterprise Security Trust Seal -->
        <div class="security-badge" aria-label="Security status">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
            </svg>
            <span>Strict Multi-Tenant Isolation &bull; Automatic Role Routing</span>
        </div>
    </div>

    <script>
        function switchLoginMode(mode) {
            const btnOwner = document.getElementById('btnModeOwner');
            const btnStaff = document.getElementById('btnModeStaff');
            const kickerText = document.getElementById('brandKickerText');
            const cardSubtitle = document.getElementById('cardSubtitle');
            const emailInput = document.getElementById('email');
            const emailLabelText = document.getElementById('emailLabelText');
            const staffHint = document.getElementById('staffRoleHint');
            const submitText = document.getElementById('loginSubmitText');
            const ownerCta = document.getElementById('ownerCtaBlock');
            const staffCta = document.getElementById('staffCtaBlock');

            if (mode === 'staff') {
                if (btnStaff) {
                    btnStaff.style.background = '#ffffff';
                    btnStaff.style.color = '#0284c7';
                    btnStaff.style.boxShadow = '0 2px 4px rgba(0,0,0,0.08)';
                }
                if (btnOwner) {
                    btnOwner.style.background = 'transparent';
                    btnOwner.style.color = '#64748b';
                    btnOwner.style.boxShadow = 'none';
                }

                if (kickerText) kickerText.textContent = 'Staff Terminal & POS Portal';
                if (cardSubtitle) cardSubtitle.textContent = 'Cashiers, Technicians & Store Personnel Sign-In';
                if (emailLabelText) emailLabelText.textContent = 'Staff Email Address';
                if (emailInput && (!emailInput.value || emailInput.value === 'owner@yourshop.com')) {
                    emailInput.placeholder = 'cashier@yourshop.com or staff@example.com';
                }
                if (staffHint) staffHint.style.display = 'block';
                if (submitText) submitText.textContent = 'Sign In to Staff Workspace';
                if (ownerCta) ownerCta.style.display = 'none';
                if (staffCta) staffCta.style.display = 'block';
            } else {
                if (btnOwner) {
                    btnOwner.style.background = '#ffffff';
                    btnOwner.style.color = '#4f46e5';
                    btnOwner.style.boxShadow = '0 2px 4px rgba(0,0,0,0.08)';
                }
                if (btnStaff) {
                    btnStaff.style.background = 'transparent';
                    btnStaff.style.color = '#64748b';
                    btnStaff.style.boxShadow = 'none';
                }

                if (kickerText) kickerText.textContent = 'Enterprise POS & SaaS Cloud';
                if (cardSubtitle) cardSubtitle.textContent = 'Retail POS, IMEI Tracking & Repair Center';
                if (emailLabelText) emailLabelText.textContent = 'Email address';
                if (emailInput && (!emailInput.value || emailInput.value.includes('staff'))) {
                    emailInput.placeholder = 'owner@yourshop.com';
                }
                if (staffHint) staffHint.style.display = 'none';
                if (submitText) submitText.textContent = 'Sign In to Workspace';
                if (ownerCta) ownerCta.style.display = 'block';
                if (staffCta) staffCta.style.display = 'none';
            }
        }

        const initialMode = "{{ $initialMode ?? 'owner' }}";
        if (initialMode === 'staff') {
            switchLoginMode('staff');
        } else {
            switchLoginMode('owner');
        }

        function toggleFallbackPassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const eyeOpen = document.getElementById('eyeIconOpen');
            const eyeClosed = document.getElementById('eyeIconClosed');

            if (input.type === 'password') {
                input.type = 'text';
                if (eyeOpen) eyeOpen.style.display = 'none';
                if (eyeClosed) eyeClosed.style.display = 'inline-block';
                btn.setAttribute('aria-label', 'Hide password');
            } else {
                input.type = 'password';
                if (eyeOpen) eyeOpen.style.display = 'inline-block';
                if (eyeClosed) eyeClosed.style.display = 'none';
                btn.setAttribute('aria-label', 'Show password');
            }
        }

        const loginForm = document.getElementById('fallbackLoginForm');
        const loginBtn = document.getElementById('loginSubmitBtn');
        const loginBtnText = document.getElementById('loginSubmitText');
        const loginSpinner = document.getElementById('loginSpinner');

        if (loginForm && loginBtn) {
            loginForm.addEventListener('submit', function() {
                loginBtn.disabled = true;
                if (loginSpinner) loginSpinner.style.display = 'inline-block';
                loginBtnText.textContent = 'Authenticating...';
                loginBtn.style.opacity = '0.85';
                loginBtn.style.cursor = 'wait';
            });
        }
    </script>
</body>
</html>
