@extends('layouts.shop')

@section('title', 'Daily Cashbook & Drawer')
@section('page-title', 'Daily Cashbook & Cashier Reconciliation')

@section('content')
<style>
    .cashbook-split-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }
    @media (max-width: 992px) {
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
<div class="card" style="margin-bottom: 1.5rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; padding: 1.25rem 1.5rem;">
        <form method="GET" action="{{ route('shop.cashbook') }}" class="cashbook-filter-form" style="display: flex; gap: 0.75rem; align-items: center;">
            <label style="font-size: 0.875rem; font-weight: 700; color: #475569; margin: 0;">Select Date:</label>
            <input type="date" name="date" class="form-control" value="{{ $selectedDate }}" style="max-width: 170px;" onchange="this.form.submit()">
            <button type="submit" class="btn btn-secondary btn-sm">Load Cashbook</button>
            @if($selectedDate !== now()->toDateString())
                <a href="{{ route('shop.cashbook') }}" class="btn btn-sm btn-secondary" style="color: #64748b;">Today</a>
            @endif
        </form>

        <div class="cashbook-action-buttons" style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
            <a href="{{ route('shop.expenses') }}" class="btn btn-secondary btn-sm">
                View All Operating Expenses &rarr;
            </a>
            <a href="{{ route('shop.sales') }}" class="btn btn-secondary btn-sm">
                View Sales Invoices &rarr;
            </a>
        </div>
    </div>
</div>

<!-- Cash Balance Summary -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem;">
    <div style="background: #ffffff; border: 1px solid #bbf7d0; border-radius: 12px; padding: 1.25rem 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #166534; display: block;">Total Cash Inflow</span>
        <div style="font-size: 1.6rem; font-weight: 800; color: #15803d; margin-top: 0.35rem;">
            + LKR {{ number_format($totalCashIn, 2) }}
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">
            Sales: LKR {{ number_format($totalSalesCashIn, 2) }} | Repairs: LKR {{ number_format($totalRepairCashIn, 2) }}
        </div>
    </div>

    <div style="background: #ffffff; border: 1px solid #fecaca; border-radius: 12px; padding: 1.25rem 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #991b1b; display: block;">Total Cash Outflow</span>
        <div style="font-size: 1.6rem; font-weight: 800; color: #b91c1c; margin-top: 0.35rem;">
            - LKR {{ number_format($totalCashOut, 2) }}
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">
            Expenses: LKR {{ number_format($totalCashExpenses, 2) }} | Purchases: LKR {{ number_format($totalPurchasesCashOut, 2) }}
        </div>
    </div>

    <div style="background: #ffffff; border: 1px solid {{ $netCashBalance >= 0 ? '#cbd5e1' : '#fca5a5' }}; border-radius: 12px; padding: 1.25rem 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #475569; display: block;">Net Cash Drawer ({{ \Carbon\Carbon::parse($selectedDate)->format('M d, Y') }})</span>
        <div style="font-size: 1.6rem; font-weight: 800; color: {{ $netCashBalance >= 0 ? '#0f172a' : '#b91c1c' }}; margin-top: 0.35rem;">
            LKR {{ number_format($netCashBalance, 2) }}
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">
            Drawer closing balance for this working day
        </div>
    </div>
</div>

<!-- Details Split Grid -->
<div class="cashbook-split-grid">
    <!-- Cash In -->
    <div class="card">
        <div class="card-header" style="background: #f0fdf4; border-bottom: 1px solid #bbf7d0;">
            <strong style="color: #166534; font-size: 0.9375rem;">+ Cash Received into Drawer</strong>
            <span class="badge badge-success">LKR {{ number_format($totalCashIn, 2) }}</span>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Source</th>
                        <th>Reference</th>
                        <th style="text-align: right;">Cash Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cashSales as $p)
                    <tr>
                        <td>
                            <span class="badge badge-success" style="font-size: 0.68rem;">POS Sale</span>
                            <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">{{ $p->sale->customer->name ?? 'Walk-in Customer' }}</div>
                        </td>
                        <td style="font-family: monospace; font-size: 0.8125rem; color: #4f46e5;">
                            {{ $p->sale->invoice_number ?? 'Invoice' }}
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #166534;">
                            LKR {{ number_format($p->amount, 2) }}
                        </td>
                    </tr>
                    @empty
                    @endforelse

                    @forelse($repairAdvances as $rep)
                    <tr>
                        <td>
                            <span class="badge badge-purple" style="font-size: 0.68rem;">Repair Advance</span>
                            <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">{{ $rep->customer->name ?? 'Customer' }} ({{ $rep->brand }} {{ $rep->model }})</div>
                        </td>
                        <td style="font-family: monospace; font-size: 0.8125rem; color: #4f46e5;">
                            {{ $rep->job_number }}
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #166534;">
                            LKR {{ number_format($rep->advance_amount, 2) }}
                        </td>
                    </tr>
                    @empty
                    @endforelse

                    @if($cashSales->isEmpty() && $repairAdvances->isEmpty())
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 2rem; color: #94a3b8;">
                            No cash received on this date.
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Cash Out -->
    <div class="card">
        <div class="card-header" style="background: #fef2f2; border-bottom: 1px solid #fecaca;">
            <strong style="color: #991b1b; font-size: 0.9375rem;">- Cash Paid Out from Drawer</strong>
            <span class="badge badge-danger">LKR {{ number_format($totalCashOut, 2) }}</span>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Details</th>
                        <th style="text-align: right;">Cash Paid</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cashExpenses as $exp)
                    <tr>
                        <td>
                            <span class="badge badge-danger" style="font-size: 0.68rem; text-transform: uppercase;">{{ $exp->category }}</span>
                        </td>
                        <td style="font-size: 0.8125rem;">
                            <strong>{{ $exp->title }}</strong>
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #b91c1c;">
                            LKR {{ number_format($exp->amount, 2) }}
                        </td>
                    </tr>
                    @empty
                    @endforelse

                    @forelse($cashPurchases as $pur)
                    <tr>
                        <td>
                            <span class="badge badge-warning" style="font-size: 0.68rem;">Stock Purchase</span>
                        </td>
                        <td style="font-size: 0.8125rem;">
                            <strong>{{ $pur->purchase_number }}</strong>
                            <div style="font-size: 0.72rem; color: #64748b;">Supplier: {{ $pur->supplier->name ?? 'Vendor' }}</div>
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #b91c1c;">
                            LKR {{ number_format($pur->paid_amount, 2) }}
                        </td>
                    </tr>
                    @empty
                    @endforelse

                    @if($cashExpenses->isEmpty() && $cashPurchases->isEmpty())
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 2rem; color: #94a3b8;">
                            No cash paid out on this date.
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

