@extends('layouts.shop')

@section('title', 'Goods Received Note ' . $grn->grn_number)
@section('page_title', 'Goods Received Note Voucher')

@section('content')
<style>
    @media print {
        .no-print {
            display: none !important;
        }
        body {
            background: white !important;
            font-size: 12pt;
        }
        .grn-voucher-card {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
        }
    }
</style>

<div style="max-width: 900px; margin: 0 auto;">
    <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
        <a href="{{ route('shop.inventory.grns') }}" class="btn btn-secondary">
            &larr; Back to GRN Directory
        </a>
        <button type="button" class="btn btn-primary" onclick="window.print()">
            🖨️ Print GRN Voucher
        </button>
    </div>

    <div class="card grn-voucher-card" style="padding: 2.5rem; border-radius: 12px; box-shadow: 0 4px 14px rgba(0,0,0,0.06); background: white;">
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 1.5rem; border-bottom: 2px solid #e2e8f0; margin-bottom: 1.75rem;">
            <div>
                <h1 style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0 0 0.25rem;">GOODS RECEIVED NOTE (GRN)</h1>
                <div style="font-size: 0.875rem; color: #64748b;">Official Inbound Stock Receiving Document</div>
                <div style="margin-top: 0.75rem;">
                    <span style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 700;">GRN Reference</span>
                    <div style="font-family: monospace; font-size: 1.25rem; font-weight: 800; color: #4f46e5;">{{ $grn->grn_number }}</div>
                </div>
            </div>

            <div style="text-align: right;">
                <span class="badge badge-success" style="font-size: 0.8125rem; padding: 0.35rem 0.75rem; text-transform: uppercase;">
                    {{ strtoupper($grn->status) }}
                </span>
                <div style="margin-top: 0.5rem; font-size: 0.875rem; color: #334155;">
                    Received Date: <strong>{{ $grn->received_date->format('M d, Y') }}</strong>
                </div>
                <div style="font-size: 0.8125rem; color: #64748b;">
                    Received By: <strong>{{ $grn->receivedBy->name ?? 'Staff In-charge' }}</strong>
                </div>
            </div>
        </div>

        <!-- Supplier Details Box -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.25rem; margin-bottom: 2rem;">
            <div>
                <span style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Supplier Details</span>
                <h3 style="font-size: 1.125rem; font-weight: 800; color: #1e293b; margin: 0.25rem 0 0.5rem;">
                    {{ $grn->supplier->name }}
                </h3>
                @if($grn->supplier->company_name)
                    <div style="font-size: 0.8125rem; color: #475569;">Company: {{ $grn->supplier->company_name }}</div>
                @endif
                @if($grn->supplier->phone)
                    <div style="font-size: 0.8125rem; color: #475569;">Phone: {{ $grn->supplier->phone }}</div>
                @endif
            </div>

            <div>
                <span style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Delivery Reference &amp; Audit</span>
                <div style="margin-top: 0.25rem; font-size: 0.875rem; color: #1e293b;">
                    Supplier Invoice / DN Ref: 
                    <strong style="font-family: monospace; color: #0284c7;">
                        {{ $grn->supplier_invoice_ref ?? 'N/A (Direct Receiving)' }}
                    </strong>
                </div>
                @if($grn->notes)
                    <div style="margin-top: 0.5rem; font-size: 0.8125rem; color: #475569;">
                        Notes: {{ $grn->notes }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <table class="data-table" style="margin-bottom: 1.5rem;">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Received Item Description</th>
                    <th>Type</th>
                    <th style="text-align: right;">Quantity</th>
                    <th style="text-align: right;">Unit Cost</th>
                    <th style="text-align: right;">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($grn->items as $idx => $item)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>
                        <strong style="color: #1e293b;">{{ $item->item_name }}</strong>
                        @if($item->phoneDevice)
                            <div style="font-family: monospace; font-size: 0.75rem; color: #0284c7; margin-top: 2px;">
                                Dual IMEI: {{ $item->phoneDevice->imei_1 }}{{ $item->phoneDevice->imei_2 ? ' / ' . $item->phoneDevice->imei_2 : '' }}
                            </div>
                        @endif
                        @if($item->batch_number)
                            <div style="font-size: 0.75rem; color: #64748b;">Batch: {{ $item->batch_number }}</div>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-info" style="text-transform: capitalize;">{{ str_replace('_', ' ', $item->item_type) }}</span>
                    </td>
                    <td style="text-align: right; font-weight: 700;">
                        {{ $item->quantity_received }}
                    </td>
                    <td style="text-align: right; color: #475569;">
                        LKR {{ number_format($item->unit_cost, 2) }}
                    </td>
                    <td style="text-align: right; font-weight: 700; color: #166534;">
                        LKR {{ number_format($item->subtotal, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="border-top: 2px solid #cbd5e1; font-size: 1rem;">
                    <td colspan="5" style="text-align: right; font-weight: 700; color: #1e293b;">Total Value of Inbound Goods:</td>
                    <td style="text-align: right; font-weight: 800; color: #059669;">
                        LKR {{ number_format($grn->total_cost, 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- Signatures Footer -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; margin-top: 4rem; padding-top: 1.5rem;">
            <div style="text-align: center; border-top: 1px dashed #94a3b8; padding-top: 0.5rem; font-size: 0.8125rem; color: #475569;">
                <strong>Received &amp; Verified By</strong>
                <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 2px;">Warehouse / Store Manager Signature</div>
            </div>
            <div style="text-align: center; border-top: 1px dashed #94a3b8; padding-top: 0.5rem; font-size: 0.8125rem; color: #475569;">
                <strong>Delivered By Supplier Representative</strong>
                <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 2px;">Name, ID &amp; Signature</div>
            </div>
        </div>
    </div>
</div>
@endsection

