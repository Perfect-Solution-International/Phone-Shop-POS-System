@extends('layouts.shop')

@section('title', 'Reports & Business Analytics')
@section('page-title', 'Financial & Operational Reports')

@section('content')
<style>
    .reports-analytics-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }
    .reports-full-span {
        grid-column: span 2;
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
</style>
<!-- Filter Date Bar -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; padding: 1.25rem 1.5rem;">
        <form method="GET" action="{{ route('shop.reports') }}" class="reports-filter-form" style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            <label style="font-size: 0.8125rem; font-weight: 700; color: #475569; margin: 0;">Date Range:</label>
            <input type="date" name="start_date" class="form-control" style="max-width: 150px;" value="{{ $startDate }}">
            <span style="color: #94a3b8;">to</span>
            <input type="date" name="end_date" class="form-control" style="max-width: 150px;" value="{{ $endDate }}">
            <button type="submit" class="btn btn-secondary btn-sm">Generate Report</button>
            <a href="{{ route('shop.reports', ['start_date' => now()->startOfMonth()->toDateString(), 'end_date' => now()->toDateString()]) }}" class="btn btn-sm btn-secondary" style="color: #64748b;">This Month</a>
            <a href="{{ route('shop.reports', ['start_date' => now()->subDays(30)->toDateString(), 'end_date' => now()->toDateString()]) }}" class="btn btn-sm btn-secondary" style="color: #64748b;">Last 30 Days</a>
        </form>

        <button type="button" class="btn btn-secondary btn-sm" onclick="window.print()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Print Executive Summary
        </button>
    </div>
</div>

<!-- Executive Summary Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem; margin-bottom: 1.75rem;">
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">Sales Revenue</span>
        <div style="font-size: 1.6rem; font-weight: 800; color: #166534; margin-top: 0.35rem;">
            LKR {{ number_format($totalSalesRevenue, 2) }}
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">
            {{ number_format($totalSalesCount) }} invoices generated (Avg: LKR {{ number_format($avgTicketValue, 2) }})
        </div>
    </div>

    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">Operating Expenses</span>
        <div style="font-size: 1.6rem; font-weight: 800; color: #b91c1c; margin-top: 0.35rem;">
            LKR {{ number_format($operatingExpenses, 2) }}
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">
            Rent, utilities, staff salaries, supplies
        </div>
    </div>

    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">Repair Service Volume</span>
        <div style="font-size: 1.6rem; font-weight: 800; color: #7c3aed; margin-top: 0.35rem;">
            LKR {{ number_format($repairRevenue, 2) }}
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">
            {{ number_format($deliveredRepairsCount) }} delivered repairs (Parts cost: LKR {{ number_format($repairPartsConsumedCost, 2) }})
        </div>
    </div>

    <div style="background: #ffffff; border: 1px solid #bbf7d0; border-radius: 12px; padding: 1.25rem 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #166534; display: block;">Estimated Net Operating Profit</span>
        <div style="font-size: 1.6rem; font-weight: 800; color: {{ $netEstimatedProfit >= 0 ? '#15803d' : '#b91c1c' }}; margin-top: 0.35rem;">
            LKR {{ number_format($netEstimatedProfit, 2) }}
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">
            Gross margin + repair margin - expenses
        </div>
    </div>
</div>

<!-- 2x2 Analytical Section Grid -->
<div class="reports-analytics-grid">
    <!-- Sales by Tender & Payment -->
    <div class="card">
        <div class="card-header">
            <strong style="color: #0f172a; font-size: 0.9375rem;">Payment Method Breakdown</strong>
            <span class="badge badge-info">Revenue Channels</span>
        </div>
        <div style="padding: 1.25rem;">
            <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.8125rem; margin-bottom: 0.25rem;">
                        <span style="font-weight: 600; color: #166534;">Cash Drawer Collections</span>
                        <strong>LKR {{ number_format($cashSalesTotal, 2) }}</strong>
                    </div>
                    <div style="height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                        <div style="height: 100%; background: #16a34a; width: {{ $totalSalesRevenue > 0 ? ($cashSalesTotal / $totalSalesRevenue * 100) : 0 }}%;"></div>
                    </div>
                </div>

                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.8125rem; margin-bottom: 0.25rem;">
                        <span style="font-weight: 600; color: #0284c7;">Card Terminal Payments</span>
                        <strong>LKR {{ number_format($cardSalesTotal, 2) }}</strong>
                    </div>
                    <div style="height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                        <div style="height: 100%; background: #0284c7; width: {{ $totalSalesRevenue > 0 ? ($cardSalesTotal / $totalSalesRevenue * 100) : 0 }}%;"></div>
                    </div>
                </div>

                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.8125rem; margin-bottom: 0.25rem;">
                        <span style="font-weight: 600; color: #7c3aed;">Split Tenders</span>
                        <strong>LKR {{ number_format($splitSalesTotal, 2) }}</strong>
                    </div>
                    <div style="height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                        <div style="height: 100%; background: #8b5cf6; width: {{ $totalSalesRevenue > 0 ? ($splitSalesTotal / $totalSalesRevenue * 100) : 0 }}%;"></div>
                    </div>
                </div>

                @if($otherSalesTotal > 0)
                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.8125rem; margin-bottom: 0.25rem;">
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
    <div class="card">
        <div class="card-header">
            <strong style="color: #0f172a; font-size: 0.9375rem;">Inventory Valuation &amp; Health</strong>
            <span class="badge badge-purple">Stock Worth</span>
        </div>
        <div style="padding: 1.25rem;">
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.75rem; margin-bottom: 0.75rem;">
                <div>
                    <span style="font-size: 0.75rem; color: #64748b; display: block;">Total Stock Valuation (Cost)</span>
                    <strong style="font-size: 1.15rem; color: #0f172a;">LKR {{ number_format($inventoryCostTotal, 2) }}</strong>
                </div>
                <div style="text-align: right;">
                    <span style="font-size: 0.75rem; color: #64748b; display: block;">Estimated Retail Worth</span>
                    <strong style="font-size: 1.15rem; color: #166534;">LKR {{ number_format($inventoryRetailTotal, 2) }}</strong>
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <span style="font-size: 0.75rem; color: #64748b; display: block;">Low Stock Products</span>
                    <strong style="font-size: 1.1rem; color: {{ $lowStockProducts > 0 ? '#b91c1c' : '#15803d' }};">
                        {{ $lowStockProducts }} items needing reorder
                    </strong>
                </div>
                <a href="{{ route('shop.inventory') }}" class="btn btn-secondary btn-sm">View Stock Movement Audit</a>
            </div>
        </div>
    </div>

    <!-- Top Selling Items -->
    <div class="card reports-full-span">
        <div class="card-header">
            <strong style="color: #0f172a; font-size: 0.9375rem;">Top Selling Items (In Selected Period)</strong>
            <span class="badge badge-success">Demand Leaders</span>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Product / Device Description</th>
                        <th style="text-align: center;">Units Sold</th>
                        <th style="text-align: right;">Total Generated Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topProducts as $p)
                    <tr>
                        <td>
                            <strong style="color: #0f172a; font-size: 0.875rem;">{{ $p->item_name }}</strong>
                        </td>
                        <td style="text-align: center; font-weight: 700; color: #4f46e5;">
                            {{ number_format($p->total_qty) }}
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #166534;">
                            LKR {{ number_format($p->total_revenue, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 2rem; color: #94a3b8;">
                            No sales items recorded in this date range.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

