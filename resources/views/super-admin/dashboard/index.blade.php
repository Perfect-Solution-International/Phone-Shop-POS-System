@extends('layouts.super-admin')

@section('title', 'Platform Dashboard')
@section('page_title', 'SaaS Platform Overview')

@section('content')
    <!-- Metric Cards Grid -->
    <div class="metric-grid">
        <div class="metric-card">
            <span class="metric-label">Monthly Recurring Revenue</span>
            <div class="metric-value">LKR {{ number_format($mrr, 2) }}</div>
            <span class="metric-sub">From active paid subscriptions</span>
        </div>

        <div class="metric-card">
            <span class="metric-label">Registered Shops</span>
            <div class="metric-value">{{ $total_shops }}</div>
            <span class="metric-sub" style="color: #64748b;">Total workspaces</span>
        </div>

        <div class="metric-card">
            <span class="metric-label">Active Subscriptions</span>
            <div class="metric-value" style="color: var(--color-success);">{{ $active_subscriptions }}</div>
            <span class="metric-sub" style="color: var(--color-success);">Paid accounts</span>
        </div>

        <div class="metric-card">
            <span class="metric-label">Free Trial Shops</span>
            <div class="metric-value" style="color: #2563eb;">{{ $trial_shops }}</div>
            <span class="metric-sub" style="color: #2563eb;">7-day evaluation</span>
        </div>

        <div class="metric-card">
            <span class="metric-label">Pending Bank Approvals</span>
            <div class="metric-value" style="color: {{ $pending_approvals > 0 ? '#ef4444' : '#64748b' }};">{{ $pending_approvals }}</div>
            <span class="metric-sub" style="color: #ef4444;">Needs verification</span>
        </div>

        <div class="metric-card">
            <span class="metric-label">Expired / Suspended</span>
            <div class="metric-value" style="color: #94a3b8;">{{ $expired_subscriptions }}</div>
            <span class="metric-sub" style="color: #64748b;">Action needed</span>
        </div>
    </div>

    <!-- Expiring Soon Notice (if any) -->
    @if ($expiring_soon->isNotEmpty())
        <div class="data-card" style="border-left: 4px solid #f59e0b;">
            <div class="data-card-header" style="background: #fffbeb;">
                <h2 class="data-card-title" style="color: #92400e; font-size: 0.95rem;">⚠️ Subscriptions Expiring Within 3 Days</h2>
            </div>
            <table class="saas-table">
                <thead>
                    <tr>
                        <th>Shop Name</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Expires At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($expiring_soon as $sub)
                        <tr>
                            <td><strong>{{ $sub->shop->name ?? 'Unknown' }}</strong></td>
                            <td>{{ $sub->plan->name ?? 'Standard' }}</td>
                            <td><span class="status-pill status-{{ $sub->status }}">{{ $sub->status }}</span></td>
                            <td>{{ $sub->current_period_ends_at->diffForHumans() }} ({{ $sub->current_period_ends_at->format('Y-m-d') }})</td>
                            <td>
                                <a href="{{ route('superadmin.shops.show', $sub->shop_id) }}" class="btn-action btn-outline">View Shop</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
        <!-- Recent Registrations -->
        <div class="data-card">
            <div class="data-card-header">
                <h2 class="data-card-title">Recent Shop Registrations</h2>
                <a href="{{ route('superadmin.shops.index') }}" class="btn-action btn-outline" style="font-size: 0.775rem;">View All</a>
            </div>
            <table class="saas-table">
                <thead>
                    <tr>
                        <th>Shop</th>
                        <th>Owner</th>
                        <th>Status</th>
                        <th>Registered</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recent_shops as $shop)
                        <tr>
                            <td>
                                <strong>{{ $shop->name }}</strong>
                            </td>
                            <td>
                                <div>{{ $shop->users->first()->name ?? 'N/A' }}</div>
                                <div style="font-size: 0.75rem; color: #64748b;">{{ $shop->users->first()->email ?? '' }}</div>
                            </td>
                            <td>
                                <span class="status-pill status-{{ $shop->subscription->status ?? 'trial' }}">
                                    {{ $shop->subscription->status ?? 'Trial' }}
                                </span>
                            </td>
                            <td>{{ $shop->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: #94a3b8; padding: 2rem;">No registered shops yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Recent Payments -->
        <div class="data-card">
            <div class="data-card-header">
                <h2 class="data-card-title">Recent Payments &amp; Invoices</h2>
                <a href="{{ route('superadmin.payments.index') }}" class="btn-action btn-outline" style="font-size: 0.775rem;">View All</a>
            </div>
            <table class="saas-table">
                <thead>
                    <tr>
                        <th>Shop</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recent_payments as $payment)
                        <tr>
                            <td><strong>{{ $payment->shop->name ?? 'N/A' }}</strong></td>
                            <td><strong>LKR {{ number_format($payment->amount, 2) }}</strong></td>
                            <td><span style="font-size: 0.75rem; text-transform: uppercase;">{{ str_replace('_', ' ', $payment->payment_method) }}</span></td>
                            <td>
                                <span class="status-pill status-{{ $payment->status }}">
                                    {{ $payment->status }}
                                </span>
                            </td>
                            <td>{{ $payment->created_at->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: #94a3b8; padding: 2rem;">No payments recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

