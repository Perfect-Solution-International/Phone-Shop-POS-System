@extends('layouts.super-admin')

@section('title', 'Platform Dashboard')
@section('page_title', 'SaaS Platform Overview & Financial Analytics')

@section('content')
<style>
    /* Metric Card Enhancements */
    .metric-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
    }
    .metric-icon-bubble {
        width: 36px;
        height: 36px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .bubble-indigo { background: #eef2ff; color: #4f46e5; }
    .bubble-emerald { background: #ecfdf5; color: #10b981; }
    .bubble-blue { background: #eff6ff; color: #2563eb; }
    .bubble-amber { background: #fffbeb; color: #d97706; }
    .bubble-rose { background: #fef2f2; color: #ef4444; }
    .bubble-violet { background: #f5f3ff; color: #7c3aed; }

    .metric-tooltip {
        position: relative;
        cursor: help;
        display: inline-flex;
        align-items: center;
        color: #94a3b8;
    }
    .metric-tooltip:hover .tooltip-box {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    .tooltip-box {
        opacity: 0;
        visibility: hidden;
        position: absolute;
        bottom: calc(100% + 8px);
        left: 50%;
        transform: translateX(-50%) translateY(4px);
        background: #0f172a;
        color: #ffffff;
        font-size: 0.725rem;
        font-weight: 500;
        line-height: 1.4;
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        width: 230px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2);
        z-index: 50;
        pointer-events: none;
        transition: all 0.15s ease;
        text-transform: none;
        letter-spacing: normal;
    }
    .tooltip-box::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        margin-left: -5px;
        border-width: 5px;
        border-style: solid;
        border-color: #0f172a transparent transparent transparent;
    }

    /* Filter Toolbar */
    .filter-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.5rem;
        background: #ffffff;
        padding: 0.85rem 1.25rem;
        border-radius: 12px;
        border: 1px solid var(--surface-border);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }
    .filter-group {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        flex-wrap: wrap;
    }
    .filter-pill {
        display: inline-flex;
        align-items: center;
        padding: 0.35rem 0.85rem;
        border-radius: 9999px;
        font-size: 0.775rem;
        font-weight: 600;
        text-decoration: none;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        color: #475569;
        transition: all 0.15s ease;
    }
    .filter-pill:hover {
        background: #eef2ff;
        color: #4f46e5;
        border-color: #c7d2fe;
    }
    .filter-pill.active {
        background: #4f46e5;
        color: #ffffff;
        border-color: #4f46e5;
        box-shadow: 0 2px 6px rgba(79, 70, 229, 0.3);
    }

    /* Payment Analytics Grid */
    .analytics-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .analytics-stat-card {
        background: #ffffff;
        border: 1px solid var(--surface-border);
        border-radius: 10px;
        padding: 1rem 1.15rem;
        display: flex;
        flex-direction: column;
        gap: 0.3rem;
    }
    .stat-label-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 0.725rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .stat-main-amount {
        font-size: 1.35rem;
        font-weight: 800;
        color: var(--text-heading);
        letter-spacing: -0.01em;
    }
    .stat-meta-note {
        font-size: 0.75rem;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }

    /* SVG Trend Chart */
    .chart-box {
        background: #ffffff;
        border: 1px solid var(--surface-border);
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
        margin-bottom: 2rem;
    }
    .chart-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.25rem;
    }
    .chart-title {
        font-size: 0.95rem;
        font-weight: 750;
        color: var(--text-heading);
    }
    .chart-legend {
        display: flex;
        align-items: center;
        gap: 1.25rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: #64748b;
    }
    .legend-item {
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }
    .legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 3px;
    }
    .bar-chart-container {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1rem;
        height: 180px;
        padding-top: 1rem;
        border-bottom: 1px solid #e2e8f0;
    }
    .bar-group {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        height: 100%;
        justify-content: flex-end;
        gap: 0.5rem;
        position: relative;
    }
    .bar-bars-wrapper {
        display: flex;
        align-items: flex-end;
        gap: 4px;
        height: 140px;
        width: 100%;
        max-width: 54px;
        justify-content: center;
    }
    .chart-bar {
        border-radius: 4px 4px 0 0;
        min-height: 4px;
        width: 22px;
        transition: height 0.3s ease, transform 0.15s ease;
        position: relative;
    }
    .chart-bar:hover {
        transform: scaleY(1.04);
        filter: brightness(1.1);
    }
    .bar-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #64748b;
        white-space: nowrap;
    }

    /* Distribution Progress Bar */
    .distribution-track {
        height: 8px;
        border-radius: 9999px;
        background: #f1f5f9;
        display: flex;
        overflow: hidden;
        margin-top: 0.85rem;
    }

    /* System Activity Stream */
    .activity-list {
        display: flex;
        flex-direction: column;
    }
    .activity-item {
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
        padding: 0.85rem 1.25rem;
        border-bottom: 1px solid #f1f5f9;
        transition: background 0.15s ease;
    }
    .activity-item:last-child {
        border-bottom: none;
    }
    .activity-item:hover {
        background: #f8fafc;
    }
    .activity-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 0.75rem;
    }
    .activity-content {
        flex: 1;
        min-width: 0;
    }
    .activity-desc {
        font-size: 0.8125rem;
        color: #334155;
        line-height: 1.4;
        word-break: break-word;
    }
    .activity-meta {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.25rem;
        font-size: 0.7rem;
        color: #94a3b8;
    }

    /* Responsive adjustments */
    @media (max-width: 1024px) {
        .analytics-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .two-col-layout {
            grid-template-columns: 1fr !important;
        }
    }
    @media (max-width: 640px) {
        .analytics-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Date Range & Analytics Filter Toolbar -->
<div class="filter-toolbar">
    <div style="display: flex; align-items: center; gap: 0.75rem;">
        <span style="font-size: 0.8125rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.04em;">Financial Period:</span>
        <div class="filter-group">
            <a href="{{ route('superadmin.dashboard', ['date_range' => 'all']) }}" class="filter-pill {{ $date_range === 'all' ? 'active' : '' }}">All Time</a>
            <a href="{{ route('superadmin.dashboard', ['date_range' => 'this_month']) }}" class="filter-pill {{ $date_range === 'this_month' ? 'active' : '' }}">This Month</a>
            <a href="{{ route('superadmin.dashboard', ['date_range' => 'last_30_days']) }}" class="filter-pill {{ $date_range === 'last_30_days' ? 'active' : '' }}">Last 30 Days</a>
            <a href="{{ route('superadmin.dashboard', ['date_range' => 'today']) }}" class="filter-pill {{ $date_range === 'today' ? 'active' : '' }}">Today</a>
            <a href="{{ route('superadmin.dashboard', ['date_range' => 'this_year']) }}" class="filter-pill {{ $date_range === 'this_year' ? 'active' : '' }}">This Year</a>
        </div>
    </div>

    <div style="display: flex; align-items: center; gap: 0.5rem;">
        <span class="status-pill status-active" style="font-size: 0.7rem;">
            ● Live Isolation Active
        </span>
        <span style="font-size: 0.75rem; color: #64748b;">Server Time: {{ now()->format('Y-m-d H:i') }}</span>
    </div>
</div>

<!-- 1. Six Primary Dashboard Metric Cards -->
<div class="metric-grid">
    <!-- 1. Monthly Recurring Revenue (MRR) -->
    <div class="metric-card">
        <div class="metric-card-header">
            <div style="display: flex; align-items: center; gap: 0.35rem;">
                <span class="metric-label">Monthly Recurring Revenue</span>
                <span class="metric-tooltip">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    <span class="tooltip-box">
                        Normalized monthly subscription run rate from active paid subscriptions. Excludes free trials and unapproved payments.
                    </span>
                </span>
            </div>
            <div class="metric-icon-bubble bubble-indigo">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
            </div>
        </div>
        <div class="metric-value">LKR {{ number_format($stats['mrr'], 2) }}</div>
        <span class="metric-sub" style="color: #6366f1;">
            Cash this month: LKR {{ number_format($stats['cash_received_this_month'], 2) }}
        </span>
    </div>

    <!-- 2. Total Registered Shops -->
    <div class="metric-card">
        <div class="metric-card-header">
            <span class="metric-label">Registered Shops</span>
            <div class="metric-icon-bubble bubble-violet">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg>
            </div>
        </div>
        <div class="metric-value">{{ $stats['total_shops'] }}</div>
        <span class="metric-sub" style="color: #64748b;">Total workspaces</span>
    </div>

    <!-- 3. Active Paid Subscriptions -->
    <div class="metric-card">
        <div class="metric-card-header">
            <div style="display: flex; align-items: center; gap: 0.35rem;">
                <span class="metric-label">Active Subscriptions</span>
                <span class="metric-tooltip">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    <span class="tooltip-box">Shops with a currently valid, active paid subscription. Excludes free trials and expired accounts.</span>
                </span>
            </div>
            <div class="metric-icon-bubble bubble-emerald">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
            </div>
        </div>
        <div class="metric-value" style="color: var(--color-success);">{{ $stats['active_subscriptions'] }}</div>
        <span class="metric-sub" style="color: var(--color-success);">Paying shop tenants</span>
    </div>

    <!-- 4. Trial Shops -->
    <div class="metric-card">
        <div class="metric-card-header">
            <span class="metric-label">Trial Shops</span>
            <div class="metric-icon-bubble bubble-blue">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            </div>
        </div>
        <div class="metric-value" style="color: #2563eb;">{{ $stats['trial_shops'] }}</div>
        <span class="metric-sub" style="color: #2563eb;">7-day free trial</span>
    </div>

    <!-- 5. Expired Subscriptions -->
    <div class="metric-card">
        <div class="metric-card-header">
            <span class="metric-label">Expired Subscriptions</span>
            <div class="metric-icon-bubble bubble-rose">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
            </div>
        </div>
        <div class="metric-value" style="color: {{ $stats['expired_subscriptions'] > 0 ? '#ef4444' : '#64748b' }};">{{ $stats['expired_subscriptions'] }}</div>
        <span class="metric-sub" style="color: #64748b;">Not covered by active plan</span>
    </div>

    <!-- 6. Pending Payments -->
    <div class="metric-card">
        <div class="metric-card-header">
            <span class="metric-label">Pending Payments</span>
            <div class="metric-icon-bubble bubble-amber">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
            </div>
        </div>
        <div class="metric-value" style="color: {{ $stats['pending_payments_count'] > 0 ? '#d97706' : '#64748b' }};">
            {{ $stats['pending_payments_count'] }}
        </div>
        <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 0.15rem;">
            <span class="metric-sub" style="color: #d97706; font-size: 0.725rem;">
                LKR {{ number_format($stats['pending_payments_amount'], 2) }}
            </span>
            <a href="{{ route('superadmin.payments.approvals') }}" style="font-size: 0.725rem; font-weight: 700; color: #4f46e5; text-decoration: none;">Verify &rarr;</a>
        </div>
    </div>
</div>

<!-- 2. Payment Analytics & Revenue Performance -->
<div class="data-card">
    <div class="data-card-header">
        <div>
            <h2 class="data-card-title">Subscription Payment Analytics &amp; Revenue Flow</h2>
            <p style="font-size: 0.775rem; color: #64748b; margin-top: 0.15rem;">Transaction totals and verified monetary amounts for: <strong>{{ $payment_analytics['date_label'] }}</strong></p>
        </div>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <a href="{{ route('superadmin.payments.index') }}" class="btn-action btn-outline" style="font-size: 0.775rem;">View Full Ledger</a>
            <a href="{{ route('superadmin.payments.approvals') }}" class="btn-action btn-primary" style="font-size: 0.775rem;">
                Review Pending ({{ $stats['pending_payments_count'] }})
            </a>
        </div>
    </div>

    <div style="padding: 1.5rem;">
        <!-- Analytics 4 Cards Row -->
        <div class="analytics-grid">
            <!-- Successful -->
            <div class="analytics-stat-card" style="border-top: 3px solid #10b981;">
                <div class="stat-label-row" style="color: #065f46;">
                    <span>Approved &amp; Collected</span>
                    <span class="status-pill status-active">{{ $payment_analytics['successful']['count'] }} Paid</span>
                </div>
                <div class="stat-main-amount" style="color: #047857;">
                    LKR {{ number_format($payment_analytics['successful']['gross_amount'], 2) }}
                </div>
                <div class="stat-meta-note">
                    <span>Net after refunds:</span>
                    <strong style="color: #0f172a;">LKR {{ number_format($payment_analytics['successful']['net_amount'], 2) }}</strong>
                </div>
            </div>

            <!-- Pending -->
            <div class="analytics-stat-card" style="border-top: 3px solid #f59e0b;">
                <div class="stat-label-row" style="color: #92400e;">
                    <span>Pending Bank Slips</span>
                    <span class="status-pill status-pending">{{ $payment_analytics['pending']['count'] }} Wait</span>
                </div>
                <div class="stat-main-amount" style="color: #b45309;">
                    LKR {{ number_format($payment_analytics['pending']['amount'], 2) }}
                </div>
                <div class="stat-meta-note">
                    <span>Awaiting manual deposit slip review</span>
                </div>
            </div>

            <!-- Failed / Rejected -->
            <div class="analytics-stat-card" style="border-top: 3px solid #ef4444;">
                <div class="stat-label-row" style="color: #991b1b;">
                    <span>Rejected / Failed</span>
                    <span class="status-pill status-rejected">{{ $payment_analytics['failed']['count'] }} Failed</span>
                </div>
                <div class="stat-main-amount" style="color: #dc2626;">
                    LKR {{ number_format($payment_analytics['failed']['amount'], 2) }}
                </div>
                <div class="stat-meta-note">
                    <span>Declined or invalid slips</span>
                </div>
            </div>

            <!-- Refunded -->
            <div class="analytics-stat-card" style="border-top: 3px solid #8b5cf6;">
                <div class="stat-label-row" style="color: #6b21a8;">
                    <span>Refunded Volume</span>
                    <span class="status-pill" style="background: #f5f3ff; color: #6b21a8; border: 1px solid #ddd6fe;">{{ $payment_analytics['refunded']['count'] }} Refund</span>
                </div>
                <div class="stat-main-amount" style="color: #7c3aed;">
                    LKR {{ number_format($payment_analytics['refunded']['amount'], 2) }}
                </div>
                <div class="stat-meta-note">
                    <span>Returned subscription amounts</span>
                </div>
            </div>
        </div>

        <!-- Visual Distribution Track -->
        @php
            $tot = max(1, $payment_analytics['total_transactions']);
            $pctSuccess = round(($payment_analytics['successful']['count'] / $tot) * 100);
            $pctPending = round(($payment_analytics['pending']['count'] / $tot) * 100);
            $pctFailed = round(($payment_analytics['failed']['count'] / $tot) * 100);
        @endphp
        <div style="margin-top: 0.5rem; margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #64748b; margin-bottom: 0.35rem;">
                <span>Transaction Status Distribution (Total: {{ $payment_analytics['total_transactions'] }})</span>
                <span>{{ $pctSuccess }}% Approved</span>
            </div>
            <div class="distribution-track">
                <div style="width: {{ $pctSuccess }}%; background: #10b981;" title="Approved: {{ $pctSuccess }}%"></div>
                <div style="width: {{ $pctPending }}%; background: #f59e0b;" title="Pending: {{ $pctPending }}%"></div>
                <div style="width: {{ $pctFailed }}%; background: #ef4444;" title="Rejected: {{ $pctFailed }}%"></div>
            </div>
        </div>

        <!-- 6-Month Monthly Payment Trends Chart -->
        <div class="chart-box" style="margin-bottom: 0;">
            <div class="chart-header">
                <div>
                    <h3 class="chart-title">Monthly Subscription Revenue Trend (Last 6 Months)</h3>
                    <p style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;">Approved revenue collected vs. pending transfer volume</p>
                </div>
                <div class="chart-legend">
                    <div class="legend-item">
                        <div class="legend-dot" style="background: #4f46e5;"></div>
                        <span>Approved (LKR)</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-dot" style="background: #fbbf24;"></div>
                        <span>Pending Review</span>
                    </div>
                </div>
            </div>

            <div class="bar-chart-container">
                @php
                    $maxVol = max(1.0, (float) $payment_analytics['monthly_trends']['max_volume']);
                @endphp
                @foreach ($payment_analytics['monthly_trends']['months'] as $m)
                    @php
                        $hApproved = max(4, round((($m['approved_amount'] / $maxVol) * 130)));
                        $hPending = max(4, round((($m['pending_amount'] / $maxVol) * 130)));
                    @endphp
                    <div class="bar-group">
                        <div class="bar-bars-wrapper" title="{{ $m['label'] }}: Approved LKR {{ number_format($m['approved_amount'], 2) }} | Pending LKR {{ number_format($m['pending_amount'], 2) }}">
                            <!-- Approved bar -->
                            <div class="chart-bar" style="height: {{ $hApproved }}px; background: linear-gradient(180deg, #6366f1 0%, #4f46e5 100%);"></div>
                            <!-- Pending bar -->
                            @if ($m['pending_amount'] > 0)
                                <div class="chart-bar" style="height: {{ $hPending }}px; background: #fbbf24;"></div>
                            @endif
                        </div>
                        <span class="bar-label">{{ $m['short_label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- 3. Subscription Expiry Alerts (Within Next 7 Days) -->
@if ($expiring_soon->isNotEmpty())
    <div class="data-card" style="border-left: 4px solid #f59e0b; margin-bottom: 2rem;">
        <div class="data-card-header" style="background: #fffbeb;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 1.1rem;">⚠️</span>
                <h2 class="data-card-title" style="color: #92400e; font-size: 0.95rem;">
                    Subscriptions Expiring Within 7 Days ({{ $expiring_soon->count() }} Accounts)
                </h2>
            </div>
            <span style="font-size: 0.775rem; color: #92400e; font-weight: 600;">Action required to prevent shop interruption</span>
        </div>
        <table class="saas-table">
            <thead>
                <tr>
                    <th>Shop Name</th>
                    <th>Subscribed Plan</th>
                    <th>Current Status</th>
                    <th>Expiry Date</th>
                    <th>Remaining Days</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($expiring_soon as $sub)
                    <tr>
                        <td>
                            <strong>{{ $sub->shop->name ?? 'Unknown Workspace' }}</strong>
                        </td>
                        <td>{{ $sub->plan->name ?? 'Default Plan' }}</td>
                        <td>
                            <span class="status-pill status-{{ $sub->status }}">{{ $sub->status }}</span>
                        </td>
                        <td>
                            <div>{{ $sub->current_period_ends_at->format('M d, Y') }}</div>
                            <div style="font-size: 0.725rem; color: #64748b;">{{ $sub->current_period_ends_at->diffForHumans() }}</div>
                        </td>
                        <td>
                            @if ($sub->remaining_days <= 2)
                                <span class="status-pill status-expired" style="background: #fef2f2; color: #b91c1c;">
                                    {{ $sub->remaining_days }} day{{ $sub->remaining_days === 1 ? '' : 's' }} left
                                </span>
                            @else
                                <span class="status-pill status-pending">
                                    {{ $sub->remaining_days }} days left
                                </span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <a href="{{ route('superadmin.shops.show', $sub->shop_id) }}" class="btn-action btn-outline" style="font-size: 0.775rem;">
                                View Shop &rarr;
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="data-card" style="margin-bottom: 2rem; border-left: 4px solid #10b981;">
        <div style="padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <span style="font-size: 1.25rem;">✅</span>
                <div>
                    <strong style="color: #065f46; font-size: 0.9375rem;">No Subscriptions Expiring Within 7 Days</strong>
                    <p style="font-size: 0.775rem; color: #64748b; margin-top: 0.15rem;">All active paid shop workspaces are currently up to date.</p>
                </div>
            </div>
            <span class="status-pill status-active">Health: 100%</span>
        </div>
    </div>
@endif

<!-- 4. Two-Column Lower Section: Recent Registrations & System Activity -->
<div class="two-col-layout" style="display: grid; grid-template-columns: 1.35fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
    <!-- Recent Shop Registrations -->
    <div class="data-card" style="margin-bottom: 0;">
        <div class="data-card-header">
            <div>
                <h2 class="data-card-title">Recent Shop Registrations</h2>
                <p style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;">Workspaces and owner accounts onboarded onto platform</p>
            </div>
            <a href="{{ route('superadmin.shops.index') }}" class="btn-action btn-outline" style="font-size: 0.775rem;">View All Shops</a>
        </div>
        <div style="overflow-x: auto;">
            <table class="saas-table">
                <thead>
                    <tr>
                        <th>Shop Name</th>
                        <th>Owner &amp; Verification</th>
                        <th>Subscription</th>
                        <th>Registered</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recent_shops as $shop)
                        @php
                            $owner = $shop->users->first();
                            $sub = $shop->subscription;
                        @endphp
                        <tr>
                            <td>
                                <strong style="color: #0f172a;">{{ $shop->name }}</strong>
                                <div style="font-size: 0.725rem; color: #94a3b8;">ID: #{{ $shop->id }}</div>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #334155;">{{ $owner->name ?? 'N/A' }}</div>
                                <div style="font-size: 0.725rem; color: #64748b;">{{ $owner->email ?? 'No email' }}</div>
                                <div style="margin-top: 0.25rem;">
                                    @if ($owner && $owner->email_verified_at)
                                        <span class="status-pill status-active" style="font-size: 0.65rem; padding: 0.1rem 0.45rem;">
                                            ✓ Verified
                                        </span>
                                    @else
                                        <span class="status-pill status-pending" style="font-size: 0.65rem; padding: 0.1rem 0.45rem;">
                                            ⏳ Unverified
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if ($sub)
                                    <span class="status-pill status-{{ $sub->status }}">
                                        {{ ucfirst($sub->status) }}
                                    </span>
                                    <div style="font-size: 0.7rem; color: #64748b; margin-top: 0.2rem;">
                                        {{ $sub->plan->name ?? 'Standard' }}
                                    </div>
                                @else
                                    <span class="status-pill status-expired">None</span>
                                @endif
                            </td>
                            <td>
                                <span title="{{ $shop->created_at->format('Y-m-d H:i') }}">
                                    {{ $shop->created_at->diffForHumans() }}
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <a href="{{ route('superadmin.shops.show', $shop->id) }}" class="btn-action btn-outline" style="font-size: 0.75rem; padding: 0.25rem 0.6rem;">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: #94a3b8; padding: 2.5rem;">
                                No shop workspaces registered yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($recent_shops->hasPages())
            <div style="padding: 1rem 1.25rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end;">
                {{ $recent_shops->links() }}
            </div>
        @endif
    </div>

    <!-- 5. System Activity Audit Stream -->
    <div class="data-card" style="margin-bottom: 0;">
        <div class="data-card-header">
            <div>
                <h2 class="data-card-title">System &amp; Admin Activity Log</h2>
                <p style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;">Live audit stream of administrative events</p>
            </div>
            <span class="status-pill status-active" style="font-size: 0.6875rem;">
                ● Stream Live
            </span>
        </div>

        <div class="activity-list">
            @forelse ($recent_activities as $act)
                @php
                    $badgeClass = match ($act->action) {
                        'payment.approved', 'subscription.activated' => 'status-active',
                        'shop.registered', 'subscription.trial_started' => 'status-trial',
                        'payment.rejected', 'shop.suspended' => 'status-rejected',
                        'payment.refunded' => 'status-pending',
                        default => 'status-trial'
                    };
                @endphp
                <div class="activity-item">
                    <div class="activity-icon bubble-indigo">
                        @if (str_contains($act->action, 'payment'))
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
                        @elseif (str_contains($act->action, 'shop'))
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg>
                        @else
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        @endif
                    </div>
                    <div class="activity-content">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;">
                            <span class="status-pill {{ $badgeClass }}" style="font-size: 0.65rem; padding: 0.08rem 0.45rem;">
                                {{ $act->action }}
                            </span>
                            <span style="font-size: 0.7rem; color: #94a3b8;">{{ $act->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="activity-desc" style="margin-top: 0.3rem;">
                            {{ $act->description }}
                        </div>
                        <div class="activity-meta">
                            <span>Actor: <strong>{{ $act->user->name ?? 'System' }}</strong></span>
                            @if ($act->shop)
                                <span>•</span>
                                <span>Shop: <a href="{{ route('superadmin.shops.show', $act->shop_id) }}" style="color: #4f46e5; text-decoration: none;">{{ $act->shop->name }}</a></span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div style="padding: 2.5rem 1.5rem; text-align: center; color: #94a3b8; font-size: 0.8125rem;">
                    No administrative activities recorded yet.
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Quick Recent Payments Table -->
<div class="data-card">
    <div class="data-card-header">
        <div>
            <h2 class="data-card-title">Recent Invoices &amp; Bank Payments</h2>
            <p style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;">Latest incoming subscription payment transactions</p>
        </div>
        <a href="{{ route('superadmin.payments.index') }}" class="btn-action btn-outline" style="font-size: 0.775rem;">View Complete Ledger</a>
    </div>
    <div style="overflow-x: auto;">
        <table class="saas-table">
            <thead>
                <tr>
                    <th>Shop</th>
                    <th>Subscribed Plan</th>
                    <th>Amount</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                    <th>Transaction Reference</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recent_payments as $payment)
                    <tr>
                        <td><strong>{{ $payment->shop->name ?? 'N/A' }}</strong></td>
                        <td>{{ $payment->plan->name ?? 'Standard' }}</td>
                        <td>
                            <strong style="color: #0f172a;">LKR {{ number_format($payment->amount, 2) }}</strong>
                            @if ($payment->refunded_amount > 0)
                                <div style="font-size: 0.7rem; color: #7c3aed;">
                                    Refunded: LKR {{ number_format($payment->refunded_amount, 2) }}
                                </div>
                            @endif
                        </td>
                        <td><span style="font-size: 0.75rem; text-transform: uppercase;">{{ str_replace('_', ' ', $payment->payment_method) }}</span></td>
                        <td>
                            <span class="status-pill status-{{ $payment->status }}">
                                {{ $payment->status }}
                            </span>
                        </td>
                        <td>
                            <code style="font-size: 0.75rem; background: #f1f5f9; padding: 0.15rem 0.4rem; border-radius: 4px;">
                                {{ $payment->reference_number ?: ($payment->transaction_id ?: 'MANUAL-SLIP') }}
                            </code>
                        </td>
                        <td>{{ $payment->created_at->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: #94a3b8; padding: 2rem;">No payment transactions recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
