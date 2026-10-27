@extends('layouts.shop')

@section('title', 'Dashboard Overview')
@section('page-title', 'Shop Overview & Daily Register')

@section('content')
<style>
    /* Modern Dashboard Hero Banner */
    .dashboard-hero-banner {
        background: linear-gradient(135deg, rgba(79, 70, 229, 0.08) 0%, rgba(6, 182, 212, 0.08) 50%, rgba(248, 250, 252, 0.9) 100%);
        border: 1px solid rgba(199, 210, 254, 0.7);
        border-radius: 16px;
        padding: 1.5rem 1.75rem;
        margin-bottom: 1.75rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1.25rem;
        box-shadow: 0 2px 10px -2px rgba(99, 102, 241, 0.06);
    }

    .hero-meta h1 {
        font-size: 1.45rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 0.35rem;
        letter-spacing: -0.02em;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .hero-meta p {
        font-size: 0.85rem;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 0.65rem;
        flex-wrap: wrap;
        margin: 0;
    }

    .hero-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
        padding: 0.15rem 0.6rem;
        border-radius: 9999px;
        font-size: 0.72rem;
        font-weight: 700;
    }

    .hero-status-pill::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #10b981;
        box-shadow: 0 0 6px #10b981;
    }

    .hero-actions {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        flex-wrap: wrap;
    }

    /* Metrics Grid */
    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(235px, 1fr));
        gap: 1.25rem;
        margin-bottom: 1.75rem;
    }

    .metric-card {
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.85);
        border-radius: 16px;
        padding: 1.35rem 1.45rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 0.75rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03), 0 6px 16px -4px rgba(0, 0, 0, 0.04);
        position: relative;
        overflow: hidden;
        transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.04), 0 16px 28px -6px rgba(0, 0, 0, 0.08);
        border-color: #cbd5e1;
    }

    .metric-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: var(--brand-indigo-600);
    }

    .metric-card.emerald::before { background: linear-gradient(180deg, #10b981 0%, #059669 100%); }
    .metric-card.cyan::before { background: linear-gradient(180deg, #06b6d4 0%, #0284c7 100%); }
    .metric-card.amber::before { background: linear-gradient(180deg, #f59e0b 0%, #d97706 100%); }
    .metric-card.rose::before { background: linear-gradient(180deg, #ef4444 0%, #e11d48 100%); }

    .metric-card-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .metric-label {
        font-size: 0.75rem;
        font-weight: 750;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
    }

    .metric-icon-wrap {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .metric-card.emerald .metric-icon-wrap { background: #ecfdf5; color: #10b981; }
    .metric-card.cyan .metric-icon-wrap { background: #e0f2fe; color: #0284c7; }
    .metric-card.amber .metric-icon-wrap { background: #fef3c7; color: #d97706; }
    .metric-card.rose .metric-icon-wrap { background: #ffe4e6; color: #e11d48; }

    .metric-value {
        font-size: 1.65rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
        letter-spacing: -0.02em;
    }

    .metric-sub {
        font-size: 0.8125rem;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }

    /* Settlement Cards */
    .settlement-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 1.15rem;
    }

    .settlement-card {
        padding: 1.15rem 1.25rem;
        border-radius: 12px;
        border: 1px solid var(--surface-border);
        background: #ffffff;
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
        position: relative;
    }

    .settlement-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
    }

    .settlement-card.cash {
        background: linear-gradient(145deg, #ffffff 0%, #f0fdf4 100%);
        border-color: #bbf7d0;
    }
    .settlement-card.card-pos {
        background: linear-gradient(145deg, #ffffff 0%, #eff6ff 100%);
        border-color: #bfdbfe;
    }
    .settlement-card.bank-qr {
        background: linear-gradient(145deg, #ffffff 0%, #faf5ff 100%);
        border-color: #e9d5ff;
    }

    .panels-2col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }

    @media (max-width: 992px) {
        .panels-2col { grid-template-columns: 1fr; }
    }

    @media (max-width: 640px) {
        .metrics-grid {
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-bottom: 1.25rem;
        }
        .metric-card {
            padding: 1rem;
        }
        .metric-value {
            font-size: 1.25rem;
        }
        .hero-meta h1 {
            font-size: 1.25rem;
        }
    }

    @media (max-width: 420px) {
        .metrics-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Modern Welcome Hero Banner -->
<div class="dashboard-hero-banner">
    <div class="hero-meta">
        <h1>
            <span>Welcome back, {{ auth()->user()->name }}</span>
            <span style="font-size: 1.2rem;">👋</span>
        </h1>
        <p>
            <span>Today is <strong>{{ now()->format('l, F j, Y') }}</strong></span>
            <span>•</span>
            <span class="hero-status-pill">Active Cash Register</span>
            <span>•</span>
            <span>Store: <strong>{{ auth()->user()->shop->name ?? 'Phone Shop' }}</strong></span>
        </p>
    </div>
    <div class="hero-actions">
        <a href="{{ route('shop.pos') }}" class="btn btn-primary" style="padding: 0.55rem 1.15rem; border-radius: 10px; font-weight: 700; box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M2 9h20"></path><rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="M6 16h4"></path><path d="M14 16h4"></path></svg>
            <span>Launch POS Counter</span>
        </a>
        <a href="{{ route('shop.repairs') }}" class="btn btn-secondary" style="border-radius: 10px; font-weight: 650;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>
            <span>New Repair Job</span>
        </a>
        <a href="{{ route('shop.phones') }}" class="btn btn-secondary" style="border-radius: 10px; font-weight: 650;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
            <span>Phones Stock</span>
        </a>
    </div>
</div>

<!-- Metrics Row -->
<div class="metrics-grid">
    <div class="metric-card emerald">
        <div class="metric-card-top">
            <span class="metric-label">Today's Total Sales</span>
            <div class="metric-icon-wrap">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
            </div>
        </div>
        <span class="metric-value">LKR {{ number_format($metrics['today_sales_total'], 2) }}</span>
        <span class="metric-sub">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
            {{ $metrics['today_transactions'] }} transactions completed
        </span>
    </div>

    <div class="metric-card cyan">
        <div class="metric-card-top">
            <span class="metric-label">In-Stock Phones (IMEI)</span>
            <div class="metric-icon-wrap">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="5" y="2" width="14" height="20" rx="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
            </div>
        </div>
        <span class="metric-value">{{ number_format($metrics['available_phones']) }}</span>
        <span class="metric-sub">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
            {{ $metrics['sold_phones_today'] }} phones sold today
        </span>
    </div>

    <div class="metric-card amber">
        <div class="metric-card-top">
            <span class="metric-label">Low Stock Inventory</span>
            <div class="metric-icon-wrap">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
            </div>
        </div>
        <span class="metric-value">{{ number_format($metrics['low_stock_count']) }} Items</span>
        <span class="metric-sub">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            Below reorder alert limit
        </span>
    </div>

    <div class="metric-card rose">
        <div class="metric-card-top">
            <span class="metric-label">Outstanding Customer Credit</span>
            <div class="metric-icon-wrap">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            </div>
        </div>
        <span class="metric-value">LKR {{ number_format($metrics['outstanding_credit'], 2) }}</span>
        <span class="metric-sub">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#e11d48" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline></svg>
            Across {{ $metrics['total_customers'] }} customer profiles
        </span>
    </div>
</div>

<!-- Collections Breakdown & Daily Cash Register -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <div style="display: flex; align-items: center; gap: 0.65rem;">
            <div style="width: 32px; height: 32px; border-radius: 8px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="2" y="4" width="20" height="16" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
            </div>
            <div>
                <span class="card-title" style="display: block; line-height: 1.2;">Daily Cash Register & Settlement Breakdown</span>
                <span style="font-size: 0.75rem; color: #64748b;">Live drawer balances and payment gateway splits</span>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span class="badge badge-info" style="font-weight: 700; padding: 0.3rem 0.75rem;">{{ now()->toFormattedDateString() }}</span>
        </div>
    </div>
    <div class="card-body" style="padding: 1.35rem 1.5rem;">
        <div class="settlement-grid">
            <div class="settlement-card cash">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 0.72rem; color: #166534; font-weight: 750; text-transform: uppercase; letter-spacing: 0.05em;">Cash Drawer</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                </div>
                <p style="font-size: 1.45rem; font-weight: 800; color: #166534; margin: 0; letter-spacing: -0.02em;">LKR {{ number_format($metrics['cash_collections'], 2) }}</p>
                <span style="font-size: 0.75rem; color: #15803d;">Physical in-drawer tender</span>
            </div>

            <div class="settlement-card card-pos">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 0.72rem; color: #1e40af; font-weight: 750; text-transform: uppercase; letter-spacing: 0.05em;">Card POS Terminal</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                </div>
                <p style="font-size: 1.45rem; font-weight: 800; color: #1e40af; margin: 0; letter-spacing: -0.02em;">LKR {{ number_format($metrics['card_collections'], 2) }}</p>
                <span style="font-size: 0.75rem; color: #1d4ed8;">Visa, Mastercard & Amex swipe</span>
            </div>

            <div class="settlement-card bank-qr">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 0.72rem; color: #6b21a8; font-weight: 750; text-transform: uppercase; letter-spacing: 0.05em;">Bank Transfer / QR</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#9333ea" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                </div>
                <p style="font-size: 1.45rem; font-weight: 800; color: #6b21a8; margin: 0; letter-spacing: -0.02em;">LKR {{ number_format($metrics['bank_collections'], 2) }}</p>
                <span style="font-size: 0.75rem; color: #7e22ce;">LankaQR & Direct NEFT deposits</span>
            </div>
        </div>
    </div>
</div>

<!-- Two Columns: Recent Invoices & Available Phones -->
<div class="panels-2col">
    <!-- Recent Sales -->
    <div class="card">
        <div class="card-header">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span class="card-title">Recent POS Transactions</span>
                <span class="badge" style="background: #f1f5f9; color: #475569; font-weight: 700;">Today</span>
            </div>
            <a href="{{ route('shop.pos') }}" class="btn btn-sm btn-primary" style="padding: 0.35rem 0.85rem; border-radius: 8px;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>New Sale</span>
            </a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($metrics['recent_sales'] as $sale)
                    <tr>
                        <td>
                            <strong style="color: #4f46e5; font-family: 'JetBrains Mono', monospace; font-size: 0.8125rem; background: #eef2ff; padding: 2px 6px; border-radius: 4px; border: 1px solid #c7d2fe;">
                                {{ $sale->invoice_number }}
                            </strong>
                        </td>
                        <td>
                            <span style="font-weight: 600; color: #1e293b;">{{ $sale->customer->name ?? 'Walk-in Customer' }}</span>
                        </td>
                        <td style="font-weight: 800; color: #0f172a;">LKR {{ number_format($sale->total_amount, 2) }}</td>
                        <td>
                            <span class="badge badge-success">{{ ucfirst($sale->payment_status) }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 2.5rem 1rem;">
                            <div style="width: 44px; height: 44px; border-radius: 50%; background: #f1f5f9; color: #94a3b8; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.5rem;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            </div>
                            <div>No sales recorded yet today. Click <strong>New Sale</strong> to start checkout.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Available Phones -->
    <div class="card">
        <div class="card-header">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span class="card-title">Latest Serialized Stock (In Stock)</span>
                <span class="badge" style="background: #e0f2fe; color: #0369a1; font-weight: 700;">Dual IMEI</span>
            </div>
            <a href="{{ route('shop.phones') }}" class="btn btn-sm btn-secondary" style="border-radius: 8px;">View All</a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Device</th>
                        <th>IMEI 1</th>
                        <th>Price</th>
                        <th>Condition</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($metrics['recent_phones'] as $phone)
                    <tr>
                        <td>
                            <strong style="color: #0f172a;">{{ $phone->brand }} {{ $phone->model }}</strong>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $phone->color }} • {{ $phone->storage }}</div>
                        </td>
                        <td>
                            <span style="font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; border: 1px solid #e2e8f0; color: #334155;">
                                {{ $phone->imei_1 }}
                            </span>
                        </td>
                        <td style="font-weight: 800; color: #166534;">LKR {{ number_format($phone->selling_price, 2) }}</td>
                        <td>
                            <span class="badge badge-info">{{ ucfirst($phone->condition) }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 2.5rem 1rem;">
                            <div style="width: 44px; height: 44px; border-radius: 50%; background: #f1f5f9; color: #94a3b8; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.5rem;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                            </div>
                            <div>No phones registered in stock. Go to <strong>Phone Inventory</strong> to add stock.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
