@extends('layouts.shop')

@section('title', 'Phone Devices & Dual IMEI Inventory')
@section('page-title', 'Serialized Phone Inventory (Dual IMEI)')

@section('content')
<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <form method="GET" action="{{ route('shop.phones') }}" style="display: flex; gap: 0.75rem; flex: 1; flex-wrap: wrap;">
            <input type="text" name="search" class="form-control" style="max-width: 280px;" placeholder="Search IMEI 1, IMEI 2, Model..." value="{{ request('search') }}">
            <select name="status" class="form-control" style="max-width: 150px;" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="in_stock" {{ request('status') === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                <option value="sold" {{ request('status') === 'sold' ? 'selected' : '' }}>Sold</option>
                <option value="reserved" {{ request('status') === 'reserved' ? 'selected' : '' }}>Reserved</option>
                <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Returned</option>
            </select>
            <select name="condition" class="form-control" style="max-width: 150px;" onchange="this.form.submit()">
                <option value="">All Conditions</option>
                <option value="new" {{ request('condition') === 'new' ? 'selected' : '' }}>Brand New</option>
                <option value="used" {{ request('condition') === 'used' ? 'selected' : '' }}>Used / Pre-owned</option>
                <option value="refurbished" {{ request('condition') === 'refurbished' ? 'selected' : '' }}>Refurbished</option>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
            @if(request()->hasAny(['search', 'status', 'condition']))
                <a href="{{ route('shop.phones') }}" class="btn btn-sm btn-secondary" style="color: #64748b;">Clear</a>
            @endif
        </form>

        <button type="button" class="btn btn-primary" onclick="openModal('addPhoneModal')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Register Phone
        </button>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Device & Model</th>
                    <th>IMEI 1 & IMEI 2</th>
                    <th>Specification</th>
                    <th>Condition</th>
                    <th>Purchase Cost</th>
                    <th>Selling Price</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($phones as $phone)
                <tr>
                    <td>
                        <strong style="color: var(--text-heading); font-size: 0.9375rem;">{{ $phone->brand }} {{ $phone->model }}</strong>
                        @if($phone->serial_number)
                            <div style="font-size: 0.75rem; color: var(--text-muted);">S/N: {{ $phone->serial_number }}</div>
                        @endif
                    </td>
                    <td>
                        <div style="font-family: monospace; font-size: 0.8125rem; font-weight: 600; color: #0284c7;">
                            IMEI 1: {{ $phone->imei_1 }}
                        </div>
                        @if($phone->imei_2)
                            <div style="font-family: monospace; font-size: 0.75rem; color: #64748b; margin-top: 2px;">
                                IMEI 2: {{ $phone->imei_2 }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <span style="font-size: 0.8125rem;">
                            {{ $phone->color ?? 'Standard' }}
                            @if($phone->storage || $phone->ram)
                                • {{ $phone->ram ? $phone->ram.'/' : '' }}{{ $phone->storage }}
                            @endif
                        </span>
                    </td>
                    <td>
                        @if($phone->condition === 'new')
                            <span class="badge badge-success">New</span>
                        @elseif($phone->condition === 'used')
                            <span class="badge badge-warning">Used</span>
                        @else
                            <span class="badge badge-purple">Refurbished</span>
                        @endif
                    </td>
                    <td style="color: var(--text-muted); font-size: 0.8125rem;">LKR {{ number_format($phone->purchase_cost, 2) }}</td>
                    <td style="font-weight: 700; color: #166534;">LKR {{ number_format($phone->selling_price, 2) }}</td>
                    <td>
                        @if($phone->status === 'in_stock')
                            <span class="badge badge-success">In Stock</span>
                        @elseif($phone->status === 'sold')
                            <span class="badge badge-info">Sold</span>
                        @else
                            <span class="badge badge-warning">{{ ucfirst($phone->status) }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 3rem;">
                        No phone devices match your search criteria.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($phones->hasPages())
    <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border);">
        {{ $phones->links() }}
    </div>
    @endif
</div>

<!-- Modal: Register Serialized Phone -->
<div class="modal-backdrop" id="addPhoneModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Register Serialized Phone (Dual IMEI)</h3>
            <button type="button" class="modal-close" onclick="closeModal('addPhoneModal')">&times;</button>
        </div>
        <form method="POST" action="{{ route('shop.phones.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Brand *</label>
                        <input type="text" name="brand" class="form-control" placeholder="e.g. Apple, Samsung, Xiaomi" required>
                    </div>
                    <div class="form-group">
                        <label>Model Name *</label>
                        <input type="text" name="model" class="form-control" placeholder="e.g. iPhone 15 Pro, Galaxy S24" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Primary IMEI (IMEI 1) *</label>
                        <input type="text" name="imei_1" class="form-control" placeholder="15-digit IMEI number" required maxlength="50" style="font-family: monospace;">
                    </div>
                    <div class="form-group">
                        <label>Secondary IMEI (IMEI 2)</label>
                        <input type="text" name="imei_2" class="form-control" placeholder="Optional second SIM IMEI" maxlength="50" style="font-family: monospace;">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Condition *</label>
                        <select name="condition" class="form-control" required>
                            <option value="new">Brand New (Sealed/Official)</option>
                            <option value="used">Used / Pre-Owned</option>
                            <option value="refurbished">Factory Refurbished</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Color</label>
                        <input type="text" name="color" class="form-control" placeholder="e.g. Space Black, Titanium">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Internal Storage</label>
                        <input type="text" name="storage" class="form-control" placeholder="e.g. 128GB, 256GB">
                    </div>
                    <div class="form-group">
                        <label>RAM</label>
                        <input type="text" name="ram" class="form-control" placeholder="e.g. 8GB, 12GB">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Purchase Cost (LKR) *</label>
                        <input type="number" step="0.01" name="purchase_cost" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="form-group">
                        <label>Selling Price (LKR) *</label>
                        <input type="number" step="0.01" name="selling_price" class="form-control" placeholder="0.00" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Serial Number (Optional)</label>
                    <input type="text" name="serial_number" class="form-control" placeholder="Device Serial Number">
                </div>

                <div class="form-group">
                    <label>Inspection Notes</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Battery health %, cosmetic rating, included accessories..."></textarea>
                </div>
            </div>
            <div style="padding: 1.25rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addPhoneModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save to Stock</button>
            </div>
        </form>
    </div>
</div>
@endsection

