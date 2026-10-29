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

<!-- End-of-Day Register Closing & Cash Drawer Reconciliation -->
<div class="card" style="margin-top: 1.75rem; border: 1.5px solid #e2e8f0; border-radius: 14px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
    <div class="card-header" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #ffffff; padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color: #38bdf8;"><rect x="2" y="4" width="20" height="16" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
                <h3 style="margin: 0; font-size: 1.15rem; font-weight: 800; color: #ffffff;">Daily Cash Drawer Closing &amp; Reconciliation</h3>
            </div>
            <p style="margin: 0.25rem 0 0; font-size: 0.8rem; color: #94a3b8;">
                Count physical cash in your drawer and reconcile with the expected POS net cash balance for <strong>{{ \Carbon\Carbon::parse($selectedDate)->format('F d, Y') }}</strong>.
            </p>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button type="button" class="btn btn-sm" onclick="printRegisterSummary()" style="background: #ffffff; color: #0f172a; font-weight: 700; border: none; border-radius: 8px; display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                Print Closing Sign-off Slip
            </button>
        </div>
    </div>

    <div style="padding: 1.5rem; background: #ffffff;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.75rem;" class="cashbook-split-grid">
            <!-- Left: Expected System Position -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem;">
                <h4 style="font-size: 0.875rem; font-weight: 800; text-transform: uppercase; color: #475569; margin-bottom: 1rem; letter-spacing: 0.04em;">
                    1. Expected System Cash Balance
                </h4>
                <div style="display: flex; flex-direction: column; gap: 0.65rem; font-size: 0.875rem;">
                    <div style="display: flex; justify-content: space-between; color: #166534;">
                        <span>+ POS Sales Cash:</span>
                        <strong style="font-family: monospace;">LKR {{ number_format($totalSalesCashIn, 2) }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; color: #166534;">
                        <span>+ Repair Workshop Advance Cash:</span>
                        <strong style="font-family: monospace;">LKR {{ number_format($totalRepairCashIn, 2) }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-top: 1px dashed #cbd5e1; padding-top: 0.5rem; font-weight: 700; color: #15803d;">
                        <span>Total Cash Received:</span>
                        <span style="font-family: monospace;">LKR {{ number_format($totalCashIn, 2) }}</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; color: #b91c1c; margin-top: 0.4rem;">
                        <span>- Operating Expenses (Cash):</span>
                        <strong style="font-family: monospace;">-LKR {{ number_format($totalCashExpenses, 2) }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; color: #b91c1c;">
                        <span>- Stock Purchases (Cash):</span>
                        <strong style="font-family: monospace;">-LKR {{ number_format($totalPurchasesCashOut, 2) }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-top: 1px dashed #cbd5e1; padding-top: 0.5rem; font-weight: 700; color: #b91c1c;">
                        <span>Total Cash Paid Out:</span>
                        <span style="font-family: monospace;">-LKR {{ number_format($totalCashOut, 2) }}</span>
                    </div>

                    <div style="border-top: 2px solid #cbd5e1; padding-top: 0.75rem; margin-top: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-weight: 800; font-size: 0.95rem; color: #0f172a;">Expected In Drawer:</span>
                        <span id="reconExpectedVal" style="font-size: 1.25rem; font-weight: 900; color: #4f46e5; font-family: monospace;">
                            LKR {{ number_format($netCashBalance, 2) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right: Actual Physical Cash Counted -->
            <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 12px; padding: 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem;">
                    <h4 style="font-size: 0.875rem; font-weight: 800; text-transform: uppercase; color: #0f172a; margin: 0; letter-spacing: 0.04em;">
                        2. Cashier Physical Count
                    </h4>
                    <div style="display: flex; gap: 0.35rem;">
                        <button type="button" class="btn btn-sm" id="btnCountModeQuick" onclick="setCountMode('quick')" style="background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe; font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.5rem;">Quick Total</button>
                        <button type="button" class="btn btn-sm" id="btnCountModeDenom" onclick="setCountMode('denom')" style="background: #f8fafc; color: #64748b; border: 1px solid #cbd5e1; font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.5rem;">Denominations</button>
                    </div>
                </div>

                <!-- Quick Total Mode -->
                <div id="quickCountWrap" style="margin-bottom: 0.85rem;">
                    <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #475569; margin-bottom: 0.35rem;">Total Counted Cash in Drawer (LKR):</label>
                    <input type="number" step="0.01" id="inpDirectCount" class="form-control" placeholder="0.00" oninput="calculateReconciliation()" style="font-size: 1.15rem; font-weight: 800; color: #0f172a; font-family: monospace;">
                </div>

                <!-- Denominations Mode -->
                <div id="denomCountWrap" style="display: none; margin-bottom: 0.85rem;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.4rem; font-size: 0.78rem;">
                        <div style="display: flex; align-items: center; gap: 0.4rem;">
                            <span style="width: 55px; font-weight: 700; color: #475569;">Rs. 5,000 &times;</span>
                            <input type="number" min="0" class="form-control denom-input" data-val="5000" placeholder="0" oninput="calculateDenoms()" style="padding: 0.25rem 0.4rem; font-size: 0.8rem;">
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.4rem;">
                            <span style="width: 55px; font-weight: 700; color: #475569;">Rs. 1,000 &times;</span>
                            <input type="number" min="0" class="form-control denom-input" data-val="1000" placeholder="0" oninput="calculateDenoms()" style="padding: 0.25rem 0.4rem; font-size: 0.8rem;">
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.4rem;">
                            <span style="width: 55px; font-weight: 700; color: #475569;">Rs. 500 &times;</span>
                            <input type="number" min="0" class="form-control denom-input" data-val="500" placeholder="0" oninput="calculateDenoms()" style="padding: 0.25rem 0.4rem; font-size: 0.8rem;">
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.4rem;">
                            <span style="width: 55px; font-weight: 700; color: #475569;">Rs. 100 &times;</span>
                            <input type="number" min="0" class="form-control denom-input" data-val="100" placeholder="0" oninput="calculateDenoms()" style="padding: 0.25rem 0.4rem; font-size: 0.8rem;">
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.4rem;">
                            <span style="width: 55px; font-weight: 700; color: #475569;">Rs. 50 &times;</span>
                            <input type="number" min="0" class="form-control denom-input" data-val="50" placeholder="0" oninput="calculateDenoms()" style="padding: 0.25rem 0.4rem; font-size: 0.8rem;">
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.4rem;">
                            <span style="width: 55px; font-weight: 700; color: #475569;">Coins &times;</span>
                            <input type="number" step="0.5" min="0" id="denomCoins" class="form-control" placeholder="0.00" oninput="calculateDenoms()" style="padding: 0.25rem 0.4rem; font-size: 0.8rem;">
                        </div>
                    </div>
                </div>

                <!-- Reconciliation Result Badge -->
                <div id="reconResultWrap" style="background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 8px; padding: 0.85rem 1rem; text-align: center; margin-bottom: 0.85rem;">
                    <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">Reconciliation Variance</span>
                    <div id="reconDiffLabel" style="font-size: 1.25rem; font-weight: 900; color: #64748b; margin-top: 0.25rem; font-family: monospace;">
                        Enter counted cash to compare
                    </div>
                    <div id="reconDiffDesc" style="font-size: 0.75rem; color: #94a3b8; margin-top: 0.2rem;">
                        Expected: LKR {{ number_format($netCashBalance, 2) }}
                    </div>
                </div>

                <!-- Cashier Sign-off Notes -->
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 0.25rem;">Cashier Closing Notes / Discrepancy Reason:</label>
                    <input type="text" id="inpClosingNotes" class="form-control" placeholder="e.g. Verified by Cashier {{ auth()->user()->name }}. No discrepancies." style="font-size: 0.8125rem;">
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

