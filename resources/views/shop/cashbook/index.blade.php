@extends('layouts.shop')

@section('title', 'Daily Cashbook & Drawer')
@section('page-title', 'Daily Cashbook & Cashier Reconciliation')

@section('content')
<style>
    .cashbook-filter-bar {
        margin-bottom: 0.85rem;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
    }
    .cashbook-summary-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.75rem;
        margin-bottom: 0.85rem;
    }
    .cashbook-metric-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .cashbook-metric-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px -2px rgba(0, 0, 0, 0.05);
    }
    .cashbook-metric-label {
        font-size: 0.6875rem;
        font-weight: 750;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .cashbook-metric-val {
        font-size: 1.35rem;
        font-weight: 800;
        margin-top: 0.2rem;
        line-height: 1.2;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, monospace;
    }
    .cashbook-metric-sub {
        font-size: 0.72rem;
        color: #64748b;
        margin-top: 0.25rem;
        line-height: 1.3;
    }
    .cashbook-split-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.85rem;
    }
    .compact-table th {
        padding: 0.5rem 0.85rem !important;
        font-size: 0.75rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.03em !important;
        background: #f8fafc !important;
        color: #64748b !important;
    }
    .compact-table td {
        padding: 0.5rem 0.85rem !important;
        font-size: 0.8125rem !important;
        vertical-align: middle !important;
    }
    @media (max-width: 992px) {
        .cashbook-summary-grid {
            grid-template-columns: 1fr;
        }
        .cashbook-split-grid {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 640px) {
        .cashbook-filter-form {
            flex-direction: column;
            align-items: stretch !important;
            width: 100%;
        }
        .cashbook-filter-form input {
            max-width: 100% !important;
            width: 100%;
        }
        .cashbook-action-buttons {
            width: 100%;
            flex-direction: column;
        }
    }
</style>

<!-- Cash & Reports Unified Sub-Navigation Tabs -->
<div class="shop-subnav-tabs no-print" style="display: flex; gap: 0.5rem; margin-bottom: 1.1rem; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.75rem; overflow-x: auto; align-items: center;">
    <a href="{{ route('shop.cashbook') }}" class="btn btn-sm {{ request()->routeIs('shop.cashbook*') ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: 8px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.45rem 0.9rem; text-decoration: none; white-space: nowrap;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
        Daily Cashbook &amp; Drawer
    </a>
    <a href="{{ route('shop.reports') }}" class="btn btn-sm {{ request()->routeIs('shop.reports*') ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: 8px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.45rem 0.9rem; text-decoration: none; white-space: nowrap;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
        Financial &amp; Performance Reports
    </a>
    <a href="{{ route('shop.expenses') }}" class="btn btn-sm {{ request()->routeIs('shop.expenses*') ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: 8px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.45rem 0.9rem; text-decoration: none; white-space: nowrap;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="2" y="4" width="20" height="16" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
        Operating Expenses
    </a>
</div>

<!-- Filter Date Bar -->
<div class="card cashbook-filter-bar">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.65rem 1rem; padding: 0.6rem 0.95rem;">
        <form method="GET" action="{{ route('shop.cashbook') }}" class="cashbook-filter-form" style="display: flex; gap: 0.45rem; align-items: center; flex-wrap: wrap; margin: 0;">
            <div style="display: inline-flex; align-items: center; gap: 0.4rem;">
                <label style="font-size: 0.775rem; font-weight: 700; color: #475569; margin: 0; white-space: nowrap;">Select Date:</label>
                <input type="date" name="date" class="form-control" value="{{ $selectedDate }}" style="width: 145px; height: 32px; padding: 0.25rem 0.55rem; font-size: 0.8rem; border-radius: 6px;" onchange="this.form.submit()">
            </div>
            <button type="submit" class="btn btn-secondary btn-sm" style="height: 32px; padding: 0.3rem 0.75rem; font-size: 0.775rem; font-weight: 650; border-radius: 6px;">Load Cashbook</button>
            <div style="display: inline-flex; gap: 0.35rem; align-items: center;">
                @if($selectedDate !== now()->toDateString())
                    <a href="{{ route('shop.cashbook') }}" class="btn btn-sm btn-outline-secondary" style="height: 32px; padding: 0.3rem 0.65rem; font-size: 0.75rem; font-weight: 600; border-radius: 6px; display: inline-flex; align-items: center; background: #eef2ff; border: 1px solid #c7d2fe; color: #4338ca;">Today</a>
                @else
                    <span class="badge" style="background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe; font-size: 0.72rem; padding: 0.35rem 0.6rem; border-radius: 6px; font-weight: 600;">Today's Register</span>
                @endif
                <a href="{{ route('shop.cashbook', ['date' => now()->subDay()->toDateString()]) }}" class="btn btn-sm btn-outline-secondary" style="height: 32px; padding: 0.3rem 0.65rem; font-size: 0.75rem; font-weight: 600; border-radius: 6px; display: inline-flex; align-items: center; background: #f8fafc; border: 1px solid #cbd5e1; color: #475569;">Yesterday</a>
            </div>
        </form>

        <div class="cashbook-action-buttons" style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
            <a href="{{ route('shop.expenses') }}" class="btn btn-secondary btn-sm" style="height: 32px; padding: 0.3rem 0.75rem; font-size: 0.775rem; font-weight: 600; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.35rem; background: #f8fafc; border: 1px solid #cbd5e1; color: #334155;">
                <span>View Operating Expenses</span>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </a>
            <a href="{{ route('shop.sales') }}" class="btn btn-secondary btn-sm" style="height: 32px; padding: 0.3rem 0.75rem; font-size: 0.775rem; font-weight: 600; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.35rem; background: #f8fafc; border: 1px solid #cbd5e1; color: #334155;">
                <span>View Sales Invoices</span>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </a>
        </div>
    </div>
</div>

<!-- Cash Balance Summary -->
<div class="cashbook-summary-grid">
    <div class="cashbook-metric-card" style="border: 1px solid #bbf7d0; border-left: 4px solid #16a34a;">
        <span class="cashbook-metric-label" style="color: #166534;">
            <span>Total Cash Inflow</span>
            <span style="background: #dcfce7; color: #15803d; font-size: 0.65rem; padding: 0.15rem 0.4rem; border-radius: 4px; font-weight: 700;">+ INFLOW</span>
        </span>
        <div class="cashbook-metric-val" style="color: #15803d;">
            + LKR {{ number_format($totalCashIn, 2) }}
        </div>
        <div class="cashbook-metric-sub">
            Sales: <strong>LKR {{ number_format($totalSalesCashIn, 2) }}</strong> &bull; Repairs: <strong>LKR {{ number_format($totalRepairCashIn, 2) }}</strong>
        </div>
    </div>

    <div class="cashbook-metric-card" style="border: 1px solid #fecaca; border-left: 4px solid #dc2626;">
        <span class="cashbook-metric-label" style="color: #991b1b;">
            <span>Total Cash Outflow</span>
            <span style="background: #fee2e2; color: #b91c1c; font-size: 0.65rem; padding: 0.15rem 0.4rem; border-radius: 4px; font-weight: 700;">- OUTFLOW</span>
        </span>
        <div class="cashbook-metric-val" style="color: #b91c1c;">
            - LKR {{ number_format($totalCashOut, 2) }}
        </div>
        <div class="cashbook-metric-sub">
            Expenses: <strong>LKR {{ number_format($totalCashExpenses, 2) }}</strong> &bull; Purchases: <strong>LKR {{ number_format($totalPurchasesCashOut, 2) }}</strong>
        </div>
    </div>

    <div class="cashbook-metric-card" style="border: 1px solid {{ $netCashBalance >= 0 ? '#cbd5e1' : '#fca5a5' }}; border-left: 4px solid {{ $netCashBalance >= 0 ? '#4f46e5' : '#dc2626' }};">
        <span class="cashbook-metric-label" style="color: #475569;">
            <span>Net Cash Drawer ({{ \Carbon\Carbon::parse($selectedDate)->format('M d, Y') }})</span>
            <span style="background: {{ $netCashBalance >= 0 ? '#e0e7ff' : '#fee2e2' }}; color: {{ $netCashBalance >= 0 ? '#4338ca' : '#b91c1c' }}; font-size: 0.65rem; padding: 0.15rem 0.4rem; border-radius: 4px; font-weight: 700;">{{ $netCashBalance >= 0 ? 'NET BALANCE' : 'DEFICIT' }}</span>
        </span>
        <div class="cashbook-metric-val" style="color: {{ $netCashBalance >= 0 ? '#0f172a' : '#b91c1c' }};">
            LKR {{ number_format($netCashBalance, 2) }}
        </div>
        <div class="cashbook-metric-sub">
            Drawer closing balance for this working day
        </div>
    </div>
</div>

<!-- Details Split Grid -->
<div class="cashbook-split-grid" style="margin-bottom: 0.85rem;">
    <!-- Cash In -->
    <div class="card" style="border-radius: 10px; overflow: hidden; margin-bottom: 0;">
        <div class="card-header" style="background: #f0fdf4; border-bottom: 1px solid #bbf7d0; padding: 0.6rem 0.95rem; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 0.4rem;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 8 12 12 14 14"></polyline></svg>
                <strong style="color: #166534; font-size: 0.85rem;">+ Cash Received into Drawer</strong>
            </div>
            <span class="badge badge-success" style="font-size: 0.72rem; padding: 0.2rem 0.55rem; font-weight: 700;">LKR {{ number_format($totalCashIn, 2) }}</span>
        </div>
        <div class="table-responsive">
            <table class="data-table compact-table" style="margin: 0;">
                <thead>
                    <tr>
                        <th style="width: 45%;">Source</th>
                        <th style="width: 25%;">Reference</th>
                        <th style="text-align: right; width: 30%;">Cash Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cashSales as $p)
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.4rem;">
                                <span class="badge badge-success" style="font-size: 0.65rem; padding: 0.15rem 0.4rem;">POS Sale</span>
                                <span style="font-size: 0.78rem; font-weight: 600; color: #1e293b;">{{ $p->sale->customer->name ?? 'Walk-in Customer' }}</span>
                            </div>
                        </td>
                        <td style="font-family: monospace; font-size: 0.78rem; color: #4f46e5;">
                            {{ $p->sale->invoice_number ?? 'Invoice' }}
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #166534; font-family: monospace; font-size: 0.8125rem;">
                            LKR {{ number_format($p->amount, 2) }}
                        </td>
                    </tr>
                    @empty
                    @endforelse

                    @forelse($repairAdvances as $rep)
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.4rem;">
                                <span class="badge badge-purple" style="font-size: 0.65rem; padding: 0.15rem 0.4rem;">Repair Advance</span>
                                <span style="font-size: 0.78rem; font-weight: 600; color: #1e293b;">{{ $rep->customer->name ?? 'Customer' }}</span>
                            </div>
                            <div style="font-size: 0.7rem; color: #64748b; margin-top: 2px;">{{ $rep->brand }} {{ $rep->model }}</div>
                        </td>
                        <td style="font-family: monospace; font-size: 0.78rem; color: #4f46e5;">
                            {{ $rep->job_number }}
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #166534; font-family: monospace; font-size: 0.8125rem;">
                            LKR {{ number_format($rep->advance_amount, 2) }}
                        </td>
                    </tr>
                    @empty
                    @endforelse

                    @forelse($repairDeliveries as $repDel)
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.4rem;">
                                <span class="badge" style="background: #0284c7; color: #ffffff; font-size: 0.65rem; padding: 0.15rem 0.4rem;">Repair Delivery</span>
                                <span style="font-size: 0.78rem; font-weight: 600; color: #1e293b;">{{ $repDel->customer->name ?? 'Customer' }}</span>
                            </div>
                            <div style="font-size: 0.7rem; color: #64748b; margin-top: 2px;">{{ $repDel->brand }} {{ $repDel->model }} (Final Settlement)</div>
                        </td>
                        <td style="font-family: monospace; font-size: 0.78rem; color: #4f46e5;">
                            {{ $repDel->job_number }}
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #166534; font-family: monospace; font-size: 0.8125rem;">
                            LKR {{ number_format(max(0, (float) $repDel->final_cost - (float) $repDel->advance_amount), 2) }}
                        </td>
                    </tr>
                    @empty
                    @endforelse

                    @forelse($debtPayments as $dp)
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.4rem;">
                                <span class="badge badge-warning" style="font-size: 0.65rem; padding: 0.15rem 0.4rem;">Debt Settlement</span>
                                <span style="font-size: 0.78rem; font-weight: 600; color: #1e293b;">{{ $dp->customer->name ?? 'Customer' }}</span>
                            </div>
                            <div style="font-size: 0.7rem; color: #64748b; margin-top: 2px;">{{ $dp->notes ?? 'Customer outstanding debt payment' }}</div>
                        </td>
                        <td style="font-family: monospace; font-size: 0.78rem; color: #4f46e5;">
                            {{ $dp->reference ?? 'Counter Receipt' }}
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #166534; font-family: monospace; font-size: 0.8125rem;">
                            LKR {{ number_format($dp->amount, 2) }}
                        </td>
                    </tr>
                    @empty
                    @endforelse

                    @if($cashSales->isEmpty() && $repairAdvances->isEmpty() && $repairDeliveries->isEmpty() && $debtPayments->isEmpty())
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 1.25rem; color: #94a3b8; font-size: 0.8125rem;">
                            No cash received on this date.
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Cash Out -->
    <div class="card" style="border-radius: 10px; overflow: hidden; margin-bottom: 0;">
        <div class="card-header" style="background: #fef2f2; border-bottom: 1px solid #fecaca; padding: 0.6rem 0.95rem; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 0.4rem;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                <strong style="color: #991b1b; font-size: 0.85rem;">- Cash Paid Out from Drawer</strong>
            </div>
            <span class="badge badge-danger" style="font-size: 0.72rem; padding: 0.2rem 0.55rem; font-weight: 700;">LKR {{ number_format($totalCashOut, 2) }}</span>
        </div>
        <div class="table-responsive">
            <table class="data-table compact-table" style="margin: 0;">
                <thead>
                    <tr>
                        <th style="width: 35%;">Category</th>
                        <th style="width: 35%;">Details</th>
                        <th style="text-align: right; width: 30%;">Cash Paid</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cashExpenses as $exp)
                    <tr>
                        <td>
                            <span class="badge badge-danger" style="font-size: 0.65rem; padding: 0.15rem 0.4rem; text-transform: uppercase;">{{ $exp->category }}</span>
                        </td>
                        <td style="font-size: 0.8125rem;">
                            <strong style="color: #1e293b;">{{ $exp->title }}</strong>
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #b91c1c; font-family: monospace; font-size: 0.8125rem;">
                            LKR {{ number_format($exp->amount, 2) }}
                        </td>
                    </tr>
                    @empty
                    @endforelse

                    @forelse($cashPurchases as $pur)
                    <tr>
                        <td>
                            <span class="badge badge-warning" style="font-size: 0.65rem; padding: 0.15rem 0.4rem;">Stock Purchase</span>
                        </td>
                        <td style="font-size: 0.8125rem;">
                            <strong style="color: #1e293b;">{{ $pur->purchase_number }}</strong>
                            <div style="font-size: 0.7rem; color: #64748b;">Supplier: {{ $pur->supplier->name ?? 'Vendor' }}</div>
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #b91c1c; font-family: monospace; font-size: 0.8125rem;">
                            LKR {{ number_format($pur->paid_amount, 2) }}
                        </td>
                    </tr>
                    @empty
                    @endforelse

                    @if($cashExpenses->isEmpty() && $cashPurchases->isEmpty())
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 1.25rem; color: #94a3b8; font-size: 0.8125rem;">
                            No cash paid out on this date.
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- End-of-Day Register Closing & Cash Drawer Reconciliation -->
<div class="card" style="margin-top: 0.85rem; border: 1.5px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 5px -1px rgba(0,0,0,0.05);">
    <div class="card-header" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #ffffff; padding: 0.75rem 1.15rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color: #38bdf8;"><rect x="2" y="4" width="20" height="16" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
                <h3 style="margin: 0; font-size: 0.95rem; font-weight: 800; color: #ffffff;">Daily Cash Drawer Closing &amp; Reconciliation</h3>
            </div>
            <p style="margin: 0.15rem 0 0; font-size: 0.75rem; color: #94a3b8;">
                Count physical cash in your drawer and reconcile with the expected POS net cash balance for <strong>{{ \Carbon\Carbon::parse($selectedDate)->format('F d, Y') }}</strong>.
            </p>
        </div>
        <div>
            <button type="button" class="btn btn-sm" onclick="printRegisterSummary()" style="background: #ffffff; color: #0f172a; font-weight: 700; border: none; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.85rem; font-size: 0.75rem; height: 30px; box-shadow: 0 1px 3px rgba(0,0,0,0.15);">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                Print Closing Sign-off Slip
            </button>
        </div>
    </div>

    <div style="padding: 1rem 1.15rem; background: #ffffff;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;" class="cashbook-split-grid">
            <!-- Left: Expected System Position -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 0.85rem 1rem;">
                <h4 style="font-size: 0.78rem; font-weight: 800; text-transform: uppercase; color: #475569; margin: 0 0 0.65rem 0; letter-spacing: 0.04em;">
                    1. Expected System Cash Balance
                </h4>
                <div style="display: flex; flex-direction: column; gap: 0.45rem; font-size: 0.8125rem;">
                    <div style="display: flex; justify-content: space-between; color: #166534;">
                        <span>+ POS Sales Cash:</span>
                        <strong style="font-family: monospace;">LKR {{ number_format($totalSalesCashIn, 2) }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; color: #166534;">
                        <span>+ Repair Workshop Advance Cash:</span>
                        <strong style="font-family: monospace;">LKR {{ number_format($totalRepairCashIn, 2) }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-top: 1px dashed #cbd5e1; padding-top: 0.35rem; font-weight: 700; color: #15803d;">
                        <span>Total Cash Received:</span>
                        <span style="font-family: monospace;">LKR {{ number_format($totalCashIn, 2) }}</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; color: #b91c1c; margin-top: 0.25rem;">
                        <span>- Operating Expenses (Cash):</span>
                        <strong style="font-family: monospace;">-LKR {{ number_format($totalCashExpenses, 2) }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; color: #b91c1c;">
                        <span>- Stock Purchases (Cash):</span>
                        <strong style="font-family: monospace;">-LKR {{ number_format($totalPurchasesCashOut, 2) }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-top: 1px dashed #cbd5e1; padding-top: 0.35rem; font-weight: 700; color: #b91c1c;">
                        <span>Total Cash Paid Out:</span>
                        <span style="font-family: monospace;">-LKR {{ number_format($totalCashOut, 2) }}</span>
                    </div>

                    <div style="border-top: 2px solid #cbd5e1; padding-top: 0.5rem; margin-top: 0.35rem; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-weight: 800; font-size: 0.875rem; color: #0f172a;">Expected In Drawer:</span>
                        <span id="reconExpectedVal" style="font-size: 1.15rem; font-weight: 900; color: #4f46e5; font-family: monospace;">
                            LKR {{ number_format($netCashBalance, 2) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right: Actual Physical Cash Counted -->
            <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 0.85rem 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.65rem;">
                    <h4 style="font-size: 0.78rem; font-weight: 800; text-transform: uppercase; color: #0f172a; margin: 0; letter-spacing: 0.04em;">
                        2. Cashier Physical Count
                    </h4>
                    <div style="display: flex; gap: 0.25rem;">
                        <button type="button" class="btn btn-sm" id="btnCountModeQuick" onclick="setCountMode('quick')" style="background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe; font-size: 0.72rem; font-weight: 700; padding: 0.15rem 0.5rem; height: 26px; border-radius: 5px;">Quick Total</button>
                        <button type="button" class="btn btn-sm" id="btnCountModeDenom" onclick="setCountMode('denom')" style="background: #f8fafc; color: #64748b; border: 1px solid #cbd5e1; font-size: 0.72rem; font-weight: 700; padding: 0.15rem 0.5rem; height: 26px; border-radius: 5px;">Denominations</button>
                    </div>
                </div>

                <!-- Quick Total Mode -->
                <div id="quickCountWrap" style="margin-bottom: 0.65rem;">
                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 0.25rem;">Total Counted Cash in Drawer (LKR):</label>
                    <input type="number" step="0.01" id="inpDirectCount" class="form-control" placeholder="0.00" oninput="calculateReconciliation()" style="height: 36px; font-size: 1.05rem; font-weight: 800; color: #0f172a; font-family: monospace; border-radius: 6px;">
                </div>

                <!-- Denominations Mode -->
                <div id="denomCountWrap" style="display: none; margin-bottom: 0.65rem;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.35rem; font-size: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.35rem;">
                            <span style="width: 55px; font-weight: 700; color: #475569;">Rs. 5,000 &times;</span>
                            <input type="number" min="0" class="form-control denom-input" data-val="5000" placeholder="0" oninput="calculateDenoms()" style="height: 28px; padding: 0.15rem 0.35rem; font-size: 0.75rem; border-radius: 5px;">
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.35rem;">
                            <span style="width: 55px; font-weight: 700; color: #475569;">Rs. 1,000 &times;</span>
                            <input type="number" min="0" class="form-control denom-input" data-val="1000" placeholder="0" oninput="calculateDenoms()" style="height: 28px; padding: 0.15rem 0.35rem; font-size: 0.75rem; border-radius: 5px;">
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.35rem;">
                            <span style="width: 55px; font-weight: 700; color: #475569;">Rs. 500 &times;</span>
                            <input type="number" min="0" class="form-control denom-input" data-val="500" placeholder="0" oninput="calculateDenoms()" style="height: 28px; padding: 0.15rem 0.35rem; font-size: 0.75rem; border-radius: 5px;">
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.35rem;">
                            <span style="width: 55px; font-weight: 700; color: #475569;">Rs. 100 &times;</span>
                            <input type="number" min="0" class="form-control denom-input" data-val="100" placeholder="0" oninput="calculateDenoms()" style="height: 28px; padding: 0.15rem 0.35rem; font-size: 0.75rem; border-radius: 5px;">
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.35rem;">
                            <span style="width: 55px; font-weight: 700; color: #475569;">Rs. 50 &times;</span>
                            <input type="number" min="0" class="form-control denom-input" data-val="50" placeholder="0" oninput="calculateDenoms()" style="height: 28px; padding: 0.15rem 0.35rem; font-size: 0.75rem; border-radius: 5px;">
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.35rem;">
                            <span style="width: 55px; font-weight: 700; color: #475569;">Coins &times;</span>
                            <input type="number" step="0.5" min="0" id="denomCoins" class="form-control" placeholder="0.00" oninput="calculateDenoms()" style="height: 28px; padding: 0.15rem 0.35rem; font-size: 0.75rem; border-radius: 5px;">
                        </div>
                    </div>
                </div>

                <!-- Reconciliation Result Badge -->
                <div id="reconResultWrap" style="background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 8px; padding: 0.6rem 0.75rem; text-align: center; margin-bottom: 0.65rem;">
                    <span style="font-size: 0.68rem; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">Reconciliation Variance</span>
                    <div id="reconDiffLabel" style="font-size: 1.05rem; font-weight: 900; color: #64748b; margin-top: 0.15rem; font-family: monospace;">
                        Enter counted cash to compare
                    </div>
                    <div id="reconDiffDesc" style="font-size: 0.72rem; color: #94a3b8; margin-top: 0.15rem;">
                        Expected: LKR {{ number_format($netCashBalance, 2) }}
                    </div>
                </div>

                <!-- Cashier Sign-off Notes -->
                <div>
                    <label style="display: block; font-size: 0.72rem; font-weight: 700; color: #475569; margin-bottom: 0.2rem;">Cashier Closing Notes / Discrepancy Reason:</label>
                    <input type="text" id="inpClosingNotes" class="form-control" placeholder="e.g. Verified by Cashier {{ auth()->user()->name }}. No discrepancies." style="height: 32px; font-size: 0.775rem; border-radius: 6px;">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Printable Closing Slip -->
<div id="printableClosingSlip" style="display: none;">
    <div style="font-family: 'Courier New', monospace; max-width: 400px; margin: 0 auto; padding: 1.5rem; border: 1px solid #000;">
        <div style="text-align: center; border-bottom: 1.5px dashed #000; padding-bottom: 0.75rem; margin-bottom: 0.75rem;">
            <h3 style="margin: 0; font-size: 1.15rem; text-transform: uppercase;">{{ auth()->user()->shop->name ?? 'PHONE SHOP' }}</h3>
            <div style="font-size: 0.8rem; margin-top: 0.2rem;">DAILY CASH DRAWER CLOSING REPORT</div>
            <div style="font-size: 0.8rem; margin-top: 0.2rem;">Date: {{ \Carbon\Carbon::parse($selectedDate)->format('Y-m-d') }}</div>
            <div style="font-size: 0.8rem;">Cashier / User: {{ auth()->user()->name }}</div>
        </div>

        <div style="font-size: 0.85rem; line-height: 1.6; border-bottom: 1.5px dashed #000; padding-bottom: 0.75rem; margin-bottom: 0.75rem;">
            <div style="display: flex; justify-content: space-between;">
                <span>+ POS Sales Cash:</span>
                <span>LKR {{ number_format($totalSalesCashIn, 2) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span>+ Repair Advance Cash:</span>
                <span>LKR {{ number_format($totalRepairCashIn, 2) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; font-weight: bold;">
                <span>TOTAL CASH INFLOW:</span>
                <span>LKR {{ number_format($totalCashIn, 2) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span>- Cash Operating Expenses:</span>
                <span>LKR {{ number_format($totalCashExpenses, 2) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span>- Cash Stock Purchases:</span>
                <span>LKR {{ number_format($totalPurchasesCashOut, 2) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; font-weight: bold;">
                <span>TOTAL CASH OUTFLOW:</span>
                <span>LKR {{ number_format($totalCashOut, 2) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; border-top: 1px solid #000; padding-top: 0.35rem; margin-top: 0.35rem; font-weight: bold; font-size: 0.95rem;">
                <span>EXPECTED DRAWER CASH:</span>
                <span>LKR {{ number_format($netCashBalance, 2) }}</span>
            </div>
        </div>

        <div style="font-size: 0.85rem; line-height: 1.6; border-bottom: 1.5px dashed #000; padding-bottom: 0.75rem; margin-bottom: 1rem;">
            <div style="display: flex; justify-content: space-between; font-weight: bold;">
                <span>PHYSICAL COUNTED CASH:</span>
                <span id="slipCountedVal">LKR 0.00</span>
            </div>
            <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 0.95rem; margin-top: 0.25rem;">
                <span>CLOSING VARIANCE:</span>
                <span id="slipVarianceVal">LKR 0.00</span>
            </div>
            <div style="margin-top: 0.5rem; font-size: 0.75rem; color: #333;">
                <span>Notes: </span><span id="slipNotesVal">—</span>
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; margin-top: 2rem; font-size: 0.8rem; text-align: center;">
            <div style="border-top: 1px solid #000; width: 45%; padding-top: 0.35rem;">
                Cashier Signature
            </div>
            <div style="border-top: 1px solid #000; width: 45%; padding-top: 0.35rem;">
                Manager Signature
            </div>
        </div>
    </div>
</div>

<script>
    const expectedDrawerCash = {{ (float) $netCashBalance }};
    let currentMode = 'quick';

    function setCountMode(mode) {
        currentMode = mode;
        const quickWrap = document.getElementById('quickCountWrap');
        const denomWrap = document.getElementById('denomCountWrap');
        const btnQuick = document.getElementById('btnCountModeQuick');
        const btnDenom = document.getElementById('btnCountModeDenom');

        if (mode === 'quick') {
            quickWrap.style.display = 'block';
            denomWrap.style.display = 'none';
            btnQuick.style.background = '#eef2ff';
            btnQuick.style.color = '#4338ca';
            btnQuick.style.border = '1px solid #c7d2fe';
            btnDenom.style.background = '#f8fafc';
            btnDenom.style.color = '#64748b';
            btnDenom.style.border = '1px solid #cbd5e1';
            calculateReconciliation();
        } else {
            quickWrap.style.display = 'none';
            denomWrap.style.display = 'block';
            btnDenom.style.background = '#eef2ff';
            btnDenom.style.color = '#4338ca';
            btnDenom.style.border = '1px solid #c7d2fe';
            btnQuick.style.background = '#f8fafc';
            btnQuick.style.color = '#64748b';
            btnQuick.style.border = '1px solid #cbd5e1';
            calculateDenoms();
        }
    }

    function calculateDenoms() {
        let total = 0;
        document.querySelectorAll('.denom-input').forEach(inp => {
            const count = parseInt(inp.value || 0);
            const val = parseFloat(inp.getAttribute('data-val') || 0);
            if (count > 0) total += count * val;
        });
        const coins = parseFloat(document.getElementById('denomCoins').value || 0);
        if (coins > 0) total += coins;

        document.getElementById('inpDirectCount').value = total.toFixed(2);
        updateReconciliationDisplay(total);
    }

    function calculateReconciliation() {
        const total = parseFloat(document.getElementById('inpDirectCount').value || 0);
        updateReconciliationDisplay(total);
    }

    function updateReconciliationDisplay(counted) {
        const wrap = document.getElementById('reconResultWrap');
        const label = document.getElementById('reconDiffLabel');
        const desc = document.getElementById('reconDiffDesc');

        const diff = counted - expectedDrawerCash;

        if (counted === 0 && !document.getElementById('inpDirectCount').value) {
            wrap.style.background = '#f8fafc';
            wrap.style.borderColor = '#cbd5e1';
            label.innerText = 'Enter counted cash to compare';
            label.style.color = '#64748b';
            desc.innerText = 'Expected: LKR ' + expectedDrawerCash.toLocaleString('en-US', {minimumFractionDigits: 2});
            return;
        }

        if (Math.abs(diff) < 0.01) {
            wrap.style.background = '#ecfdf5';
            wrap.style.borderColor = '#6ee7b7';
            label.innerText = '✓ Balanced (LKR 0.00 Variance)';
            label.style.color = '#047857';
            desc.innerText = 'Physical drawer cash perfectly matches expected POS sales balance.';
        } else if (diff > 0) {
            wrap.style.background = '#eff6ff';
            wrap.style.borderColor = '#93c5fd';
            label.innerText = '+LKR ' + diff.toLocaleString('en-US', {minimumFractionDigits: 2}) + ' (Surplus / Overage)';
            label.style.color = '#1d4ed8';
            desc.innerText = 'Physical cash is greater than system balance. Check for unrecorded entries.';
        } else {
            wrap.style.background = '#fef2f2';
            wrap.style.borderColor = '#fca5a5';
            label.innerText = '-LKR ' + Math.abs(diff).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' (Shortage / Deficit)';
            label.style.color = '#b91c1c';
            desc.innerText = 'Physical cash is less than system balance. Investigate shortfall.';
        }
    }

    function printRegisterSummary() {
        const counted = parseFloat(document.getElementById('inpDirectCount').value || 0);
        const diff = counted - expectedDrawerCash;
        const notes = document.getElementById('inpClosingNotes').value.trim() || 'No additional notes provided.';

        document.getElementById('slipCountedVal').innerText = 'LKR ' + counted.toLocaleString('en-US', {minimumFractionDigits: 2});
        let diffText = 'LKR ' + Math.abs(diff).toLocaleString('en-US', {minimumFractionDigits: 2});
        if (Math.abs(diff) < 0.01) {
            diffText += ' (BALANCED)';
        } else if (diff > 0) {
            diffText += ' (SURPLUS / OVER)';
        } else {
            diffText += ' (SHORTAGE / DEFICIT)';
        }
        document.getElementById('slipVarianceVal').innerText = diffText;
        document.getElementById('slipNotesVal').innerText = notes;

        const printWin = window.open('', '', 'width=500,height=650');
        printWin.document.write('<html><head><title>Cash Drawer Closing Report</title></head><body>' + document.getElementById('printableClosingSlip').innerHTML + '</body></html>');
        printWin.document.close();
        printWin.focus();
        setTimeout(function() {
            printWin.print();
            printWin.close();
        }, 300);
    }
</script>
@endsection

