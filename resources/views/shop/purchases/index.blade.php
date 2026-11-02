@extends('layouts.shop')

@section('title', 'Purchases & Inbound Stock')
@section('page-title', 'Purchasing & Supplier Invoices')

@section('content')
@include('shop.partials.inventory-subnav')

<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <form method="GET" action="{{ route('shop.purchases') }}" style="display: flex; gap: 0.75rem; flex: 1; flex-wrap: wrap;">
            <input type="text" name="search" class="form-control" style="max-width: 260px;" placeholder="Search Purchase #, Supplier, Item..." value="{{ request('search') }}">
            <select name="supplier_id" class="form-control" style="max-width: 180px;" onchange="this.form.submit()">
                <option value="">All Suppliers</option>
                @foreach($suppliers as $sup)
                    <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                @endforeach
            </select>
            <input type="date" name="date" class="form-control" style="max-width: 150px;" value="{{ request('date') }}">
            <select name="payment_status" class="form-control" style="max-width: 140px;" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Fully Paid</option>
                <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>Partial</option>
                <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid / Credit</option>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
            @if(request()->hasAny(['search', 'supplier_id', 'date', 'payment_status']))
                <a href="{{ route('shop.purchases') }}" class="btn btn-secondary btn-sm" style="color: #64748b;">Clear</a>
            @endif
        </form>

        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <button type="button" class="btn btn-primary" onclick="openNewPurchaseModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Inbound Stock Purchase
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Purchase #</th>
                    <th>Date</th>
                    <th>Supplier</th>
                    <th>Items Received &amp; Serialized</th>
                    <th>Total Cost</th>
                    <th>Paid Amount</th>
                    <th>Payment Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $pur)
                <tr>
                    <td>
                        <strong style="color: #4f46e5; font-family: monospace; font-size: 0.9375rem;">
                            {{ $pur->purchase_number }}
                        </strong>
                    </td>
                    <td style="font-size: 0.8125rem; color: #64748b; white-space: nowrap;">
                        {{ $pur->purchase_date->format('M d, Y') }}
                    </td>
                    <td>
                        <strong style="color: #0f172a; font-size: 0.875rem;">{{ $pur->supplier->name ?? 'Unknown Supplier' }}</strong>
                        @if($pur->supplier && $pur->supplier->company_name)
                            <div style="font-size: 0.72rem; color: #64748b;">{{ $pur->supplier->company_name }}</div>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; flex-direction: column; gap: 0.2rem;">
                            @foreach($pur->items as $item)
                                <div style="font-size: 0.8125rem;">
                                    <span style="font-weight: 600;">{{ $item->item_name }}</span>
                                    <span style="color: var(--text-muted);">x{{ $item->quantity }}</span>
                                    @if($item->phoneDevice)
                                        <span class="badge badge-info" style="font-size: 0.68rem; font-family: monospace; padding: 0.1rem 0.35rem;">
                                            IMEI: {{ $item->phoneDevice->imei_1 }}
                                        </span>
                                    @endif
                                    <span style="font-size: 0.72rem; color: #64748b;">@ LKR {{ number_format($item->unit_cost, 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </td>
                    <td>
                        <strong style="color: #0f172a; font-size: 0.9375rem;">
                            LKR {{ number_format($pur->total_amount, 2) }}
                        </strong>
                    </td>
                    <td>
                        <span style="color: #166534; font-weight: 600; font-size: 0.875rem;">
                            LKR {{ number_format($pur->paid_amount, 2) }}
                        </span>
                    </td>
                    <td>
                        @if($pur->payment_status === 'paid')
                            <span class="badge badge-success">Paid</span>
                        @elseif($pur->payment_status === 'partial')
                            <span class="badge badge-warning">Partial</span>
                        @else
                            <span class="badge badge-danger">Unpaid Credit</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 3rem; color: #94a3b8;">
                        No purchases recorded. Click "+ Inbound Stock Purchase" to receive phones, accessories, or spare parts from suppliers.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($purchases->hasPages())
    <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border);">
        {{ $purchases->links() }}
    </div>
    @endif
</div>

<!-- Modal: New Inbound Stock Purchase -->
<div class="modal-backdrop" id="purchaseModal">
    <div class="modal-box" style="max-width: 780px;">
        <div class="modal-header">
            <h3>Record Inbound Stock Purchase</h3>
            <button type="button" class="modal-close" onclick="closeModal('purchaseModal')">&times;</button>
        </div>
        <form action="{{ route('shop.purchases.store') }}" method="POST" id="purchaseForm">
            @csrf
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Select Supplier *</label>
                        <select name="supplier_id" class="form-control" required>
                            <option value="">— Select Supplier Vendor —</option>
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}">{{ $sup->name }} {{ $sup->company_name ? "({$sup->company_name})" : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Purchase Date *</label>
                        <input type="date" name="purchase_date" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>
                </div>

                <div style="margin-top: 0.5rem; margin-bottom: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.6rem;">
                        <span style="font-size: 0.8125rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted);">
                            Purchase Line Items (Auto Stock Increment)
                        </span>
                        <div style="display: flex; gap: 0.5rem;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="addProductItemRow()">+ Accessory / Product</button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="addPhoneItemRow()">+ Dual IMEI Phone</button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="addSparePartRow()">+ Spare Part</button>
                        </div>
                    </div>

                    <div id="purchaseItemsContainer" style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <!-- Dynamic Item Rows -->
                    </div>
                </div>

                <div class="form-row" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem; margin-top: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Amount Paid Now to Supplier (LKR)</label>
                        <input type="number" step="0.01" name="paid_amount" id="inpPaidAmount" class="form-control" placeholder="0.00" value="0.00" oninput="calculatePurchaseTotals()">
                        <span style="font-size: 0.72rem; color: #64748b;">Remaining un-settled balance automatically adds to supplier debt.</span>
                    </div>
                    <div style="display: flex; flex-direction: column; justify-content: center; text-align: right;">
                        <span style="font-size: 0.75rem; text-transform: uppercase; color: #64748b; font-weight: 700;">Grand Total Cost</span>
                        <span id="lblPurchaseGrandTotal" style="font-size: 1.35rem; font-weight: 800; color: #166534;">LKR 0.00</span>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 1rem; margin-bottom: 0;">
                    <label>Internal Invoice / Goods Receipt Notes</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Supplier Bill # 48291, delivered via courier"></textarea>
                </div>
            </div>
            <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('purchaseModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Purchase &amp; Increment Stock</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const availableProducts = @json($products);
    const availableSpareParts = @json($spareParts);
    let itemIndex = 0;

    function openNewPurchaseModal() {
        document.getElementById('purchaseItemsContainer').innerHTML = '';
        itemIndex = 0;
        addProductItemRow();
        openModal('purchaseModal');
    }

    function addProductItemRow() {
        const container = document.getElementById('purchaseItemsContainer');
        const row = document.createElement('div');
        row.className = 'card';
        row.style.padding = '0.75rem';
        row.style.background = '#f8fafc';
        row.innerHTML = `
            <input type="hidden" name="items[${itemIndex}][item_type]" value="product">
            <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.4rem;">
                <span class="badge badge-info" style="font-size: 0.7rem;">Product / Accessory</span>
                <span style="flex:1;"></span>
                <button type="button" onclick="this.closest('.card').remove(); calculatePurchaseTotals();" style="border:none;background:none;color:#ef4444;font-size:1.1rem;cursor:pointer;">&times;</button>
            </div>
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 0.5rem;">
                <div>
                    <label style="font-size: 0.72rem; font-weight: 600;">Select Product *</label>
                    <select name="items[${itemIndex}][product_id]" class="form-control" required onchange="onProductSelect(this, ${itemIndex})">
                        <option value="">— Select Catalog Product —</option>
                        ${availableProducts.map(p => `<option value="${p.id}" data-name="${p.name}" data-cost="${p.cost_price}">${p.name} (SKU: ${p.code})</option>`).join('')}
                    </select>
                    <input type="hidden" name="items[${itemIndex}][item_name]" id="itemName_${itemIndex}" value="">
                </div>
                <div>
                    <label style="font-size: 0.72rem; font-weight: 600;">Qty *</label>
                    <input type="number" name="items[${itemIndex}][quantity]" class="form-control pur-qty" value="1" min="1" required oninput="calculatePurchaseTotals()">
                </div>
                <div>
                    <label style="font-size: 0.72rem; font-weight: 600;">Unit Cost (LKR) *</label>
                    <input type="number" step="0.01" name="items[${itemIndex}][unit_cost]" id="itemCost_${itemIndex}" class="form-control pur-cost" value="0.00" min="0" required oninput="calculatePurchaseTotals()">
                </div>
            </div>
        `;
        container.appendChild(row);
        itemIndex++;
        calculatePurchaseTotals();
    }

    function onProductSelect(sel, idx) {
        const opt = sel.options[sel.selectedIndex];
        if (opt && opt.value) {
            document.getElementById(`itemName_${idx}`).value = opt.getAttribute('data-name');
            document.getElementById(`itemCost_${idx}`).value = opt.getAttribute('data-cost');
            calculatePurchaseTotals();
        }
    }

    function addSparePartRow() {
        const container = document.getElementById('purchaseItemsContainer');
        const row = document.createElement('div');
        row.className = 'card';
        row.style.padding = '0.75rem';
        row.style.background = '#f8fafc';
        row.innerHTML = `
            <input type="hidden" name="items[${itemIndex}][item_type]" value="spare_part">
            <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.4rem;">
                <span class="badge badge-purple" style="font-size: 0.7rem;">Spare Part</span>
                <span style="flex:1;"></span>
                <button type="button" onclick="this.closest('.card').remove(); calculatePurchaseTotals();" style="border:none;background:none;color:#ef4444;font-size:1.1rem;cursor:pointer;">&times;</button>
            </div>
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 0.5rem;">
                <div>
                    <label style="font-size: 0.72rem; font-weight: 600;">Select Spare Part *</label>
                    <select name="items[${itemIndex}][spare_part_id]" class="form-control" required onchange="onSparePartSelect(this, ${itemIndex})">
                        <option value="">— Select Spare Part —</option>
                        ${availableSpareParts.map(p => `<option value="${p.id}" data-name="${p.name}" data-cost="${p.cost_price}">${p.name} (#${p.part_number})</option>`).join('')}
                    </select>
                    <input type="hidden" name="items[${itemIndex}][item_name]" id="itemPartName_${itemIndex}" value="">
                </div>
                <div>
                    <label style="font-size: 0.72rem; font-weight: 600;">Qty *</label>
                    <input type="number" name="items[${itemIndex}][quantity]" class="form-control pur-qty" value="1" min="1" required oninput="calculatePurchaseTotals()">
                </div>
                <div>
                    <label style="font-size: 0.72rem; font-weight: 600;">Unit Cost (LKR) *</label>
                    <input type="number" step="0.01" name="items[${itemIndex}][unit_cost]" id="itemPartCost_${itemIndex}" class="form-control pur-cost" value="0.00" min="0" required oninput="calculatePurchaseTotals()">
                </div>
            </div>
        `;
        container.appendChild(row);
        itemIndex++;
        calculatePurchaseTotals();
    }

    function onSparePartSelect(sel, idx) {
        const opt = sel.options[sel.selectedIndex];
        if (opt && opt.value) {
            document.getElementById(`itemPartName_${idx}`).value = opt.getAttribute('data-name');
            document.getElementById(`itemPartCost_${idx}`).value = opt.getAttribute('data-cost');
            calculatePurchaseTotals();
        }
    }

    function addPhoneItemRow() {
        const container = document.getElementById('purchaseItemsContainer');
        const row = document.createElement('div');
        row.className = 'card';
        row.style.padding = '0.75rem';
        row.style.background = '#f0fdf4';
        row.style.borderColor = '#bbf7d0';
        row.innerHTML = `
            <input type="hidden" name="items[${itemIndex}][item_type]" value="phone">
            <input type="hidden" name="items[${itemIndex}][quantity]" class="pur-qty" value="1">
            <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.4rem;">
                <span class="badge badge-success" style="font-size: 0.7rem;">Dual-IMEI Smartphone Device</span>
                <span style="flex:1;"></span>
                <button type="button" onclick="this.closest('.card').remove(); calculatePurchaseTotals();" style="border:none;background:none;color:#ef4444;font-size:1.1rem;cursor:pointer;">&times;</button>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1.2fr 1fr 1fr; gap: 0.5rem;">
                <div>
                    <label style="font-size: 0.72rem; font-weight: 600;">Brand *</label>
                    <input type="text" name="items[${itemIndex}][brand]" class="form-control" required placeholder="Samsung" oninput="updatePhoneItemName(${itemIndex})">
                </div>
                <div>
                    <label style="font-size: 0.72rem; font-weight: 600;">Model *</label>
                    <input type="text" name="items[${itemIndex}][model]" class="form-control" required placeholder="Galaxy A55" oninput="updatePhoneItemName(${itemIndex})">
                    <input type="hidden" name="items[${itemIndex}][item_name]" id="phoneItemName_${itemIndex}" value="Smartphone Device">
                </div>
                <div>
                    <label style="font-size: 0.72rem; font-weight: 600;">IMEI 1 (15 digits) *</label>
                    <input type="text" name="items[${itemIndex}][imei_1]" class="form-control" required placeholder="358291048291048" style="font-family: monospace;">
                </div>
                <div>
                    <label style="font-size: 0.72rem; font-weight: 600;">Cost (LKR) *</label>
                    <input type="number" step="0.01" name="items[${itemIndex}][unit_cost]" class="form-control pur-cost" value="0.00" min="0" required oninput="calculatePurchaseTotals()">
                </div>
                <div>
                    <label style="font-size: 0.72rem; font-weight: 600;">Sell Target</label>
                    <input type="number" step="0.01" name="items[${itemIndex}][selling_price]" class="form-control" placeholder="Selling Price">
                </div>
            </div>
        `;
        container.appendChild(row);
        itemIndex++;
        calculatePurchaseTotals();
    }

    function updatePhoneItemName(idx) {
        const brand = document.querySelector(`[name="items[${idx}][brand]"]`).value || '';
        const model = document.querySelector(`[name="items[${idx}][model]"]`).value || '';
        document.getElementById(`phoneItemName_${idx}`).value = `${brand} ${model}`.trim() || 'Smartphone Device';
    }

    function calculatePurchaseTotals() {
        let total = 0;
        const rows = document.querySelectorAll('#purchaseItemsContainer .card');
        rows.forEach(r => {
            const qty = parseFloat(r.querySelector('.pur-qty')?.value || 1);
            const cost = parseFloat(r.querySelector('.pur-cost')?.value || 0);
            total += (qty * cost);
        });

        document.getElementById('lblPurchaseGrandTotal').innerText = 'LKR ' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
</script>
@endpush
@endsection

