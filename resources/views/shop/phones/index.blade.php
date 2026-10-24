@extends('layouts.shop')

@section('title', request('status') === 'sold' ? 'Sold Phones & IMEI Sales Archive' : 'Phone Devices & Dual IMEI Inventory')
@section('page-title', request('status') === 'sold' ? 'Sold Phone Devices (IMEI Sales Archive)' : 'Serialized Phone Inventory (Dual IMEI)')

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
                    <th style="text-align: right;">Actions</th>
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
                        @if($phone->supplier)
                            <div style="font-size: 0.75rem; color: #4f46e5; margin-top: 2px;">
                                Supplier: {{ $phone->supplier->name }}
                            </div>
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
                            @if($phone->sale)
                                <div style="font-size: 0.72rem; color: #4338ca; margin-top: 2px;">
                                    <a href="{{ route('shop.sales.show', $phone->sale) }}" target="_blank" style="text-decoration: underline; color: inherit; font-family: monospace;">
                                        {{ $phone->sale->invoice_number }}
                                    </a>
                                </div>
                            @endif
                            @if($phone->sold_at)
                                <div style="font-size: 0.68rem; color: #64748b;">{{ \Carbon\Carbon::parse($phone->sold_at)->format('M d, Y') }}</div>
                            @endif
                        @else
                            <span class="badge badge-warning">{{ ucfirst($phone->status) }}</span>
                        @endif
                    </td>
                    <td style="text-align: right; white-space: nowrap;">
                        <div style="display: inline-flex; align-items: center; gap: 0.35rem;">
                            <button type="button" 
                                    class="btn btn-secondary btn-sm" 
                                    onclick='openEditPhoneModal(@json($phone))' 
                                    title="Edit phone details" 
                                    style="padding: 0.3rem 0.6rem; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                Edit
                            </button>
                            @if(! $phone->isSold() && ! $phone->sale_id)
                            <form method="POST" action="{{ route('shop.phones.destroy', $phone) }}" onsubmit="return confirm('Delete {{ addslashes($phone->brand . ' ' . $phone->model) }} (IMEI: {{ $phone->imei_1 }}) from inventory?');" style="display: inline; margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="btn btn-danger btn-sm" 
                                        title="Delete device" 
                                        style="padding: 0.3rem 0.55rem; font-size: 0.75rem; display: inline-flex; align-items: center; justify-content: center;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </button>
                            </form>
                            @else
                            <span class="badge badge-secondary" style="font-size: 0.7rem;" title="Sold devices cannot be deleted">Sold</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 3rem;">
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
                        <input type="text" name="brand" list="brandSuggestions" class="form-control" placeholder="e.g. Apple, Samsung, Xiaomi" required>
                        <datalist id="brandSuggestions">
                            @foreach($brands as $b)
                                <option value="{{ $b->name }}">
                            @endforeach
                        </datalist>
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

                <div class="form-row">
                    <div class="form-group">
                        <label>Serial Number (Optional)</label>
                        <input type="text" name="serial_number" class="form-control" placeholder="Device Serial Number">
                    </div>
                    <div class="form-group">
                        <label>Supplier (Optional)</label>
                        <select name="supplier_id" class="form-control">
                            <option value="">No Supplier / Store Direct</option>
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                            @endforeach
                        </select>
                    </div>
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

<!-- Modal: Edit Serialized Phone -->
<div class="modal-backdrop" id="editPhoneModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Edit Serialized Phone Device</h3>
            <button type="button" class="modal-close" onclick="closeModal('editPhoneModal')">&times;</button>
        </div>
        <form method="POST" id="editPhoneForm" action="">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Brand *</label>
                        <input type="text" name="brand" id="edit_phone_brand" list="brandSuggestions" class="form-control" placeholder="e.g. Apple, Samsung, Xiaomi" required>
                    </div>
                    <div class="form-group">
                        <label>Model Name *</label>
                        <input type="text" name="model" id="edit_phone_model" class="form-control" placeholder="e.g. iPhone 15 Pro, Galaxy S24" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Primary IMEI (IMEI 1) *</label>
                        <input type="text" name="imei_1" id="edit_phone_imei_1" class="form-control" placeholder="15-digit IMEI number" required maxlength="50" style="font-family: monospace;">
                    </div>
                    <div class="form-group">
                        <label>Secondary IMEI (IMEI 2)</label>
                        <input type="text" name="imei_2" id="edit_phone_imei_2" class="form-control" placeholder="Optional second SIM IMEI" maxlength="50" style="font-family: monospace;">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Status *</label>
                        <select name="status" id="edit_phone_status" class="form-control" required>
                            <option value="in_stock">In Stock</option>
                            <option value="reserved">Reserved</option>
                            <option value="sold">Sold</option>
                            <option value="defective">Defective</option>
                            <option value="returned">Returned</option>
                            <option value="in_transfer">In Transfer</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Condition *</label>
                        <select name="condition" id="edit_phone_condition" class="form-control" required>
                            <option value="new">Brand New (Sealed/Official)</option>
                            <option value="used">Used / Pre-Owned</option>
                            <option value="refurbished">Factory Refurbished</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Color</label>
                        <input type="text" name="color" id="edit_phone_color" class="form-control" placeholder="e.g. Space Black, Titanium">
                    </div>
                    <div class="form-group">
                        <label>Internal Storage</label>
                        <input type="text" name="storage" id="edit_phone_storage" class="form-control" placeholder="e.g. 128GB, 256GB">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>RAM</label>
                        <input type="text" name="ram" id="edit_phone_ram" class="form-control" placeholder="e.g. 8GB, 12GB">
                    </div>
                    <div class="form-group">
                        <label>Serial Number (Optional)</label>
                        <input type="text" name="serial_number" id="edit_phone_serial_number" class="form-control" placeholder="Device Serial Number">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Purchase Cost (LKR) *</label>
                        <input type="number" step="0.01" name="purchase_cost" id="edit_phone_purchase_cost" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="form-group">
                        <label>Selling Price (LKR) *</label>
                        <input type="number" step="0.01" name="selling_price" id="edit_phone_selling_price" class="form-control" placeholder="0.00" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Supplier (Optional)</label>
                    <select name="supplier_id" id="edit_phone_supplier_id" class="form-control">
                        <option value="">No Supplier / Store Direct</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Inspection Notes</label>
                    <textarea name="notes" id="edit_phone_notes" class="form-control" rows="2" placeholder="Battery health %, cosmetic rating, included accessories..."></textarea>
                </div>
            </div>
            <div style="padding: 1.25rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editPhoneModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Device</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openEditPhoneModal(phone) {
        const form = document.getElementById('editPhoneForm');
        form.action = `/shop/phones/${phone.id}`;

        document.getElementById('edit_phone_brand').value = phone.brand || '';
        document.getElementById('edit_phone_model').value = phone.model || '';
        document.getElementById('edit_phone_imei_1').value = phone.imei_1 || '';
        document.getElementById('edit_phone_imei_2').value = phone.imei_2 || '';
        document.getElementById('edit_phone_status').value = phone.status || 'in_stock';
        document.getElementById('edit_phone_condition').value = phone.condition || 'new';
        document.getElementById('edit_phone_color').value = phone.color || '';
        document.getElementById('edit_phone_storage').value = phone.storage || '';
        document.getElementById('edit_phone_ram').value = phone.ram || '';
        document.getElementById('edit_phone_serial_number').value = phone.serial_number || '';
        document.getElementById('edit_phone_purchase_cost').value = phone.purchase_cost || '0.00';
        document.getElementById('edit_phone_selling_price').value = phone.selling_price || '0.00';
        document.getElementById('edit_phone_supplier_id').value = phone.supplier_id || '';
        document.getElementById('edit_phone_notes').value = phone.notes || '';

        openModal('editPhoneModal');
    }
</script>
@endpush
@endsection

