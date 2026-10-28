@extends('layouts.shop')

@section('title', 'Goods Received Notes (GRN)')
@section('page_title', 'Goods Received Notes & Inbound Stock Receiving')

@section('content')
<div class="metrics-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <div class="card" style="padding: 1.25rem; border-left: 4px solid #4f46e5;">
        <span style="font-size: 0.8125rem; color: #64748b; font-weight: 600;">TOTAL GRNS PROCESSED</span>
        <div style="font-size: 1.75rem; font-weight: 800; color: #0f172a; margin-top: 0.25rem;">
            {{ number_format($totalGrnCount) }}
        </div>
        <span style="font-size: 0.75rem; color: #94a3b8;">Inbound deliveries recorded</span>
    </div>

    <div class="card" style="padding: 1.25rem; border-left: 4px solid #059669;">
        <span style="font-size: 0.8125rem; color: #64748b; font-weight: 600;">TOTAL GOODS RECEIVED VALUE</span>
        <div style="font-size: 1.75rem; font-weight: 800; color: #059669; margin-top: 0.25rem;">
            LKR {{ number_format($totalReceivedValue, 2) }}
        </div>
        <span style="font-size: 0.75rem; color: #94a3b8;">Total inventory cost value received</span>
    </div>
</div>

<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <form method="GET" action="{{ route('shop.inventory.grns') }}" style="display: flex; gap: 0.75rem; flex: 1; flex-wrap: wrap;">
            <input type="text" name="search" class="form-control" style="max-width: 280px;" placeholder="Search GRN #, Invoice Ref, Item..." value="{{ request('search') }}">
            <select name="supplier_id" class="form-control" style="max-width: 200px;" onchange="this.form.submit()">
                <option value="">All Suppliers</option>
                @foreach($suppliers as $sup)
                    <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                @endforeach
            </select>
            <input type="date" name="date" class="form-control" style="max-width: 170px;" value="{{ request('date') }}" onchange="this.form.submit()">
            <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
            @if(request()->hasAny(['search', 'supplier_id', 'date']))
                <a href="{{ route('shop.inventory.grns') }}" class="btn btn-sm btn-secondary" style="color: #64748b;">Clear</a>
            @endif
        </form>

        <button type="button" class="btn btn-primary" onclick="openModal('addGrnModal')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Receive Goods (New GRN)
        </button>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>GRN Number</th>
                    <th>Supplier / Delivery Ref</th>
                    <th>Received Date</th>
                    <th>Received By</th>
                    <th>Items Count</th>
                    <th>Total Received Cost</th>
                    <th>Status</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($grns as $grn)
                <tr>
                    <td>
                        <a href="{{ route('shop.inventory.grns.show', $grn) }}" style="font-family: monospace; font-weight: 700; color: #4f46e5; text-decoration: underline;">
                            {{ $grn->grn_number }}
                        </a>
                    </td>
                    <td>
                        <strong>{{ $grn->supplier->name ?? 'N/A' }}</strong>
                        @if($grn->supplier_invoice_ref)
                            <div style="font-family: monospace; font-size: 0.75rem; color: #0284c7;">
                                Ref: {{ $grn->supplier_invoice_ref }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <span style="font-size: 0.8125rem;">{{ $grn->received_date->format('M d, Y') }}</span>
                    </td>
                    <td>
                        <span style="font-size: 0.8125rem; color: #475569;">{{ $grn->receivedBy->name ?? 'System' }}</span>
                    </td>
                    <td>
                        <span class="badge badge-secondary">{{ $grn->items->count() }} line items</span>
                    </td>
                    <td>
                        <strong style="color: #059669; font-size: 0.9375rem;">LKR {{ number_format($grn->total_cost, 2) }}</strong>
                    </td>
                    <td>
                        <span class="badge badge-success">Completed</span>
                    </td>
                    <td style="text-align: right;">
                        <a href="{{ route('shop.inventory.grns.show', $grn) }}" class="btn btn-secondary btn-sm" style="font-size: 0.75rem; padding: 0.3rem 0.6rem;">
                            View GRN
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 3rem;">
                        No Goods Received Notes recorded yet. Click "Receive Goods (New GRN)" to log supplier deliveries.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($grns->hasPages())
    <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border);">
        {{ $grns->links() }}
    </div>
    @endif
</div>

<!-- Modal: New GRN Goods Receiving -->
<div class="modal-backdrop" id="addGrnModal">
    <div class="modal-box" style="max-width: 850px;">
        <div class="modal-header">
            <h3>Receive Inbound Goods (New GRN)</h3>
            <button type="button" class="modal-close" onclick="closeModal('addGrnModal')">&times;</button>
        </div>
        <form method="POST" action="{{ route('shop.inventory.grns.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Supplier *</label>
                        <select name="supplier_id" class="form-control" required>
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Supplier Invoice / Delivery Ref (Unique)</label>
                        <input type="text" name="supplier_invoice_ref" class="form-control" placeholder="e.g. INV-9042 or DN-5510">
                        <small style="color: #64748b; font-size: 0.72rem;">Prevents duplicate receiving of the same delivery note</small>
                    </div>
                    <div class="form-group">
                        <label>Received Date *</label>
                        <input type="date" name="received_date" class="form-control" value="{{ now()->toDateString() }}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Internal Receiving Notes</label>
                    <textarea name="notes" class="form-control" rows="1" placeholder="Delivery vehicle number, inspector notes, batch details..."></textarea>
                </div>

                <!-- Line Items Table -->
                <div style="margin-top: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <strong style="font-size: 0.875rem; color: #1e293b;">Goods Line Items</strong>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="addGrnItemRow()">+ Add Item</button>
                    </div>

                    <table class="data-table" id="grnItemsTable">
                        <thead>
                            <tr>
                                <th>Item Type</th>
                                <th>Name / Device</th>
                                <th style="width: 100px;">Qty</th>
                                <th style="width: 130px;">Unit Cost</th>
                                <th style="width: 130px;">Subtotal</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody id="grnItemsBody">
                            <tr>
                                <td>
                                    <select name="items[0][item_type]" class="form-control grn-type-select" onchange="onGrnTypeChange(this, 0)" required>
                                        <option value="product">Product / Accessory</option>
                                        <option value="spare_part">Spare Part</option>
                                        <option value="phone">Serialized Phone (IMEI)</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="items[0][item_name]" class="form-control" placeholder="Item name" required>
                                    <div class="grn-phone-imei-box" id="grnPhoneImeiBox_0" style="display: none; margin-top: 0.35rem;">
                                        <input type="text" name="items[0][imei_1]" class="form-control" placeholder="IMEI 1 (15 digits)" style="font-family: monospace; font-size: 0.75rem; margin-bottom: 0.25rem;">
                                        <input type="text" name="items[0][imei_2]" class="form-control" placeholder="IMEI 2 (Optional)" style="font-family: monospace; font-size: 0.75rem;">
                                    </div>
                                </td>
                                <td>
                                    <input type="number" name="items[0][quantity]" class="form-control grn-qty" value="1" min="1" oninput="calculateGrnTotal()" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="items[0][unit_cost]" class="form-control grn-cost" value="0.00" min="0" oninput="calculateGrnTotal()" required>
                                </td>
                                <td>
                                    <span class="grn-subtotal-display" style="font-weight: 700; color: #166534; font-size: 0.85rem;">LKR 0.00</span>
                                </td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>

                    <div style="display: flex; justify-content: flex-end; align-items: center; gap: 1rem; margin-top: 1rem; padding: 0.75rem; background: #f8fafc; border-radius: 8px;">
                        <span style="font-size: 0.875rem; color: #64748b;">Total Inbound Value:</span>
                        <strong id="grnTotalDisplay" style="font-size: 1.125rem; color: #059669;">LKR 0.00</strong>
                    </div>
                </div>
            </div>
            <div style="padding: 1.25rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addGrnModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Process GRN &amp; Update Stock</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    let grnItemIndex = 1;

    function addGrnItemRow() {
        const i = grnItemIndex++;
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <select name="items[${i}][item_type]" class="form-control grn-type-select" onchange="onGrnTypeChange(this, ${i})" required>
                    <option value="product">Product / Accessory</option>
                    <option value="spare_part">Spare Part</option>
                    <option value="phone">Serialized Phone (IMEI)</option>
                </select>
            </td>
            <td>
                <input type="text" name="items[${i}][item_name]" class="form-control" placeholder="Item name" required>
                <div class="grn-phone-imei-box" id="grnPhoneImeiBox_${i}" style="display: none; margin-top: 0.35rem;">
                    <input type="text" name="items[${i}][imei_1]" class="form-control" placeholder="IMEI 1 (15 digits)" style="font-family: monospace; font-size: 0.75rem; margin-bottom: 0.25rem;">
                    <input type="text" name="items[${i}][imei_2]" class="form-control" placeholder="IMEI 2 (Optional)" style="font-family: monospace; font-size: 0.75rem;">
                </div>
            </td>
            <td>
                <input type="number" name="items[${i}][quantity]" class="form-control grn-qty" value="1" min="1" oninput="calculateGrnTotal()" required>
            </td>
            <td>
                <input type="number" step="0.01" name="items[${i}][unit_cost]" class="form-control grn-cost" value="0.00" min="0" oninput="calculateGrnTotal()" required>
            </td>
            <td>
                <span class="grn-subtotal-display" style="font-weight: 700; color: #166534; font-size: 0.85rem;">LKR 0.00</span>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove(); calculateGrnTotal();" style="padding: 0.25rem 0.5rem;">&times;</button>
            </td>
        `;
        document.getElementById('grnItemsBody').appendChild(row);
    }

    function onGrnTypeChange(select, index) {
        const box = document.getElementById('grnPhoneImeiBox_' + index);
        if (box) {
            box.style.display = (select.value === 'phone') ? 'block' : 'none';
        }
    }

    function calculateGrnTotal() {
        let total = 0;
        const rows = document.querySelectorAll('#grnItemsBody tr');
        rows.forEach(function(row) {
            const qty = parseFloat(row.querySelector('.grn-qty')?.value || 0);
            const cost = parseFloat(row.querySelector('.grn-cost')?.value || 0);
            const sub = qty * cost;
            const subDisplay = row.querySelector('.grn-subtotal-display');
            if (subDisplay) {
                subDisplay.innerText = 'LKR ' + sub.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }
            total += sub;
        });

        const totalDisplay = document.getElementById('grnTotalDisplay');
        if (totalDisplay) {
            totalDisplay.innerText = 'LKR ' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
    }
</script>
@endpush
@endsection

