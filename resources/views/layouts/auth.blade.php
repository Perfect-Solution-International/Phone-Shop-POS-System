<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Workspace Authentication') — {{ config('app.name') === 'Laravel' ? 'Phone Shop POS SaaS' : config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <style>
        :root {
            --brand-navy-950: #0a0f1d;
            --brand-navy-900: #0f172a;
            --brand-navy-800: #1e293b;
            --brand-indigo-950: #1e1b4b;
            --brand-indigo-700: #4338ca;
            --brand-indigo-600: #4f46e5;
            --brand-indigo-500: #6366f1;
            --brand-indigo-100: #e0e7ff;
            --brand-indigo-50: #eef2ff;
            --brand-violet-600: #7c3aed;
            --brand-violet-500: #8b5cf6;
            --brand-violet-50: #f5f3ff;
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
            --radius-xl: 18px;
            --radius-lg: 14px;
            --radius-md: 8px;
            --radius-sm: 6px;
            --shadow-card: 0 20px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
            --shadow-glow: 0 10px 25px -5px rgba(79, 70, 229, 0.25);
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
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, var(--brand-navy-950) 0%, var(--brand-indigo-950) 50%, var(--brand-navy-900) 100%);
            background-attachment: fixed;
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: clamp(1rem, 3.5vw, 2.75rem) clamp(0.75rem, 2.5vw, 1.5rem);
            color: var(--text-body);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            line-height: 1.5;
            overflow-x: hidden;
        }

        .auth-container {
            width: 100%;
            max-width: 480px;
            margin: 0 auto;
        }

        .auth-card {
            background: var(--surface-white);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-card);
            border: 1px solid rgba(255, 255, 255, 0.12);
            padding: clamp(1.5rem, 4.5vw, 2.5rem) clamp(1.125rem, 3.5vw, 2.25rem);
            position: relative;
            width: 100%;
        }

        .brand-header {
            text-align: center;
            margin-bottom: clamp(1.25rem, 3vw, 2rem);
        }

        .brand-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: clamp(44px, 10vw, 52px);
            height: clamp(44px, 10vw, 52px);
            background: linear-gradient(135deg, var(--brand-indigo-600) 0%, var(--brand-violet-600) 100%);
            border-radius: var(--radius-lg);
            color: #ffffff;
            margin-bottom: 0.75rem;
            box-shadow: 0 10px 18px -2px rgba(79, 70, 229, 0.35);
        }

        .brand-title {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--brand-indigo-600);
            margin-bottom: 0.35rem;
        }

        .page-title {
            font-size: clamp(1.3rem, 3.5vw, 1.5rem);
            font-weight: 800;
            color: var(--text-heading);
            letter-spacing: -0.025em;
            margin-bottom: 0.35rem;
            line-height: 1.25;
        }

        .page-subtitle {
            font-size: clamp(0.8125rem, 2vw, 0.875rem);
            color: var(--text-muted);
            line-height: 1.45;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.45rem;
        }

        .required-mark {
            color: var(--color-danger);
            margin-left: 0.2rem;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .form-input {
            width: 100%;
            min-height: 44px;
            padding: 0.75rem 1rem;
            font-size: 1rem; /* 16px prevents iOS Safari auto-zoom on mobile */
            font-family: inherit;
            color: var(--text-heading);
            background-color: #ffffff;
            border: 1.5px solid var(--surface-border);
            border-radius: var(--radius-md);
            transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
            outline: none;
            -webkit-appearance: none;
            appearance: none;
        }

        .form-input:focus {
            border-color: var(--brand-indigo-500);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
        }

        .form-input.is-invalid {
            border-color: var(--color-danger);
            background-color: #fffaf0;
        }

        .form-input.is-invalid:focus {
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15);
        }

        .input-toggle-btn {
            position: absolute;
            right: 0.5rem;
            min-height: 36px;
            padding: 0.35rem 0.65rem;
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 0.8125rem;
            font-weight: 600;
            cursor: pointer;
            border-radius: var(--radius-sm);
            transition: color 0.15s, background-color 0.15s;
            display: flex;
            align-items: center;
            touch-action: manipulation;
        }

        .input-toggle-btn:hover {
            color: var(--text-heading);
            background-color: var(--surface-muted);
        }

        .field-error {
            font-size: 0.8125rem;
            color: var(--color-danger);
            margin-top: 0.375rem;
            display: flex;
            align-items: center;
            gap: 0.35rem;
            font-weight: 500;
        }

        .field-error svg {
            width: 14px;
            height: 14px;
            flex-shrink: 0;
        }

        .field-help {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 0.375rem;
            line-height: 1.4;
        }

        .alert {
            padding: 0.875rem 1rem;
            border-radius: var(--radius-md);
            margin-bottom: 1.25rem;
            font-size: 0.875rem;
            line-height: 1.45;
            display: flex;
            align-items: flex-start;
            gap: 0.625rem;
            word-break: break-word;
        }

        .alert-success {
            background-color: var(--color-success-bg);
            border: 1px solid var(--color-success-border);
            color: #065f46;
        }

        .alert-danger {
            background-color: var(--color-danger-bg);
            border: 1px solid var(--color-danger-border);
            color: #991b1b;
        }

        .btn-submit {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            min-height: 46px;
            padding: 0.8125rem 1.5rem;
            font-size: 0.9375rem;
            font-weight: 700;
            color: #ffffff;
            background: linear-gradient(135deg, var(--brand-indigo-600) 0%, var(--brand-indigo-700) 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-md);
            cursor: pointer;
            box-shadow: var(--shadow-glow);
            transition: all 0.15s ease-in-out;
            margin-top: 1.35rem;
            outline: none;
            touch-action: manipulation;
        }

        .btn-submit:hover:not(:disabled) {
            background: linear-gradient(135deg, var(--brand-indigo-700) 0%, #3730a3 100%);
            box-shadow: 0 12px 24px -6px rgba(79, 70, 229, 0.4);
            transform: translateY(-1px);
        }

        .btn-submit:active:not(:disabled) {
            transform: translateY(0);
        }

        .btn-submit:disabled {
            opacity: 0.75;
            cursor: wait;
            transform: none !important;
        }

        .auth-footer-links {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--surface-border);
            font-size: 0.875rem;
            color: var(--text-muted);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .auth-link {
            color: var(--brand-indigo-600);
            font-weight: 600;
            text-decoration: none;
            transition: color 0.15s;
            display: inline-flex;
            align-items: center;
        }

        .auth-link:hover {
            color: var(--brand-indigo-700);
            text-decoration: underline;
        }

        .security-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            margin-top: 1.25rem;
            font-size: 0.75rem;
            color: #94a3b8;
            text-align: center;
            padding: 0 0.5rem;
        }

        .security-badge svg {
            width: 14px;
            height: 14px;
            color: var(--color-success);
            flex-shrink: 0;
        }

        /* Responsive Breakpoints */
        /* Tablet: 641px - 1024px */
        @media (min-width: 641px) and (max-width: 1024px) {
            .auth-container {
                max-width: 480px;
            }

            .auth-card {
                padding: 2.25rem 2rem;
            }
        }

        /* Mobile Standard: <= 640px */
        @media (max-width: 640px) {
            body {
                padding: 1.25rem 0.875rem;
                justify-content: flex-start;
            }

            .auth-container {
                max-width: 100%;
            }

            .auth-card {
                padding: 1.75rem 1.25rem;
                border-radius: var(--radius-lg);
            }

            .page-title {
                font-size: 1.375rem;
            }

            .auth-footer-links {
                flex-direction: column;
                gap: 0.35rem;
            }
        }

        /* Mobile Small: <= 380px */
        @media (max-width: 380px) {
            body {
                padding: 1rem 0.5rem;
            }

            .auth-card {
                padding: 1.35rem 0.875rem;
                border-radius: 12px;
            }

            .page-title {
                font-size: 1.25rem;
            }

            .form-input {
                padding: 0.65rem 0.75rem;
            }

            .btn-submit {
                padding: 0.75rem 1rem;
                font-size: 0.875rem;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="auth-container">
        <main class="auth-card" role="main">
            <header class="brand-header">
                <div class="brand-icon" aria-hidden="true">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
                        <line x1="12" y1="18" x2="12.01" y2="18"></line>
                    </svg>
                </div>
                <p class="brand-title">{{ $applicationName ?? 'Phone Shop POS SaaS' }}</p>
                <h1 class="page-title">@yield('heading')</h1>
                <p class="page-subtitle">@yield('subheading')</p>
            </header>

            @if (session('status'))
                <div class="alert alert-success" role="status">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; margin-top: 0.1rem;">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <div>{{ session('status') }}</div>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger" role="alert">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; margin-top: 0.1rem;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            @if ($errors->any() && !isset($hideGlobalErrors))
                <div class="alert alert-danger" role="alert">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; margin-top: 0.1rem;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <div>
                        <strong style="display: block; margin-bottom: 0.25rem;">Please check the following:</strong>
                        <ul style="padding-left: 1.25rem; margin: 0; font-size: 0.8125rem;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @yield('content')
        </main>

        <div class="security-badge" aria-label="Security status">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
            </svg>
            <span>Strict Tenant Isolation &amp; End-to-End Workspace Security</span>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
