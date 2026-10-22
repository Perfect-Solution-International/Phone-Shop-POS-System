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

        <button type="button" class="btn btn-primary" onclick="openModal('addProductModal')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Add New Item
        </button>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Product Name & Category</th>
                    <th>Barcode / SKU</th>
                    <th>In Stock</th>
                    <th>Cost Price</th>
                    <th>Selling Price</th>
                    <th>Alert Level</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td>
                        <strong style="color: var(--text-heading); font-size: 0.9375rem;">{{ $product->name }}</strong>
                        <div style="font-size: 0.75rem; color: #4f46e5; font-weight: 600;">
                            {{ $product->category->name ?? 'Accessories' }}
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
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 3rem;">
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
        <form method="POST" action="{{ route('shop.products.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Item Name *</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. 20W Type-C Fast Charger" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Category *</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Brand (Optional)</label>
                        <select name="brand_id" class="form-control">
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
            </div>
            <div style="padding: 1.25rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addProductModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Product</button>
            </div>
        </form>
    </div>
</div>
@endsection

