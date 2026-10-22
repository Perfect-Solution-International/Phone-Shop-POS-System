<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <title>Login | {{ $applicationName }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ mix('css/app.min.css') }}">
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
            --radius-xl: 24px;
            --radius-lg: 14px;
            --radius-md: 12px;
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

        /* 
         * STUNNING AURORA & HIGH-TECH MESH BACKGROUND 
         */
        body {
            background-color: var(--brand-navy-950) !important;
            background-image: 
                /* Top illuminated Aurora spotlight */
                radial-gradient(ellipse 90% 60% at 50% -10%, rgba(99, 102, 241, 0.32), transparent 70%),
                /* Floating electric violet light on right */
                radial-gradient(circle 480px at 85% 75%, rgba(124, 58, 237, 0.2), transparent 70%),
                /* Deep royal indigo glow on left */
                radial-gradient(circle 480px at 15% 30%, rgba(79, 70, 229, 0.16), transparent 70%),
                /* High-tech matrix dot grid pattern */
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
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            overflow-x: hidden;
            position: relative;
        }

        /* Dynamic background ambient glow orb */
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
            max-width: 440px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
            min-height: auto !important;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0 !important;
        }

        /* 
         * ULTRA-PREMIUM CARD (APPLIES TO BOTH FALLBACK AND VUE COMPONENT) 
         */
        .fallback-card,
        .login-card {
            background: var(--surface-white) !important;
            border-radius: var(--radius-xl) !important;
            border: 1px solid rgba(255, 255, 255, 0.9) !important;
            box-shadow: 
                0 0 0 1px rgba(226, 232, 240, 0.8),
                0 25px 60px -15px rgba(0, 0, 0, 0.45),
                0 0 50px -10px rgba(99, 102, 241, 0.25) !important;
            padding: clamp(2rem, 4.5vw, 2.75rem) clamp(1.35rem, 3.5vw, 2.25rem) 2.25rem !important;
            width: 100% !important;
            max-width: 440px !important;
            position: relative !important;
            overflow: hidden !important;
            transition: all 0.25s ease !important;
        }

        /* Iridescent top lightbar on the card */
        .fallback-card::before,
        .login-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #4f46e5 0%, #7c3aed 50%, #ec4899 100%) !important;
            border-radius: 24px 24px 0 0;
            z-index: 5;
        }

        .login-card > .card-body {
            padding: 0 !important;
            background: transparent !important;
        }

        /* 
         * ICON & BRAND IDENTITY HEADER 
         */
        .card-header-block,
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
        }

        .fallback-brand-icon,
        .login-brand-mark {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 58px !important;
            height: 58px !important;
            background: linear-gradient(135deg, var(--brand-indigo-600) 0%, var(--brand-violet-600) 100%) !important;
            border-radius: 16px !important;
            color: #ffffff !important;
            margin: 0 auto 0.875rem !important;
            box-shadow: 0 12px 24px -4px rgba(79, 70, 229, 0.5), inset 0 1px 1px rgba(255, 255, 255, 0.4) !important;
            border: 1px solid rgba(255, 255, 255, 0.25) !important;
            transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
        }

        .fallback-brand-icon:hover,
        .login-brand-mark:hover {
            transform: scale(1.08) rotate(-2deg) !important;
        }

        .fallback-brand-icon svg,
        .login-brand-mark svg {
            width: 28px !important;
            height: 28px !important;
        }

        /* Pill Badge */
        .brand-kicker-pill,
        .login-brand-name {
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.4rem !important;
            font-size: 0.6875rem !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.1em !important;
            color: var(--brand-indigo-600) !important;
            background: var(--brand-indigo-50) !important;
            border: 1px solid var(--brand-indigo-100) !important;
            border-radius: 9999px !important;
            padding: 0.28rem 0.85rem !important;
            margin: 0 auto 0.65rem !important;
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
            font-size: 1.5rem !important;
            font-weight: 800 !important;
            color: #0f172a !important;
            margin-bottom: 0.35rem !important;
            letter-spacing: -0.025em !important;
            line-height: 1.25 !important;
        }

        .card-subtitle,
        .login-header .text-muted {
            font-size: 0.875rem !important;
            color: #64748b !important;
            font-weight: 500 !important;
            line-height: 1.5 !important;
        }

        /* 
         * INPUT FIELDS WITH TACTILE ICON HIGHLIGHTS 
         */
        .form-group,
        .login-card .form-group {
            margin-bottom: 1.25rem !important;
        }

        .form-label-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.45rem;
        }

        .form-label,
        .login-card .form-group label {
            font-size: 0.8125rem !important;
            font-weight: 600 !important;
            color: #1e293b !important;
            margin-bottom: 0 !important;
        }

        .required-star {
            color: var(--color-danger);
            margin-left: 0.2rem;
            font-weight: 700;
        }

        .forgot-link {
            font-size: 0.75rem !important;
            color: var(--brand-indigo-600) !important;
            text-decoration: none !important;
            font-weight: 600 !important;
            transition: color 0.15s ease !important;
        }

        .forgot-link:hover {
            color: var(--brand-indigo-700) !important;
            text-decoration: underline !important;
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
            color: var(--text-light);
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            transition: color 0.15s ease;
            z-index: 2;
        }

        .fallback-input,
        .login-card .form-control {
            width: 100% !important;
            min-height: 48px !important;
            padding: 0.75rem 1rem 0.75rem 2.85rem !important;
            font-size: 1rem !important; /* 16px prevents iOS Safari auto-zoom */
            font-family: inherit !important;
            color: var(--text-heading) !important;
            background-color: #f8fafc !important;
            border: 1.5px solid var(--surface-border) !important;
            border-radius: var(--radius-md) !important;
            outline: none !important;
            -webkit-appearance: none;
            appearance: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.02) !important;
        }

        .fallback-input.has-toggle {
            padding-right: 4rem !important;
        }

        .fallback-input::placeholder,
        .login-card .form-control::placeholder {
            color: #94a3b8 !important;
            font-weight: 400 !important;
        }

        .fallback-input:focus,
        .login-card .form-control:focus {
            background-color: #ffffff !important;
            border-color: var(--brand-indigo-500) !important;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.18), 0 2px 4px 0 rgba(0, 0, 0, 0.05) !important;
        }

        .fallback-input:focus ~ .input-icon-prefix,
        .fallback-input:focus + .input-icon-prefix {
            color: var(--brand-indigo-600) !important;
        }

        /* Show/Hide password toggle */
        .input-toggle-btn,
        .login-card .input-group .btn {
            position: absolute;
            right: 0.5rem;
            min-height: 32px !important;
            min-width: 48px;
            background: #f1f5f9 !important;
            border: 1px solid #e2e8f0 !important;
            color: var(--text-muted) !important;
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            cursor: pointer;
            padding: 0.25rem 0.6rem !important;
            border-radius: var(--radius-sm) !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s ease;
            touch-action: manipulation;
            z-index: 2;
        }

        .input-toggle-btn:hover,
        .login-card .input-group .btn:hover {
            color: var(--brand-indigo-700) !important;
            background-color: var(--brand-indigo-50) !important;
            border-color: var(--brand-indigo-100) !important;
        }

        /* Vue input group reset */
        .login-card .input-group .form-control {
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
        }

        .login-card .input-group-append .btn {
            position: static !important;
            border-radius: 0 12px 12px 0 !important;
            min-height: 48px !important;
            border-left: none !important;
        }

        /* 
         * CUSTOM REMEMBER ME ROW 
         */
        .remember-row,
        .login-card .custom-control {
            display: flex !important;
            align-items: center !important;
            gap: 0.65rem !important;
            margin: 1.15rem 0 1.35rem !important;
            cursor: pointer !important;
            padding-left: 0 !important;
        }

        .custom-checkbox,
        .login-card .custom-control-input {
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

        .custom-checkbox:checked,
        .login-card .custom-control-input:checked {
            background-color: var(--brand-indigo-600) !important;
            border-color: var(--brand-indigo-600) !important;
        }

        .custom-checkbox:checked::after,
        .login-card .custom-control-input:checked::after {
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

        .remember-label,
        .login-card .custom-control-label {
            font-size: 0.8125rem !important;
            font-weight: 500 !important;
            color: #475569 !important;
            cursor: pointer !important;
            user-select: none !important;
        }

        /* 
         * HERO "LOGIN" BUTTON (SPECTACULAR GLOW & INTERACTION) 
         */
        .fallback-btn,
        .login-card .btn-primary {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 0.6rem !important;
            width: 100% !important;
            min-height: 50px !important;
            padding: 0.875rem 1.5rem !important;
            font-size: 0.9375rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.01em !important;
            color: #ffffff !important;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 50%, #7c3aed 100%) !important;
            border: 1px solid rgba(255, 255, 255, 0.22) !important;
            border-radius: var(--radius-md) !important;
            cursor: pointer !important;
            box-shadow: 0 10px 25px -4px rgba(99, 102, 241, 0.5), 0 4px 10px -2px rgba(124, 58, 237, 0.3) !important;
            transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
            touch-action: manipulation;
            position: relative !important;
            overflow: hidden !important;
        }

        .fallback-btn:hover:not(:disabled),
        .login-card .btn-primary:hover:not(:disabled) {
            background: linear-gradient(135deg, #4338ca 0%, #4f46e5 50%, #6d28d9 100%) !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 16px 32px -4px rgba(99, 102, 241, 0.6), 0 6px 14px -2px rgba(124, 58, 237, 0.4) !important;
        }

        .fallback-btn:hover:not(:disabled) .btn-arrow-icon,
        .login-card .btn-primary:hover:not(:disabled) .btn-arrow-icon {
            transform: translateX(4px) !important;
        }

        .fallback-btn:active:not(:disabled),
        .login-card .btn-primary:active:not(:disabled) {
            transform: translateY(0) !important;
        }

        .fallback-btn:disabled,
        .login-card .btn-primary:disabled {
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

        /* Bottom helper note */
        .card-bottom-note,
        .login-help {
            text-align: center !important;
            font-size: 0.75rem !important;
            color: var(--text-light) !important;
            margin-top: 1.5rem !important;
            line-height: 1.4 !important;
            border-top: none !important;
            padding-top: 0 !important;
        }

        /* Outer Footer Navigation */
        .auth-footer-nav {
            text-align: center;
            padding-top: 1.5rem;
            font-size: 0.875rem;
            color: #94a3b8;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .auth-footer-nav a {
            color: #a5b4fc;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .auth-footer-nav a:hover {
            color: #ffffff;
            text-decoration: underline;
        }

        /* Security Trust Seal */
        .security-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            margin-top: 1.25rem;
            font-size: 0.75rem;
            color: #64748b;
            text-align: center;
        }

        .security-badge svg {
            width: 14px;
            height: 14px;
            color: var(--color-success);
            flex-shrink: 0;
        }

        /* Responsive Breakpoints */
        @media (max-width: 640px) {
            body {
                padding: 1.25rem 0.875rem !important;
                justify-content: flex-start !important;
            }

            .ambient-aurora {
                display: none !important;
            }

            .fallback-card,
            .login-card {
                padding: 1.85rem 1.25rem 1.75rem !important;
                border-radius: var(--radius-lg) !important;
            }

            .card-title,
            #login-heading {
                font-size: 1.375rem !important;
            }

            .auth-footer-nav {
                flex-direction: column;
                gap: 0.35rem;
            }
        }

        @media (max-width: 380px) {
            body {
                padding: 1rem 0.5rem !important;
            }

            .fallback-card,
            .login-card {
                padding: 1.35rem 0.875rem !important;
                border-radius: 14px !important;
            }

            .card-title,
            #login-heading {
                font-size: 1.25rem !important;
            }

            .fallback-input,
            .login-card .form-control {
                font-size: 0.9375rem !important;
                padding-left: 2.5rem !important;
            }

            .input-icon-prefix {
                left: 0.8rem;
            }

            .fallback-btn,
            .login-card .btn-primary {
                padding: 0.75rem 1rem !important;
                font-size: 0.875rem !important;
            }
        }

        /* 1-Click Demo Account Switcher */
        .role-switcher-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: var(--radius-md);
            padding: 0.85rem;
            margin-bottom: 1.25rem;
        }

        .role-switcher-title {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #4f46e5;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .role-btn-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.4rem;
        }

        .role-pill-btn {
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            padding: 0.45rem 0.25rem;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .role-pill-btn:hover {
            border-color: #4f46e5;
            background: #eef2ff;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.1);
        }

        .role-pill-btn.active {
            border-color: #4f46e5;
            background: #eef2ff;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.25);
        }

        .role-pill-title {
            font-size: 0.75rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.2;
        }

        .role-pill-route {
            font-size: 0.65rem;
            color: #64748b;
            margin-top: 0.15rem;
            font-family: monospace;
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

                    <h1 class="card-title">{{ $applicationName }}</h1>
                    <p class="card-subtitle">Retail POS, Inventory &amp; Repairs</p>
                </header>

                @if (session('status'))
                    <div style="background-color: var(--color-success-bg); border: 1px solid var(--color-success-border); color: #065f46; padding: 0.875rem 1rem; border-radius: 10px; margin-bottom: 1.25rem; font-size: 0.875rem; display: flex; align-items: center; gap: 0.5rem;" role="status">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div style="background-color: var(--color-danger-bg); border: 1px solid var(--color-danger-border); color: #991b1b; padding: 0.875rem 1rem; border-radius: 10px; margin-bottom: 1.25rem; font-size: 0.8125rem;" role="alert">
                        <div style="display: flex; align-items: flex-start; gap: 0.5rem;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="flex-shrink: 0; margin-top: 0.15rem;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
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

                <!-- 1-Click Role Switcher Demo Buttons -->
                <div class="role-switcher-card">
                    <div class="role-switcher-title">
                        <span>Select Demo Role &amp; Workspace</span>
                        <span style="font-size: 0.68rem; color: #94a3b8; font-weight: normal;">1-Click Autofill</span>
                    </div>
                    <div class="role-btn-grid">
                        <button type="button" class="role-pill-btn" onclick="selectRole('superadmin@pos.lk', 'password', this)">
                            <span class="role-pill-title">🏢 Super Admin</span>
                            <span class="role-pill-route">/super-admin</span>
                        </button>
                        <button type="button" class="role-pill-btn" onclick="selectRole('t2@gmail.com', 'password', this)">
                            <span class="role-pill-title">📱 Shop Owner</span>
                            <span class="role-pill-route">/shop (POS)</span>
                        </button>
                        <button type="button" class="role-pill-btn" onclick="selectRole('customer@gmail.com', 'password', this)">
                            <span class="role-pill-title">👤 Customer</span>
                            <span class="role-pill-route">/portal</span>
                        </button>
                    </div>
                </div>

                <form id="fallbackLoginForm" method="POST" action="{{ route('login.store') }}" novalidate>
                    @csrf

                    <!-- Email Input with Icon Prefix -->
                    <div class="form-group">
                        <div class="form-label-row">
                            <label for="email" class="form-label">
                                Email address <span class="required-star">*</span>
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

                    <!-- Password Input with Lock Icon Prefix & Toggle -->
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
                            <button type="button" class="input-toggle-btn" onclick="toggleFallbackPassword('password', this)" aria-label="Toggle password visibility">Show</button>
                        </div>
                    </div>

                    <!-- Remember Me Checkbox -->
                    <label class="remember-row" for="remember">
                        <input type="checkbox" id="remember" name="remember" value="1" class="custom-checkbox">
                        <span class="remember-label">Keep me signed in on this device</span>
                    </label>

                    <!-- Submit Hero Button -->
                    <button type="submit" id="loginSubmitBtn" class="fallback-btn">
                        <svg id="loginSpinner" class="btn-spinner" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <circle cx="12" cy="12" r="10" stroke-opacity="0.25"></circle>
                            <path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"></path>
                        </svg>
                        <span id="loginSubmitText">Login</span>
                        <svg class="btn-arrow-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </button>
                </form>

                <p class="card-bottom-note">
                    Need workspace access? Contact your store administrator.
                </p>
            </main>
        </div>

        <!-- Footer Navigation Links -->
        <footer class="auth-footer-nav">
            <span>New here? <a href="{{ route('register') }}">Create a shop workspace &rarr;</a></span>
        </footer>

        <!-- Enterprise Security Trust Seal -->
        <div class="security-badge" aria-label="Security status">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
            </svg>
            <span>Strict Tenant Partitioning &amp; 256-bit Encrypted Workspace</span>
        </div>
    </div>

    <script defer src="{{ mix('js/login.min.js') }}"></script>
    <script>
        function toggleFallbackPassword(inputId, btn) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                btn.textContent = 'Hide';
                btn.setAttribute('aria-label', 'Hide password');
            } else {
                input.type = 'password';
                btn.textContent = 'Show';
                btn.setAttribute('aria-label', 'Show password');
            }
        }

        function selectRole(email, password, btn) {
            document.querySelectorAll('.role-pill-btn').forEach(function(b) {
                b.classList.remove('active');
            });
            if (btn) btn.classList.add('active');

            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');

            if (emailInput) {
                emailInput.value = email;
            }
            if (passwordInput) {
                passwordInput.value = password;
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
                loginBtnText.textContent = 'Logging in...';
                loginBtn.style.opacity = '0.75';
                loginBtn.style.cursor = 'wait';
            });
        }
    </script>
</body>
</html>