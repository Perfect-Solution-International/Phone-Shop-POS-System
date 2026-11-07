<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>Register Shop Workspace — {{ $applicationName ?? 'SaaS Phone Shop POS System' }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

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
            --brand-cyan-500: #06b6d4;
            --brand-violet-600: #7c3aed;
            --surface-white: #ffffff;
            --surface-muted: #f8fafc;
            --surface-border: #e2e8f0;
            --text-heading: #0f172a;
            --text-body: #334155;
            --text-muted: #64748b;
            --color-danger: #ef4444;
            --color-danger-bg: #fef2f2;
            --color-danger-border: #fecaca;
            --color-success: #10b981;
            --color-success-bg: #ecfdf5;
            --color-success-border: #a7f3d0;
            --color-warning: #f59e0b;
            --radius-xl: 20px;
            --radius-lg: 14px;
            --radius-md: 10px;
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

        body {
            background-color: var(--brand-navy-950);
            background-image: 
                radial-gradient(ellipse 90% 60% at 50% -10%, rgba(99, 102, 241, 0.32), transparent 70%),
                radial-gradient(circle 520px at 85% 75%, rgba(124, 58, 237, 0.18), transparent 70%),
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
            padding: clamp(1.5rem, 3.5vw, 3rem) clamp(1rem, 2.5vw, 1.5rem);
            color: var(--text-body);
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
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
            width: clamp(320px, 80vw, 900px);
            height: clamp(320px, 80vw, 900px);
            background: radial-gradient(circle, rgba(99, 102, 241, 0.22) 0%, rgba(124, 58, 237, 0.1) 40%, transparent 70%);
            filter: blur(80px);
            pointer-events: none;
            z-index: 0;
        }

        /* 2-Column SaaS Card Shell */
        .register-container {
            width: 100%;
            max-width: 1040px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .register-card-2col {
            background: #ffffff;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-card);
            border: 1px solid rgba(255, 255, 255, 0.95);
            display: grid;
            grid-template-columns: 360px 1fr;
            width: 100%;
            overflow: hidden;
            position: relative;
        }

        .register-card-2col::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #4f46e5 0%, #7c3aed 50%, #06b6d4 100%);
            z-index: 5;
        }

        /* Left Feature Panel */
        .register-left-panel {
            background: linear-gradient(180deg, #090e1d 0%, #060913 100%);
            padding: clamp(2rem, 3.5vw, 2.75rem) 2rem;
            color: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            border-right: 1px solid rgba(255, 255, 255, 0.08);
        }

        .register-left-panel::after {
            content: '';
            position: absolute;
            top: -20%;
            right: -20%;
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.2) 0%, transparent 70%);
            filter: blur(50px);
            pointer-events: none;
        }

        .left-panel-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 2rem;
        }

        .left-brand-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #06b6d4 0%, #4f46e5 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            box-shadow: 0 4px 16px rgba(6, 182, 212, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.2);
            flex-shrink: 0;
        }

        .left-brand-text h2 {
            font-size: 1.05rem;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.01em;
            line-height: 1.2;
        }

        .left-brand-text span {
            font-size: 0.72rem;
            color: #38bdf8;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .left-hero-intro {
            margin-bottom: 2rem;
        }

        .left-hero-intro h3 {
            font-size: 1.35rem;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 0.5rem;
            line-height: 1.25;
            letter-spacing: -0.02em;
        }

        .left-hero-intro p {
            font-size: 0.85rem;
            color: #94a3b8;
            line-height: 1.5;
        }

        .features-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 1.15rem;
            margin-bottom: 2rem;
        }

        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .feature-icon-bubble {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: rgba(99, 102, 241, 0.15);
            border: 1px solid rgba(99, 102, 241, 0.3);
            color: #818cf8;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .feature-text strong {
            display: block;
            font-size: 0.82rem;
            color: #f1f5f9;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .feature-text span {
            font-size: 0.75rem;
            color: #94a3b8;
            line-height: 1.4;
            display: block;
        }

        .left-trial-banner {
            background: rgba(79, 70, 229, 0.15);
            border: 1px solid rgba(99, 102, 241, 0.35);
            border-radius: 12px;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .left-trial-banner svg {
            color: #f59e0b;
            flex-shrink: 0;
        }

        .left-trial-banner p {
            font-size: 0.78rem;
            color: #e0e7ff;
            line-height: 1.4;
            margin: 0;
        }

        /* Right Form Panel */
        .register-right-panel {
            padding: clamp(2rem, 3.5vw, 2.75rem) clamp(1.5rem, 3.5vw, 2.5rem);
            background: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .form-header-title {
            margin-bottom: 1.5rem;
        }

        .form-header-title h1 {
            font-size: 1.45rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
            margin-bottom: 0.25rem;
        }

        .form-header-title p {
            font-size: 0.85rem;
            color: #64748b;
        }

        .trial-feature-pill {
            background: linear-gradient(135deg, #eef2ff 0%, #f5f3ff 100%);
            border: 1px solid #c7d2fe;
            border-radius: 9999px;
            padding: 0.45rem 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.35rem;
            font-size: 0.8rem;
            color: #3730a3;
            width: 100%;
        }

        .trial-feature-pill svg {
            color: #f59e0b;
            flex-shrink: 0;
        }

        /* Form Grouping & Sections */
        .form-section-divider {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            margin: 1.35rem 0 0.85rem;
            font-size: 0.72rem;
            font-weight: 750;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
        }

        .form-section-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-group {
            margin-bottom: 1.15rem;
        }

        .form-label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8125rem;
            font-weight: 650;
            color: #1e293b;
            margin-bottom: 0.4rem;
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
            left: 0.95rem;
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
            height: 44px;
            padding: 0.65rem 0.95rem 0.65rem 2.75rem;
            border: 1.5px solid var(--surface-border);
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            font-weight: 500;
            color: #0f172a;
            background-color: #ffffff;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            outline: none;
            font-family: inherit;
        }

        .form-input:hover {
            border-color: #cbd5e1;
            background-color: #fafbfc;
        }

        .form-input:focus {
            border-color: var(--brand-indigo-600);
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.16);
        }

        .input-wrapper:focus-within .input-icon-prefix {
            color: var(--brand-indigo-600);
        }

        select.form-input {
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.85rem center;
            background-size: 15px 15px;
            cursor: pointer;
            padding-right: 2.5rem;
        }

        .form-input.has-toggle {
            padding-right: 4.5rem;
        }

        .form-input-toggle {
            position: absolute;
            right: 0.55rem;
            height: 30px;
            padding: 0 0.65rem;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            color: #475569;
            font-size: 0.72rem;
            font-weight: 700;
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
            font-size: 0.72rem;
            color: var(--text-muted);
            margin-top: 0.3rem;
            line-height: 1.35;
        }

        .field-error {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.72rem;
            color: var(--color-danger);
            font-weight: 600;
            margin-top: 0.35rem;
        }

        .field-error svg {
            width: 14px;
            height: 14px;
            min-width: 14px;
            min-height: 14px;
            max-width: 14px;
            max-height: 14px;
            flex-shrink: 0;
        }

        /* Live Password Strength Feedback */
        .password-strength-box {
            background: #f8fafc;
            border: 1px solid var(--surface-border);
            border-radius: var(--radius-md);
            padding: 0.85rem 1rem;
            margin-top: 0.65rem;
        }

        .strength-meter-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }

        .strength-label {
            font-weight: 750;
        }

        .strength-bars {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.4rem;
            height: 4px;
            margin-bottom: 0.65rem;
        }

        .strength-bar {
            background: #e2e8f0;
            border-radius: 9999px;
            transition: background-color 0.25s ease;
        }

        .strength-bar.weak { background-color: var(--color-danger); }
        .strength-bar.medium { background-color: var(--color-warning); }
        .strength-bar.strong { background-color: #3b82f6; }
        .strength-bar.secure { background-color: var(--color-success); }

        .rules-list {
            list-style: none;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.4rem 0.75rem;
            padding: 0;
            margin: 0;
        }

        .rule-item {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.72rem;
            color: var(--text-muted);
            transition: color 0.2s ease;
        }

        .rule-item svg {
            width: 13px;
            height: 13px;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }

        .rule-item.valid {
            color: #15803d;
            font-weight: 600;
        }

        .rule-item.valid svg {
            color: var(--color-success);
        }

        .match-indicator {
            font-size: 0.72rem;
            margin-top: 0.35rem;
            font-weight: 650;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        /* Subscription Plans Grid */
        .plans-selection-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 0.85rem;
            margin-bottom: 1.15rem;
        }

        .plan-card {
            border: 2px solid var(--surface-border);
            border-radius: var(--radius-md);
            padding: 1rem;
            background: #ffffff;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            position: relative;
            user-select: none;
        }

        .plan-card:hover {
            border-color: #cbd5e1;
            background: #fafbfc;
            transform: translateY(-2px);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
        }

        .plan-card.selected {
            border-color: var(--brand-indigo-600);
            background: #f5f3ff;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.16);
        }

        .plan-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.35rem;
        }

        .plan-card-title {
            font-size: 0.95rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
        }

        .plan-check-icon {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            border: 2px solid #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            color: transparent;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .plan-card.selected .plan-check-icon {
            border-color: var(--brand-indigo-600);
            background: var(--brand-indigo-600);
            color: #ffffff;
        }

        .plan-card-price-box {
            margin: 0.25rem 0 0.5rem;
        }

        .plan-card-price {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--brand-indigo-600);
        }

        .plan-card-period {
            font-size: 0.72rem;
            color: var(--text-muted);
            font-weight: 600;
        }

        .plan-trial-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
            border-radius: 9999px;
            font-size: 0.68rem;
            font-weight: 700;
            padding: 0.15rem 0.45rem;
            margin-bottom: 0.5rem;
            width: fit-content;
        }

        .plan-card-specs {
            font-size: 0.75rem;
            color: #334155;
            margin-bottom: 0.65rem;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .plan-spec-item {
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .plan-spec-item svg {
            color: #6366f1;
            flex-shrink: 0;
        }

        .plan-feature-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.3rem;
            margin-top: auto;
            padding-top: 0.5rem;
            border-top: 1px dashed #e2e8f0;
        }

        .plan-tag {
            font-size: 0.65rem;
            font-weight: 700;
            background: #eef2ff;
            color: #4338ca;
            padding: 0.12rem 0.4rem;
            border-radius: 4px;
        }

        .cycle-switcher-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            margin: 0 auto 1rem;
            background: #f1f5f9;
            border-radius: 9999px;
            padding: 0.25rem 0.4rem;
            width: fit-content;
        }

        .cycle-btn {
            border: none;
            background: transparent;
            padding: 0.35rem 0.85rem;
            font-size: 0.78rem;
            font-weight: 700;
            color: #64748b;
            border-radius: 9999px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .cycle-btn.active {
            background: #ffffff;
            color: #1e1b4b;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }

        /* Card Details Box */
        .card-details-box {
            background: #f8fafc;
            border: 1.5px solid #cbd5e1;
            border-radius: var(--radius-md);
            padding: 1.15rem 1.25rem;
            margin-bottom: 1.15rem;
        }

        .trial-due-banner {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: var(--radius-sm);
            padding: 0.75rem 0.85rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
            font-size: 0.8rem;
            color: #1e40af;
            line-height: 1.4;
        }

        .card-brand-pills {
            display: flex;
            gap: 0.35rem;
            align-items: center;
        }

        .card-brand-badge {
            font-size: 0.65rem;
            font-weight: 800;
            padding: 0.15rem 0.35rem;
            border-radius: 4px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #475569;
        }

        /* Payment Method Radio Selector */
        .payment-method-selector {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .payment-method-card {
            border: 2px solid #cbd5e1;
            background: #ffffff;
            border-radius: var(--radius-md);
            padding: 0.85rem 1rem;
            cursor: pointer;
            display: flex;
            align-items: flex-start;
            gap: 0.65rem;
            transition: all 0.2s ease;
        }

        .payment-method-card:hover {
            border-color: #94a3b8;
            background: #f8fafc;
        }

        .payment-method-card.selected {
            border-color: #4f46e5;
            background: #eef2ff;
            box-shadow: 0 0 0 1px #4f46e5;
        }

        .payment-method-card input[type="radio"] {
            margin-top: 0.2rem;
            cursor: pointer;
            accent-color: #4f46e5;
        }

        .payment-method-info strong {
            display: block;
            font-size: 0.86rem;
            font-weight: 750;
            color: #0f172a;
            line-height: 1.25;
            margin-bottom: 0.15rem;
        }

        .payment-method-card.selected .payment-method-info strong {
            color: #3730a3;
        }

        .payment-method-info span {
            display: block;
            font-size: 0.74rem;
            color: #64748b;
            line-height: 1.35;
        }

        .payment-method-card.selected .payment-method-info span {
            color: #6366f1;
        }

        /* Terms Row */
        .terms-row {
            display: flex;
            align-items: flex-start;
            gap: 0.65rem;
            margin: 1.15rem 0 1.35rem;
            cursor: pointer;
        }

        .terms-checkbox {
            margin-top: 0.2rem;
            width: 17px;
            height: 17px;
            border-radius: 4px;
            border: 1.5px solid #cbd5e1;
            cursor: pointer;
            accent-color: var(--brand-indigo-600);
            flex-shrink: 0;
        }

        .terms-label {
            font-size: 0.8rem;
            color: #475569;
            line-height: 1.45;
            user-select: none;
        }

        .terms-label a {
            color: var(--brand-indigo-600);
            font-weight: 600;
            text-decoration: underline;
        }

        /* Submit Hero Button */
        .btn-register-submit {
            width: 100%;
            height: 48px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: #ffffff;
            border: none;
            border-radius: var(--radius-md);
            font-size: 0.95rem;
            font-weight: 750;
            letter-spacing: -0.01em;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.65rem;
            cursor: pointer;
            box-shadow: var(--shadow-glow);
            transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            font-family: inherit;
        }

        .btn-register-submit:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 14px 28px -4px rgba(99, 102, 241, 0.6), 0 6px 14px -2px rgba(124, 58, 237, 0.4);
        }

        .btn-register-submit:active:not(:disabled) {
            transform: translateY(0);
        }

        .btn-spinner {
            display: none;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Footer Navigation */
        .auth-footer-nav {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.85rem;
            color: #94a3b8;
        }

        .footer-nav-link {
            color: #818cf8;
            font-weight: 700;
            text-decoration: none;
            margin-left: 0.35rem;
            transition: color 0.15s ease;
        }

        .footer-nav-link:hover {
            color: #c7d2fe;
            text-decoration: underline;
        }

        /* Active Session Indicator */
        .active-session-box {
            background: #eef2ff;
            border: 1.5px solid #c7d2fe;
            border-radius: 12px;
            padding: 0.85rem 1rem;
            margin-bottom: 1.25rem;
            font-size: 0.8125rem;
        }

        .alert-box {
            border-radius: var(--radius-md);
            padding: 0.85rem 1rem;
            font-size: 0.8125rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: flex-start;
            gap: 0.65rem;
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

        /* Responsive Breakpoints */
        @media (max-width: 860px) {
            .register-card-2col {
                grid-template-columns: 1fr;
            }

            .register-left-panel {
                padding: 1.75rem 1.5rem;
                border-right: none;
                border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            }

            .features-list {
                display: none;
            }

            .left-hero-intro {
                margin-bottom: 1rem;
            }
        }

        @media (max-width: 580px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .rules-list {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="ambient-aurora" aria-hidden="true"></div>

    <div class="register-container">
        <div class="register-card-2col">
            <!-- Left Branding & Feature Showcase Panel -->
            <div class="register-left-panel">
                <div>
                    <div class="left-panel-brand">
                        <div class="left-brand-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
                                <line x1="12" y1="18" x2="12.01" y2="18"></line>
                            </svg>
                        </div>
                        <div class="left-brand-text">
                            <h2>{{ $applicationName ?? 'Phone Shop POS' }}</h2>
                            <span>Enterprise Retail Cloud</span>
                        </div>
                    </div>

                    <div class="left-hero-intro">
                        <h3>Power Your Phone Retail &amp; Repair Center</h3>
                        <p>Complete retail workspace with instant dual IMEI management, fast checkout, and customer repair tracking.</p>
                    </div>

                    <ul class="features-list">
                        <li class="feature-item">
                            <div class="feature-icon-bubble">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M2 9h20"></path><rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="M6 16h4"></path><path d="M14 16h4"></path></svg>
                            </div>
                            <div class="feature-text">
                                <strong>Fast POS Terminal Counter</strong>
                                <span>Barcode lookup, split cash/card tender, and printable thermal receipts.</span>
                            </div>
                        </li>
                        <li class="feature-item">
                            <div class="feature-icon-bubble">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="5" y="2" width="14" height="20" rx="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                            </div>
                            <div class="feature-text">
                                <strong>Dual IMEI Serialized Tracking</strong>
                                <span>Stock intake, sold phone history archive, and automatic warranty issuance.</span>
                            </div>
                        </li>
                        <li class="feature-item">
                            <div class="feature-icon-bubble">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>
                            </div>
                            <div class="feature-text">
                                <strong>8-Stage Repair Ticketing</strong>
                                <span>Technician queue, spare parts costing, and customer SMS/WhatsApp alerts.</span>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="left-trial-banner">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                    </svg>
                    <p>
                        <strong>7-Day Free Trial</strong> included.<br>
                        No credit card required. Cancel or upgrade anytime.
                    </p>
                </div>
            </div>

            <!-- Right Registration Form Panel -->
            <div class="register-right-panel">
                <div>
                    <div class="form-header-title">
                        <h1>Register Shop Workspace</h1>
                        <p>Set up your isolated store management account in less than a minute.</p>
                    </div>

                    @if (isset($currentUser) && $currentUser)
                        <div class="active-session-box">
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
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
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

                    <form id="registrationForm" method="POST" action="{{ route('register.store') }}" enctype="multipart/form-data" novalidate>
                        @csrf

                        <!-- Section: Store Details -->
                        <div class="form-section-divider">
                            <span>1. Store &amp; Owner Profile</span>
                        </div>

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
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
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
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
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
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
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
                            @if(config('auth.verification.enabled', false))
                                <p id="email_help" class="field-help">We will send a verification link to confirm your ownership.</p>
                            @else
                                <p id="email_help" class="field-help">Instant access — email verification is currently optional.</p>
                            @endif
                            @error('email')
                                <p id="email_error" class="field-error" role="alert">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                    <span>{{ $message }}</span>
                                </p>
                            @enderror
                        </div>

                        <!-- Section: Location & Business Details -->
                        <div class="form-section-divider">
                            <span>2. Location &amp; Credentials</span>
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
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
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
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
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
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
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
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                    <span>{{ $message }}</span>
                                </p>
                            @enderror
                        </div>

                        <!-- 2-Column Row: Password & Confirm Password -->
                        <div class="form-grid">
                            <!-- Password -->
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
                                @error('password')
                                    <p id="password_error" class="field-error" role="alert">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
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
                                    <span>8+ characters</span>
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

                        <!-- Section 3: Subscription Plan -->
                        <div class="form-section-divider">
                            <span>3. Choose Subscription Plan</span>
                        </div>

                        <!-- Billing Cycle Switcher -->
                        <div class="cycle-switcher-wrapper">
                            <button type="button" class="cycle-btn active" id="btnCycleMonthly" onclick="setBillingCycle('monthly')">
                                Monthly Billing
                            </button>
                            <button type="button" class="cycle-btn" id="btnCycleAnnual" onclick="setBillingCycle('annual')">
                                Annual Billing <span style="background: #10b981; color: #ffffff; font-size: 0.65rem; padding: 0.1rem 0.35rem; border-radius: 9999px; margin-left: 0.25rem;">Save ~15%</span>
                            </button>
                        </div>

                        <input type="hidden" name="billing_cycle" id="billing_cycle_input" value="{{ old('billing_cycle', 'monthly') }}">
                        <input type="hidden" name="plan_id" id="plan_id_input" value="{{ old('plan_id', $plans->firstWhere('slug', 'starter')?->id ?? $plans->first()?->id) }}">

                        <!-- Plans Grid -->
                        <div class="plans-selection-grid">
                            @foreach ($plans as $plan)
                                @php
                                    $isSelected = old('plan_id', $plans->firstWhere('slug', 'starter')?->id ?? $plans->first()?->id) == $plan->id;
                                @endphp
                                <div class="plan-card {{ $isSelected ? 'selected' : '' }}" 
                                     id="plan_card_{{ $plan->id }}" 
                                     onclick="selectPlan({{ $plan->id }}, '{{ addslashes($plan->name) }}')">
                                    <div class="plan-card-header">
                                        <h4 class="plan-card-title">{{ $plan->name }}</h4>
                                        <div class="plan-check-icon">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                        </div>
                                    </div>

                                    <div class="plan-card-price-box">
                                        <span class="plan-card-price" id="price_display_{{ $plan->id }}" data-monthly="{{ $plan->monthly_price }}" data-annual="{{ $plan->annual_price ?? ($plan->monthly_price * 12) }}">
                                            LKR {{ number_format($plan->monthly_price, 0) }}
                                        </span>
                                        <span class="plan-card-period" id="period_display_{{ $plan->id }}">/ month</span>
                                    </div>

                                    <div class="plan-trial-badge">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                        {{ $plan->trial_days ?: 7 }}-Day Free Trial
                                    </div>

                                    <div class="plan-card-specs">
                                        <div class="plan-spec-item">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg>
                                            <span><strong>{{ $plan->max_branches }}</strong> branch{{ $plan->max_branches > 1 ? 'es' : '' }}</span>
                                        </div>
                                        <div class="plan-spec-item">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                                            <span>Up to <strong>{{ $plan->max_staff }}</strong> staff members</span>
                                        </div>
                                    </div>

                                    <div class="plan-feature-tags">
                                        @if($plan->has_imei_tracking)<span class="plan-tag">IMEI</span>@endif
                                        @if($plan->has_repair_management)<span class="plan-tag">Repairs</span>@endif
                                        @if($plan->has_warranty_management)<span class="plan-tag">Warranty</span>@endif
                                        @if($plan->has_multi_branch)<span class="plan-tag" style="background: #fdf2f8; color: #be185d;">Multi-Branch</span>@endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @error('plan_id')
                            <p class="field-error" style="margin-top: -0.5rem; margin-bottom: 1rem;" role="alert">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror

                        <!-- Section 4: Card Details for 7-Day Free Trial -->
                        <div class="form-section-divider">
                            <span>4. Payment Card — 7-Day Free Trial</span>
                        </div>

                        <!-- Payment Method Selector -->
                        <div class="payment-method-selector">
                            <label class="payment-method-card {{ old('payment_method', 'card') === 'card' ? 'selected' : '' }}" id="method_card_label" onclick="switchRegisterPaymentMethod('card')">
                                <input type="radio" name="payment_method" value="card" id="method_card_radio" {{ old('payment_method', 'card') === 'card' ? 'checked' : '' }}>
                                <div class="payment-method-info">
                                    <strong>Credit / Debit Card</strong>
                                    <span>Instant verification &amp; 7-day free trial</span>
                                </div>
                            </label>

                            <label class="payment-method-card {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}" id="method_bank_label" onclick="switchRegisterPaymentMethod('bank_transfer')">
                                <input type="radio" name="payment_method" value="bank_transfer" id="method_bank_radio" {{ old('payment_method') === 'bank_transfer' ? 'checked' : '' }}>
                                <div class="payment-method-info">
                                    <strong>Bank Transfer Slip</strong>
                                    <span>Upload slip &amp; start 7-day free trial</span>
                                </div>
                            </label>
                        </div>

                        <!-- Container 1: Card Details -->
                        <div id="cardPaymentContainer" class="card-details-box" style="{{ old('payment_method', 'card') === 'bank_transfer' ? 'display: none;' : '' }}">
                            <div class="trial-due-banner">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0; margin-top: 0.1rem;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                <div>
                                    <strong>Payment Card — 7-Day Free Trial:</strong>
                                    You will <strong>NOT be charged today (LKR 0.00 due now)</strong>. Your card is saved securely to verify your retail workspace and will only be billed when your 7-day free trial ends, unless cancelled anytime.
                                </div>
                            </div>

                            <!-- Cardholder Name -->
                            <div class="form-group">
                                <label for="cardholder_name" class="form-label">
                                    <span>Cardholder Name <span class="required-mark">*</span></span>
                                </label>
                                <div class="input-wrapper">
                                    <span class="input-icon-prefix" aria-hidden="true">
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                    </span>
                                    <input
                                        id="cardholder_name"
                                        name="cardholder_name"
                                        type="text"
                                        class="form-input @error('cardholder_name') is-invalid @enderror"
                                        value="{{ old('cardholder_name') }}"
                                        placeholder="Name as printed on card"
                                        autocomplete="cc-name"
                                    >
                                </div>
                                @error('cardholder_name')
                                    <p class="field-error" role="alert">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                        <span>{{ $message }}</span>
                                    </p>
                                @enderror
                            </div>

                            <!-- Card Number -->
                            <div class="form-group">
                                <label for="card_number" class="form-label">
                                    <span>Card Number <span class="required-mark">*</span></span>
                                    <div class="card-brand-pills">
                                        <span class="card-brand-badge">VISA</span>
                                        <span class="card-brand-badge">MC</span>
                                        <span class="card-brand-badge">AMEX</span>
                                    </div>
                                </label>
                                <div class="input-wrapper">
                                    <span class="input-icon-prefix" aria-hidden="true">
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                                    </span>
                                    <input
                                        id="card_number"
                                        name="card_number"
                                        type="text"
                                        class="form-input @error('card_number') is-invalid @enderror"
                                        value="{{ old('card_number') }}"
                                        placeholder="4242 •••• •••• 4242"
                                        maxlength="23"
                                        autocomplete="cc-number"
                                        oninput="formatCardNumber(this)"
                                    >
                                </div>
                                @error('card_number')
                                    <p class="field-error" role="alert">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                        <span>{{ $message }}</span>
                                    </p>
                                @enderror
                            </div>

                            <!-- Expiry & CVV -->
                            <div class="form-grid" style="margin-bottom: 0;">
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label for="card_expiry" class="form-label">
                                        <span>Expiry Date <span class="required-mark">*</span></span>
                                    </label>
                                    <div class="input-wrapper">
                                        <span class="input-icon-prefix" aria-hidden="true">
                                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                        </span>
                                        <input
                                            id="card_expiry"
                                            name="card_expiry"
                                            type="text"
                                            class="form-input @error('card_expiry') is-invalid @enderror"
                                            value="{{ old('card_expiry') }}"
                                            placeholder="MM/YY"
                                            maxlength="5"
                                            autocomplete="cc-exp"
                                            oninput="formatCardExpiry(this)"
                                        >
                                    </div>
                                    @error('card_expiry')
                                        <p class="field-error" role="alert">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                            <span>{{ $message }}</span>
                                        </p>
                                    @enderror
                                </div>

                                <div class="form-group" style="margin-bottom: 0;">
                                    <label for="card_cvv" class="form-label">
                                        <span>CVV / CVC <span class="required-mark">*</span></span>
                                    </label>
                                    <div class="input-wrapper">
                                        <span class="input-icon-prefix" aria-hidden="true">
                                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                        </span>
                                        <input
                                            id="card_cvv"
                                            name="card_cvv"
                                            type="password"
                                            class="form-input @error('card_cvv') is-invalid @enderror"
                                            value="{{ old('card_cvv') }}"
                                            placeholder="123"
                                            maxlength="4"
                                            autocomplete="cc-csc"
                                        >
                                    </div>
                                    @error('card_cvv')
                                        <p class="field-error" role="alert">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                            <span>{{ $message }}</span>
                                        </p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Container 2: Bank Slip Upload Details -->
                        <div id="bankPaymentContainer" class="card-details-box" style="{{ old('payment_method') === 'bank_transfer' ? '' : 'display: none;' }}">
                            <div class="trial-due-banner" style="background: #f0fdf4; border-color: #86efac; color: #166534;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" style="flex-shrink: 0; margin-top: 0.1rem;"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                <div>
                                    <strong>7-Day Free Trial Activated (Bank Transfer):</strong>
                                    Your shop workspace is activated immediately with a <strong>7-Day Free Trial (LKR 0.00 due today)</strong>. You can start setting up your inventory and sales right away. Please upload your deposit slip below so our team can approve your recurring subscription under Bank Approvals.
                                </div>
                            </div>

                            <!-- Platform Bank Account Information Box -->
                            <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 0.9rem 1rem; margin-bottom: 1rem;">
                                <div style="font-weight: 750; font-size: 0.82rem; color: #1e293b; margin-bottom: 0.45rem; display: flex; align-items: center; gap: 0.4rem;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2.2"><path d="M3 21h18"></path><path d="M5 21V10l7-5 7 5v11"></path><line x1="9" y1="14" x2="9" y2="17"></line><line x1="15" y1="14" x2="15" y2="17"></line></svg>
                                    <span>Official Bank Deposit Account Details:</span>
                                </div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.4rem 1rem; font-size: 0.78rem; color: #334155; line-height: 1.45;">
                                    <div><strong>Bank:</strong> Bank of Ceylon (BOC)</div>
                                    <div><strong>Account No:</strong> 0089271638</div>
                                    <div><strong>Branch:</strong> Corporate Branch Colombo</div>
                                    <div><strong>Account Name:</strong> SaaS POS Lanka (Pvt) Ltd</div>
                                </div>
                            </div>

                            <!-- Reference Number -->
                            <div class="form-group">
                                <label for="reference_number" class="form-label">
                                    <span>Bank Deposit / Transfer Reference No. <span class="required-mark">*</span></span>
                                </label>
                                <div class="input-wrapper">
                                    <span class="input-icon-prefix" aria-hidden="true">
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                    </span>
                                    <input
                                        id="reference_number"
                                        name="reference_number"
                                        type="text"
                                        class="form-input @error('reference_number') is-invalid @enderror"
                                        value="{{ old('reference_number') }}"
                                        placeholder="e.g. BOC-TXN-98421"
                                    >
                                </div>
                                @error('reference_number')
                                    <p class="field-error" role="alert">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                        <span>{{ $message }}</span>
                                    </p>
                                @enderror
                            </div>

                            <!-- Receipt File Upload -->
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="receipt" class="form-label">
                                    <span>Upload Bank Deposit Slip / Receipt</span>
                                </label>
                                <div class="input-wrapper" style="padding: 0.35rem 0.5rem; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px;">
                                    <input
                                        id="receipt"
                                        name="receipt"
                                        type="file"
                                        accept="image/*,.pdf"
                                        class="form-control-file @error('receipt') is-invalid @enderror"
                                        style="width: 100%; font-size: 0.8125rem; color: #334155;"
                                    >
                                </div>
                                <span class="field-help">Upload receipt in JPG, PNG, or PDF (Max 5MB). Verified under Bank Approvals.</span>
                                @error('receipt')
                                    <p class="field-error" role="alert">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                        <span>{{ $message }}</span>
                                    </p>
                                @enderror
                            </div>
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
                            <p id="terms_error" class="field-error" style="margin-top: -0.75rem; margin-bottom: 1.15rem;" role="alert">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror

                        <!-- Hero Submit Button -->
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
                </div>

                <!-- Footer Link -->
                <footer class="auth-footer-nav">
                    <span>Already have a workspace?</span>
                    <a href="{{ route('login') }}" class="footer-nav-link">Login to your shop &rarr;</a>
                </footer>
            </div>
        </div>
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
            const hasLen = val.length >= 8;
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
                bar1.classList.add('weak');
            } else if (score === 2) {
                strengthText.textContent = 'Fair';
                strengthText.style.color = 'var(--color-warning)';
                bar1.classList.add('medium');
                bar2.classList.add('medium');
            } else if (score === 3) {
                strengthText.textContent = 'Good';
                strengthText.style.color = '#3b82f6';
                bar1.classList.add('strong');
                bar2.classList.add('strong');
                bar3.classList.add('strong');
            } else {
                strengthText.textContent = 'Strong & Secure';
                strengthText.style.color = 'var(--color-success)';
                bars.forEach(b => b.classList.add('secure'));
            }

            checkMatch();
        }

        function setRuleState(el, isValid) {
            if (isValid) {
                el.classList.add('valid');
                el.querySelector('svg').style.color = 'var(--color-success)';
            } else {
                el.classList.remove('valid');
                el.querySelector('svg').style.color = 'currentColor';
            }
        }

        function checkMatch() {
            const passVal = passwordInput.value;
            const confirmVal = confirmInput.value;

            if (!confirmVal) {
                matchIndicator.style.display = 'none';
                return;
            }

            matchIndicator.style.display = 'flex';
            if (passVal === confirmVal) {
                matchIndicator.style.color = 'var(--color-success)';
                matchIndicator.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg> Passwords match';
            } else {
                matchIndicator.style.color = 'var(--color-danger)';
                matchIndicator.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg> Passwords do not match';
            }
        }

        passwordInput.addEventListener('input', updatePasswordStrength);
        confirmInput.addEventListener('input', checkMatch);

        // Subscription Plan & Card Interactivity
        function selectPlan(planId, planName) {
            document.querySelectorAll('.plan-card').forEach(el => el.classList.remove('selected'));
            const card = document.getElementById('plan_card_' + planId);
            if (card) card.classList.add('selected');
            document.getElementById('plan_id_input').value = planId;
        }

        function setBillingCycle(cycle) {
            const btnMonthly = document.getElementById('btnCycleMonthly');
            const btnAnnual = document.getElementById('btnCycleAnnual');
            const cycleInput = document.getElementById('billing_cycle_input');

            if (cycle === 'annual') {
                btnAnnual.classList.add('active');
                btnMonthly.classList.remove('active');
                cycleInput.value = 'annual';
            } else {
                btnMonthly.classList.add('active');
                btnAnnual.classList.remove('active');
                cycleInput.value = 'monthly';
            }

            document.querySelectorAll('.plan-card-price').forEach(el => {
                const monthly = parseFloat(el.getAttribute('data-monthly')) || 0;
                const annual = parseFloat(el.getAttribute('data-annual')) || (monthly * 12);
                const planId = el.id.replace('price_display_', '');
                const periodEl = document.getElementById('period_display_' + planId);

                if (cycle === 'annual') {
                    el.textContent = 'LKR ' + Number(annual).toLocaleString(undefined, {minimumFractionDigits: 0});
                    if (periodEl) periodEl.textContent = '/ year';
                } else {
                    el.textContent = 'LKR ' + Number(monthly).toLocaleString(undefined, {minimumFractionDigits: 0});
                    if (periodEl) periodEl.textContent = '/ month';
                }
            });
        }

        function formatCardNumber(input) {
            let val = input.value.replace(/\D/g, '');
            let formatted = val.match(/.{1,4}/g)?.join(' ') || val;
            input.value = formatted.substring(0, 19);
        }

        function formatCardExpiry(input) {
            let val = input.value.replace(/\D/g, '');
            if (val.length >= 2) {
                input.value = val.substring(0, 2) + '/' + val.substring(2, 4);
            } else {
                input.value = val;
            }
        }

        // Auto-fill cardholder name from owner full name if untouched
        const nameInput = document.getElementById('name');
        const cardholderInput = document.getElementById('cardholder_name');
        if (nameInput && cardholderInput) {
            nameInput.addEventListener('input', function() {
                if (!cardholderInput.dataset.touched) {
                    cardholderInput.value = nameInput.value;
                }
            });
            cardholderInput.addEventListener('input', function() {
                cardholderInput.dataset.touched = 'true';
            });
        }

        // Payment Method Switcher (Card vs Bank Transfer)
        function switchRegisterPaymentMethod(method) {
            const cardBox = document.getElementById('cardPaymentContainer');
            const bankBox = document.getElementById('bankPaymentContainer');
            const cardLabel = document.getElementById('method_card_label');
            const bankLabel = document.getElementById('method_bank_label');
            const cardRadio = document.getElementById('method_card_radio');
            const bankRadio = document.getElementById('method_bank_radio');
            const cardName = document.getElementById('cardholder_name');
            const cardNum = document.getElementById('card_number');
            const cardExp = document.getElementById('card_expiry');
            const cardCvv = document.getElementById('card_cvv');
            const bankRef = document.getElementById('reference_number');

            if (method === 'bank_transfer') {
                if (cardBox) cardBox.style.display = 'none';
                if (bankBox) bankBox.style.display = 'block';
                if (cardLabel) cardLabel.classList.remove('selected');
                if (bankLabel) bankLabel.classList.add('selected');
                if (bankRadio) bankRadio.checked = true;

                if (cardName) cardName.removeAttribute('required');
                if (cardNum) cardNum.removeAttribute('required');
                if (cardExp) cardExp.removeAttribute('required');
                if (cardCvv) cardCvv.removeAttribute('required');
                if (bankRef) bankRef.setAttribute('required', 'required');
            } else {
                if (cardBox) cardBox.style.display = 'block';
                if (bankBox) bankBox.style.display = 'none';
                if (cardLabel) cardLabel.classList.add('selected');
                if (bankLabel) bankLabel.classList.remove('selected');
                if (cardRadio) cardRadio.checked = true;

                if (cardName) cardName.setAttribute('required', 'required');
                if (cardNum) cardNum.setAttribute('required', 'required');
                if (cardExp) cardExp.setAttribute('required', 'required');
                if (cardCvv) cardCvv.setAttribute('required', 'required');
                if (bankRef) bankRef.removeAttribute('required');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const currentMethod = document.querySelector('input[name="payment_method"]:checked')?.value || 'card';
            switchRegisterPaymentMethod(currentMethod);
        });

        // Form Submit Loading Indicator
        const form = document.getElementById('registrationForm');
        const submitBtn = document.getElementById('submitBtn');
        const submitSpinner = document.getElementById('submitSpinner');
        const submitBtnText = document.getElementById('submitBtnText');
        const submitArrow = document.getElementById('submitArrow');

        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitSpinner.style.display = 'inline-block';
            submitArrow.style.display = 'none';
            submitBtnText.textContent = 'Provisioning Workspace...';
            submitBtn.style.opacity = '0.85';
            submitBtn.style.cursor = 'wait';
        });
    </script>
</body>
</html>
