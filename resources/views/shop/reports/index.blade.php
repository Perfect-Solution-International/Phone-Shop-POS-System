@extends('layouts.shop')

@section('title', 'Reports & Business Analytics')
@section('page-title', 'Financial & Operational Reports')

@section('content')
<style>
    .reports-analytics-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.85rem;
    }
    .reports-full-span {
        grid-column: span 2;
    }
    .print-only-header,
    .print-only-footer {
        display: none;
    }

    /* Compact Metric Cards */
    .reports-summary-cards {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.75rem;
        margin-bottom: 0.85rem;
    }
    .report-metric-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.75rem 0.95rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .report-metric-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px -2px rgba(0, 0, 0, 0.06);
    }
    .report-metric-label {
        font-size: 0.6875rem;
        font-weight: 750;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        display: block;
    }
    .report-metric-val {
        font-size: 1.35rem;
        font-weight: 800;
        margin-top: 0.2rem;
        line-height: 1.2;
    }
    .report-metric-sub {
        font-size: 0.72rem;
        color: #64748b;
        margin-top: 0.2rem;
        line-height: 1.3;
    }

    @media (max-width: 1080px) {
        .reports-summary-cards {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }
    @media (max-width: 992px) {
        .reports-analytics-grid {
            grid-template-columns: 1fr;
        }
        .reports-full-span {
            grid-column: 1 / -1 !important;
        }
    }
    @media (max-width: 640px) {
        .reports-summary-cards {
            grid-template-columns: 1fr !important;
        }
        .reports-filter-form {
            flex-direction: column;
            align-items: stretch !important;
            width: 100%;
        }
        .reports-filter-form input {
            max-width: 100% !important;
            width: 100%;
        }
    }

    /* Print Specific Styling */
    @media print {
        @page {
            size: A4 portrait;
            margin: 0;
        }
        html, body {
            background: #ffffff !important;
            color: #0f172a !important;
            font-size: 10pt !important;
            margin: 0 !important;
            padding: 12mm 15mm !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .shop-sidebar, 
        .shop-topbar, 
        .shop-sidebar-overlay,
        .mobile-sidebar-toggle,
        .alert, 
        .no-print,
        .btn {
            display: none !important;
        }
        .shop-workspace, 
        .shop-content {
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
        }

        .print-only-header {
            display: block !important;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #0f172a;
        }
        .print-only-footer {
            display: block !important;
            margin-top: 2rem;
            page-break-inside: avoid;
        }

        .report-metric-card {
            border: 1px solid #cbd5e1 !important;
            box-shadow: none !important;
            padding: 0.85rem 1rem !important;
            background: #f8fafc !important;
            border-radius: 8px !important;
            page-break-inside: avoid;
        }

        .reports-summary-cards {
            display: grid !important;
            grid-template-columns: repeat(4, 1fr) !important;
            gap: 0.75rem !important;
            margin-bottom: 1.25rem !important;
        }

        .card {
            border: 1px solid #cbd5e1 !important;
            box-shadow: none !important;
            border-radius: 8px !important;
            page-break-inside: avoid;
            margin-bottom: 1rem !important;
        }

        .card-header {
            background: #f1f5f9 !important;
            border-bottom: 1px solid #cbd5e1 !important;
            padding: 0.5rem 0.85rem !important;
        }

        .reports-analytics-grid {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 1rem !important;
        }

        .data-table th, .data-table td {
            border-bottom: 1px solid #e2e8f0 !important;
            padding: 0.45rem 0.75rem !important;
            font-size: 0.8125rem !important;
        }
        .data-table th {
            background: #f8fafc !important;
            border-bottom: 2px solid #cbd5e1 !important;
        }
    }
</style>

<!-- Official Print Header (Only visible on paper / PDF printout) -->
<div class="print-only-header">
    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
        <div>
            <h1 style="margin: 0; font-size: 1.6rem; font-weight: 900; color: #0f172a; letter-spacing: -0.02em;">
                {{ auth()->user()->shop->name ?? 'PHONE SHOP' }}
            </h1>
            <div style="font-size: 0.8125rem; color: #64748b; margin-top: 0.25rem; line-height: 1.45;">
                <div>Branch: <strong>{{ session('active_outlet_name', auth()->user()->shop->mainOutlet->name ?? 'Main Branch') }}</strong></div>
                @if(auth()->user()->shop->address)
                    <div>Address: {{ auth()->user()->shop->address }}</div>
                @endif
                @if(auth()->user()->shop->phone)
                    <div>Phone: {{ auth()->user()->shop->phone }}</div>
                @endif
            </div>
        </div>

        <div style="text-align: right;">
            <div style="font-size: 0.7rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; color: #4f46e5;">Official Performance Audit</div>
            <h2 style="font-size: 1.25rem; font-weight: 900; color: #0f172a; margin: 0.2rem 0 0.35rem; letter-spacing: -0.01em;">
                EXECUTIVE BUSINESS REPORT
            </h2>
            <div style="font-size: 0.8125rem; color: #334155;">
                Period: <strong style="color: #0f172a;">{{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }}</strong> to <strong style="color: #0f172a;">{{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</strong>
            </div>
            <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.2rem;">
                Generated: {{ now()->format('M d, Y • h:i A') }} | By: <strong>{{ auth()->user()->name }}</strong>
            </div>
        </div>
    </div>
</div>

<!-- Filter Date Bar (Screen Only - Hidden in Print) -->
<div class="card no-print" style="margin-bottom: 0.85rem; border-radius: 10px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.65rem 1rem; padding: 0.6rem 0.95rem;">
        <form method="GET" action="{{ route('shop.reports') }}" class="reports-filter-form" style="display: flex; gap: 0.45rem; align-items: center; flex-wrap: wrap; margin: 0;">
            <div style="display: inline-flex; align-items: center; gap: 0.35rem;">
                <label style="font-size: 0.775rem; font-weight: 700; color: #475569; margin: 0; white-space: nowrap;">Date Range:</label>
                <input type="date" name="start_date" class="form-control" style="width: 130px; height: 32px; padding: 0.25rem 0.55rem; font-size: 0.8rem; border-radius: 6px;" value="{{ $startDate }}">
                <span style="color: #94a3b8; font-size: 0.8rem;">to</span>
                <input type="date" name="end_date" class="form-control" style="width: 130px; height: 32px; padding: 0.25rem 0.55rem; font-size: 0.8rem; border-radius: 6px;" value="{{ $endDate }}">
            </div>
            <button type="submit" class="btn btn-secondary btn-sm" style="height: 32px; padding: 0.3rem 0.75rem; font-size: 0.775rem; font-weight: 650; border-radius: 6px;">Generate Report</button>
            <div style="display: inline-flex; gap: 0.35rem; align-items: center;">
                <a href="{{ route('shop.reports', ['start_date' => now()->startOfMonth()->toDateString(), 'end_date' => now()->toDateString()]) }}" class="btn btn-sm btn-outline-secondary" style="height: 32px; padding: 0.3rem 0.65rem; font-size: 0.75rem; font-weight: 600; border-radius: 6px; display: inline-flex; align-items: center; background: #f8fafc; border: 1px solid #cbd5e1; color: #475569;">This Month</a>
                <a href="{{ route('shop.reports', ['start_date' => now()->subDays(30)->toDateString(), 'end_date' => now()->toDateString()]) }}" class="btn btn-sm btn-outline-secondary" style="height: 32px; padding: 0.3rem 0.65rem; font-size: 0.75rem; font-weight: 600; border-radius: 6px; display: inline-flex; align-items: center; background: #f8fafc; border: 1px solid #cbd5e1; color: #475569;">Last 30 Days</a>
            </div>
        </form>

        <button type="button" class="btn btn-primary btn-sm" onclick="window.print()" style="display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 700; height: 32px; padding: 0.3rem 0.85rem; font-size: 0.775rem; border-radius: 6px; white-space: nowrap;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            <span>Print Executive Summary</span>
        </button>
    </div>
</div>

<!-- Executive Summary Cards -->
<div class="reports-summary-cards">
    <div class="report-metric-card">
        <span class="report-metric-label">Sales Revenue</span>
        <div class="report-metric-val" style="color: #166534;">
            LKR {{ number_format($totalSalesRevenue, 2) }}
        </div>
        <div class="report-metric-sub">
            {{ number_format($totalSalesCount) }} invoices (Avg: LKR {{ number_format($avgTicketValue, 2) }})
        </div>
    </div>

    <div class="report-metric-card">
        <span class="report-metric-label">Operating Expenses</span>
        <div class="report-metric-val" style="color: #b91c1c;">
            LKR {{ number_format($operatingExpenses, 2) }}
        </div>
        <div class="report-metric-sub">
            Rent, utilities, staff salaries, supplies
        </div>
    </div>

    <div class="report-metric-card">
        <span class="report-metric-label">Repair Service Volume</span>
        <div class="report-metric-val" style="color: #7c3aed;">
            LKR {{ number_format($repairRevenue, 2) }}
        </div>
        <div class="report-metric-sub">
            {{ number_format($deliveredRepairsCount) }} delivered repairs (Parts: LKR {{ number_format($repairPartsConsumedCost, 2) }})
        </div>
    </div>

    <div class="report-metric-card" style="border-color: #bbf7d0; background: #fcfdfd;">
        <span class="report-metric-label" style="color: #166534;">Estimated Net Operating Profit</span>
        <div class="report-metric-val" style="color: {{ $netEstimatedProfit >= 0 ? '#15803d' : '#b91c1c' }};">
            LKR {{ number_format($netEstimatedProfit, 2) }}
        </div>
        <div class="report-metric-sub">
            Gross margin + repair margin - expenses
        </div>
    </div>
</div>

<!-- 2x2 Analytical Section Grid -->
<div class="reports-analytics-grid">
    <!-- Sales by Tender & Payment -->
    <div class="card" style="border-radius: 10px;">
        <div class="card-header" style="padding: 0.55rem 0.95rem;">
            <strong style="color: #0f172a; font-size: 0.9rem;">Payment Method Breakdown</strong>
            <span class="badge badge-info" style="font-size: 0.7rem; padding: 0.15rem 0.5rem;">Revenue Channels</span>
        </div>
        <div style="padding: 0.85rem 1rem;">
            <div style="display: flex; flex-direction: column; gap: 0.65rem;">
                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.8125rem; margin-bottom: 0.2rem;">
                        <span style="font-weight: 600; color: #166534;">Cash Drawer Collections</span>
                        <strong>LKR {{ number_format($cashSalesTotal, 2) }}</strong>
                    </div>
                    <div style="height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                        <div style="height: 100%; background: #16a34a; width: {{ $totalSalesRevenue > 0 ? ($cashSalesTotal / $totalSalesRevenue * 100) : 0 }}%;"></div>
                    </div>
                </div>

                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.8125rem; margin-bottom: 0.2rem;">
                        <span style="font-weight: 600; color: #0284c7;">Card Terminal Payments</span>
                        <strong>LKR {{ number_format($cardSalesTotal, 2) }}</strong>
                    </div>
                    <div style="height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                        <div style="height: 100%; background: #0284c7; width: {{ $totalSalesRevenue > 0 ? ($cardSalesTotal / $totalSalesRevenue * 100) : 0 }}%;"></div>
                    </div>
                </div>

                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.8125rem; margin-bottom: 0.2rem;">
                        <span style="font-weight: 600; color: #7c3aed;">Split Tenders</span>
                        <strong>LKR {{ number_format($splitSalesTotal, 2) }}</strong>
                    </div>
                    <div style="height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                        <div style="height: 100%; background: #8b5cf6; width: {{ $totalSalesRevenue > 0 ? ($splitSalesTotal / $totalSalesRevenue * 100) : 0 }}%;"></div>
                    </div>
                </div>

                @if($otherSalesTotal > 0)
                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.8125rem; margin-bottom: 0.2rem;">
                        <span style="font-weight: 600; color: #64748b;">Other / Bank Transfer</span>
                        <strong>LKR {{ number_format($otherSalesTotal, 2) }}</strong>
                    </div>
                    <div style="height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                        <div style="height: 100%; background: #64748b; width: {{ $totalSalesRevenue > 0 ? ($otherSalesTotal / $totalSalesRevenue * 100) : 0 }}%;"></div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Inventory Valuation -->
    <div class="card" style="border-radius: 10px;">
        <div class="card-header" style="padding: 0.55rem 0.95rem;">
            <strong style="color: #0f172a; font-size: 0.9rem;">Inventory Valuation &amp; Health</strong>
            <span class="badge badge-purple" style="font-size: 0.7rem; padding: 0.15rem 0.5rem;">Stock Worth</span>
        </div>
        <div style="padding: 0.85rem 1rem;">
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.65rem; margin-bottom: 0.65rem;">
                <div>
                    <span style="font-size: 0.72rem; color: #64748b; display: block;">Total Stock Valuation (Cost)</span>
                    <strong style="font-size: 1.05rem; color: #0f172a;">LKR {{ number_format($inventoryCostTotal, 2) }}</strong>
                </div>
                <div style="text-align: right;">
                    <span style="font-size: 0.72rem; color: #64748b; display: block;">Estimated Retail Worth</span>
                    <strong style="font-size: 1.05rem; color: #166534;">LKR {{ number_format($inventoryRetailTotal, 2) }}</strong>
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <span style="font-size: 0.72rem; color: #64748b; display: block;">Low Stock Products</span>
                    <strong style="font-size: 0.95rem; color: {{ $lowStockProducts > 0 ? '#b91c1c' : '#15803d' }};">
                        {{ $lowStockProducts }} items needing reorder
                    </strong>
                </div>
                <a href="{{ route('shop.inventory') }}" class="btn btn-secondary btn-sm no-print" style="height: 28px; padding: 0.2rem 0.6rem; font-size: 0.75rem; border-radius: 6px;">View Stock Movement Audit</a>
            </div>
        </div>
    </div>

    <!-- Top Selling Items -->
    <div class="card reports-full-span" style="border-radius: 10px;">
        <div class="card-header" style="padding: 0.55rem 0.95rem;">
            <strong style="color: #0f172a; font-size: 0.9rem;">Top Selling Items (In Selected Period)</strong>
            <span class="badge badge-success" style="font-size: 0.7rem; padding: 0.15rem 0.5rem;">Demand Leaders</span>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="padding: 0.5rem 0.85rem; font-size: 0.78rem;">Product / Device Description</th>
                        <th style="text-align: center; padding: 0.5rem 0.85rem; font-size: 0.78rem;">Units Sold</th>
                        <th style="text-align: right; padding: 0.5rem 0.85rem; font-size: 0.78rem;">Total Generated Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topProducts as $p)
                    <tr>
                        <td style="padding: 0.55rem 0.85rem;">
                            <strong style="color: #0f172a; font-size: 0.85rem;">{{ $p->item_name }}</strong>
                        </td>
                        <td style="text-align: center; font-weight: 700; color: #4f46e5; padding: 0.55rem 0.85rem; font-size: 0.85rem;">
                            {{ number_format($p->total_qty) }}
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #166534; padding: 0.55rem 0.85rem; font-size: 0.85rem;">
                            LKR {{ number_format($p->total_revenue, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 1.5rem; color: #94a3b8; font-size: 0.85rem;">
                            No sales items recorded in this date range.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Official Sign-off Footer (Only visible on paper / PDF printout) -->
<div class="print-only-footer">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; margin-top: 2rem; padding-top: 1rem;">
        <div style="text-align: center;">
            <div style="border-top: 1.5px solid #0f172a; padding-top: 0.5rem;">
                <strong style="font-size: 0.85rem; color: #0f172a; display: block;">Prepared &amp; Verified By</strong>
                <span style="font-size: 0.75rem; color: #64748b;">Accountant / Branch Supervisor</span>
                <div style="font-size: 0.72rem; color: #94a3b8; margin-top: 0.25rem;">Signature &amp; Date: ________________________</div>
            </div>
        </div>
        <div style="text-align: center;">
            <div style="border-top: 1.5px solid #0f172a; padding-top: 0.5rem;">
                <strong style="font-size: 0.85rem; color: #0f172a; display: block;">Approved By</strong>
                <span style="font-size: 0.75rem; color: #64748b;">Managing Director / Shop Owner</span>
                <div style="font-size: 0.72rem; color: #94a3b8; margin-top: 0.25rem;">Signature &amp; Date: ________________________</div>
            </div>
        </div>
    </div>

    <div style="text-align: center; margin-top: 2rem; font-size: 0.72rem; color: #94a3b8; border-top: 1px dashed #cbd5e1; padding-top: 0.5rem;">
        Confidential Business Performance Document &bull; Generated by {{ auth()->user()->shop->name ?? 'POS System' }} &bull; For Management Review Only
    </div>
</div>
@endsection
