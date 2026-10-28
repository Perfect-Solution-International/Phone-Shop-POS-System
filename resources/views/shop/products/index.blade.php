@extends('layouts.shop')

@section('title', 'Products & Accessories')
@section('page-title', 'Products, Accessories & Inventory Stock')

@section('content')
<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <form method="GET" action="{{ route('shop.products') }}" style="display: flex; gap: 0.75rem; flex: 1; flex-wrap: wrap;">
            <input type="text" name="search" class="form-control" style="max-width: 300px;" placeholder="Search name, barcode, SKU..." value="{{ request('search') }}">
            <select name="category_id" class="form-control" style="max-width: 180px;" onchange="this.form.submit()">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
            @if(request()->hasAny(['search', 'category_id']))
                <a href="{{ route('shop.products') }}" class="btn btn-sm btn-secondary" style="color: #64748b;">Clear</a>
            @endif
        </form>

        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <button type="button" class="btn btn-secondary" onclick="openModal('manageCatalogModal')">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h7"></path></svg>
                Categories &amp; Brands
            </button>
            <button type="button" class="btn btn-primary" onclick="openModal('addProductModal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Add New Item
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="min-width: 240px;">Product Name & Category</th>
                    <th>Barcode / SKU</th>
                    <th>In Stock</th>
                    <th>Cost Price</th>
                    <th>Selling Price</th>
                    <th>Alert Level</th>
                    <th>Status</th>
                    <th style="text-align: right; min-width: 110px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            @if($product->image_url)
                                <div style="position: relative; display: inline-block; flex-shrink: 0;">
                                    <img src="{{ $product->image_url }}" 
                                         alt="{{ $product->name }}" 
                                         data-title="{{ $product->name }}"
                                         data-delete-url="{{ route('shop.products.delete-image', $product) }}"
                                         style="width: 64px; height: 64px; min-width: 64px; object-fit: cover; border-radius: 10px; border: 1.5px solid var(--surface-border); box-shadow: 0 1px 3px rgba(0,0,0,0.08); cursor: pointer;" 
                                         onclick="handleProductLightboxClick(this)" 
                                         title="Click to view full image">
                                    <form method="POST" action="{{ route('shop.products.delete-image', $product) }}" data-confirm="Delete photo for {{ $product->name }}?" onsubmit="return confirm(this.getAttribute('data-confirm'));" style="margin: 0; position: absolute; top: -5px; right: -5px;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Delete image" style="width: 20px; height: 20px; border-radius: 50%; background: #dc2626; color: #ffffff; border: 2px solid #ffffff; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 13px; line-height: 1; padding: 0; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">&times;</button>
                                    </form>
                                </div>
                            @else
                                <div style="width: 64px; height: 64px; min-width: 64px; border-radius: 10px; background: #f1f5f9; border: 1.5px solid var(--surface-border); display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: #94a3b8;" title="No image uploaded">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                                </div>
                            @endif
                            <div style="min-width: 0;">
                                <strong style="color: var(--text-heading); font-size: 0.9375rem; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $product->name }}</strong>
                                <div style="font-size: 0.75rem; color: #4f46e5; font-weight: 600;">
                                    {{ $product->category->name ?? 'Accessories' }}
                                    @if($product->brand)
                                        <span style="color: #64748b; font-weight: normal;">• {{ $product->brand->name }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="font-family: monospace; font-size: 0.8125rem; font-weight: 600; color: #0284c7;">
                            {{ $product->barcode }}
                        </div>
                        <div style="font-family: monospace; font-size: 0.75rem; color: var(--text-muted);">
                            {{ $product->sku }}
                        </div>
                    </td>
                    <td>
                        <strong style="font-size: 1rem; color: {{ $product->isLowStock() ? '#b91c1c' : '#15803d' }};">
                            {{ $product->stock_quantity }}
                        </strong>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">units</span>
                    </td>
                    <td style="color: var(--text-muted); font-size: 0.8125rem;">LKR {{ number_format($product->cost_price, 2) }}</td>
                    <td style="font-weight: 700; color: #166534;">LKR {{ number_format($product->selling_price, 2) }}</td>
                    <td>
                        @if($product->isLowStock())
                            <span class="badge badge-danger">Low Stock (≤ {{ $product->alert_quantity }})</span>
                        @else
                            <span class="badge badge-success">Sufficient</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-success">{{ ucfirst($product->status) }}</span>
                    </td>
                    <td style="text-align: right; white-space: nowrap;">
                        <div style="display: inline-flex; align-items: center; gap: 0.35rem;">
                            <a href="{{ route('shop.labels.index', ['type' => 'product', 'id' => $product->id]) }}" 
                               class="btn btn-secondary btn-sm" 
                               title="Print Barcode Label" 
                               style="padding: 0.3rem 0.55rem; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.2rem; color: #4338ca; background: #eef2ff; border-color: #c7d2fe;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
                                Label
                            </a>
                            <button type="button" 
                                    class="btn btn-secondary btn-sm" 
                                    onclick='openEditProductModal(@json($product))' 
                                    title="Edit product details and photo" 
                                    style="padding: 0.3rem 0.6rem; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                Edit
                            </button>
                            <form method="POST" action="{{ route('shop.products.destroy', $product) }}" onsubmit="return confirm('Delete product \'{{ addslashes($product->name) }}\' from inventory?');" style="display: inline; margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="btn btn-danger btn-sm" 
                                        title="Delete product" 
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
                        No products registered in inventory. Add an item above.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($products->hasPages())
    <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border);">
        {{ $products->links() }}
    </div>
    @endif
</div>

<!-- Modal: Add Product -->
<div class="modal-backdrop" id="addProductModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Add Product or Accessory</h3>
            <button type="button" class="modal-close" onclick="closeModal('addProductModal')">&times;</button>
        </div>
        <form method="POST" action="{{ route('shop.products.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Item Name *</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. 20W Type-C Fast Charger" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Category *</label>
                        <select name="category_id" id="product_category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Brand (Optional)</label>
                        <select name="brand_id" id="product_brand_id" class="form-control">
                            <option value="">Select Brand</option>
                            @foreach($brands as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Barcode / EAN (Auto-generated if empty)</label>
                        <input type="text" name="barcode" class="form-control" placeholder="Scan barcode or leave blank" style="font-family: monospace;">
                    </div>
                    <div class="form-group">
                        <label>SKU Code</label>
                        <input type="text" name="sku" class="form-control" placeholder="e.g. CHG-20W-WHT" style="font-family: monospace;">
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
                        <input type="number" name="stock_quantity" class="form-control" value="10" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>Low Stock Alert Level</label>
                        <input type="number" name="alert_quantity" class="form-control" value="3" min="0">
                    </div>
                </div>

                <!-- Product Image Upload -->
                <div class="form-group" style="background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 8px; padding: 0.85rem; margin-top: 0.5rem;">
                    <label style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                        <span style="font-weight: 700;">Product Image (Optional)</span>
                        <span style="font-size: 0.75rem; color: #64748b;">JPG, PNG, WEBP up to 5MB</span>
                    </label>
                    <div style="display: flex; gap: 0.85rem; align-items: center;">
                        <div id="newProductImagePreviewContainer" style="width: 60px; height: 60px; border-radius: 8px; border: 1px dashed #94a3b8; background: #ffffff; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative; flex-shrink: 0;">
                            <svg id="newProductImagePlaceholderIcon" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                            <img id="newProductImagePreview" src="" alt="Preview" style="display: none; width: 100%; height: 100%; object-fit: cover;">
                            <button type="button" id="btnClearNewProductImage" onclick="clearNewProductImage()" style="display: none; position: absolute; top: 2px; right: 2px; background: rgba(15,23,42,0.75); color: white; border: none; border-radius: 50%; width: 16px; height: 16px; font-size: 11px; line-height: 1; cursor: pointer; align-items: center; justify-content: center;">&times;</button>
                        </div>
                        <div style="flex: 1;">
                            <input type="file" name="image" id="product_image_input" class="form-control" accept="image/png,image/jpeg,image/webp,image/gif" onchange="previewNewProductImage(this)" style="font-size: 0.8125rem;">
                            <span style="font-size: 0.72rem; color: #64748b; display: block; margin-top: 0.2rem;">Photo will appear on POS screen and inventory lists.</span>
                        </div>
                    </div>
                </div>
            </div>
            <div style="padding: 1.25rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addProductModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Product</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Product -->
<div class="modal-backdrop" id="editProductModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Edit Product / Accessory</h3>
            <button type="button" class="modal-close" onclick="closeModal('editProductModal')">&times;</button>
        </div>
        <form id="editProductForm" method="POST" action="" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="remove_image" id="editProductRemoveImage" value="0">
            <div class="modal-body">
                <div class="form-group">
                    <label>Item Name *</label>
                    <input type="text" name="name" id="edit_product_name" class="form-control" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Category *</label>
                        <select name="category_id" id="edit_product_category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Brand (Optional)</label>
                        <select name="brand_id" id="edit_product_brand_id" class="form-control">
                            <option value="">Select Brand</option>
                            @foreach($brands as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Barcode / EAN</label>
                        <input type="text" name="barcode" id="edit_product_barcode" class="form-control" style="font-family: monospace;">
                    </div>
                    <div class="form-group">
                        <label>SKU Code</label>
                        <input type="text" name="sku" id="edit_product_sku" class="form-control" style="font-family: monospace;">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Purchase Cost (LKR) *</label>
                        <input type="number" step="0.01" name="cost_price" id="edit_product_cost_price" class="form-control" required min="0">
                    </div>
                    <div class="form-group">
                        <label>Selling Price (LKR) *</label>
                        <input type="number" step="0.01" name="selling_price" id="edit_product_selling_price" class="form-control" required min="0">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Stock Quantity *</label>
                        <input type="number" name="stock_quantity" id="edit_product_stock_quantity" class="form-control" required min="0">
                    </div>
                    <div class="form-group">
                        <label>Low Stock Alert Level</label>
                        <input type="number" name="alert_quantity" id="edit_product_alert_quantity" class="form-control" min="0">
                    </div>
                </div>

                <!-- Product Image Management (Add / Replace / Delete) -->
                <div class="form-group" style="background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 8px; padding: 0.85rem; margin-top: 0.5rem;">
                    <label style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <span style="font-weight: 700;">Product Image</span>
                        <span style="font-size: 0.75rem; color: #64748b;">JPG, PNG, WEBP up to 5MB</span>
                    </label>

                    <!-- Existing Image Section -->
                    <div id="editProductExistingImageWrap" style="display: none; align-items: center; justify-content: space-between; background: #ffffff; border: 1px solid var(--surface-border); border-radius: 6px; padding: 0.5rem 0.75rem; margin-bottom: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <img id="editProductExistingImg" src="" alt="Current image" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0; cursor: pointer;" onclick="openImageLightbox(this.src, document.getElementById('edit_product_name').value)">
                            <div>
                                <span style="font-size: 0.8125rem; font-weight: 600; color: #1e293b; display: block;">Current Active Photo</span>
                                <span style="font-size: 0.72rem; color: #10b981; font-weight: 600;">Saved on Cloud/Storage</span>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm" onclick="markDeleteProductImage()" style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; font-size: 0.75rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.3rem 0.65rem; border-radius: 6px;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            Delete Image
                        </button>
                    </div>

                    <div id="editProductImageRemovedNotice" style="display: none; background: #fee2e2; border: 1px solid #fca5a5; border-radius: 6px; padding: 0.45rem 0.75rem; margin-bottom: 0.75rem; font-size: 0.78rem; color: #991b1b; align-items: center; justify-content: space-between;">
                        <span>⚠️ Image will be removed upon saving.</span>
                        <button type="button" onclick="undoDeleteProductImage()" style="background: none; border: none; color: #4338ca; text-decoration: underline; font-size: 0.75rem; font-weight: 700; cursor: pointer;">Undo</button>
                    </div>

                    <!-- Replace / Upload New Image -->
                    <div style="display: flex; gap: 0.75rem; align-items: center;">
                        <div id="editProductNewImagePreviewWrap" style="width: 60px; height: 60px; border-radius: 8px; border: 1px dashed #94a3b8; background: #ffffff; display: none; align-items: center; justify-content: center; overflow: hidden; position: relative; flex-shrink: 0;">
                            <img id="editProductNewImagePreview" src="" alt="New Preview" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <div style="flex: 1;">
                            <input type="file" name="image" id="edit_product_image_input" class="form-control" accept="image/png,image/jpeg,image/webp,image/gif" onchange="previewEditProductImage(this)" style="font-size: 0.8125rem;">
                            <span style="font-size: 0.72rem; color: #64748b; display: block; margin-top: 0.2rem;">Select a file to upload or replace the current photo.</span>
                        </div>
                    </div>
                </div>
            </div>
            <div style="padding: 1.25rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editProductModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Image Lightbox Preview -->
<div class="modal-backdrop" id="imageLightboxModal" style="z-index: 1070;" onclick="closeModal('imageLightboxModal')">
    <div class="modal-box" style="max-width: 500px; padding: 0.5rem; background: #0f172a; border: 1px solid #334155;" onclick="event.stopPropagation()">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0.75rem; color: #ffffff;">
            <strong id="lightboxImageTitle" style="font-size: 0.9375rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Product Photo</strong>
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
<!-- Modal: Manage Categories & Brands -->
<div class="modal-backdrop" id="manageCatalogModal">
    <div class="modal-box" style="max-width: 650px;">
        <div class="modal-header">
            <h3>Manage Categories &amp; Brands</h3>
            <button type="button" class="modal-close" onclick="closeModal('manageCatalogModal')">&times;</button>
        </div>
        <div class="modal-body">
            <!-- Tab Navigation -->
            <div style="display: flex; gap: 0.5rem; border-bottom: 2px solid var(--surface-border); margin-bottom: 1.25rem;">
                <button type="button" id="tabBtnCat" onclick="switchCatalogTab('cat')" style="padding: 0.6rem 1rem; border: none; background: none; font-weight: 700; font-size: 0.875rem; color: #4338ca; border-bottom: 3px solid #4f46e5; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                    Categories (<span id="catTotalCount">{{ $categories->count() }}</span>)
                </button>
                <button type="button" id="tabBtnBrand" onclick="switchCatalogTab('brand')" style="padding: 0.6rem 1rem; border: none; background: none; font-weight: 600; font-size: 0.875rem; color: #64748b; border-bottom: 3px solid transparent; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
                    Brands (<span id="brandTotalCount">{{ $brands->count() }}</span>)
                </button>
            </div>

            <!-- TAB 1: CATEGORIES -->
            <div id="catalogTabCategories">
                <form method="POST" action="{{ route('shop.categories.store') }}" style="display: flex; gap: 0.5rem; margin-bottom: 1.25rem;">
                    @csrf
                    <input type="text" name="name" class="form-control" placeholder="New Category Name (e.g. Wireless Audio)" required style="flex: 1;">
                    <button type="submit" class="btn btn-primary" style="white-space: nowrap;">+ Add Category</button>
                </form>

                <div style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">
                    Active Categories
                </div>

                <div id="categoryCardsContainer" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 0.65rem; max-height: 290px; overflow-y: auto; padding: 0.25rem;">
                    @forelse($categories as $cat)
                        <div class="catalog-card" id="cat-card-{{ $cat->id }}" style="background: #ffffff; border: 1.5px solid var(--surface-border); border-radius: 8px; padding: 0.65rem 0.85rem; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                            <div style="min-width: 0; flex: 1;">
                                <strong id="cat-name-display-{{ $cat->id }}" style="display: block; font-size: 0.875rem; color: var(--text-heading); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $cat->name }}">
                                    {{ $cat->name }}
                                </strong>
                                <div style="display: flex; align-items: center; gap: 0.4rem; margin-top: 0.2rem;">
                                    <span style="font-size: 0.72rem; color: #64748b;">
                                        {{ $cat->products_count ?? 0 }} {{ Str::plural('item', $cat->products_count ?? 0) }}
                                    </span>
                                    @if(($cat->products_count ?? 0) > 0)
                                        <span class="badge badge-success" style="font-size: 0.6rem; padding: 0.1rem 0.35rem; line-height: 1;" title="Assigned to products">In use</span>
                                    @endif
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.3rem; flex-shrink: 0;">
                                <!-- Edit Button -->
                                <button type="button" 
                                        id="cat-edit-btn-{{ $cat->id }}"
                                        data-name="{{ $cat->name }}"
                                        onclick="openEditCatalogModal('category', {{ $cat->id }}, this.getAttribute('data-name'), {{ $cat->products_count ?? 0 }})" 
                                        title="Edit category" 
                                        style="background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; color: #475569; cursor: pointer; transition: all 0.15s ease;">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                </button>
                                <!-- Delete Button -->
                                <form method="POST" action="{{ route('shop.categories.destroy', $cat) }}" onsubmit="return deleteCatalogItem('category', {{ $cat->id }}, '{{ addslashes($cat->name) }}', {{ $cat->products_count ?? 0 }}, this);" style="margin: 0; display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            title="Delete category" 
                                            style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; color: #ef4444; cursor: pointer; transition: all 0.15s ease;">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: 1.5rem; font-size: 0.85rem;">
                            No categories registered yet. Add one above or initialize default catalog.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- TAB 2: BRANDS -->
            <div id="catalogTabBrands" style="display: none;">
                <form method="POST" action="{{ route('shop.brands.store') }}" style="display: flex; gap: 0.5rem; margin-bottom: 1.25rem;">
                    @csrf
                    <input type="text" name="name" class="form-control" placeholder="New Brand Name (e.g. Google Pixel, Anker)" required style="flex: 1;">
                    <button type="submit" class="btn btn-primary" style="white-space: nowrap;">+ Add Brand</button>
                </form>

                <div style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">
                    Active Brands
                </div>

                <div id="brandCardsContainer" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 0.65rem; max-height: 290px; overflow-y: auto; padding: 0.25rem;">
                    @forelse($brands as $b)
                        <div class="catalog-card" id="brand-card-{{ $b->id }}" style="background: #ffffff; border: 1.5px solid var(--surface-border); border-radius: 8px; padding: 0.65rem 0.85rem; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                            <div style="min-width: 0; flex: 1;">
                                <strong id="brand-name-display-{{ $b->id }}" style="display: block; font-size: 0.875rem; color: var(--text-heading); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $b->name }}">
                                    {{ $b->name }}
                                </strong>
                                <div style="display: flex; align-items: center; gap: 0.4rem; margin-top: 0.2rem;">
                                    <span style="font-size: 0.72rem; color: #64748b;">
                                        {{ $b->products_count ?? 0 }} {{ Str::plural('item', $b->products_count ?? 0) }}
                                    </span>
                                    @if(($b->products_count ?? 0) > 0)
                                        <span class="badge badge-success" style="font-size: 0.6rem; padding: 0.1rem 0.35rem; line-height: 1;" title="Assigned to products">In use</span>
                                    @endif
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.3rem; flex-shrink: 0;">
                                <!-- Edit Button -->
                                <button type="button" 
                                        id="brand-edit-btn-{{ $b->id }}"
                                        data-name="{{ $b->name }}"
                                        onclick="openEditCatalogModal('brand', {{ $b->id }}, this.getAttribute('data-name'), {{ $b->products_count ?? 0 }})" 
                                        title="Edit brand" 
                                        style="background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; color: #475569; cursor: pointer; transition: all 0.15s ease;">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                </button>
                                <!-- Delete Button -->
                                <form method="POST" action="{{ route('shop.brands.destroy', $b) }}" onsubmit="return deleteCatalogItem('brand', {{ $b->id }}, '{{ addslashes($b->name) }}', {{ $b->products_count ?? 0 }}, this);" style="margin: 0; display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            title="Delete brand" 
                                            style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; color: #ef4444; cursor: pointer; transition: all 0.15s ease;">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: 1.5rem; font-size: 0.85rem;">
                            No brands registered yet. Add one above or initialize default catalog.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- 1-Click Starter Presets -->
            <div style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px dashed var(--surface-border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                <span style="font-size: 0.75rem; color: #64748b;">
                    Need standard phone store items (Smartphones, Chargers, Apple, Samsung)?
                </span>
                <form method="POST" action="{{ route('shop.catalog.seed-defaults') }}">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm" style="font-size: 0.75rem;">
                        ✨ Load Standard Phone Catalog
                    </button>
                </form>
            </div>
        </div>
        <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end;">
            <button type="button" class="btn btn-secondary" onclick="closeModal('manageCatalogModal')">Close</button>
        </div>
    </div>
</div>

<!-- Modal: Edit Category / Brand -->
<div class="modal-backdrop" id="editCatalogItemModal" style="z-index: 1060;">
    <div class="modal-box" style="max-width: 440px;">
        <div class="modal-header">
            <h3 id="editCatalogModalTitle" style="display: flex; align-items: center; gap: 0.5rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                <span>Edit Item</span>
            </h3>
            <button type="button" class="modal-close" onclick="closeModal('editCatalogItemModal')">&times;</button>
        </div>
        <form id="editCatalogItemForm" method="POST" action="">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group" style="margin-bottom: 0.75rem;">
                    <label id="editCatalogItemLabel" for="editCatalogItemName">Name *</label>
                    <input type="text" id="editCatalogItemName" name="name" class="form-control" required placeholder="Enter name">
                </div>
                <div id="editCatalogItemHint" style="font-size: 0.78rem; color: #64748b; line-height: 1.4; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.6rem 0.75rem;">
                    Saving will update the item across all associated products.
                </div>
            </div>
            <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editCatalogItemModal')">Cancel</button>
                <button type="submit" class="btn btn-primary" id="btnSaveEditCatalog">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Dynamic Notification Toast -->
<div id="catalogToast" style="position: fixed; top: 1.5rem; right: 1.5rem; z-index: 9999; display: none; background: #0f172a; color: #ffffff; padding: 0.85rem 1.25rem; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); font-size: 0.875rem; font-weight: 600; align-items: center; gap: 0.5rem; transition: all 0.3s ease;">
    <span id="catalogToastText">Notification message</span>
</div>

@push('scripts')
<script>
    // Tab switching in Manage Catalog Modal
    function switchCatalogTab(tab) {
        const catTab = document.getElementById('catalogTabCategories');
        const brandTab = document.getElementById('catalogTabBrands');
        const btnCat = document.getElementById('tabBtnCat');
        const btnBrand = document.getElementById('tabBtnBrand');

        if (tab === 'cat') {
            catTab.style.display = 'block';
            brandTab.style.display = 'none';
            btnCat.style.color = '#4338ca';
            btnCat.style.borderBottomColor = '#4f46e5';
            btnBrand.style.color = '#64748b';
            btnBrand.style.borderBottomColor = 'transparent';
        } else {
            catTab.style.display = 'none';
            brandTab.style.display = 'block';
            btnBrand.style.color = '#4338ca';
            btnBrand.style.borderBottomColor = '#4f46e5';
            btnCat.style.color = '#64748b';
            btnCat.style.borderBottomColor = 'transparent';
        }
    }

    // Toast helper
    function showToast(message, isError) {
        const toast = document.getElementById('catalogToast');
        const text = document.getElementById('catalogToastText');
        if (!toast || !text) return;

        text.textContent = message;
        toast.style.background = isError ? '#991b1b' : '#065f46';
        toast.style.display = 'flex';

        setTimeout(() => {
            toast.style.display = 'none';
        }, 3500);
    }

    let activeCatalogEditType = 'category';

    // Open Edit Catalog Modal
    function openEditCatalogModal(type, id, currentName, count) {
        activeCatalogEditType = type;
        const form = document.getElementById('editCatalogItemForm');
        const titleSpan = document.getElementById('editCatalogModalTitle').querySelector('span');
        const label = document.getElementById('editCatalogItemLabel');
        const input = document.getElementById('editCatalogItemName');
        const hint = document.getElementById('editCatalogItemHint');

        if (type === 'category') {
            titleSpan.textContent = 'Edit Category: ' + currentName;
            label.textContent = 'Category Name *';
            form.action = `/shop/categories/${id}`;
            hint.innerHTML = count > 0 
                ? `ℹ️ <strong>${count} product(s)</strong> are currently assigned to this category. Renaming it will automatically update all connected inventory items.`
                : 'Renaming this category will update it in your shop catalog and product dropdowns.';
        } else {
            titleSpan.textContent = 'Edit Brand: ' + currentName;
            label.textContent = 'Brand Name *';
            form.action = `/shop/brands/${id}`;
            hint.innerHTML = count > 0 
                ? `ℹ️ <strong>${count} product(s)</strong> are currently assigned to this brand. Renaming it will automatically update all connected inventory items.`
                : 'Renaming this brand will update it in your shop catalog and product dropdowns.';
        }

        input.value = currentName;
        openModal('editCatalogItemModal');
        setTimeout(() => {
            input.focus();
            input.select();
        }, 150);
    }

    // Submit Edit Catalog Item via AJAX with DOM update
    document.getElementById('editCatalogItemForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const btn = document.getElementById('btnSaveEditCatalog');
        const input = document.getElementById('editCatalogItemName');
        const newName = input.value.trim();

        if (!newName) return;

        btn.disabled = true;
        btn.textContent = 'Saving...';

        fetch(form.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                _method: 'PUT',
                name: newName
            })
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.textContent = 'Save Changes';

            if (data.success) {
                closeModal('editCatalogItemModal');
                showToast(data.message, false);

                if (activeCatalogEditType === 'category' && data.category) {
                    const titleEl = document.getElementById(`cat-name-display-${data.category.id}`);
                    if (titleEl) titleEl.textContent = data.category.name;

                    const editBtn = document.getElementById(`cat-edit-btn-${data.category.id}`);
                    if (editBtn) editBtn.setAttribute('data-name', data.category.name);

                    const opt = document.querySelector(`#product_category_id option[value="${data.category.id}"]`);
                    if (opt) opt.textContent = data.category.name;
                } else if (activeCatalogEditType === 'brand' && data.brand) {
                    const titleEl = document.getElementById(`brand-name-display-${data.brand.id}`);
                    if (titleEl) titleEl.textContent = data.brand.name;

                    const editBtn = document.getElementById(`brand-edit-btn-${data.brand.id}`);
                    if (editBtn) editBtn.setAttribute('data-name', data.brand.name);

                    const opt = document.querySelector(`#product_brand_id option[value="${data.brand.id}"]`);
                    if (opt) opt.textContent = data.brand.name;
                }
            } else {
                alert(data.message || 'Error updating item.');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.textContent = 'Save Changes';
            form.submit();
        });
    });

    // Delete Catalog Item with safe confirmation & AJAX removal
    function deleteCatalogItem(type, id, name, count, formEl) {
        let confirmText = `Are you sure you want to delete ${type} "${name}"?`;
        if (count > 0) {
            const itemType = type === 'category' ? 'category' : 'brand';
            const unassignDesc = type === 'category' ? 'marked as Uncategorized' : 'left without a brand';
            confirmText = `⚠️ Warning: "${name}" currently has ${count} product(s) assigned.\n\nDeleting this ${itemType} will keep the products in your inventory, but they will be ${unassignDesc}.\n\nAre you sure you want to proceed with deletion?`;
        }

        if (!confirm(confirmText)) {
            return false;
        }

        fetch(formEl.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ _method: 'DELETE' })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, false);
                const card = document.getElementById(`${type === 'category' ? 'cat' : 'brand'}-card-${id}`);
                if (card) {
                    card.style.transition = 'all 0.25s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.9)';
                    setTimeout(() => card.remove(), 250);
                }

                if (type === 'category') {
                    const countEl = document.getElementById('catTotalCount');
                    if (countEl) countEl.textContent = Math.max(0, parseInt(countEl.textContent || '1') - 1);
                    const opt = document.querySelector(`#product_category_id option[value="${id}"]`);
                    if (opt) opt.remove();
                } else {
                    const countEl = document.getElementById('brandTotalCount');
                    if (countEl) countEl.textContent = Math.max(0, parseInt(countEl.textContent || '1') - 1);
                    const opt = document.querySelector(`#product_brand_id option[value="${id}"]`);
                    if (opt) opt.remove();
                }
            } else {
                alert(data.message || 'Error deleting item.');
            }
        })
        .catch(err => {
            formEl.submit();
        });

        return false;
    }

    // Product Image Preview for Add Modal
    function previewNewProductImage(input) {
        const preview = document.getElementById('newProductImagePreview');
        const placeholder = document.getElementById('newProductImagePlaceholderIcon');
        const clearBtn = document.getElementById('btnClearNewProductImage');

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

    function clearNewProductImage() {
        const input = document.getElementById('product_image_input');
        const preview = document.getElementById('newProductImagePreview');
        const placeholder = document.getElementById('newProductImagePlaceholderIcon');
        const clearBtn = document.getElementById('btnClearNewProductImage');

        input.value = '';
        preview.src = '';
        preview.style.display = 'none';
        placeholder.style.display = 'block';
        clearBtn.style.display = 'none';
    }

    // Open Edit Product Modal
    function openEditProductModal(product) {
        const form = document.getElementById('editProductForm');
        form.action = `/shop/products/${product.id}`;

        document.getElementById('edit_product_name').value = product.name || '';
        document.getElementById('edit_product_category_id').value = product.category_id || '';
        document.getElementById('edit_product_brand_id').value = product.brand_id || '';
        document.getElementById('edit_product_barcode').value = product.barcode || product.code || '';
        document.getElementById('edit_product_sku').value = product.sku || product.code || '';
        document.getElementById('edit_product_cost_price').value = product.cost_price || '0.00';
        document.getElementById('edit_product_selling_price').value = product.selling_price || product.retail_price || '0.00';
        document.getElementById('edit_product_stock_quantity').value = product.stock_quantity ?? '0';
        document.getElementById('edit_product_alert_quantity').value = product.alert_quantity ?? '3';

        // Reset image states
        document.getElementById('editProductRemoveImage').value = '0';
        document.getElementById('edit_product_image_input').value = '';
        document.getElementById('editProductNewImagePreviewWrap').style.display = 'none';
        document.getElementById('editProductNewImagePreview').src = '';
        document.getElementById('editProductImageRemovedNotice').style.display = 'none';

        const existingWrap = document.getElementById('editProductExistingImageWrap');
        const existingImg = document.getElementById('editProductExistingImg');

        if (product.image_url) {
            existingImg.src = product.image_url;
            existingWrap.style.display = 'flex';
        } else {
            existingImg.src = '';
            existingWrap.style.display = 'none';
        }

        openModal('editProductModal');
    }

    // Preview replacement image in Edit Modal
    function previewEditProductImage(input) {
        const previewWrap = document.getElementById('editProductNewImagePreviewWrap');
        const preview = document.getElementById('editProductNewImagePreview');

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

    // Mark current image for deletion
    function markDeleteProductImage() {
        document.getElementById('editProductRemoveImage').value = '1';
        document.getElementById('editProductExistingImageWrap').style.display = 'none';
        document.getElementById('editProductImageRemovedNotice').style.display = 'flex';
    }

    // Undo delete image
    function undoDeleteProductImage() {
        document.getElementById('editProductRemoveImage').value = '0';
        document.getElementById('editProductExistingImageWrap').style.display = 'flex';
        document.getElementById('editProductImageRemovedNotice').style.display = 'none';
    }

    function handleProductLightboxClick(el) {
        openImageLightbox(el.src, el.getAttribute('data-title'), el.getAttribute('data-delete-url'));
    }

    // Lightbox viewer
    function openImageLightbox(imageUrl, title, deleteUrl = '') {
        document.getElementById('lightboxImageEl').src = imageUrl;
        document.getElementById('lightboxImageTitle').textContent = title || 'Product Photo';

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


