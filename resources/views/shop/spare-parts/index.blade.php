@extends('layouts.shop')

@section('title', 'Spare Parts Stock')
@section('page-title', 'Repair Spare Parts & Component Inventory')

@section('content')
<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <form method="GET" action="{{ route('shop.spare-parts') }}" style="display: flex; gap: 0.75rem; flex: 1; flex-wrap: wrap;">
            <input type="text" name="search" class="form-control" style="max-width: 320px;" placeholder="Search part name, part #, models..." value="{{ request('search') }}">
            <select name="quality_type" class="form-control" style="max-width: 170px;" onchange="this.form.submit()">
                <option value="">All Qualities</option>
                <option value="original" {{ request('quality_type') === 'original' ? 'selected' : '' }}>Original (Service Pack)</option>
                <option value="oem" {{ request('quality_type') === 'oem' ? 'selected' : '' }}>OEM Grade A</option>
                <option value="compatible" {{ request('quality_type') === 'compatible' ? 'selected' : '' }}>Compatible / Aftermarket</option>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
            @if(request()->hasAny(['search', 'quality_type']))
                <a href="{{ route('shop.spare-parts') }}" class="btn btn-sm btn-secondary" style="color: #64748b;">Clear</a>
            @endif
        </form>

        <button type="button" class="btn btn-primary" onclick="openModal('addPartModal')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Add Spare Part
        </button>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Part Name & Number</th>
                    <th>Compatible Models</th>
                    <th>Quality</th>
                    <th>In Stock</th>
                    <th>Cost Price</th>
                    <th>Selling Price</th>
                    <th>Stock Alert</th>
                </tr>
            </thead>
            <tbody>
                @forelse($parts as $part)
                <tr>
                    <td>
                        <strong style="font-size: 0.9375rem; color: var(--text-heading);">{{ $part->name }}</strong>
                        <div style="font-family: monospace; font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                            P/N: {{ $part->part_number }}
                        </div>
                    </td>
                    <td>
                        <span style="font-size: 0.8125rem; color: #475569;">{{ $part->compatible_models ?? 'Universal' }}</span>
                    </td>
                    <td>
                        @if($part->quality_type === 'original')
                            <span class="badge badge-success">Original</span>
                        @elseif($part->quality_type === 'oem')
                            <span class="badge badge-info">OEM</span>
                        @else
                            <span class="badge badge-secondary">Compatible</span>
                        @endif
                    </td>
                    <td>
                        <strong style="font-size: 1rem; color: {{ $part->stock_quantity <= ($part->alert_quantity ?? 2) ? '#b91c1c' : '#15803d' }};">
                            {{ $part->stock_quantity }}
                        </strong>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">units</span>
                    </td>
                    <td style="color: var(--text-muted); font-size: 0.8125rem;">LKR {{ number_format($part->cost_price, 2) }}</td>
                    <td style="font-weight: 700; color: #166534;">LKR {{ number_format($part->selling_price, 2) }}</td>
                    <td>
                        @if($part->stock_quantity <= ($part->alert_quantity ?? 2))
                            <span class="badge badge-danger">Low Stock Alert</span>
                        @else
                            <span class="badge badge-success">Normal</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 3rem;">
                        No spare parts found in inventory.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($parts->hasPages())
    <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border);">
        {{ $parts->links() }}
    </div>
    @endif
</div>

<!-- Modal: Add Spare Part -->
<div class="modal-backdrop" id="addPartModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Add Spare Part to Stock</h3>
            <button type="button" class="modal-close" onclick="closeModal('addPartModal')">&times;</button>
        </div>
        <form method="POST" action="{{ route('shop.spare-parts.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Part Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. iPhone 13 OLED Display Assembly" required>
                    </div>
                    <div class="form-group">
                        <label>Part Number / Code *</label>
                        <input type="text" name="part_number" class="form-control" placeholder="e.g. DISP-IP13-OLED" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Quality Grade *</label>
                        <select name="quality_type" class="form-control" required>
                            <option value="original">Original Service Pack</option>
                            <option value="oem">OEM High Copy (Grade A)</option>
                            <option value="compatible">Compatible / Aftermarket</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Compatible Models</label>
                        <input type="text" name="compatible_models" class="form-control" placeholder="e.g. iPhone 13, iPhone 13 Pro">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Purchase Cost (LKR) *</label>
                        <input type="number" step="0.01" name="cost_price" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="form-group">
                        <label>Selling Price (LKR) *</label>
                        <input type="number" step="0.01" name="selling_price" class="form-control" placeholder="0.00" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Initial Stock Quantity *</label>
                        <input type="number" name="stock_quantity" class="form-control" value="5" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>Low Stock Alert Level</label>
                        <input type="number" name="alert_quantity" class="form-control" value="2" min="0">
                    </div>
                </div>
            </div>
            <div style="padding: 1.25rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addPartModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Spare Part</button>
            </div>
        </form>
    </div>
</div>
@endsection

