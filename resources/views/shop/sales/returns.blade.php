@extends('layouts.shop')

@section('title', 'Sales Returns & Refunds')
@section('page-title', 'Sales Returns & Customer Refunds')

@section('content')
<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <form method="GET" action="{{ route('shop.sales.returns') }}" style="display: flex; gap: 0.75rem; flex: 1; max-width: 460px;">
            <input type="text" name="search" class="form-control" placeholder="Search Return #, Invoice #, Customer..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-secondary btn-sm">Search</button>
            @if(request('search'))
                <a href="{{ route('shop.sales.returns') }}" class="btn btn-secondary btn-sm" style="color: #64748b;">Clear</a>
            @endif
        </form>

        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="font-size: 0.8125rem; background: #fff1f2; color: #be123c; padding: 0.45rem 0.85rem; border-radius: 8px; font-weight: 700; border: 1px solid #fecdd3;">
                Total Refunded: LKR {{ number_format($totalRefundsAmount, 2) }}
            </div>
            <button type="button" class="btn btn-primary" onclick="openNewReturnModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>
                Process Sales Return
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Return #</th>
                    <th>Date</th>
                    <th>Original Invoice</th>
                    <th>Customer</th>
                    <th>Returned Items &amp; Serial</th>
                    <th>Refund Amount</th>
                    <th>Return Reason</th>
                </tr>
            </thead>
            <tbody>
                @forelse($returns as $ret)
                <tr>
                    <td>
                        <strong style="color: #b91c1c; font-family: monospace; font-size: 0.9375rem;">
                            {{ $ret->return_number }}
                        </strong>
                    </td>
                    <td style="font-size: 0.8125rem; color: #64748b; white-space: nowrap;">
                        {{ $ret->return_date->format('M d, Y') }}
                    </td>
                    <td>
                        @if($ret->sale)
                            <a href="{{ route('shop.sales.show', $ret->sale) }}" style="font-family: monospace; font-weight: 700; color: #4f46e5; text-decoration: none;">
                                {{ $ret->sale->invoice_number }}
                            </a>
                        @else
                            <span style="color: #94a3b8;">—</span>
                        @endif
                    </td>
                    <td>
                        <strong style="color: #0f172a; font-size: 0.875rem;">{{ $ret->customer->name ?? ($ret->sale->customer->name ?? 'Walk-in Retail') }}</strong>
                        @if($ret->customer && $ret->customer->phone)
                            <div style="font-size: 0.72rem; color: #64748b;">{{ $ret->customer->phone }}</div>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                            @foreach($ret->items as $item)
                                <div style="font-size: 0.8125rem;">
                                    <span style="font-weight: 600;">{{ $item->saleItem->item_name ?? 'Item' }}</span>
                                    <span style="color: var(--text-muted);">x{{ $item->quantity }}</span>
                                    @if($item->condition === 'defective')
                                        <span class="badge badge-danger" style="font-size: 0.68rem;">Defective</span>
                                    @else
                                        <span class="badge badge-success" style="font-size: 0.68rem;">Restocked</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </td>
                    <td>
                        <strong style="color: #b91c1c; font-size: 0.9375rem;">
                            LKR {{ number_format($ret->total_refund_amount, 2) }}
                        </strong>
                    </td>
                    <td style="font-size: 0.8125rem; color: #475569;">
                        {{ $ret->reason }}
                        @if($ret->notes)
                            <div style="font-size: 0.72rem; color: #64748b;">{{ $ret->notes }}</div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 3rem; color: #94a3b8;">
                        No returns on record. To process a return or exchange, click "+ Process Sales Return".
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($returns->hasPages())
    <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border);">
        {{ $returns->links() }}
    </div>
    @endif
</div>

<!-- Modal: Process Return -->
<div class="modal-backdrop" id="returnModal">
    <div class="modal-box" style="max-width: 680px;">
        <div class="modal-header">
            <h3>Process Sales Return &amp; Restock</h3>
            <button type="button" class="modal-close" onclick="closeModal('returnModal')">&times;</button>
        </div>
        <form action="{{ route('shop.sales.returns.store') }}" method="POST" id="returnForm">
            @csrf
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Select Original Sales Invoice *</label>
                        <select name="sale_id" id="returnSaleSelect" class="form-control" required onchange="onSaleInvoiceSelected(this)">
                            <option value="">— Select Completed Invoice —</option>
                            @foreach($recentSales as $sale)
                                <option value="{{ $sale->id }}" data-items='@json($sale->items)'>
                                    {{ $sale->invoice_number }} ({{ $sale->customer->name ?? 'Walk-in' }} - LKR {{ number_format($sale->total_amount, 2) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Return Date *</label>
                        <input type="date" name="return_date" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label>Return Reason *</label>
                    <input type="text" name="reason" class="form-control" required placeholder="e.g. Wrong model purchased / Display touch defect / Customer exchange">
                </div>

                <!-- Invoice Line Items Container -->
                <div style="margin-top: 1rem;">
                    <label style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); display: block; margin-bottom: 0.5rem;">
                        Select Items Being Returned
                    </label>
                    <div id="returnItemsList" style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <div style="text-align: center; padding: 1.5rem; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; color: #94a3b8; font-size: 0.8125rem;">
                            Select a sale invoice above to view line items.
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 1.25rem; margin-bottom: 0;">
                    <label>Internal Notes / Inspection Assessment</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Accessories and box in mint condition. Customer refunded in cash."></textarea>
                </div>
            </div>
            <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('returnModal')">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background: #b91c1c; border-color: #b91c1c;">Submit Return &amp; Restock</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openNewReturnModal() {
        document.getElementById('returnSaleSelect').value = '';
        document.getElementById('returnItemsList').innerHTML = `
            <div style="text-align: center; padding: 1.5rem; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; color: #94a3b8; font-size: 0.8125rem;">
                Select a sale invoice above to view line items.
            </div>
        `;
        openModal('returnModal');
    }

    function onSaleInvoiceSelected(sel) {
        const opt = sel.options[sel.selectedIndex];
        const container = document.getElementById('returnItemsList');

        if (!opt || !opt.value) {
            container.innerHTML = `<div style="text-align: center; padding: 1.5rem; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; color: #94a3b8; font-size: 0.8125rem;">Select a sale invoice above to view line items.</div>`;
            return;
        }

        const items = JSON.parse(opt.getAttribute('data-items') || '[]');
        if (items.length === 0) {
            container.innerHTML = `<div style="padding: 1rem; color: #b91c1c;">No items found on this invoice.</div>`;
            return;
        }

        container.innerHTML = items.map((item, idx) => `
            <div class="card" style="padding: 0.85rem; background: #f8fafc; display: flex; flex-direction: column; gap: 0.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong style="color: #0f172a; font-size: 0.875rem;">${item.item_name}</strong>
                        <span style="font-size: 0.75rem; color: #64748b;">(Sold Qty: ${item.quantity} @ LKR ${parseFloat(item.unit_price).toFixed(2)})</span>
                        ${item.phone_device ? `<span class="badge badge-info" style="font-family: monospace; font-size: 0.68rem; margin-left: 0.4rem;">IMEI: ${item.phone_device.imei_1}</span>` : ''}
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1.2fr 1fr 1fr; gap: 0.5rem; align-items: center; margin-top: 0.25rem;">
                    <input type="hidden" name="items[${idx}][sale_item_id]" value="${item.id}">
                    <div>
                        <label style="font-size: 0.7rem; font-weight: 600;">Return Qty *</label>
                        <input type="number" name="items[${idx}][quantity]" class="form-control" value="1" min="1" max="${item.quantity}" required>
                    </div>
                    <div>
                        <label style="font-size: 0.7rem; font-weight: 600;">Refund (LKR) *</label>
                        <input type="number" step="0.01" name="items[${idx}][refund_amount]" class="form-control" value="${parseFloat(item.unit_price).toFixed(2)}" required>
                    </div>
                    <div>
                        <label style="font-size: 0.7rem; font-weight: 600;">Condition *</label>
                        <select name="items[${idx}][condition]" class="form-control" required>
                            <option value="resalable">Resalable / Mint</option>
                            <option value="defective">Defective / Damaged</option>
                        </select>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.35rem; padding-top: 1rem;">
                        <input type="checkbox" name="items[${idx}][restock]" value="1" checked id="chkRestock_${idx}">
                        <label for="chkRestock_${idx}" style="font-size: 0.75rem; margin: 0; cursor: pointer;">Restock</label>
                    </div>
                </div>
            </div>
        `).join('');
    }
</script>
@endpush
@endsection

