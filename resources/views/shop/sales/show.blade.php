@extends('layouts.shop')

@section('title', 'Invoice ' . $sale->invoice_number)
@section('page-title', 'Tax Invoice: ' . $sale->invoice_number)

@section('content')
<style>
    .invoice-card {
        background: #ffffff;
        border: 1px solid var(--surface-border);
        border-radius: 12px;
        max-width: 800px;
        margin: 0 auto;
        padding: 2.5rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    }

    @media (max-width: 640px) {
        .invoice-card {
            padding: 1.25rem 0.85rem;
        }
        .invoice-header-row {
            flex-direction: column !important;
            gap: 1rem;
        }
        .invoice-header-row > div {
            text-align: left !important;
        }
        .invoice-totals-box {
            width: 100% !important;
        }
    }

    @media print {
        body {
            background: #ffffff !important;
            padding: 0 !important;
        }
        .shop-sidebar, .shop-topbar, .no-print {
            display: none !important;
        }
        .shop-workspace, .shop-content {
            padding: 0 !important;
            margin: 0 !important;
        }
        .invoice-card {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            max-width: 100% !important;
        }
    }
</style>

<div class="no-print" style="max-width: 800px; margin: 0 auto 1.25rem; display: flex; justify-content: space-between; align-items: center;">
    <a href="{{ route('shop.sales') }}" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 0.35rem;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        Back to Sales List
    </a>
    <div style="display: flex; gap: 0.5rem;">
        <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Print Tax Invoice
        </button>
        <a href="{{ route('shop.pos') }}" class="btn btn-secondary btn-sm">New POS Sale</a>
    </div>
</div>

<div class="invoice-card">
    <!-- Header -->
    <div class="invoice-header-row" style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #e2e8f0; padding-bottom: 1.5rem; margin-bottom: 1.75rem;">
        <div>
            <h2 style="margin: 0; font-size: 1.5rem; font-weight: 800; color: #0f172a;">{{ auth()->user()->shop->name ?? 'Phone Shop' }}</h2>
            <div style="font-size: 0.8125rem; color: #64748b; margin-top: 0.25rem;">
                Branch: {{ session('active_outlet_name', auth()->user()->shop->mainOutlet->name ?? 'Main Branch') }}<br>
                @if(auth()->user()->shop->address)
                    Address: {{ auth()->user()->shop->address }}<br>
                @endif
                @if(auth()->user()->shop->phone)
                    Phone: {{ auth()->user()->shop->phone }}
                @endif
            </div>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 1.25rem; font-weight: 800; color: #4f46e5; font-family: monospace;">
                {{ $sale->invoice_number }}
            </div>
            <div style="font-size: 0.8125rem; color: #64748b; margin-top: 0.25rem;">
                Date: <strong>{{ \Carbon\Carbon::parse($sale->sale_date)->format('F d, Y') }}</strong><br>
                Time: {{ \Carbon\Carbon::parse($sale->created_at)->format('h:i A') }}<br>
                Cashier: {{ $sale->cashier->name ?? 'Staff' }}
            </div>
            <span class="badge {{ $sale->status === 'completed' ? 'badge-success' : 'badge-warning' }}" style="margin-top: 0.4rem; font-size: 0.75rem;">
                Payment: {{ strtoupper($sale->status) }}
            </span>
        </div>
    </div>

    <!-- Customer Info -->
    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem 1.25rem; margin-bottom: 1.75rem;">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; display: block; margin-bottom: 0.25rem;">Billed To Customer</span>
        @if($sale->customer)
            <strong style="font-size: 1rem; color: #0f172a;">{{ $sale->customer->name }}</strong>
            <div style="font-size: 0.8125rem; color: #475569; margin-top: 0.2rem;">
                Phone: {{ $sale->customer->phone }}
                @if($sale->customer->email) | Email: {{ $sale->customer->email }} @endif
                @if($sale->customer->address) <br>Address: {{ $sale->customer->address }} @endif
            </div>
        @else
            <strong style="font-size: 0.9375rem; color: #475569;">Walk-in Retail Customer</strong>
            <div style="font-size: 0.8125rem; color: #64748b;">Direct counter cash/card checkout</div>
        @endif
    </div>

    <!-- Line Items Table -->
    <div class="table-responsive">
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 1.5rem;">
            <thead>
                <tr style="border-bottom: 2px solid #cbd5e1; text-align: left; font-size: 0.75rem; text-transform: uppercase; color: #64748b;">
                    <th style="padding: 0.75rem 0.5rem;">#</th>
                    <th style="padding: 0.75rem 0.5rem;">Item Description &amp; Serial/IMEI</th>
                    <th style="padding: 0.75rem 0.5rem; text-align: center;">Qty</th>
                    <th style="padding: 0.75rem 0.5rem; text-align: right;">Unit Price (LKR)</th>
                    <th style="padding: 0.75rem 0.5rem; text-align: right;">Amount (LKR)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $index => $item)
                <tr style="border-bottom: 1px solid #f1f5f9; font-size: 0.875rem;">
                    <td style="padding: 0.75rem 0.5rem; color: #64748b;">{{ $index + 1 }}</td>
                    <td style="padding: 0.75rem 0.5rem;">
                        <strong style="color: #0f172a;">{{ $item->item_name }}</strong>
                        @if($item->phoneDevice)
                            <div style="font-family: monospace; font-size: 0.75rem; color: #0284c7; margin-top: 3px;">
                                IMEI 1: {{ $item->phoneDevice->imei_1 }}
                                @if($item->phoneDevice->imei_2)
                                    | IMEI 2: {{ $item->phoneDevice->imei_2 }}
                                @endif
                                @if($item->phoneDevice->serial_number)
                                    | S/N: {{ $item->phoneDevice->serial_number }}
                                @endif
                            </div>
                        @endif
                    </td>
                    <td style="padding: 0.75rem 0.5rem; text-align: center; color: #475569;">{{ $item->quantity }}</td>
                    <td style="padding: 0.75rem 0.5rem; text-align: right; color: #475569;">{{ number_format($item->unit_price, 2) }}</td>
                    <td style="padding: 0.75rem 0.5rem; text-align: right; font-weight: 700; color: #0f172a;">{{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Totals Calculation -->
    <div style="display: flex; justify-content: flex-end; margin-bottom: 2rem;">
        <div class="invoice-totals-box" style="width: 320px;">
            <div style="display: flex; justify-content: space-between; font-size: 0.875rem; color: #475569; margin-bottom: 0.4rem;">
                <span>Subtotal:</span>
                <span style="font-weight: 600;">LKR {{ number_format($sale->subtotal, 2) }}</span>
            </div>
            @if($sale->discount_amount > 0)
            <div style="display: flex; justify-content: space-between; font-size: 0.875rem; color: #b91c1c; margin-bottom: 0.4rem;">
                <span>Discount:</span>
                <span>-LKR {{ number_format($sale->discount_amount, 2) }}</span>
            </div>
            @endif
            @if($sale->tax_amount > 0)
            <div style="display: flex; justify-content: space-between; font-size: 0.875rem; color: #475569; margin-bottom: 0.4rem;">
                <span>Tax:</span>
                <span>LKR {{ number_format($sale->tax_amount, 2) }}</span>
            </div>
            @endif
            <div style="display: flex; justify-content: space-between; font-size: 1.25rem; font-weight: 800; color: #0f172a; border-top: 2px solid #e2e8f0; padding-top: 0.6rem; margin-top: 0.4rem;">
                <span>Total Amount:</span>
                <span style="color: #166534;">LKR {{ number_format($sale->total_amount, 2) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 0.875rem; color: #64748b; margin-top: 0.3rem;">
                <span>Total Tendered:</span>
                <span>LKR {{ number_format($sale->paid_amount, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Payments Tender Breakdown -->
    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem 1.25rem; margin-bottom: 2rem;">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; display: block; margin-bottom: 0.5rem;">Payment Tender Breakdown</span>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem;">
            @foreach($sale->payments as $pay)
            <div>
                <span style="font-size: 0.75rem; color: #64748b; display: block;">Method</span>
                <strong style="color: #0f172a; font-size: 0.875rem; text-transform: uppercase;">{{ str_replace('_', ' ', $pay->payment_method) }}</strong>
                <div style="font-weight: 700; color: #166534; font-size: 0.9375rem;">LKR {{ number_format($pay->amount, 2) }}</div>
                @if($pay->reference)
                    <div style="font-size: 0.72rem; color: #64748b;">Ref: {{ $pay->reference }}</div>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    <!-- Warranty & Footer Note -->
    <div style="border-top: 1px dashed #cbd5e1; padding-top: 1rem; text-align: center; color: #64748b; font-size: 0.75rem;">
        <p style="margin: 0 0 0.25rem;">
            <strong>Warranty Notice:</strong> Any serialized phone or repair covered by standard warranty terms requires presentation of this invoice receipt.
        </p>
        <p style="margin: 0;">Thank you for your valuable business with {{ auth()->user()->shop->name ?? 'Phone Shop' }}!</p>
    </div>
</div>
@endsection

