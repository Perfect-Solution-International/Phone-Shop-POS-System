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

        <button type="button" class="btn btn-primary" onclick="openModal('addPhoneModal')" style="display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 700;">
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
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($phones as $phone)
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            @if($phone->image_url)
                                <div style="position: relative; display: inline-block; flex-shrink: 0;">
                                    <img src="{{ $phone->image_url }}" 
                                         alt="{{ $phone->brand }} {{ $phone->model }}"
                                         data-title="{{ $phone->brand }} {{ $phone->model }} (IMEI: {{ $phone->imei_1 }})"
                                         data-delete-url="{{ route('shop.phones.delete-image', $phone) }}"
                                         style="width: 50px; height: 50px; min-width: 50px; object-fit: cover; border-radius: 8px; border: 1.5px solid var(--surface-border); box-shadow: 0 1px 3px rgba(0,0,0,0.06); cursor: pointer;"
                                         onclick="handlePhoneLightboxClick(this)"
                                         title="Click to view full photo">
                                    <form method="POST" action="{{ route('shop.phones.delete-image', $phone) }}" data-confirm="Delete photo for {{ $phone->brand }} {{ $phone->model }}?" onsubmit="return confirm(this.getAttribute('data-confirm'));" style="margin: 0; position: absolute; top: -5px; right: -5px;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Delete image" style="width: 18px; height: 18px; border-radius: 50%; background: #dc2626; color: #ffffff; border: 2px solid #ffffff; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 11px; line-height: 1; padding: 0; box-shadow: 0 1px 3px rgba(0,0,0,0.2);">&times;</button>
                                    </form>
                                </div>
                            @else
                                <div style="width: 50px; height: 50px; min-width: 50px; border-radius: 8px; background: #f8fafc; border: 1.5px solid var(--surface-border); display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: #94a3b8;" title="No image uploaded">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="2" width="14" height="20" rx="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                                </div>
                            @endif
                            <div style="min-width: 0;">
                                <strong style="color: var(--text-heading); font-size: 0.9375rem; display: block;">{{ $phone->brand }} {{ $phone->model }}</strong>
                                @if($phone->serial_number)
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">S/N: {{ $phone->serial_number }}</div>
                                @endif
                                @if($phone->supplier)
                                    <div style="font-size: 0.75rem; color: #4f46e5; margin-top: 2px;">
                                        Supplier: {{ $phone->supplier->name }}
                                    </div>
                                @endif
                            </div>
                        </div>
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
                            <a href="{{ route('shop.labels.index', ['type' => 'phone', 'id' => $phone->id]) }}" 
                               class="btn btn-secondary btn-sm" 
                               title="Print IMEI Barcode Label" 
                               style="padding: 0.3rem 0.55rem; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.2rem; color: #4338ca; background: #eef2ff; border-color: #c7d2fe;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
                                Label
                            </a>
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
                    <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 3.5rem 1rem;">
                        <div style="width: 48px; height: 48px; border-radius: 50%; background: #f1f5f9; color: #94a3b8; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.75rem;">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                        </div>
                        <div style="font-weight: 700; color: #334155; font-size: 0.95rem;">No serialized phone devices found</div>
                        <div style="font-size: 0.8125rem; color: #94a3b8; margin: 0.25rem 0 1rem;">Register new or used phones with IMEI 1 &amp; IMEI 2 to start selling in POS.</div>
                        <button type="button" class="btn btn-primary btn-sm" onclick="openModal('addPhoneModal')" style="display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 600;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            Register First Phone
                        </button>
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
        <form method="POST" action="{{ route('shop.phones.store') }}" enctype="multipart/form-data">
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

                <!-- Device Image Upload -->
                <div class="form-group" style="background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 8px; padding: 0.85rem; margin-top: 0.5rem;">
                    <label style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                        <span style="font-weight: 700; color: #1e293b;">Device Photo / Image (Optional)</span>
                        <span style="font-size: 0.75rem; color: #64748b;">JPG, PNG, WEBP up to 5MB</span>
                    </label>
                    <div style="display: flex; gap: 0.85rem; align-items: center;">
                        <div id="newPhoneImagePreviewContainer" style="width: 60px; height: 60px; border-radius: 8px; border: 1px dashed #94a3b8; background: #ffffff; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative; flex-shrink: 0;">
                            <svg id="newPhoneImagePlaceholderIcon" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.8"><rect x="5" y="2" width="14" height="20" rx="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                            <img id="newPhoneImagePreview" src="" alt="Preview" style="display: none; width: 100%; height: 100%; object-fit: cover;">
                            <button type="button" id="btnClearNewPhoneImage" onclick="clearNewPhoneImage()" style="display: none; position: absolute; top: 2px; right: 2px; background: rgba(15,23,42,0.75); color: white; border: none; border-radius: 50%; width: 16px; height: 16px; font-size: 11px; line-height: 1; cursor: pointer; align-items: center; justify-content: center;">&times;</button>
                        </div>
                        <div style="flex: 1;">
                            <input type="file" name="image" id="phone_image_input" class="form-control" accept="image/png,image/jpeg,image/webp,image/gif" onchange="previewNewPhoneImage(this)" style="font-size: 0.8125rem;">
                            <span style="font-size: 0.72rem; color: #64748b; display: block; margin-top: 0.2rem;">Photo will appear in Inventory lists and on POS Terminal.</span>
                        </div>
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
        <form method="POST" id="editPhoneForm" action="" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="remove_image" id="editPhoneRemoveImage" value="0">
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

                <!-- Device Image Upload (Edit) -->
                <div class="form-group" style="background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 8px; padding: 0.85rem; margin-top: 0.5rem;">
                    <label style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                        <span style="font-weight: 700; color: #1e293b;">Device Photo / Image</span>
                        <span style="font-size: 0.75rem; color: #64748b;">JPG, PNG, WEBP up to 5MB</span>
                    </label>

                    <!-- Existing Image Section -->
                    <div id="editPhoneExistingImageWrap" style="display: none; align-items: center; justify-content: space-between; background: #ffffff; border: 1px solid var(--surface-border); border-radius: 6px; padding: 0.5rem 0.75rem; margin-bottom: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <img id="editPhoneExistingImg" src="" alt="Current image" style="width: 55px; height: 55px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0; cursor: pointer;" onclick="openPhoneImageLightbox(this.src, document.getElementById('edit_phone_brand').value + ' ' + document.getElementById('edit_phone_model').value)">
                            <div>
                                <span style="font-size: 0.8125rem; font-weight: 600; color: #1e293b; display: block;">Current Active Photo</span>
                                <span style="font-size: 0.72rem; color: #10b981; font-weight: 600;">Saved on storage</span>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm" onclick="markDeletePhoneImage()" style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; font-size: 0.75rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.3rem 0.65rem; border-radius: 6px;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            Delete Photo
                        </button>
                    </div>

                    <div id="editPhoneImageRemovedNotice" style="display: none; background: #fee2e2; border: 1px solid #fca5a5; border-radius: 6px; padding: 0.45rem 0.75rem; margin-bottom: 0.75rem; font-size: 0.78rem; color: #991b1b; align-items: center; justify-content: space-between;">
                        <span style="display: inline-flex; align-items: center; gap: 0.35rem;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                            Photo will be removed upon saving.
                        </span>
                        <button type="button" onclick="undoDeletePhoneImage()" style="background: none; border: none; color: #4338ca; text-decoration: underline; font-size: 0.75rem; font-weight: 700; cursor: pointer;">Undo</button>
                    </div>

                    <!-- Replace / Upload New Image -->
                    <div style="display: flex; gap: 0.75rem; align-items: center;">
                        <div id="editPhoneNewImagePreviewWrap" style="width: 55px; height: 55px; border-radius: 8px; border: 1px dashed #94a3b8; background: #ffffff; display: none; align-items: center; justify-content: center; overflow: hidden; position: relative; flex-shrink: 0;">
                            <img id="editPhoneNewImagePreview" src="" alt="New Preview" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <div style="flex: 1;">
                            <input type="file" name="image" id="edit_phone_image_input" class="form-control" accept="image/png,image/jpeg,image/webp,image/gif" onchange="previewEditPhoneImage(this)" style="font-size: 0.8125rem;">
                            <span style="font-size: 0.72rem; color: #64748b; display: block; margin-top: 0.2rem;">Select a file to upload or replace the current photo.</span>
                        </div>
                    </div>
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

<!-- Modal: Image Lightbox Preview -->
<div class="modal-backdrop" id="phoneImageLightboxModal" style="z-index: 1070;" onclick="closeModal('phoneImageLightboxModal')">
    <div class="modal-box" style="max-width: 500px; padding: 0.5rem; background: #0f172a; border: 1px solid #334155;" onclick="event.stopPropagation()">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0.75rem; color: #ffffff;">
            <strong id="phoneLightboxTitle" style="font-size: 0.9375rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Device Photo</strong>
            <button type="button" class="modal-close" style="color: #ffffff; font-size: 1.5rem;" onclick="closeModal('phoneImageLightboxModal')">&times;</button>
        </div>
        <div style="text-align: center; padding: 0.5rem;">
            <img id="phoneLightboxImg" src="" alt="Full preview" style="max-width: 100%; max-height: 65vh; object-fit: contain; border-radius: 8px;">
        </div>
        <div style="padding: 0.75rem 0.75rem 0.5rem; border-top: 1px solid #334155; display: flex; justify-content: space-between; align-items: center;">
            <button type="button" class="btn btn-sm btn-secondary" onclick="closeModal('phoneImageLightboxModal')" style="font-size: 0.78rem; background: #334155; border-color: #475569; color: #f8fafc;">Close</button>
            <form id="phoneLightboxDeleteForm" method="POST" action="" onsubmit="return confirm('Are you sure you want to permanently delete this device photo?');" style="margin: 0; display: none;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger" style="font-size: 0.78rem; display: inline-flex; align-items: center; gap: 0.35rem; background: #dc2626; border-color: #dc2626;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    Delete Photo
                </button>
            </form>
        </div>
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

        // Reset and populate image handling
        document.getElementById('editPhoneRemoveImage').value = '0';
        document.getElementById('editPhoneImageRemovedNotice').style.display = 'none';
        document.getElementById('edit_phone_image_input').value = '';
        document.getElementById('editPhoneNewImagePreviewWrap').style.display = 'none';

        const existingImg = document.getElementById('editPhoneExistingImg');
        const existingWrap = document.getElementById('editPhoneExistingImageWrap');
        if (phone.image_url) {
            existingImg.src = phone.image_url;
            existingWrap.style.display = 'flex';
        } else {
            existingImg.src = '';
            existingWrap.style.display = 'none';
        }

        openModal('editPhoneModal');
    }

    // Preview new image in Create Modal
    function previewNewPhoneImage(input) {
        const preview = document.getElementById('newPhoneImagePreview');
        const placeholder = document.getElementById('newPhoneImagePlaceholderIcon');
        const clearBtn = document.getElementById('btnClearNewPhoneImage');

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                placeholder.style.display = 'none';
                clearBtn.style.display = 'flex';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function clearNewPhoneImage() {
        const input = document.getElementById('phone_image_input');
        input.value = '';
        document.getElementById('newPhoneImagePreview').src = '';
        document.getElementById('newPhoneImagePreview').style.display = 'none';
        document.getElementById('newPhoneImagePlaceholderIcon').style.display = 'block';
        document.getElementById('btnClearNewPhoneImage').style.display = 'none';
    }

    // Preview replacement image in Edit Modal
    function previewEditPhoneImage(input) {
        const previewWrap = document.getElementById('editPhoneNewImagePreviewWrap');
        const preview = document.getElementById('editPhoneNewImagePreview');

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                previewWrap.style.display = 'flex';
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            previewWrap.style.display = 'none';
        }
    }

    function markDeletePhoneImage() {
        document.getElementById('editPhoneRemoveImage').value = '1';
        document.getElementById('editPhoneExistingImageWrap').style.display = 'none';
        document.getElementById('editPhoneImageRemovedNotice').style.display = 'flex';
    }

    function undoDeletePhoneImage() {
        document.getElementById('editPhoneRemoveImage').value = '0';
        document.getElementById('editPhoneExistingImageWrap').style.display = 'flex';
        document.getElementById('editPhoneImageRemovedNotice').style.display = 'none';
    }

    function handlePhoneLightboxClick(el) {
        openPhoneImageLightbox(el.src, el.getAttribute('data-title'), el.getAttribute('data-delete-url'));
    }

    function openPhoneImageLightbox(imageUrl, title, deleteUrl = '') {
        document.getElementById('phoneLightboxImg').src = imageUrl;
        document.getElementById('phoneLightboxTitle').textContent = title || 'Device Photo';

        const deleteForm = document.getElementById('phoneLightboxDeleteForm');
        if (deleteForm) {
            if (deleteUrl) {
                deleteForm.action = deleteUrl;
                deleteForm.style.display = 'inline-block';
            } else {
                deleteForm.style.display = 'none';
            }
        }

        openModal('phoneImageLightboxModal');
    }
</script>
@endpush
@endsection

