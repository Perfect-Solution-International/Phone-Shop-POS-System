<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Shop Console') — {{ auth()->user()->shop->name ?? 'Phone Shop POS' }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --brand-navy-950: #060913;
            --brand-navy-900: #0a0f1d;
            --brand-navy-850: #10172a;
            --brand-navy-800: #1e293b;
            --brand-indigo-600: #4f46e5;
            --brand-indigo-500: #6366f1;
            --brand-indigo-700: #4338ca;
            --brand-cyan-500: #06b6d4;
            --brand-cyan-400: #22d3ee;
            --surface-card: #ffffff;
            --surface-bg: #f8fafc;
            --surface-border: #e2e8f0;
            --surface-border-subtle: #f1f5f9;
            --text-heading: #0f172a;
            --text-body: #334155;
            --text-muted: #64748b;
            --color-success: #10b981;
            --color-warning: #f59e0b;
            --color-danger: #ef4444;
            --color-info: #3b82f6;
            --radius-xl: 18px;
            --radius-lg: 14px;
            --radius-md: 10px;
            --radius-sm: 6px;
            --shadow-card: 0 1px 3px 0 rgba(0, 0, 0, 0.04), 0 8px 24px -4px rgba(0, 0, 0, 0.05);
            --shadow-hover: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 16px 32px -4px rgba(0, 0, 0, 0.08);
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        /* Modern Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.4);
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 116, 139, 0.7);
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: var(--surface-bg);
            color: var(--text-body);
            min-height: 100vh;
            display: flex;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* High-Tech Dark Sidebar */
        .shop-sidebar {
            width: 268px;
            background: linear-gradient(180deg, #070b16 0%, #050811 100%);
            color: #ffffff;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            position: sticky;
            top: 0;
            height: 100vh;
            overflow: hidden;
            box-shadow: 4px 0 24px -4px rgba(0, 0, 0, 0.35);
            z-index: 30;
        }

        .shop-brand {
            padding: 1.35rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.85rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.07);
            background: rgba(255, 255, 255, 0.02);
            position: relative;
        }

        .shop-brand-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, #06b6d4 0%, #4f46e5 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            box-shadow: 0 4px 16px rgba(6, 182, 212, 0.45), inset 0 1px 1px rgba(255, 255, 255, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.2);
            flex-shrink: 0;
        }

        .shop-brand-text {
            display: flex;
            flex-direction: column;
            overflow: hidden;
            min-width: 0;
        }

        .shop-brand-title {
            font-size: 0.95rem;
            font-weight: 800;
            color: #ffffff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            letter-spacing: -0.01em;
        }

        .shop-brand-subtitle {
            font-size: 0.6875rem;
            color: #38bdf8;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.35rem;
            margin-top: 2px;
        }

        .shop-brand-subtitle::before {
            content: '';
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background-color: #10b981;
            box-shadow: 0 0 8px #10b981;
        }

        .shop-nav {
            padding: 1rem 0.85rem;
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
            flex: 1;
            overflow-y: auto;
        }

        .shop-nav-header {
            font-size: 0.6875rem;
            font-weight: 750;
            text-transform: uppercase;
            letter-spacing: 0.09em;
            color: #475569;
            padding: 0.85rem 0.65rem 0.35rem;
        }

        .shop-nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.625rem 0.8rem;
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 550;
            border-radius: 10px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .shop-nav-item svg {
            flex-shrink: 0;
            opacity: 0.85;
            transition: transform 0.2s ease, opacity 0.2s ease;
        }

        .shop-nav-item:hover {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.06);
            transform: translateX(3px);
        }

        .shop-nav-item:hover svg {
            opacity: 1;
            transform: scale(1.08);
        }

        .shop-nav-item.active {
            color: #ffffff;
            background: linear-gradient(90deg, rgba(79, 70, 229, 0.22) 0%, rgba(6, 182, 212, 0.12) 100%);
            border-left: 3px solid #06b6d4;
            font-weight: 650;
            box-shadow: inset 0 0 16px rgba(6, 182, 212, 0.06);
        }

        .shop-nav-item.active svg {
            color: #38bdf8;
            opacity: 1;
        }

        .shop-nav-badge {
            margin-left: auto;
            padding: 0.15rem 0.55rem;
            border-radius: 9999px;
            font-size: 0.6875rem;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.1);
            color: #e2e8f0;
            letter-spacing: 0.02em;
        }

        /* Modern Glass User Footer */
        .shop-user-footer {
            flex-shrink: 0;
            padding: 0.95rem 1.1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(4, 7, 14, 0.7);
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.65rem;
        }

        .shop-user-meta {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            overflow: hidden;
            min-width: 0;
        }

        .shop-user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, #06b6d4 0%, #4f46e5 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 0.875rem;
            font-weight: 800;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.2);
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
            letter-spacing: 0.06em;
            font-weight: 700;
            margin-top: 2px;
        }

        .shop-logout-btn {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.28);
            color: #fca5a5;
            padding: 0.4rem 0.7rem;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 650;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            flex-shrink: 0;
        }

        .shop-logout-btn:hover {
            background: #ef4444;
            color: #ffffff;
            border-color: #ef4444;
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.4);
        }

        /* Main Workspace Container */
        .shop-workspace {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            overflow-x: hidden;
            background: #f8fafc;
        }

        /* Glassmorphic Responsive Topbar */
        .shop-topbar {
            min-height: 64px;
            height: auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.85);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.65rem 1rem;
            padding: 0.75rem clamp(0.85rem, 2vw, 1.75rem);
            position: sticky;
            top: 0;
            z-index: 25;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.03);
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }

        .shop-topbar-left {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            min-width: 0;
            flex: 1 1 auto;
        }

        .shop-topbar-title {
            font-size: clamp(0.95rem, 1.5vw, 1.15rem);
            font-weight: 800;
            color: var(--text-heading);
            display: flex;
            align-items: center;
            gap: 0.5rem 0.65rem;
            letter-spacing: -0.02em;
            flex-wrap: wrap;
            line-height: 1.35;
            min-width: 0;
        }

        .page-title-text {
            line-height: 1.35;
            display: inline;
            word-break: normal;
            overflow-wrap: break-word;
        }

        .topbar-branch-name {
            display: inline-block;
            max-width: 140px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            vertical-align: middle;
        }

        .shop-topbar-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-left: auto;
            flex-shrink: 0;
        }

        .shop-topbar-actions .btn {
            white-space: nowrap;
        }

        .shop-plan-pill {
            font-size: 0.6875rem;
            padding: 0.22rem 0.65rem;
            border-radius: 9999px;
            font-weight: 750;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            background: linear-gradient(135deg, #e0f2fe 0%, #e0e7ff 100%);
            color: #0369a1;
            border: 1px solid #bae6fd;
            white-space: nowrap;
        }

        .shop-content {
            padding: clamp(1rem, 2.5vw, 2rem);
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

        /* Global Metrics Grid & Modern Stat Cards */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 1.25rem;
            margin-bottom: 1.75rem;
        }

        .stat-card {
            background: #ffffff;
            border: 1px solid var(--surface-border);
            border-radius: 14px;
            padding: 1.35rem 1.4rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.07);
            border-color: #cbd5e1;
        }

        .stat-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }

        .stat-card-title {
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-card-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .stat-card-value {
            font-size: 1.625rem;
            font-weight: 800;
            color: var(--text-heading);
            line-height: 1.2;
            letter-spacing: -0.02em;
            margin-bottom: 0.35rem;
        }

        .stat-card-change {
            font-size: 0.75rem;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 0.35rem;
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

        /* Mobile Off-Canvas Sidebar & Overlay */
        .shop-sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(6, 9, 19, 0.65);
            backdrop-filter: blur(2px);
            z-index: 1040;
            opacity: 0;
            transition: opacity 0.25s ease;
        }

        .shop-sidebar-overlay.active {
            display: block;
            opacity: 1;
        }

        .mobile-sidebar-toggle {
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

        .mobile-sidebar-toggle:hover {
            background: #f1f5f9;
        }

        .sidebar-close-btn {
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

        .sidebar-close-btn:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.12);
        }

        /* Responsive Breakpoints */
        @media (max-width: 1250px) {
            .shop-topbar {
                padding: 0.65rem 1rem;
                gap: 0.5rem 0.75rem;
            }
            .shop-topbar-title {
                font-size: 1rem;
            }
            .shop-topbar-actions {
                gap: 0.4rem;
            }
            .shop-topbar-actions .topbar-action-text {
                font-size: 0.75rem;
            }
            .shop-topbar-actions .btn {
                padding: 0.32rem 0.58rem;
            }
            .topbar-branch-name {
                max-width: 95px;
            }
        }

        @media (max-width: 1080px) {
            body {
                position: relative;
                overflow-x: hidden;
            }

            .mobile-sidebar-toggle {
                display: inline-flex;
            }

            .sidebar-close-btn {
                display: block;
            }

            .shop-sidebar {
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                width: 280px;
                max-width: 85vw;
                height: 100vh;
                z-index: 1050;
                transform: translateX(-100%);
                transition: transform 0.28s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: none;
            }

            .shop-sidebar.open {
                transform: translateX(0);
                box-shadow: 12px 0 35px -5px rgba(0, 0, 0, 0.5);
            }

            .shop-workspace {
                width: 100%;
                min-width: 0;
            }

            .shop-topbar {
                min-height: 58px;
                height: auto;
                padding: 0.65rem 1rem;
                gap: 0.5rem;
            }

            .shop-content {
                padding: 1.15rem 1rem;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }

            /* Responsive 2-column grids */
            .grid-2col, [style*="grid-template-columns: 1fr 1fr"], [style*="grid-template-columns: 1fr 380px"], [style*="grid-template-columns: 360px 1fr"] {
                grid-template-columns: 1fr !important;
            }
        }

        @media (max-width: 860px) {
            .shop-topbar-actions .topbar-action-text {
                display: none;
            }
            .shop-topbar-actions .btn {
                padding: 0.35rem 0.6rem;
                font-size: 0.75rem;
            }
            .shop-topbar-title {
                font-size: 0.95rem;
            }
        }

        @media (max-width: 768px) {
            .shop-topbar {
                padding: 0.5rem 0.75rem;
                gap: 0.5rem;
            }

            .shop-topbar-title {
                font-size: 0.9rem;
            }

            .shop-plan-pill {
                display: none;
            }

            .card-header {
                flex-direction: column;
                align-items: stretch !important;
                gap: 0.75rem;
            }

            .card-header form {
                flex-direction: column;
                width: 100%;
            }

            .card-header form input,
            .card-header form select {
                max-width: 100% !important;
                width: 100% !important;
            }

            .modal-box {
                width: 95vw !important;
                max-width: 95vw !important;
                margin: 0.5rem auto !important;
                max-height: 92vh;
            }

            .modal-header, .modal-body, .modal-footer {
                padding: 0.85rem 1rem;
            }
        }

        @media (max-width: 480px) {
            .shop-topbar {
                padding: 0.55rem 0.65rem;
                gap: 0.45rem;
            }

            .shop-topbar-left {
                width: 100%;
            }

            .shop-topbar-actions {
                width: 100%;
                justify-content: flex-end;
                margin-left: 0;
                padding-top: 0.35rem;
                border-top: 1px dashed rgba(226, 232, 240, 0.7);
            }

            .shop-content {
                padding: 0.65rem;
            }

            .card {
                border-radius: 8px;
            }

            .table th, .table td {
                padding: 0.5rem 0.65rem;
                font-size: 0.8rem;
            }

            .btn {
                padding: 0.45rem 0.75rem;
                font-size: 0.8rem;
            }
        }

        @media print {
            @page {
                margin: 0;
            }
            .shop-sidebar,
            .shop-topbar,
            .shop-sidebar-overlay,
            .mobile-sidebar-toggle,
            .alert,
            .no-print {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                color: #0f172a !important;
                min-height: auto !important;
            }
            .shop-workspace,
            .shop-content {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Off-Canvas Backdrop -->
    <div class="shop-sidebar-overlay" id="shopSidebarOverlay" onclick="closeShopSidebar()"></div>

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
                <span class="shop-brand-subtitle">Branch: {{ session('active_outlet_name', auth()->user()->shop->mainOutlet->name ?? 'Main Branch') }}</span>
            </div>
            <button type="button" class="sidebar-close-btn" onclick="closeShopSidebar()" aria-label="Close navigation">&times;</button>
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
            <a href="{{ route('shop.sales') }}" class="shop-nav-item {{ request()->routeIs('shop.sales') || request()->routeIs('shop.sales.show') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                Sales &amp; Sold Invoices
            </a>
            <a href="{{ route('shop.sales.returns') }}" class="shop-nav-item {{ request()->routeIs('shop.sales.returns*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>
                Sales Returns &amp; Refunds
            </a>
            <a href="{{ route('shop.quotations.index') }}" class="shop-nav-item {{ request()->routeIs('shop.quotations*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                Quotations &amp; Estimates
            </a>

            <span class="shop-nav-header">Phone Inventory &amp; Stock</span>
            <a href="{{ route('shop.phones') }}" class="shop-nav-item {{ request()->routeIs('shop.phones*') && request('status') !== 'sold' ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                Dual IMEI Phones
            </a>
            <a href="{{ route('shop.trade-ins.index') }}" class="shop-nav-item {{ request()->routeIs('shop.trade-ins*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 3h5v5"></path><path d="M4 20L21 3"></path><path d="M21 16v5h-5"></path><path d="M15 15l6 6"></path><path d="M4 4l5 5"></path></svg>
                Used Phone Trade-Ins
            </a>
            <a href="{{ route('shop.labels.index') }}" class="shop-nav-item {{ request()->routeIs('shop.labels*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="9" x2="20" y2="9"></line><line x1="4" y1="15" x2="20" y2="15"></line><line x1="10" y1="3" x2="8" y2="21"></line><line x1="16" y1="3" x2="14" y2="21"></line></svg>
                Barcode &amp; IMEI Labels
            </a>
            <a href="{{ route('shop.phones', ['status' => 'sold']) }}" class="shop-nav-item {{ request()->routeIs('shop.phones*') && request('status') === 'sold' ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"></path><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                Sold Phones (IMEI Archive)
                <span class="shop-nav-badge" style="background:#10b981; color:#fff;">Sold</span>
            </a>
            <a href="{{ route('shop.products') }}" class="shop-nav-item {{ request()->routeIs('shop.products*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                Products &amp; Accessories
            </a>
            <a href="{{ route('shop.spare-parts') }}" class="shop-nav-item {{ request()->routeIs('shop.spare-parts*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>
                Spare Parts Stock
            </a>
            <a href="{{ route('shop.inventory') }}" class="shop-nav-item {{ request()->routeIs('shop.inventory*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                Stock Movements &amp; Audit
            </a>
            <a href="{{ route('shop.purchases') }}" class="shop-nav-item {{ request()->routeIs('shop.purchases*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                Inbound Purchases
            </a>
            <a href="{{ route('shop.inventory.grns') }}" class="shop-nav-item {{ request()->routeIs('shop.inventory.grns*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                Goods Receiving (GRN)
            </a>
            <a href="{{ route('shop.suppliers') }}" class="shop-nav-item {{ request()->routeIs('shop.suppliers*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                Suppliers &amp; Vendors
            </a>

            <span class="shop-nav-header">Service & Warranties</span>
            <a href="{{ route('shop.repairs') }}" class="shop-nav-item {{ request()->routeIs('shop.repairs*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>
                Repair Jobs (8-Stages)
            </a>
            <a href="{{ route('shop.warranties') }}" class="shop-nav-item {{ request()->routeIs('shop.warranties*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                Warranty &amp; Claims
            </a>
            <a href="{{ route('shop.notifications.index') }}" class="shop-nav-item {{ request()->routeIs('shop.notifications*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                SMS &amp; WhatsApp Alerts
            </a>

            <span class="shop-nav-header">Finance &amp; Cashbook</span>
            <a href="{{ route('shop.expenses') }}" class="shop-nav-item {{ request()->routeIs('shop.expenses*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
                Operating Expenses
            </a>
            <a href="{{ route('shop.cashbook') }}" class="shop-nav-item {{ request()->routeIs('shop.cashbook*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                Daily Cashbook &amp; Drawer
            </a>
            <a href="{{ route('shop.reports') }}" class="shop-nav-item {{ request()->routeIs('shop.reports*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                Reports &amp; Analytics
            </a>

            <span class="shop-nav-header">Directory & Administration</span>
            <a href="{{ route('shop.customers') }}" class="shop-nav-item {{ request()->routeIs('shop.customers*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                Customers &amp; Credit
            </a>
            <a href="{{ route('shop.outlets.index') }}" class="shop-nav-item {{ request()->routeIs('shop.outlets*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                Outlets &amp; Branches
            </a>
            <a href="{{ route('shop.transfers.index') }}" class="shop-nav-item {{ request()->routeIs('shop.transfers*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"></polyline><path d="M3 11V9a4 4 0 0 1 4-4h14"></path><polyline points="7 23 3 19 7 15"></polyline><path d="M21 13v2a4 4 0 0 1-4 4H3"></path></svg>
                Branch Stock Transfers
            </a>
            <a href="{{ route('shop.settings') }}" class="shop-nav-item {{ request()->routeIs('shop.settings*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                Shop Settings
            </a>
            <a href="{{ route('shop.subscription.index') }}" class="shop-nav-item {{ request()->routeIs('shop.subscription*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                Subscription &amp; Billing
            </a>
            <a href="{{ route('shop.staff') }}" class="shop-nav-item {{ request()->routeIs('shop.staff*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                Staff HRM &amp; Payroll
            </a>

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
            <div class="shop-topbar-left">
                <button type="button" class="mobile-sidebar-toggle" onclick="openShopSidebar()" aria-label="Toggle navigation menu">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
                <div class="shop-topbar-title">
                    <span class="page-title-text">@yield('page-title', 'Overview')</span>
                    <span class="shop-plan-pill">
                        {{ auth()->user()->shop->activeSubscription->plan->name ?? 'Free Trial' }}
                    </span>
                    @if(auth()->user()->hasRole('Super Administrator') || auth()->user()->can('superadmin.view'))
                        <span class="badge" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: #ffffff; padding: 0.2rem 0.55rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.3rem;">
                            Super Admin Mode
                        </span>
                    @endif
                </div>
            </div>

            <div class="shop-topbar-actions">
                @if(auth()->user()->hasRole('Super Administrator') || auth()->user()->can('superadmin.view'))
                    <a href="{{ route('superadmin.dashboard') }}" class="btn btn-sm" style="background: #1e1b4b; color: #c7d2fe; border: 1px solid #4f46e5; font-size: 0.78rem; display: inline-flex; align-items: center; gap: 0.35rem;" title="Return to Platform Super Admin">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                        <span class="topbar-action-text">Super Admin</span>
                    </a>
                @endif
                <a href="{{ route('shop.outlets.index') }}" class="btn btn-sm btn-secondary" style="border-radius: 9999px; font-size: 0.78rem; display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.75rem;" title="Manage Outlets & Switch Branch">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                    <span class="topbar-action-text"><strong class="topbar-branch-name">{{ session('active_outlet_name', auth()->user()->shop->mainOutlet->name ?? 'Main') }}</strong></span>
                </a>
                <a href="{{ route('public.repair-track') }}" target="_blank" class="btn btn-sm btn-secondary" title="Open Customer Repair Tracking Portal" style="display: inline-flex; align-items: center; gap: 0.35rem; border-radius: 9999px; font-size: 0.78rem; padding: 0.35rem 0.75rem;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <span class="topbar-action-text">Track Portal</span>
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

    <!-- Modal helper & Mobile Navigation script -->
    <script>
        function openShopSidebar() {
            var sidebar = document.querySelector('.shop-sidebar');
            var overlay = document.getElementById('shopSidebarOverlay');
            if (sidebar) sidebar.classList.add('open');
            if (overlay) overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        function closeShopSidebar() {
            var sidebar = document.querySelector('.shop-sidebar');
            var overlay = document.getElementById('shopSidebarOverlay');
            if (sidebar) sidebar.classList.remove('open');
            if (overlay) overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
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
        document.addEventListener('DOMContentLoaded', function() {
            var navLinks = document.querySelectorAll('.shop-nav-item');
            navLinks.forEach(function(link) {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 1080) {
                        closeShopSidebar();
                    }
                });
            });
        });
    </script>
    @stack('scripts')
</body>
</html>

