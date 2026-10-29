@extends('layouts.shop')

@section('title', 'Goods Received Note ' . $grn->grn_number)
@section('page-title', 'Goods Received Note: ' . $grn->grn_number)

@section('content')
<style>
    .grn-voucher-card {
        background: #ffffff;
        border: 1px solid var(--surface-border, #e2e8f0);
        border-radius: 14px;
        max-width: 920px;
        margin: 0 auto;
        padding: 2.5rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }

    .grn-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 1.5rem;
    }

    .grn-table th {
        background: #f8fafc;
        color: #475569;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 0.75rem 1rem;
        border-top: 1px solid #e2e8f0;
        border-bottom: 2px solid #cbd5e1;
    }

    .grn-table td {
        padding: 0.85rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.875rem;
        color: #1e293b;
        vertical-align: middle;
    }

    .grn-table tbody tr:hover {
        background-color: #fafafa;
    }

    @media (max-width: 640px) {
        .grn-voucher-card {
            padding: 1.25rem 0.85rem;
        }
        .grn-header-row {
            flex-direction: column !important;
            gap: 1.25rem;
        }
        .grn-header-right {
            text-align: left !important;
        }
    }

    @media print {
        @page {
            size: A4 portrait;
            margin: 0;
        }
        html, body {
            background: #ffffff !important;
            color: #0f172a !important;
            font-size: 10.5pt !important;
            margin: 0 !important;
            padding: 14mm 16mm !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .shop-sidebar, 
        .shop-topbar, 
        .shop-sidebar-overlay,
        .mobile-sidebar-toggle,
        .alert, 
        .no-print {
            display: none !important;
        }
        .shop-workspace, 
        .shop-content {
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
        }
        .grn-voucher-card {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
        }
        .grn-table th {
            background: #f1f5f9 !important;
            color: #0f172a !important;
            border-bottom: 2px solid #94a3b8 !important;
        }
        .grn-table td {
            border-bottom: 1px solid #cbd5e1 !important;
        }
        .grn-box {
            border: 1px solid #cbd5e1 !important;
            background: #f8fafc !important;
        }
    }
</style>

<div style="max-width: 920px; margin: 0 auto;">
    <!-- Action Bar (Hidden in Print) -->
    <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
        <a href="{{ route('shop.inventory.grns') }}" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 0.35rem;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            Back to GRN Directory
        </a>
        <div style="display: flex; gap: 0.5rem;">
            <button type="button" class="btn btn-primary btn-sm" onclick="window.print()" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                Print Official GRN
            </button>
            <a href="{{ route('shop.purchases') }}" class="btn btn-secondary btn-sm">Purchases Log</a>
        </div>
    </div>

    <div class="grn-voucher-card">
        <!-- Top Company & GRN Header -->
        <div class="grn-header-row" style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #e2e8f0; padding-bottom: 1.5rem; margin-bottom: 1.75rem;">
            <div>
                <h2 style="margin: 0; font-size: 1.5rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em;">
                    {{ auth()->user()->shop->name ?? 'PHONE SHOP' }}
                </h2>
                <div style="font-size: 0.8125rem; color: #64748b; margin-top: 0.3rem; line-height: 1.5;">
                    <div>Branch: <strong>{{ session('active_outlet_name', auth()->user()->shop->mainOutlet->name ?? 'Main Branch') }}</strong></div>
                    @if(auth()->user()->shop->address)
                        <div>Address: {{ auth()->user()->shop->address }}</div>
                    @endif
                    @if(auth()->user()->shop->phone)
                        <div>Phone: {{ auth()->user()->shop->phone }}</div>
                    @endif
                </div>
            </div>

            <div class="grn-header-right" style="text-align: right;">
                <div style="font-size: 0.72rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; color: #4f46e5;">Official Inbound Stock Document</div>
                <h1 style="font-size: 1.45rem; font-weight: 900; color: #0f172a; margin: 0.2rem 0 0.35rem; letter-spacing: -0.01em;">GOODS RECEIVED NOTE (GRN)</h1>
                
                <div style="display: flex; justify-content: flex-end; align-items: center; gap: 0.5rem; margin-top: 0.35rem;">
                    <span style="font-size: 0.75rem; color: #64748b; font-weight: 700;">GRN REF:</span>
                    <span style="font-family: monospace; font-size: 1.15rem; font-weight: 900; color: #4f46e5; letter-spacing: 0.03em;">{{ $grn->grn_number }}</span>
                </div>

                <div style="margin-top: 0.4rem;">
                    <span class="badge badge-success" style="font-size: 0.72rem; font-weight: 700; padding: 0.25rem 0.65rem; text-transform: uppercase; letter-spacing: 0.03em;">
                        ✓ {{ strtoupper($grn->status) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Supplier & Delivery Audit Grid -->
        <div class="grn-box" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.25rem 1.5rem; margin-bottom: 2rem;">
            <div>
                <span style="font-size: 0.72rem; color: #64748b; text-transform: uppercase; font-weight: 800; letter-spacing: 0.04em; display: block; margin-bottom: 0.35rem;">SUPPLIER (VENDOR)</span>
                <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0 0 0.3rem;">
                    {{ $grn->supplier->name }}
                </h3>
                <div style="font-size: 0.8125rem; color: #475569; line-height: 1.5;">
                    @if($grn->supplier->company_name)
                        <div>Company: <strong>{{ $grn->supplier->company_name }}</strong></div>
                    @endif
                    @if($grn->supplier->phone)
                        <div>Phone: {{ $grn->supplier->phone }}</div>
                    @endif
                    @if($grn->supplier->email)
                        <div>Email: {{ $grn->supplier->email }}</div>
                    @endif
                </div>
            </div>

            <div style="border-left: 1px dashed #cbd5e1; padding-left: 1.5rem;">
                <span style="font-size: 0.72rem; color: #64748b; text-transform: uppercase; font-weight: 800; letter-spacing: 0.04em; display: block; margin-bottom: 0.35rem;">DELIVERY AUDIT &amp; TIMELINE</span>
                <div style="font-size: 0.8125rem; color: #334155; line-height: 1.6;">
                    <div>Received Date: <strong>{{ $grn->received_date->format('F d, Y') }}</strong></div>
                    <div>Received By: <strong>{{ $grn->receivedBy->name ?? 'Storekeeper / Staff' }}</strong></div>
                    <div>Supplier Invoice / DN Ref: 
                        <strong style="font-family: monospace; color: #0284c7;">
                            {{ $grn->supplier_invoice_ref ?? 'N/A (Direct Receiving)' }}
                        </strong>
                    </div>
                    @if($grn->notes)
                        <div style="margin-top: 0.25rem; color: #64748b; font-style: italic;">
                            Notes: {{ $grn->notes }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Received Line Items Table -->
        <table class="grn-table">
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">#</th>
                    <th>Received Item Description</th>
                    <th>Type</th>
                    <th style="text-align: right; width: 100px;">Quantity</th>
                    <th style="text-align: right; width: 130px;">Unit Cost</th>
                    <th style="text-align: right; width: 140px;">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($grn->items as $idx => $item)
                <tr>
                    <td style="text-align: center; color: #64748b; font-weight: 600;">{{ $idx + 1 }}</td>
                    <td>
                        <strong style="color: #0f172a; font-size: 0.9rem;">{{ $item->item_name }}</strong>
                        @if($item->phoneDevice)
                            <div style="font-family: monospace; font-size: 0.78rem; color: #0284c7; margin-top: 3px;">
                                Dual IMEI: {{ $item->phoneDevice->imei_1 }}{{ $item->phoneDevice->imei_2 ? ' / ' . $item->phoneDevice->imei_2 : '' }}
                            </div>
                        @endif
                        @if($item->batch_number)
                            <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">Batch / Serial: {{ $item->batch_number }}</div>
                        @endif
                    </td>
                    <td>
                        <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 0.72rem; text-transform: capitalize; border: 1px solid #e2e8f0;">
                            {{ str_replace('_', ' ', $item->item_type) }}
                        </span>
                    </td>
                    <td style="text-align: right; font-weight: 800; font-family: monospace; font-size: 0.95rem;">
                        {{ $item->quantity_received }}
                    </td>
                    <td style="text-align: right; color: #475569; font-family: monospace;">
                        LKR {{ number_format($item->unit_cost, 2) }}
                    </td>
                    <td style="text-align: right; font-weight: 800; color: #0f172a; font-family: monospace;">
                        LKR {{ number_format($item->subtotal, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="border-top: 2px solid #0f172a; font-size: 1rem; background: #f8fafc;">
                    <td colspan="4" style="border-bottom: none;"></td>
                    <td style="text-align: right; font-weight: 700; color: #475569; padding: 1rem; border-bottom: none;">
                        Total Value:
                    </td>
                    <td style="text-align: right; font-weight: 900; color: #0f172a; font-size: 1.15rem; font-family: monospace; padding: 1rem; border-bottom: none;">
                        LKR {{ number_format($grn->total_cost, 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- Signatures & Verification Section -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; margin-top: 4rem; padding-top: 1.5rem;">
            <div style="text-align: center;">
                <div style="border-top: 1.5px solid #0f172a; padding-top: 0.6rem;">
                    <strong style="font-size: 0.85rem; color: #0f172a; display: block;">Received &amp; Verified By</strong>
                    <span style="font-size: 0.75rem; color: #64748b;">Storekeeper / Warehouse In-charge</span>
                    <div style="font-size: 0.72rem; color: #94a3b8; margin-top: 0.25rem;">Date: ________________________</div>
                </div>
            </div>
            <div style="text-align: center;">
                <div style="border-top: 1.5px solid #0f172a; padding-top: 0.6rem;">
                    <strong style="font-size: 0.85rem; color: #0f172a; display: block;">Delivered By</strong>
                    <span style="font-size: 0.75rem; color: #64748b;">Supplier / Courier Representative</span>
                    <div style="font-size: 0.72rem; color: #94a3b8; margin-top: 0.25rem;">Name, ID &amp; Signature</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
