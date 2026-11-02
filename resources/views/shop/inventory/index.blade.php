@extends('layouts.shop')

@section('title', 'Stock Movements & Inventory Audit')
@section('page-title', 'Stock Movements & Inventory Audits')

@section('content')
<!-- Valuation & Inventory Overview -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">Total Inventory Valuation (Cost)</span>
        <div style="font-size: 1.5rem; font-weight: 800; color: #166534; margin-top: 0.35rem;">
            LKR {{ number_format($totalInventoryCostValuation, 2) }}
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">Combined cost worth across store</div>
    </div>

    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">In-Stock Phones</span>
        <div style="font-size: 1.5rem; font-weight: 800; color: #4f46e5; margin-top: 0.35rem;">
            {{ number_format($inStockPhonesCount) }} devices
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">Serialized dual-IMEI units ready to sell</div>
    </div>

    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">Products &amp; Accessories</span>
        <div style="font-size: 1.5rem; font-weight: 800; color: #0284c7; margin-top: 0.35rem;">
            {{ number_format($totalProductsCount) }} items
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">Standard accessories in stock</div>
    </div>

    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">Spare Parts Stock</span>
        <div style="font-size: 1.5rem; font-weight: 800; color: #7c3aed; margin-top: 0.35rem;">
            {{ number_format($totalSparePartsCount) }} units
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">Displays, batteries, ICs</div>
    </div>
</div>

<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <form method="GET" action="{{ route('shop.inventory') }}" style="display: flex; gap: 0.75rem; flex: 1; flex-wrap: wrap;">
            <input type="text" name="search" class="form-control" style="max-width: 280px;" placeholder="Search item, notes, IMEI..." value="{{ request('search') }}">
            <select name="type" class="form-control" style="max-width: 170px;" onchange="this.form.submit()">
                <option value="">All Movement Types</option>
                <option value="sale" {{ request('type') === 'sale' ? 'selected' : '' }}>Sales Out</option>
                <option value="purchase" {{ request('type') === 'purchase' ? 'selected' : '' }}>Purchase In</option>
                <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>Manual Adjustment</option>
                <option value="return" {{ request('type') === 'return' ? 'selected' : '' }}>Sales Return</option>
                <option value="transfer" {{ request('type') === 'transfer' ? 'selected' : '' }}>Branch Transfer</option>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
            @if(request()->hasAny(['search', 'type']))
                <a href="{{ route('shop.inventory') }}" class="btn btn-secondary btn-sm" style="color: #64748b;">Clear</a>
            @endif
        </form>

        <button type="button" class="btn btn-primary" onclick="openModal('adjustmentModal')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
            Stock Adjustment (Audit / Shrinkage)
        </button>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Item Description</th>
                    <th>Identifier / IMEI</th>
                    <th>Movement Type</th>
                    <th>Quantity Change</th>
                    <th>Audit Reason / Reference</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $m)
                <tr>
                    <td style="font-size: 0.8125rem; color: #64748b; white-space: nowrap;">
                        {{ $m->created_at->format('M d, Y h:i A') }}
                    </td>
                    <td>
                        @if($m->product)
                            <strong style="color: #0f172a; font-size: 0.875rem;">{{ $m->product->name }}</strong>
                            <div style="font-size: 0.72rem; color: #64748b;">SKU: {{ $m->product->code }}</div>
                        @elseif($m->phoneDevice)
                            <strong style="color: #0f172a; font-size: 0.875rem;">{{ $m->phoneDevice->brand }} {{ $m->phoneDevice->model }}</strong>
                            <div style="font-size: 0.72rem; color: #64748b;">Serialized Device</div>
                        @else
                            <span style="color: #64748b;">Item Record</span>
                        @endif
                    </td>
                    <td>
                        @if($m->phoneDevice)
                            <span class="badge badge-info" style="font-family: monospace; font-size: 0.75rem;">
                                IMEI: {{ $m->phoneDevice->imei_1 }}
                            </span>
                        @else
                            <span style="color: #94a3b8; font-size: 0.8125rem;">Standard Item</span>
                        @endif
                    </td>
                    <td>
                        @if($m->type === 'sale')
                            <span class="badge badge-danger">Sale Out</span>
                        @elseif($m->type === 'purchase')
                            <span class="badge badge-success">Purchase In</span>
                        @elseif($m->type === 'adjustment')
                            <span class="badge badge-purple">Adjustment</span>
                        @elseif($m->type === 'return')
                            <span class="badge badge-warning">Return Restocked</span>
                        @else
                            <span class="badge badge-info">{{ ucfirst($m->type) }}</span>
                        @endif
                    </td>
                    <td>
                        @if($m->quantity > 0)
                            <strong style="color: #166534; font-size: 0.9375rem;">+{{ $m->quantity }}</strong>
                        @else
                            <strong style="color: #b91c1c; font-size: 0.9375rem;">{{ $m->quantity }}</strong>
                        @endif
                    </td>
                    <td style="font-size: 0.8125rem; color: #475569;">
                        {{ $m->notes ?? '—' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 3rem; color: #94a3b8;">
                        No stock movements logged yet. Movements are automatically captured from sales, purchases, and manual audits.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($movements->hasPages())
    <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border);">
        {{ $movements->links() }}
    </div>
    @endif
</div>

<!-- Modal: Stock Adjustment -->
<div class="modal-backdrop" id="adjustmentModal">
    <div class="modal-box" style="max-width: 520px;">
        <div class="modal-header">
            <h3>Manual Stock Count Adjustment</h3>
            <button type="button" class="modal-close" onclick="closeModal('adjustmentModal')">&times;</button>
        </div>
        <form action="{{ route('shop.inventory.adjust') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Adjustment Target Type *</label>
                    <select name="item_type" id="adjType" class="form-control" required onchange="toggleAdjustmentTarget(this.value)">
                        <option value="product">Product / Accessory</option>
                        <option value="spare_part">Repair Spare Part</option>
                    </select>
                </div>
                <div class="form-group" id="prodSelectGroup">
                    <label>Select Product *</label>
                    <select name="item_id" id="prodSelect" class="form-control" required>
                        <option value="">— Select Catalog Product —</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} (Current Stock: {{ $p->stock_quantity }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" id="partSelectGroup" style="display: none;">
                    <label>Select Spare Part *</label>
                    <select name="item_id_disabled" id="partSelect" class="form-control">
                        <option value="">— Select Spare Part —</option>
                        @foreach($spareParts as $sp)
                            <option value="{{ $sp->id }}">{{ $sp->name }} (Current Stock: {{ $sp->stock_quantity }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Quantity Delta (+ or -) *</label>
                        <input type="number" name="quantity" class="form-control" required placeholder="e.g. -2 or 5">
                        <span style="font-size: 0.72rem; color: #64748b;">Use negative for damaged/shrinkage, positive for recount found.</span>
                    </div>
                    <div class="form-group">
                        <label>Audit Reason *</label>
                        <select name="reason" class="form-control" required>
                            <option value="inventory_count">Physical Inventory Count</option>
                            <option value="damaged">Damaged in Store</option>
                            <option value="shrinkage">Shrinkage / Missing Item</option>
                            <option value="correction">Clerical Entry Correction</option>
                            <option value="other">Other Reason</option>
                        </select>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Audit Notes</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Broken screen protector dropped during shelf reorganization"></textarea>
                </div>
            </div>
            <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('adjustmentModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Apply Stock Adjustment</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function toggleAdjustmentTarget(val) {
        const prodGroup = document.getElementById('prodSelectGroup');
        const partGroup = document.getElementById('partSelectGroup');
        const prodSelect = document.getElementById('prodSelect');
        const partSelect = document.getElementById('partSelect');

        if (val === 'product') {
            prodGroup.style.display = 'block';
            partGroup.style.display = 'none';
            prodSelect.name = 'item_id';
            prodSelect.required = true;
            partSelect.name = 'item_id_disabled';
            partSelect.required = false;
        } else {
            prodGroup.style.display = 'none';
            partGroup.style.display = 'block';
            partSelect.name = 'item_id';
            partSelect.required = true;
            prodSelect.name = 'item_id_disabled';
            prodSelect.required = false;
        }
    }
</script>
@endpush
@endsection

