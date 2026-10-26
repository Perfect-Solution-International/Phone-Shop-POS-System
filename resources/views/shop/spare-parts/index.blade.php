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
                    <th style="min-width: 250px;">Part Name & Number</th>
                    <th>Compatible Models</th>
                    <th>Quality</th>
                    <th>In Stock</th>
                    <th>Cost Price</th>
                    <th>Selling Price</th>
                    <th>Stock Alert</th>
                    <th style="text-align: right; min-width: 110px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($parts as $part)
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            @if($part->image_url)
                                <div style="position: relative; display: inline-block; flex-shrink: 0;">
                                    <img src="{{ $part->image_url }}" 
                                         alt="{{ $part->name }}" 
                                         style="width: 64px; height: 64px; min-width: 64px; object-fit: cover; border-radius: 10px; border: 1.5px solid var(--surface-border); box-shadow: 0 1px 3px rgba(0,0,0,0.08); cursor: pointer;" 
                                         onclick="openImageLightbox('{{ $part->image_url }}', '{{ addslashes($part->name) }}', '{{ route('shop.spare-parts.delete-image', $part) }}')" 
                                         title="Click to view full image">
                                    <form method="POST" action="{{ route('shop.spare-parts.delete-image', $part) }}" onsubmit="return confirm('Delete photo for \'{{ addslashes($part->name) }}\'?');" style="margin: 0; position: absolute; top: -5px; right: -5px;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Delete image" style="width: 20px; height: 20px; border-radius: 50%; background: #dc2626; color: #ffffff; border: 2px solid #ffffff; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 13px; line-height: 1; padding: 0; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">&times;</button>
                                    </form>
                                </div>
                            @else
                                <div style="width: 64px; height: 64px; min-width: 64px; border-radius: 10px; background: #f1f5f9; border: 1.5px solid var(--surface-border); display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: #94a3b8;" title="No image uploaded">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="4" width="16" height="16" rx="2"></rect><rect x="9" y="9" width="6" height="6"></rect><line x1="9" y1="1" x2="9" y2="4"></line><line x1="15" y1="1" x2="15" y2="4"></line><line x1="9" y1="20" x2="9" y2="23"></line><line x1="15" y1="20" x2="15" y2="23"></line><line x1="20" y1="9" x2="23" y2="9"></line><line x1="20" y1="14" x2="23" y2="14"></line><line x1="1" y1="9" x2="4" y2="9"></line><line x1="1" y1="14" x2="4" y2="14"></line></svg>
                                </div>
                            @endif
                            <div style="min-width: 0;">
                                <strong style="font-size: 0.9375rem; color: var(--text-heading); display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $part->name }}</strong>
                                <div style="font-family: monospace; font-size: 0.75rem; color: #0284c7; margin-top: 2px;">
                                    P/N: {{ $part->part_number }}
                                </div>
                            </div>
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
                    <td style="text-align: right; white-space: nowrap;">
                        <div style="display: inline-flex; align-items: center; gap: 0.35rem;">
                            <button type="button" 
                                    class="btn btn-secondary btn-sm" 
                                    onclick='openEditPartModal(@json($part))' 
                                    title="Edit spare part" 
                                    style="padding: 0.3rem 0.6rem; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                Edit
                            </button>
                            <form method="POST" action="{{ route('shop.spare-parts.destroy', $part) }}" onsubmit="return confirm('Delete spare part \'{{ addslashes($part->name) }}\' from inventory?');" style="display: inline; margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="btn btn-danger btn-sm" 
                                        title="Delete spare part" 
                                        style="padding: 0.3rem 0.55rem; font-size: 0.75rem; display: inline-flex; align-items: center; justify-content: center;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 3rem;">
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
        <form method="POST" action="{{ route('shop.spare-parts.store') }}" enctype="multipart/form-data">
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

                <!-- Spare Part Image Upload -->
                <div class="form-group" style="background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 8px; padding: 0.85rem; margin-top: 0.5rem;">
                    <label style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                        <span style="font-weight: 700;">Part Photo (Optional)</span>
                        <span style="font-size: 0.75rem; color: #64748b;">JPG, PNG, WEBP up to 5MB</span>
                    </label>
                    <div style="display: flex; gap: 0.85rem; align-items: center;">
                        <div id="newPartImagePreviewContainer" style="width: 60px; height: 60px; border-radius: 8px; border: 1px dashed #94a3b8; background: #ffffff; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative; flex-shrink: 0;">
                            <svg id="newPartImagePlaceholderIcon" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.8"><rect x="4" y="4" width="16" height="16" rx="2"></rect><rect x="9" y="9" width="6" height="6"></rect><line x1="9" y1="1" x2="9" y2="4"></line><line x1="15" y1="1" x2="15" y2="4"></line><line x1="9" y1="20" x2="9" y2="23"></line><line x1="15" y1="20" x2="15" y2="23"></line><line x1="20" y1="9" x2="23" y2="9"></line><line x1="20" y1="14" x2="23" y2="14"></line><line x1="1" y1="9" x2="4" y2="9"></line><line x1="1" y1="14" x2="4" y2="14"></line></svg>
                            <img id="newPartImagePreview" src="" alt="Preview" style="display: none; width: 100%; height: 100%; object-fit: cover;">
                            <button type="button" id="btnClearNewPartImage" onclick="clearNewPartImage()" style="display: none; position: absolute; top: 2px; right: 2px; background: rgba(15,23,42,0.75); color: white; border: none; border-radius: 50%; width: 16px; height: 16px; font-size: 11px; line-height: 1; cursor: pointer; align-items: center; justify-content: center;">&times;</button>
                        </div>
                        <div style="flex: 1;">
                            <input type="file" name="image" id="part_image_input" class="form-control" accept="image/png,image/jpeg,image/webp,image/gif" onchange="previewNewPartImage(this)" style="font-size: 0.8125rem;">
                            <span style="font-size: 0.72rem; color: #64748b; display: block; margin-top: 0.2rem;">Photo will help technicians identify the exact component or connector.</span>
                        </div>
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

<!-- Modal: Edit Spare Part -->
<div class="modal-backdrop" id="editPartModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Edit Spare Part</h3>
            <button type="button" class="modal-close" onclick="closeModal('editPartModal')">&times;</button>
        </div>
        <form id="editPartForm" method="POST" action="" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="remove_image" id="editPartRemoveImage" value="0">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Part Name *</label>
                        <input type="text" name="name" id="edit_part_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Part Number / Code *</label>
                        <input type="text" name="part_number" id="edit_part_number" class="form-control" required style="font-family: monospace;">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Quality Grade *</label>
                        <select name="quality_type" id="edit_part_quality_type" class="form-control" required>
                            <option value="original">Original Service Pack</option>
                            <option value="oem">OEM High Copy (Grade A)</option>
                            <option value="compatible">Compatible / Aftermarket</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Compatible Models</label>
                        <input type="text" name="compatible_models" id="edit_part_compatible_models" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Purchase Cost (LKR) *</label>
                        <input type="number" step="0.01" name="cost_price" id="edit_part_cost_price" class="form-control" required min="0">
                    </div>
                    <div class="form-group">
                        <label>Selling Price (LKR) *</label>
                        <input type="number" step="0.01" name="selling_price" id="edit_part_selling_price" class="form-control" required min="0">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Stock Quantity *</label>
                        <input type="number" name="stock_quantity" id="edit_part_stock_quantity" class="form-control" required min="0">
                    </div>
                    <div class="form-group">
                        <label>Low Stock Alert Level</label>
                        <input type="number" name="alert_quantity" id="edit_part_alert_quantity" class="form-control" min="0">
                    </div>
                </div>

                <!-- Image Management (Add / Replace / Delete) -->
                <div class="form-group" style="background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 8px; padding: 0.85rem; margin-top: 0.5rem;">
                    <label style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <span style="font-weight: 700;">Component Photo</span>
                        <span style="font-size: 0.75rem; color: #64748b;">JPG, PNG, WEBP up to 5MB</span>
                    </label>

                    <!-- Existing Image Section -->
                    <div id="editPartExistingImageWrap" style="display: none; align-items: center; justify-content: space-between; background: #ffffff; border: 1px solid var(--surface-border); border-radius: 6px; padding: 0.5rem 0.75rem; margin-bottom: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <img id="editPartExistingImg" src="" alt="Current image" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0; cursor: pointer;" onclick="openImageLightbox(this.src, document.getElementById('edit_part_name').value)">
                            <div>
                                <span style="font-size: 0.8125rem; font-weight: 600; color: #1e293b; display: block;">Current Component Photo</span>
                                <span style="font-size: 0.72rem; color: #10b981; font-weight: 600;">Saved on Cloud/Storage</span>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm" onclick="markDeletePartImage()" style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; font-size: 0.75rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.3rem 0.65rem; border-radius: 6px;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            Delete Image
                        </button>
                    </div>

                    <div id="editPartImageRemovedNotice" style="display: none; background: #fee2e2; border: 1px solid #fca5a5; border-radius: 6px; padding: 0.45rem 0.75rem; margin-bottom: 0.75rem; font-size: 0.78rem; color: #991b1b; align-items: center; justify-content: space-between;">
                        <span>⚠️ Image will be removed upon saving.</span>
                        <button type="button" onclick="undoDeletePartImage()" style="background: none; border: none; color: #4338ca; text-decoration: underline; font-size: 0.75rem; font-weight: 700; cursor: pointer;">Undo</button>
                    </div>

                    <!-- Replace / Upload New Image -->
                    <div style="display: flex; gap: 0.75rem; align-items: center;">
                        <div id="editPartNewImagePreviewWrap" style="width: 60px; height: 60px; border-radius: 8px; border: 1px dashed #94a3b8; background: #ffffff; display: none; align-items: center; justify-content: center; overflow: hidden; position: relative; flex-shrink: 0;">
                            <img id="editPartNewImagePreview" src="" alt="New Preview" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <div style="flex: 1;">
                            <input type="file" name="image" id="edit_part_image_input" class="form-control" accept="image/png,image/jpeg,image/webp,image/gif" onchange="previewEditPartImage(this)" style="font-size: 0.8125rem;">
                            <span style="font-size: 0.72rem; color: #64748b; display: block; margin-top: 0.2rem;">Select a photo to upload or replace the current image.</span>
                        </div>
                    </div>
                </div>
            </div>
            <div style="padding: 1.25rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editPartModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Image Lightbox Preview -->
<div class="modal-backdrop" id="imageLightboxModal" style="z-index: 1070;" onclick="closeModal('imageLightboxModal')">
    <div class="modal-box" style="max-width: 500px; padding: 0.5rem; background: #0f172a; border: 1px solid #334155;" onclick="event.stopPropagation()">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0.75rem; color: #ffffff;">
            <strong id="lightboxImageTitle" style="font-size: 0.9375rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Spare Part Photo</strong>
            <button type="button" class="modal-close" style="color: #ffffff; font-size: 1.5rem;" onclick="closeModal('imageLightboxModal')">&times;</button>
        </div>
        <div style="text-align: center; padding: 0.5rem;">
            <img id="lightboxImageEl" src="" alt="Full preview" style="max-width: 100%; max-height: 65vh; object-fit: contain; border-radius: 8px;">
        </div>
        <div style="padding: 0.75rem 0.75rem 0.5rem; border-top: 1px solid #334155; display: flex; justify-content: space-between; align-items: center;">
            <button type="button" class="btn btn-sm btn-secondary" onclick="closeModal('imageLightboxModal')" style="font-size: 0.78rem; background: #334155; border-color: #475569; color: #f8fafc;">Close</button>
            <form id="lightboxDeleteForm" method="POST" action="" onsubmit="return confirm('Are you sure you want to permanently delete this photo?');" style="margin: 0; display: none;">
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
    // Preview image on Add Modal
    function previewNewPartImage(input) {
        const preview = document.getElementById('newPartImagePreview');
        const placeholder = document.getElementById('newPartImagePlaceholderIcon');
        const clearBtn = document.getElementById('btnClearNewPartImage');

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

    function clearNewPartImage() {
        const input = document.getElementById('part_image_input');
        const preview = document.getElementById('newPartImagePreview');
        const placeholder = document.getElementById('newPartImagePlaceholderIcon');
        const clearBtn = document.getElementById('btnClearNewPartImage');

        input.value = '';
        preview.src = '';
        preview.style.display = 'none';
        placeholder.style.display = 'block';
        clearBtn.style.display = 'none';
    }

    // Open Edit Modal
    function openEditPartModal(part) {
        const form = document.getElementById('editPartForm');
        form.action = `/shop/spare-parts/${part.id}`;

        document.getElementById('edit_part_name').value = part.name || '';
        document.getElementById('edit_part_number').value = part.part_number || '';
        document.getElementById('edit_part_quality_type').value = part.quality_type || 'original';
        document.getElementById('edit_part_compatible_models').value = part.compatible_models || '';
        document.getElementById('edit_part_cost_price').value = part.cost_price || '0.00';
        document.getElementById('edit_part_selling_price').value = part.selling_price || '0.00';
        document.getElementById('edit_part_stock_quantity').value = part.stock_quantity ?? '0';
        document.getElementById('edit_part_alert_quantity').value = part.alert_quantity ?? '2';

        // Reset image states
        document.getElementById('editPartRemoveImage').value = '0';
        document.getElementById('edit_part_image_input').value = '';
        document.getElementById('editPartNewImagePreviewWrap').style.display = 'none';
        document.getElementById('editPartNewImagePreview').src = '';
        document.getElementById('editPartImageRemovedNotice').style.display = 'none';

        const existingWrap = document.getElementById('editPartExistingImageWrap');
        const existingImg = document.getElementById('editPartExistingImg');

        if (part.image_url) {
            existingImg.src = part.image_url;
            existingWrap.style.display = 'flex';
        } else {
            existingImg.src = '';
            existingWrap.style.display = 'none';
        }

        openModal('editPartModal');
    }

    // Preview replacement image on Edit Modal
    function previewEditPartImage(input) {
        const previewWrap = document.getElementById('editPartNewImagePreviewWrap');
        const preview = document.getElementById('editPartNewImagePreview');

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                previewWrap.style.display = 'flex';
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            previewWrap.style.display = 'none';
            preview.src = '';
        }
    }

    function markDeletePartImage() {
        document.getElementById('editPartRemoveImage').value = '1';
        document.getElementById('editPartExistingImageWrap').style.display = 'none';
        document.getElementById('editPartImageRemovedNotice').style.display = 'flex';
    }

    function undoDeletePartImage() {
        document.getElementById('editPartRemoveImage').value = '0';
        document.getElementById('editPartExistingImageWrap').style.display = 'flex';
        document.getElementById('editPartImageRemovedNotice').style.display = 'none';
    }

    function openImageLightbox(imageUrl, title, deleteUrl = '') {
        document.getElementById('lightboxImageEl').src = imageUrl;
        document.getElementById('lightboxImageTitle').textContent = title || 'Spare Part Photo';

        const deleteForm = document.getElementById('lightboxDeleteForm');
        if (deleteForm) {
            if (deleteUrl) {
                deleteForm.action = deleteUrl;
                deleteForm.style.display = 'inline';
            } else {
                deleteForm.action = '';
                deleteForm.style.display = 'none';
            }
        }

        openModal('imageLightboxModal');
    }
</script>
@endpush
@endsection

