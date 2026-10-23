@extends('layouts.customer')

@section('title', 'My Customer Portal')

@section('content')
<style>
    .portal-metrics {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    .p-metric {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        border-left: 4px solid var(--brand-primary);
    }

    .p-metric.cyan { border-left-color: #06b6d4; }
    .p-metric.green { border-left-color: var(--color-success); }
    .p-metric.amber { border-left-color: var(--color-warning); }

    .p-metric-label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted);
    }

    .p-metric-val {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--text-heading);
    }

    .portal-tabs {
        display: flex;
        gap: 0.5rem;
        border-bottom: 1px solid var(--border-color);
        margin-bottom: 1.5rem;
    }

    .portal-tab-btn {
        padding: 0.75rem 1.25rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-muted);
        background: none;
        border: none;
        border-bottom: 2px solid transparent;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .portal-tab-btn.active {
        color: var(--brand-primary);
        border-bottom-color: var(--brand-primary);
    }

    .tab-badge {
        background: #e0e7ff;
        color: var(--brand-primary);
        font-size: 0.7rem;
        padding: 0.15rem 0.5rem;
        border-radius: 9999px;
    }

    /* Modal */
    .modal-backdrop {
        display: none;
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(15, 23, 42, 0.6);
        z-index: 100;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .modal-backdrop.open { display: flex; }

    .modal-box {
        background: #ffffff;
        border-radius: 12px;
        max-width: 480px;
        width: 100%;
        padding: 1.5rem;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    }
</style>

<!-- Top Welcome Banner -->
<div style="background: linear-gradient(135deg, #4f46e5 0%, #06b6d4 100%); border-radius: 14px; padding: 1.75rem 2rem; color: #ffffff; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 0.25rem;">Welcome back, {{ auth()->user()->name }}!</h1>
        <p style="font-size: 0.875rem; opacity: 0.9;">Manage your device repairs, active warranties, and purchase invoices directly from your personal account.</p>
    </div>
    <div>
        <a href="{{ route('public.repair-track') }}" target="_blank" class="btn" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.4);">
            Public IMEI Search &rarr;
        </a>
    </div>
</div>

<!-- Metrics Cards -->
<div class="portal-metrics">
    <div class="p-metric">
        <span class="p-metric-label">Active Repairs</span>
        <span class="p-metric-val">{{ $metrics['active_repairs'] }}</span>
        <span style="font-size: 0.75rem; color: var(--text-muted);">Workshop tickets in progress</span>
    </div>

    <div class="p-metric cyan">
        <span class="p-metric-label">Active Warranties</span>
        <span class="p-metric-val">{{ $metrics['active_warranties'] }}</span>
        <span style="font-size: 0.75rem; color: var(--text-muted);">Protected mobile devices</span>
    </div>

    <div class="p-metric green">
        <span class="p-metric-label">Purchase Invoices</span>
        <span class="p-metric-val">{{ $metrics['total_invoices'] }}</span>
        <span style="font-size: 0.75rem; color: var(--text-muted);">Official shop sales receipts</span>
    </div>

    <div class="p-metric amber">
        <span class="p-metric-label">Account Balance</span>
        <span class="p-metric-val" style="font-size: 1.35rem; color: {{ $metrics['balance'] > 0 ? '#b91c1c' : '#15803d' }};">
            LKR {{ number_format($metrics['balance'], 2) }}
        </span>
        <span style="font-size: 0.75rem; color: var(--text-muted);">{{ $metrics['balance'] > 0 ? 'Payment due to shop' : 'No outstanding dues' }}</span>
    </div>
</div>

<!-- Navigation Tabs -->
<div class="portal-tabs">
    <button type="button" class="portal-tab-btn active" onclick="showTab('repairs', this)">
        🛠️ My Repair Jobs <span class="tab-badge">{{ $repairs->count() }}</span>
    </button>
    <button type="button" class="portal-tab-btn" onclick="showTab('warranties', this)">
        🛡️ Device Warranties <span class="tab-badge">{{ $warranties->count() }}</span>
    </button>
    <button type="button" class="portal-tab-btn" onclick="showTab('invoices', this)">
        🧾 Invoices & Receipts <span class="tab-badge">{{ $sales->count() }}</span>
    </button>
</div>

<!-- Tab 1: Repairs -->
<div id="tabContentRepairs" class="card">
    <div class="card-header">
        <span class="card-title">My Phone Repair Workshop Tickets</span>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Job Number</th>
                    <th>Device</th>
                    <th>Reported Problem</th>
                    <th>Current Stage</th>
                    <th>Estimated Ready</th>
                    <th>Total / Advance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($repairs as $r)
                <tr>
                    <td>
                        <span style="font-family: monospace; font-weight: 700; color: #4f46e5;">{{ $r->job_number }}</span>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Booked: {{ $r->created_at->toFormattedDateString() }}</div>
                    </td>
                    <td>
                        <strong>{{ $r->brand }} {{ $r->model }}</strong>
                        @if($r->imei)
                            <div style="font-family: monospace; font-size: 0.75rem; color: #64748b;">IMEI: {{ $r->imei }}</div>
                        @endif
                    </td>
                    <td style="max-width: 250px;">
                        <span style="font-size: 0.8125rem;">{{ $r->fault_description }}</span>
                    </td>
                    <td>
                        @php
                            $badgeClass = match($r->status) {
                                'delivered' => 'badge-success',
                                'ready_for_collection' => 'badge-info',
                                'in_repair', 'diagnosing' => 'badge-purple',
                                'waiting_for_parts', 'waiting_for_approval' => 'badge-warning',
                                default => 'badge-secondary',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}" style="font-size: 0.8rem; padding: 0.3rem 0.7rem;">
                            {{ ucwords(str_replace('_', ' ', $r->status)) }}
                        </span>
                        @if($r->status === 'ready_for_collection')
                            <div style="font-size: 0.7rem; color: #166534; font-weight: 700; margin-top: 4px;">Ready for Pickup!</div>
                        @endif
                    </td>
                    <td>
                        @if($r->estimated_completion_date)
                            <div style="font-size: 0.8125rem; font-weight: 600; color: #0369a1;">{{ $r->estimated_completion_date->toFormattedDateString() }}</div>
                        @else
                            <span style="font-size: 0.75rem; color: var(--text-muted);">In diagnosis</span>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight: 700;">LKR {{ number_format($r->final_cost ?? $r->estimated_cost, 2) }}</div>
                        @if($r->advance_amount > 0)
                            <div style="font-size: 0.75rem; color: #166534;">Adv: LKR {{ number_format($r->advance_amount, 2) }}</div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 3rem;">
                        You have no repair jobs registered with the workshop yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Tab 2: Warranties -->
<div id="tabContentWarranties" class="card" style="display: none;">
    <div class="card-header">
        <span class="card-title">My Device Warranty Certificates</span>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Certificate Number</th>
                    <th>IMEI / Serial</th>
                    <th>Warranty Type</th>
                    <th>Coverage Period</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($warranties as $w)
                <tr>
                    <td>
                        <span style="font-family: monospace; font-weight: 700; color: #4f46e5;">{{ $w->warranty_number }}</span>
                    </td>
                    <td>
                        <span style="font-family: monospace; font-weight: 600; color: #0284c7;">{{ $w->imei ?? 'Standard Item' }}</span>
                    </td>
                    <td>
                        <span style="text-transform: uppercase; font-size: 0.75rem; font-weight: 600; color: #475569;">{{ $w->warranty_type }} warranty</span>
                    </td>
                    <td>
                        <div style="font-size: 0.8125rem;">{{ $w->starts_on->toFormattedDateString() }} &rarr; <strong>{{ $w->ends_on->toFormattedDateString() }}</strong></div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $w->duration_months }} Months duration</div>
                    </td>
                    <td>
                        @if($w->isValid())
                            <span class="badge badge-success">Active & Protected</span>
                        @else
                            <span class="badge badge-danger">Expired</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 3rem;">
                        No warranty certificates found under your account.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Tab 3: Invoices & Receipts -->
<div id="tabContentInvoices" class="card" style="display: none;">
    <div class="card-header">
        <span class="card-title">My Purchase Invoices & Receipts</span>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Date</th>
                    <th>Items Purchased</th>
                    <th>Payment Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $s)
                <tr>
                    <td>
                        <span style="font-family: monospace; font-weight: 700; color: #4f46e5;">{{ $s->invoice_number }}</span>
                    </td>
                    <td>
                        <div style="font-size: 0.8125rem;">{{ $s->created_at->toFormattedDateString() }}</div>
                    </td>
                    <td>
                        <div style="font-size: 0.8125rem;">
                            @foreach($s->items as $item)
                                <div>• {{ $item->item_name }} (x{{ $item->quantity }})</div>
                            @endforeach
                        </div>
                    </td>
                    <td>
                        <strong style="color: var(--text-heading); font-size: 0.9375rem;">LKR {{ number_format($s->total_amount, 2) }}</strong>
                    </td>
                    <td>
                        <span class="badge badge-success">{{ ucfirst($s->payment_status) }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 3rem;">
                        No purchase invoices recorded under your account yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    function showTab(tab, el) {
        document.querySelectorAll('.portal-tab-btn').forEach(btn => btn.classList.remove('active'));
        el.classList.add('active');

        document.getElementById('tabContentRepairs').style.display = tab === 'repairs' ? 'block' : 'none';
        document.getElementById('tabContentWarranties').style.display = tab === 'warranties' ? 'block' : 'none';
        document.getElementById('tabContentInvoices').style.display = tab === 'invoices' ? 'block' : 'none';
    }
</script>
@endpush
@endsection

