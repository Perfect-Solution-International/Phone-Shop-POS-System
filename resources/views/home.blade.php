<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Premium multi-tenant phone shop operations, POS, inventory, and employee management platform.">
    <link rel="canonical" href="{{ route('home') }}">
    <title>{{ config('app.name') === 'Laravel' ? 'Phone Shop POS SaaS' : config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ mix('css/app.min.css') }}">
    <style>
        .hero-section {
            padding: 4rem 0;
            text-align: center;
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.35rem 0.85rem;
            background: #eef2ff;
            color: #4f46e5;
            font-size: 0.8125rem;
            font-weight: 700;
            border-radius: 9999px;
            margin-bottom: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        .hero-title {
            font-size: 3rem;
            font-weight: 800;
            line-height: 1.15;
            color: #0f172a;
            letter-spacing: -0.03em;
            max-width: 720px;
            margin: 0 auto 1.25rem;
        }
        .hero-desc {
            font-size: 1.25rem;
            color: #64748b;
            max-width: 600px;
            margin: 0 auto 2.5rem;
            line-height: 1.6;
        }
        .hero-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .btn-outline {
            display: inline-block;
            font-weight: 600;
            text-align: center;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            border-radius: 0.5rem;
            text-decoration: none;
            background: #ffffff;
            color: #0f172a;
            border: 1.5px solid #cbd5e1;
            transition: all 0.15s ease-in-out;
        }
        .btn-outline:hover {
            border-color: #94a3b8;
            background: #f8fafc;
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.5rem;
            margin-top: 4rem;
        }
        .feature-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.75rem;
            text-align: left;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: transform 0.15s;
        }
        .feature-card:hover {
            transform: translateY(-2px);
        }
        .feature-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: #eef2ff;
            color: #4f46e5;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
        .feature-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.5rem;
        }
        .feature-text {
            font-size: 0.875rem;
            color: #64748b;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <main class="container">
        <section class="hero-section">
            <span class="hero-badge">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                Independent Tenant Workspaces
            </span>
            <h1 class="hero-title">Phone Shop POS & Team Management</h1>
            <p class="hero-desc">The modern operations platform built for mobile phone retail and repair centers. Manage attendance, payroll, permissions, and daily workflows with strict isolation.</p>
            
            <div class="hero-actions">
                <a class="btn btn-primary" href="{{ route('register') }}">Start Free Shop Workspace &rarr;</a>
                <a class="btn-outline" href="{{ route('login') }}">Login to Workspace</a>
            </div>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    </div>
                    <h2 class="feature-title">Smart HRM & Attendance</h2>
                    <p class="feature-text">Track clock-ins, leave approvals, and employee profiles with automated shift and presence monitoring.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    </div>
                    <h2 class="feature-title">Automated Payroll</h2>
                    <p class="feature-text">Generate monthly salary statements, allowances, and deductions with one-click PDF and Excel exports.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    </div>
                    <h2 class="feature-title">Strict Tenant Isolation</h2>
                    <p class="feature-text">Every shop workspace runs completely isolated. Data, employees, and settings can never leak across accounts.</p>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
