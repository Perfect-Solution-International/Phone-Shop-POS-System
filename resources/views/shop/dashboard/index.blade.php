@extends('layouts.shop')

@section('title', 'Dashboard Overview')
@section('page-title', 'Shop Overview & Daily Register')

@section('content')
<style>
    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    .metric-card {
        background: #ffffff;
        border: 1px solid var(--surface-border);
        border-radius: 12px;
        padding: 1.35rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        position: relative;
        overflow: hidden;
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

    .metric-card.emerald::before { background: var(--color-success); }
    .metric-card.cyan::before { background: var(--brand-cyan-500); }
    .metric-card.amber::before { background: var(--color-warning); }
    .metric-card.rose::before { background: var(--color-danger); }

    .metric-label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted);
    }

    .metric-value {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--text-heading);
        line-height: 1.2;
    }

    .metric-sub {
        font-size: 0.8125rem;
        color: var(--text-muted);
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
            padding: 0.9rem;
        }
        .metric-value {
            font-size: 1.25rem;
        }
    }

    @media (max-width: 420px) {
        .metrics-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Metrics Row -->
<div class="metrics-grid">
    <div class="metric-card emerald">
        <span class="metric-label">Today's Total Sales</span>
        <span class="metric-value">LKR {{ number_format($metrics['today_sales_total'], 2) }}</span>
        <span class="metric-sub">{{ $metrics['today_transactions'] }} transactions completed</span>
    </div>

    <div class="metric-card cyan">
        <span class="metric-label">In-Stock Phones (IMEI)</span>
        <span class="metric-value">{{ number_format($metrics['available_phones']) }}</span>
        <span class="metric-sub">{{ $metrics['sold_phones_today'] }} phones sold today</span>
    </div>

    <div class="metric-card amber">
        <span class="metric-label">Low Stock Inventory</span>
        <span class="metric-value">{{ number_format($metrics['low_stock_count']) }} Items</span>
        <span class="metric-sub">Below reorder alert limit</span>
    </div>

    <div class="metric-card rose">
        <span class="metric-label">Outstanding Customer Credit</span>
        <span class="metric-value">LKR {{ number_format($metrics['outstanding_credit'], 2) }}</span>
        <span class="metric-sub">Across {{ $metrics['total_customers'] }} customer profiles</span>
    </div>
</div>

<!-- Collections Breakdown & Quick Actions -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <span class="card-title">Daily Cash Register & Settlement Breakdown</span>
        <span style="font-size: 0.8125rem; color: var(--text-muted);">{{ now()->toFormattedDateString() }}</span>
    </div>
    <div class="card-body" style="padding: 1.25rem 1.5rem;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div style="background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px solid var(--surface-border);">
                <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Cash Drawer</span>
                <p style="font-size: 1.25rem; font-weight: 700; color: #166534; margin-top: 0.25rem;">LKR {{ number_format($metrics['cash_collections'], 2) }}</p>
            </div>
            <div style="background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px solid var(--surface-border);">
                <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Card POS Terminal</span>
                <p style="font-size: 1.25rem; font-weight: 700; color: #1e40af; margin-top: 0.25rem;">LKR {{ number_format($metrics['card_collections'], 2) }}</p>
            </div>
            <div style="background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px solid var(--surface-border);">
                <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Bank Transfer / QR</span>
                <p style="font-size: 1.25rem; font-weight: 700; color: #6b21a8; margin-top: 0.25rem;">LKR {{ number_format($metrics['bank_collections'], 2) }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Two Columns: Recent Invoices & Available Phones -->
<div class="panels-2col">
    <!-- Recent Sales -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">Recent POS Transactions</span>
            <a href="{{ route('shop.pos') }}" class="btn btn-sm btn-secondary">New Sale</a>
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
                        <td style="font-weight: 600; font-family: monospace;">{{ $sale->invoice_number }}</td>
                        <td>{{ $sale->customer->name ?? 'Walk-in Customer' }}</td>
                        <td style="font-weight: 700;">LKR {{ number_format($sale->total_amount, 2) }}</td>
                        <td>
                            <span class="badge badge-success">{{ ucfirst($sale->payment_status) }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 2rem;">No sales recorded yet today.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Available Phones -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">Latest Serialized Stock (In Stock)</span>
            <a href="{{ route('shop.phones') }}" class="btn btn-sm btn-secondary">View All</a>
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
                            <strong>{{ $phone->brand }} {{ $phone->model }}</strong>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $phone->color }} {{ $phone->storage }}</div>
                        </td>
                        <td style="font-family: monospace; font-size: 0.8125rem;">{{ $phone->imei_1 }}</td>
                        <td style="font-weight: 700;">LKR {{ number_format($phone->selling_price, 2) }}</td>
                        <td>
                            <span class="badge badge-info">{{ ucfirst($phone->condition) }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 2rem;">No phones registered in stock.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

