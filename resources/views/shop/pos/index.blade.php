@extends('layouts.shop')

@section('title', 'POS Counter')
@section('page-title', 'Point of Sale (POS) Counter')

@section('content')
<style>
    /* Modern POS Workspace Layout */
    .pos-workspace-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.25rem;
        flex-wrap: wrap;
        gap: 0.85rem;
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.85);
        border-radius: 14px;
        padding: 0.85rem 1.25rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    }

    .pos-layout {
        display: grid;
        grid-template-columns: 1.3fr 1fr;
        gap: 1.25rem;
        min-height: calc(100vh - 175px);
        align-items: start;
    }

    /* Left Catalog Container */
    .pos-catalog {
        display: flex;
        flex-direction: column;
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03), 0 6px 16px -4px rgba(0, 0, 0, 0.04);
    }

    .pos-catalog-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--surface-border);
        display: flex;
        gap: 0.85rem;
        align-items: center;
        background: #fafbfc;
    }

    .search-input-shell {
        position: relative;
        flex: 1;
        display: flex;
        align-items: center;
    }

    .search-input-shell svg {
        position: absolute;
        left: 0.95rem;
        color: #94a3b8;
        pointer-events: none;
    }

    .search-input-shell input {
        padding-left: 2.65rem;
        height: 42px;
        border-radius: 10px;
        font-size: 0.875rem;
        font-weight: 500;
        border: 1.5px solid var(--surface-border);
        transition: all 0.2s ease;
    }

    .search-input-shell input:focus {
        border-color: var(--brand-indigo-600);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }

    .pos-tabs {
        display: flex;
        border-bottom: 1px solid var(--surface-border);
        background: #f8fafc;
        padding: 0.4rem 0.6rem 0;
        gap: 0.4rem;
    }

    .pos-tab {
        padding: 0.65rem 1.25rem;
        font-size: 0.85rem;
        font-weight: 650;
        cursor: pointer;
        border-radius: 10px 10px 0 0;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 0.45rem;
        transition: all 0.18s ease;
        border: 1px solid transparent;
        border-bottom: none;
    }

    .pos-tab:hover {
        color: var(--brand-indigo-600);
        background: rgba(255, 255, 255, 0.6);
    }

    .pos-tab.active {
        color: #4f46e5;
        background: #ffffff;
        border-color: var(--surface-border);
        border-bottom-color: #ffffff;
        font-weight: 750;
        box-shadow: 0 -2px 6px rgba(0, 0, 0, 0.02);
    }

    .pos-items-grid {
        padding: 1.15rem;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(175px, 1fr));
        gap: 0.85rem;
        overflow-y: auto;
        flex: 1;
        background: #ffffff;
    }

    .pos-item-card {
        border: 1.5px solid var(--surface-border);
        border-radius: 12px;
        padding: 0.95rem;
        cursor: pointer;
        background: #ffffff;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
    }

    .pos-item-card:hover {
        border-color: #6366f1;
        box-shadow: 0 6px 18px -2px rgba(99, 102, 241, 0.12);
        transform: translateY(-2px);
    }

    .pos-item-card:active {
        transform: scale(0.98);
    }

    .pos-item-brand {
        display: inline-block;
        font-size: 0.6875rem;
        font-weight: 750;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #0369a1;
        background: #e0f2fe;
        padding: 0.15rem 0.5rem;
        border-radius: 6px;
    }

    .pos-item-price {
        margin-top: 0.75rem;
        font-weight: 800;
        color: #166534;
        font-size: 0.95rem;
        letter-spacing: -0.01em;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .pos-item-add-btn {
        height: 28px;
        padding: 0 0.65rem;
        border-radius: 6px;
        background: #f1f5f9;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #475569;
        font-weight: 750;
        font-size: 0.75rem;
        transition: all 0.18s ease;
        white-space: nowrap;
        border: 1px solid #e2e8f0;
    }

    .pos-item-card:hover .pos-item-add-btn {
        background: #4f46e5;
        color: #ffffff;
        border-color: #4f46e5;
    }

    /* Right Cart Container */
    .pos-cart {
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 16px;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03), 0 6px 16px -4px rgba(0, 0, 0, 0.04);
        position: sticky;
        top: 80px;
    }

    .pos-cart-header {
        padding: 0.9rem 1.25rem;
        border-bottom: 1px solid var(--surface-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #ffffff;
    }

    .pos-cart-title {
        font-size: 0.95rem;
        font-weight: 800;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .pos-cart-items {
        flex: 1;
        overflow-y: auto;
        padding: 0.85rem;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
        min-height: 220px;
        max-height: 380px;
    }

    .pos-cart-item-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 0.75rem 0.85rem;
        display: flex;
        gap: 0.75rem;
        align-items: center;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        transition: all 0.15s ease;
    }

    .pos-cart-item-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    .pos-cart-summary {
        padding: 1rem 1.25rem;
        border-top: 1px solid var(--surface-border);
        background: #ffffff;
    }

    .summary-total-banner {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(135deg, #eef2ff 0%, #f0fdf4 100%);
        border: 1.5px solid #c7d2fe;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        margin: 0.65rem 0 1rem;
    }

    .split-row {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 0.65rem;
        margin-top: 0.45rem;
    }

    .split-tender-box {
        background: #ffffff;
        border: 1px solid var(--surface-border);
        border-radius: 8px;
        padding: 0.45rem 0.65rem;
    }

    .split-tender-box label {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 0.2rem;
    }

    .split-tender-box input {
        border: none;
        padding: 0;
        font-size: 0.9375rem;
        font-weight: 750;
        color: #0f172a;
        width: 100%;
        background: transparent;
        outline: none;
    }

    .quick-cash-row {
        display: flex;
        gap: 0.4rem;
        margin-top: 0.65rem;
        flex-wrap: wrap;
    }

    .quick-cash-btn {
        flex: 1;
        padding: 0.3rem 0.45rem;
        font-size: 0.72rem;
        font-weight: 700;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        color: #334155;
        cursor: pointer;
        transition: all 0.15s ease;
        text-align: center;
    }

    .quick-cash-btn:hover {
        background: #f1f5f9;
        border-color: #94a3b8;
    }

    @media (max-width: 1024px) {
        .pos-layout {
            grid-template-columns: 1fr;
            height: auto;
        }
    }

    @media (max-width: 768px) {
        .pos-catalog-header {
            flex-direction: column;
            align-items: stretch;
            gap: 0.65rem;
            padding: 0.85rem;
        }

        .pos-catalog-header select {
            max-width: 100% !important;
            width: 100% !important;
        }

        .pos-tab {
            padding: 0.65rem 0.85rem;
            font-size: 0.8125rem;
            text-align: center;
            flex: 1;
        }
    }

    @media (max-width: 640px) {
        .split-row {
            grid-template-columns: 1fr;
            gap: 0.6rem;
        }

        .pos-cart-summary {
            padding: 0.85rem;
        }

        .pos-items-grid {
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            padding: 0.75rem;
            gap: 0.5rem;
        }

        .pos-item-card {
            padding: 0.65rem;
        }
    }
</style>

<!-- Modern Status Bar Above POS -->
<div class="pos-workspace-bar">
    <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #10b981; box-shadow: 0 0 8px #10b981;"></span>
            <span style="font-size: 0.85rem; color: #64748b;">
                Active Counter: <strong style="color: #0f172a;">{{ session('active_outlet_name', auth()->user()->shop->mainOutlet->name ?? 'Main Branch') }}</strong>
            </span>
        </div>
        <span class="badge" style="background: #eef2ff; color: #4f46e5; font-weight: 700;">Live POS Terminal</span>
    </div>

    <div style="display: flex; gap: 0.65rem; align-items: center; flex-wrap: wrap;">
        <!-- Top Cart Button (Appears as soon as items are added) -->
        <button type="button" id="topCartBtn" class="btn btn-primary btn-sm" onclick="scrollToCart()" style="display: none; border-radius: 8px; font-weight: 750; gap: 0.45rem; background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); box-shadow: 0 2px 8px rgba(79, 70, 229, 0.35);">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
            <span>Cart (<span id="topCartCount">0</span>)</span>
            <span id="topCartTotal" style="background: rgba(255,255,255,0.22); padding: 1px 7px; border-radius: 6px; font-size: 0.72rem; font-weight: 700;">LKR 0.00</span>
        </button>
        <button type="button" class="btn btn-secondary btn-sm" onclick="openModal('recentSalesModal')" style="border-radius: 8px; font-weight: 650; gap: 0.4rem;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            Recent Sales ({{ count($recentSales) }})
        </button>
        <a href="{{ route('shop.sales') }}" class="btn btn-secondary btn-sm" style="border-radius: 8px; font-weight: 650; gap: 0.4rem;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
            Sales Directory
        </a>
    </div>
</div>

<div class="pos-layout" id="posApp">
    <!-- Left: Catalog & Search -->
    <div class="pos-catalog">
        <div class="pos-catalog-header">
            <div class="search-input-shell">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" id="searchInput" class="form-control" placeholder="Scan Barcode or Search by Name, IMEI, Brand..." onkeyup="filterItems()">
            </div>
            <select id="categoryFilter" class="form-control" style="max-width: 190px; height: 42px; border-radius: 10px; font-size: 0.85rem;" onchange="filterItems()">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->name }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="pos-tabs">
            <div class="pos-tab active" onclick="switchTab('phones', this)">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="5" y="2" width="14" height="20" rx="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                <span>Mobile Phones (IMEI)</span>
            </div>
            <div class="pos-tab" onclick="switchTab('products', this)">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                <span>Accessories &amp; Items</span>
            </div>
        </div>

        <!-- Phone Items Tab -->
        <div id="tabPhones" class="pos-items-grid">
            @forelse($phones as $phone)
                <div class="pos-item-card item-card" 
                     data-type="phone" 
                     data-id="{{ $phone->id }}" 
                     data-name="{{ $phone->brand }} {{ $phone->model }}" 
                     data-price="{{ $phone->selling_price }}" 
                     data-imei="{{ $phone->imei_1 }}"
                     data-category="Phones"
                     onclick="addPhoneToCart({{ $phone->id }}, '{{ addslashes($phone->brand.' '.$phone->model) }}', {{ $phone->selling_price }}, '{{ $phone->imei_1 }}', '{{ addslashes($phone->brand) }}', '{{ addslashes(($phone->color ? $phone->color.' • ' : '').$phone->storage) }}', '{{ $phone->image_url }}')">
                    @if($phone->image_url)
                        <img src="{{ $phone->image_url }}" alt="{{ $phone->brand }} {{ $phone->model }}" style="width: 100%; height: 80px; object-fit: cover; border-radius: 8px; margin-bottom: 0.5rem; border: 1px solid var(--surface-border);">
                    @endif
                    <div>
                        <span class="pos-item-brand">{{ $phone->brand }}</span>
                        <div style="font-weight: 750; font-size: 0.9rem; margin-top: 0.35rem; color: #0f172a; line-height: 1.3;">{{ $phone->model }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">
                            {{ $phone->color }} • {{ $phone->storage }}
                        </div>
                        <div style="font-family: 'JetBrains Mono', monospace; font-size: 0.68rem; color: #475569; margin-top: 0.45rem; background: #f1f5f9; padding: 2px 5px; border-radius: 4px; border: 1px solid #e2e8f0;">
                            IMEI: {{ $phone->imei_1 }}
                        </div>
                    </div>
                    <div class="pos-item-price">
                        <span>LKR {{ number_format($phone->selling_price, 2) }}</span>
                        <div class="pos-item-add-btn item-add-btn">+ Add</div>
                    </div>
                </div>
            @empty
                <div style="grid-column: 1/-1; text-align: center; color: var(--text-muted); padding: 3.5rem 1rem;">
                    <div style="width: 48px; height: 48px; border-radius: 50%; background: #f1f5f9; color: #94a3b8; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.75rem;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                    </div>
                    <div style="font-weight: 600;">No serialized phones currently in stock.</div>
                    <div style="font-size: 0.8rem; margin-top: 0.25rem;">Register phones under <strong>Phone Inventory</strong> to start sales.</div>
                </div>
            @endforelse
        </div>

        <!-- Products / Accessories Tab -->
        <div id="tabProducts" class="pos-items-grid" style="display: none;">
            @forelse($products as $product)
                <div class="pos-item-card item-card" 
                     data-type="product" 
                     data-id="{{ $product->id }}" 
                     data-name="{{ $product->name }}" 
                     data-price="{{ $product->selling_price }}" 
                     data-barcode="{{ $product->barcode }}"
                     data-category="{{ $product->category->name ?? 'General' }}"
                     onclick="addProductToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->selling_price }}, {{ $product->stock_quantity }}, '{{ addslashes($product->category->name ?? 'Accessory') }}', '{{ $product->image_url }}')">
                    @if($product->image_url)
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" style="width: 100%; height: 80px; object-fit: cover; border-radius: 8px; margin-bottom: 0.5rem; border: 1px solid var(--surface-border);">
                    @endif
                    <div>
                        <span class="pos-item-brand" style="color: #4f46e5; background: #eef2ff;">{{ $product->category->name ?? 'Accessory' }}</span>
                        <div style="font-weight: 750; font-size: 0.9rem; margin-top: 0.35rem; color: #0f172a; line-height: 1.3;">{{ $product->name }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">
                            Stock: <strong>{{ $product->stock_quantity }}</strong> units
                        </div>
                    </div>
                    <div class="pos-item-price">
                        <span>LKR {{ number_format($product->selling_price, 2) }}</span>
                        <div class="pos-item-add-btn item-add-btn">+ Add</div>
                    </div>
                </div>
            @empty
                <div style="grid-column: 1/-1; text-align: center; color: var(--text-muted); padding: 3.5rem 1rem;">
                    <div style="width: 48px; height: 48px; border-radius: 50%; background: #f1f5f9; color: #94a3b8; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.75rem;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                    </div>
                    <div style="font-weight: 600;">No accessories or products in stock.</div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Right: Cart & Split Tender Checkout -->
    <div class="pos-cart">
        <div class="pos-cart-header">
            <span class="pos-cart-title">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 9px; background: #0f172a; color: #ffffff;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                </span>
                <span>Active Register Cart</span>
                <span id="cartCountBadge" style="display: none; background: #0f172a; color: #ffffff; font-size: 0.72rem; font-weight: 800; border-radius: 9999px; padding: 0.15rem 0.55rem; margin-left: 0.3rem;">0</span>
            </span>
            <button type="button" class="btn btn-sm btn-secondary" onclick="clearCart()" style="border-radius: 8px; color: #ef4444; font-size: 0.75rem; border-color: #fee2e2; background: #fff;">Clear Cart</button>
        </div>

        <!-- Customer selection -->
        <div style="padding: 0.65rem 1.15rem; border-bottom: 1px solid var(--surface-border); background: #ffffff; display: flex; align-items: center; gap: 0.6rem;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            <select id="customerSelect" class="form-control" style="height: 36px; border-radius: 8px; font-size: 0.825rem; border: 1px solid #e2e8f0; flex: 1;">
                <option value="">Walk-in Customer (Cash / Default)</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->phone }})</option>
                @endforeach
            </select>
        </div>

        <!-- Cart Items List (Mockup Card Style) -->
        <div class="pos-cart-items" id="cartContainer">
            <div id="emptyCartNotice" style="text-align: center; color: var(--text-muted); padding: 3rem 1rem;">
                <div style="width: 44px; height: 44px; border-radius: 50%; background: #f1f5f9; color: #94a3b8; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.5rem;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                </div>
                <div style="font-weight: 750; color: #334155; font-size: 0.9rem;">Cart is currently empty</div>
                <div style="font-size: 0.775rem; margin-top: 0.2rem; color: #94a3b8;">Click items on the left or scan a barcode to add.</div>
            </div>
            <div id="cartItemsList" style="display: flex; flex-direction: column; gap: 0.65rem;"></div>
        </div>

        <!-- Checkout Form & Split Payment (Streamlined & Space-Efficient) -->
        <div class="pos-cart-summary">
            <!-- Subtotal & Discount Row -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.65rem;">
                <div>
                    <div style="font-size: 0.7rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Subtotal</div>
                    <div id="lblSubtotal" style="font-size: 1.1rem; font-weight: 850; color: #0f172a; letter-spacing: -0.01em;">LKR 0.00</div>
                </div>
                <div style="display: flex; align-items: center; gap: 0.4rem;">
                    <label style="font-size: 0.72rem; color: #64748b; font-weight: 600; margin: 0;">Discount:</label>
                    <input type="number" id="inpDiscount" class="form-control" style="width: 85px; text-align: right; padding: 0.2rem 0.4rem; height: 32px; border-radius: 6px; font-weight: 700; font-size: 0.8rem;" value="0" min="0" oninput="recalc()">
                </div>
            </div>

            <!-- Compact Payment Tender Box -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 0.6rem 0.75rem; margin-bottom: 0.75rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                    <span style="font-size: 0.68rem; font-weight: 750; text-transform: uppercase; color: #64748b; letter-spacing: 0.04em;">Payment Tender (Split Allowed)</span>
                    <div style="display: flex; gap: 0.25rem;">
                        <button type="button" class="btn btn-sm btn-secondary" onclick="setExactCash()" style="padding: 1px 6px; font-size: 0.68rem; border-radius: 4px; font-weight: 700;">Exact</button>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="addCashPreset(1000)" style="padding: 1px 6px; font-size: 0.68rem; border-radius: 4px;">+1k</button>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="addCashPreset(5000)" style="padding: 1px 6px; font-size: 0.68rem; border-radius: 4px;">+5k</button>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="addCashPreset(10000)" style="padding: 1px 6px; font-size: 0.68rem; border-radius: 4px;">+10k</button>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.45rem;">
                    <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.25rem 0.45rem;">
                        <label style="font-size: 0.65rem; font-weight: 750; color: #166534; display: block; margin: 0;">💵 Cash</label>
                        <input type="number" id="payCash" placeholder="0.00" style="width: 100%; border: none; outline: none; font-size: 0.825rem; font-weight: 700; text-align: right; padding: 0;" oninput="updatePaymentRemaining()">
                    </div>
                    <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.25rem 0.45rem;">
                        <label style="font-size: 0.65rem; font-weight: 750; color: #1e40af; display: block; margin: 0;">💳 Card</label>
                        <input type="number" id="payCard" placeholder="0.00" style="width: 100%; border: none; outline: none; font-size: 0.825rem; font-weight: 700; text-align: right; padding: 0;" oninput="updatePaymentRemaining()">
                    </div>
                    <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.25rem 0.45rem;">
                        <label style="font-size: 0.65rem; font-weight: 750; color: #6b21a8; display: block; margin: 0;">🏦 Bank</label>
                        <input type="number" id="payBank" placeholder="0.00" style="width: 100%; border: none; outline: none; font-size: 0.825rem; font-weight: 700; text-align: right; padding: 0;" oninput="updatePaymentRemaining()">
                    </div>
                </div>
                <div id="paymentStatusNotice" style="font-size: 0.72rem; font-weight: 700; margin-top: 0.35rem; color: #b45309; text-align: center;">
                    Remaining to settle: LKR 0.00
                </div>
            </div>

            <!-- Pinned Checkout Action Bar like the Mockup -->
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 0.75rem;">
                <div>
                    <div style="font-size: 0.68rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Total Payable</div>
                    <div id="lblGrandTotal" style="font-size: 1.25rem; font-weight: 850; color: #0f172a; letter-spacing: -0.02em;">LKR 0.00</div>
                </div>
                <button type="button" id="btnSubmitSale" class="btn btn-primary" onclick="processCheckout()" style="flex: 1; height: 44px; border-radius: 12px; font-weight: 800; font-size: 0.925rem; background: #0f172a; border: none; display: flex; align-items: center; justify-content: center; gap: 0.5rem; box-shadow: 0 4px 14px rgba(15, 23, 42, 0.25); cursor: pointer; transition: all 0.2s ease;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span>Complete Sale &amp; Print</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Floating View Cart Button (Shows dynamically when items are added to cart) -->
<button type="button" id="floatingCartBtn" onclick="scrollToCart()" style="display: none; position: fixed; bottom: 24px; right: 24px; z-index: 999; background: #4f46e5; color: #ffffff; border: none; border-radius: 50px; padding: 0.75rem 1.35rem; font-weight: 800; font-size: 0.9rem; box-shadow: 0 8px 24px rgba(79, 70, 229, 0.45); cursor: pointer; align-items: center; gap: 0.65rem; transition: transform 0.2s ease, box-shadow 0.2s ease;">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
    <span>Cart (<span id="floatingCartCount">0</span>)</span>
    <span id="floatingCartTotal" style="background: rgba(255,255,255,0.22); padding: 2px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: 700;">LKR 0.00</span>
</button>

<!-- Receipt Modal -->
<div class="modal-backdrop" id="receiptModal">
    <div class="modal-box" style="max-width: 460px; font-family: 'JetBrains Mono', monospace; border-radius: 16px;">
        <div class="modal-header">
            <h3 style="font-weight: 800;">Invoice Receipt</h3>
            <button class="modal-close" onclick="closeModal('receiptModal')">&times;</button>
        </div>
        <div class="modal-body" id="receiptContent" style="font-size: 0.8125rem;">
            <!-- Dynamic Receipt populated by JS -->
        </div>
        <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; gap: 0.5rem; background: #fafbfc; border-radius: 0 0 16px 16px;">
            <a href="#" id="receiptFullViewBtn" target="_blank" class="btn btn-secondary" style="font-size: 0.8125rem; border-radius: 8px;">View Full Invoice</a>
            <button class="btn btn-primary" style="flex: 1; border-radius: 8px; font-weight: 700;" onclick="printPosReceipt()">Print Receipt</button>
            <button class="btn btn-secondary" onclick="closeModal('receiptModal')" style="border-radius: 8px;">Done</button>
        </div>
    </div>
</div>

<!-- Recent Sales Modal -->
<div class="modal-backdrop" id="recentSalesModal">
    <div class="modal-box" style="max-width: 740px; border-radius: 16px;">
        <div class="modal-header">
            <h3 style="font-weight: 800; display: flex; align-items: center; gap: 0.5rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                <span>Recent Sales &amp; Invoices</span>
            </h3>
            <button class="modal-close" onclick="closeModal('recentSalesModal')">&times;</button>
        </div>
        <div class="modal-body" style="padding: 0;">
            <div class="table-responsive" style="max-height: 440px; overflow-y: auto;">
                <table class="data-table" style="margin: 0;">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Time</th>
                            <th>Items &amp; IMEI</th>
                            <th>Total</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentSales as $sale)
                        <tr>
                            <td>
                                <strong style="color: #4f46e5; font-family: 'JetBrains Mono', monospace; font-size: 0.8125rem; background: #eef2ff; padding: 2px 6px; border-radius: 4px; border: 1px solid #c7d2fe;">
                                    {{ $sale->invoice_number }}
                                </strong>
                                <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 3px;">
                                    {{ $sale->customer->name ?? 'Walk-in Customer' }}
                                </div>
                            </td>
                            <td style="font-size: 0.75rem; color: var(--text-muted); white-space: nowrap;">
                                {{ \Carbon\Carbon::parse($sale->created_at)->format('h:i A') }}
                            </td>
                            <td>
                                <div style="font-size: 0.8125rem; max-width: 280px;">
                                    @foreach($sale->items as $it)
                                        <div style="margin-bottom: 2px;">
                                            <span>{{ $it->item_name }}</span>
                                            @if($it->phoneDevice)
                                                <span class="badge badge-info" style="font-size: 0.65rem; font-family: 'JetBrains Mono', monospace; padding: 0.05rem 0.3rem;">
                                                    IMEI: {{ $it->phoneDevice->imei_1 }}
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <strong style="color: #166534; font-size: 0.875rem;">LKR {{ number_format($sale->total_amount, 2) }}</strong>
                                <div style="font-size: 0.7rem; color: #64748b; text-transform: uppercase;">{{ $sale->payment_method }}</div>
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                <a href="{{ route('shop.sales.show', $sale) }}" target="_blank" class="btn btn-secondary btn-sm" style="padding: 0.25rem 0.55rem; font-size: 0.75rem; border-radius: 6px;">
                                    Details / Print
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2.5rem;">
                                No recent sales transactions found today.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: space-between; align-items: center; background: #fafbfc; border-radius: 0 0 16px 16px;">
            <a href="{{ route('shop.sales') }}" class="btn btn-secondary btn-sm" style="border-radius: 8px;">View Full Sales Directory</a>
            <button type="button" class="btn btn-primary btn-sm" onclick="closeModal('recentSalesModal')" style="border-radius: 8px;">Close</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let cart = [];

    function switchTab(tab, el) {
        document.querySelectorAll('.pos-tab').forEach(t => t.classList.remove('active'));
        el.classList.add('active');
        if (tab === 'phones') {
            document.getElementById('tabPhones').style.display = 'grid';
            document.getElementById('tabProducts').style.display = 'none';
        } else {
            document.getElementById('tabPhones').style.display = 'none';
            document.getElementById('tabProducts').style.display = 'grid';
        }
    }

    function filterItems() {
        const query = document.getElementById('searchInput').value.toLowerCase();
        const cat = document.getElementById('categoryFilter').value.toLowerCase();

        document.querySelectorAll('.item-card').forEach(card => {
            const name = card.dataset.name.toLowerCase();
            const imei = (card.dataset.imei || '').toLowerCase();
            const barcode = (card.dataset.barcode || '').toLowerCase();
            const category = (card.dataset.category || '').toLowerCase();

            const matchesQuery = name.includes(query) || imei.includes(query) || barcode.includes(query);
            const matchesCat = !cat || category.includes(cat);

            card.style.display = (matchesQuery && matchesCat) ? 'flex' : 'none';
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const searchInp = document.getElementById('searchInput');
        if (searchInp) {
            searchInp.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const val = this.value.trim().toLowerCase();
                    if (!val) return;

                    const visibleCards = Array.from(document.querySelectorAll('.item-card')).filter(c => c.style.display !== 'none');
                    if (visibleCards.length === 1) {
                        visibleCards[0].click();
                        this.value = '';
                        filterItems();
                    } else if (visibleCards.length > 1) {
                        const exact = visibleCards.find(c => (c.dataset.imei && c.dataset.imei.toLowerCase() === val) || (c.dataset.barcode && c.dataset.barcode.toLowerCase() === val));
                        if (exact) {
                            exact.click();
                            this.value = '';
                            filterItems();
                        }
                    }
                }
            });
        }
    });

    function addPhoneToCart(id, name, price, imei, brand, variant, image) {
        if (cart.some(item => item.phone_device_id === id)) {
            alert('This specific serialized phone (IMEI ' + imei + ') is already in your cart.');
            return;
        }
        cart.push({
            type: 'phone',
            phone_device_id: id,
            product_id: null,
            name: name,
            brand: brand || 'Mobile Phone',
            variant: variant || '',
            image: image || null,
            imei: imei,
            quantity: 1,
            unit_price: parseFloat(price)
        });
        showAddedToast(name);
        renderCart();
    }

    function addProductToCart(id, name, price, maxStock, brand, image) {
        const existing = cart.find(item => item.product_id === id);
        if (existing) {
            if (existing.quantity >= maxStock) {
                alert('Maximum available stock reached for this product (' + maxStock + ').');
                return;
            }
            existing.quantity++;
        } else {
            cart.push({
                type: 'product',
                product_id: id,
                phone_device_id: null,
                name: name,
                brand: brand || 'Accessory',
                variant: '',
                image: image || null,
                imei: null,
                quantity: 1,
                unit_price: parseFloat(price),
                max_stock: maxStock
            });
        }
        showAddedToast(name);
        renderCart();
    }

    function showAddedToast(itemName) {
        let toast = document.getElementById('posAddedToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'posAddedToast';
            toast.style.cssText = 'position: fixed; bottom: 85px; right: 24px; z-index: 1000; background: #0f172a; color: #ffffff; padding: 0.65rem 1rem; border-radius: 10px; font-size: 0.8125rem; font-weight: 600; display: flex; align-items: center; gap: 0.6rem; box-shadow: 0 10px 25px rgba(0,0,0,0.25); transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); opacity: 0; transform: translateY(10px); pointer-events: none;';
            document.body.appendChild(toast);
        }
        toast.innerHTML = `
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
            <span>Added to Cart: <strong style="color: #38bdf8;">${itemName}</strong></span>
        `;
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';

        if (window._toastTimeout) clearTimeout(window._toastTimeout);
        window._toastTimeout = setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(10px)';
        }, 1800);
    }

    function scrollToCart() {
        const cartEl = document.querySelector('.pos-cart');
        if (cartEl) {
            cartEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            cartEl.style.transition = 'box-shadow 0.3s ease';
            cartEl.style.boxShadow = '0 0 0 3px #4f46e5, 0 10px 25px rgba(79, 70, 229, 0.25)';
            setTimeout(() => {
                cartEl.style.boxShadow = '';
            }, 1200);
        }
    }

    function updateItemQuantity(index, delta) {
        const item = cart[index];
        if (!item) return;
        if (item.type === 'phone') return;

        const newQty = item.quantity + delta;
        if (newQty <= 0) {
            removeFromCart(index);
            return;
        }
        if (item.max_stock && newQty > item.max_stock) {
            alert('Maximum available stock reached for this product (' + item.max_stock + ').');
            return;
        }
        item.quantity = newQty;
        renderCart();
    }

    function removeFromCart(index) {
        cart.splice(index, 1);
        renderCart();
    }

    function clearCart() {
        cart = [];
        renderCart();
    }

    function renderCart() {
        const list = document.getElementById('cartItemsList');
        const emptyNotice = document.getElementById('emptyCartNotice');
        const badge = document.getElementById('cartCountBadge');

        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        const subtotal = cart.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
        const formattedTotal = 'LKR ' + subtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

        // Dynamic Top & Floating Cart Buttons
        const topCartBtn = document.getElementById('topCartBtn');
        const topCartCount = document.getElementById('topCartCount');
        const topCartTotal = document.getElementById('topCartTotal');

        const floatingCartBtn = document.getElementById('floatingCartBtn');
        const floatingCartCount = document.getElementById('floatingCartCount');
        const floatingCartTotal = document.getElementById('floatingCartTotal');

        if (totalItems > 0) {
            if (topCartBtn) topCartBtn.style.display = 'inline-flex';
            if (topCartCount) topCartCount.innerText = totalItems;
            if (topCartTotal) topCartTotal.innerText = formattedTotal;

            if (floatingCartBtn) floatingCartBtn.style.display = 'inline-flex';
            if (floatingCartCount) floatingCartCount.innerText = totalItems;
            if (floatingCartTotal) floatingCartTotal.innerText = formattedTotal;
        } else {
            if (topCartBtn) topCartBtn.style.display = 'none';
            if (floatingCartBtn) floatingCartBtn.style.display = 'none';
        }

        if (cart.length === 0) {
            list.innerHTML = '';
            emptyNotice.style.display = 'block';
            if (badge) badge.style.display = 'none';
        } else {
            emptyNotice.style.display = 'none';
            if (badge) {
                badge.innerText = totalItems + (totalItems === 1 ? ' item' : ' items');
                badge.style.display = 'inline-block';
            }

            list.innerHTML = cart.map((item, idx) => `
                <div class="pos-cart-item-card">
                    <!-- 1. Thumbnail Photo on Left (like Mockup) -->
                    <div style="width: 52px; height: 52px; border-radius: 10px; background: #f8fafc; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
                        ${item.image ? `
                            <img src="${item.image}" alt="${item.name}" style="width: 100%; height: 100%; object-fit: cover;">
                        ` : `
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.8">
                                ${item.type === 'phone' ? '<rect x="5" y="2" width="14" height="20" rx="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line>' : '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>'}
                            </svg>
                        `}
                    </div>

                    <!-- 2. Middle & Right Grid (like Mockup) -->
                    <div style="flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 0.35rem;">
                        <!-- Top Row: Brand & Model on left, Variant/IMEI on right -->
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.4rem;">
                            <div style="min-width: 0;">
                                <div style="font-size: 0.68rem; color: #64748b; font-weight: 750; text-transform: uppercase; letter-spacing: 0.04em;">${item.brand || 'Item'}</div>
                                <div style="font-size: 0.875rem; font-weight: 750; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${item.name}">${item.name}</div>
                            </div>
                            <div style="text-align: right; flex-shrink: 0;">
                                ${item.variant ? `<div style="font-size: 0.72rem; color: #475569; font-weight: 600;">${item.variant}</div>` : ''}
                                ${item.imei ? `<div style="font-size: 0.68rem; color: #0284c7; font-family: 'JetBrains Mono', monospace; font-weight: 700;">IMEI: ${item.imei}</div>` : ''}
                            </div>
                        </div>

                        <!-- Bottom Row: Bold Price on left, Stepper & Trash button on right -->
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.15rem;">
                            <div style="font-weight: 850; font-size: 0.95rem; color: #0f172a; letter-spacing: -0.01em;">
                                LKR ${(item.quantity * item.unit_price).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                            </div>

                            <div style="display: flex; align-items: center; gap: 0.45rem;">
                                <!-- Stepper Pill like Mockup -->
                                ${item.type === 'product' ? `
                                    <div style="display: inline-flex; align-items: center; border: 1.5px solid #e2e8f0; border-radius: 9999px; background: #f8fafc; padding: 2px;">
                                        <button type="button" onclick="updateItemQuantity(${idx}, -1)" style="width: 22px; height: 22px; border: none; background: #ffffff; border-radius: 50%; font-weight: 800; color: #475569; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.06); font-size: 0.85rem;" title="Decrease">−</button>
                                        <span style="min-width: 24px; text-align: center; font-weight: 800; font-size: 0.8rem; color: #0f172a;">${item.quantity}</span>
                                        <button type="button" onclick="updateItemQuantity(${idx}, 1)" style="width: 22px; height: 22px; border: none; background: #ffffff; border-radius: 50%; font-weight: 800; color: #475569; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.06); font-size: 0.85rem;" title="Increase">+</button>
                                    </div>
                                ` : `
                                    <div style="display: inline-flex; align-items: center; border: 1.5px solid #e2e8f0; border-radius: 9999px; background: #f8fafc; padding: 2px 8px; font-size: 0.7rem; font-weight: 750; color: #475569;">
                                        Qty: 1
                                    </div>
                                `}

                                <!-- Red Circular Trash Button like Mockup -->
                                <button type="button" onclick="removeFromCart(${idx})" style="width: 26px; height: 26px; border-radius: 50%; border: none; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.15s ease;" onmouseover="this.style.background='#ef4444'; this.style.color='#fff';" onmouseout="this.style.background='#fee2e2'; this.style.color='#dc2626';" title="Delete item">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        }
        recalc();
        updateCatalogBadges();
    }

    function updateCatalogBadges() {
        document.querySelectorAll('.item-card').forEach(card => {
            const type = card.dataset.type;
            const id = parseInt(card.dataset.id);
            const existing = cart.find(i => (type === 'phone' ? i.phone_device_id === id : i.product_id === id));
            
            let badgeEl = card.querySelector('.cart-in-badge');
            const addBtn = card.querySelector('.item-add-btn');

            if (existing) {
                card.style.borderColor = '#4f46e5';
                if (!badgeEl) {
                    badgeEl = document.createElement('div');
                    badgeEl.className = 'cart-in-badge';
                    badgeEl.style.cssText = 'position: absolute; top: 8px; right: 8px; background: #4f46e5; color: white; border-radius: 9999px; padding: 2px 7px; font-size: 0.68rem; font-weight: 800; z-index: 2; box-shadow: 0 2px 6px rgba(79, 70, 229, 0.4); pointer-events: none;';
                    card.appendChild(badgeEl);
                }
                badgeEl.textContent = type === 'phone' ? '✓ In Cart' : `In Cart (${existing.quantity})`;

                if (addBtn) {
                    if (type === 'phone') {
                        addBtn.innerHTML = '✓ In Cart';
                        addBtn.style.background = '#dcfce7';
                        addBtn.style.color = '#15803d';
                        addBtn.style.borderColor = '#86efac';
                    } else {
                        addBtn.innerHTML = '+ Add (' + existing.quantity + ')';
                        addBtn.style.background = '#e0e7ff';
                        addBtn.style.color = '#4338ca';
                        addBtn.style.borderColor = '#c7d2fe';
                    }
                }
            } else {
                card.style.borderColor = '';
                if (badgeEl) badgeEl.remove();

                if (addBtn) {
                    addBtn.innerHTML = '+ Add';
                    addBtn.style.background = '#f1f5f9';
                    addBtn.style.color = '#475569';
                    addBtn.style.borderColor = '#e2e8f0';
                }
            }
        });
    }

    function recalc() {
        const subtotal = cart.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
        const discount = parseFloat(document.getElementById('inpDiscount').value || 0);
        const total = Math.max(0, subtotal - discount);

        document.getElementById('lblSubtotal').innerText = 'LKR ' + subtotal.toFixed(2);
        document.getElementById('lblGrandTotal').innerText = 'LKR ' + total.toFixed(2);

        // Default cash payment to grand total if not manually split
        const cashInput = document.getElementById('payCash');
        const cardInput = document.getElementById('payCard');
        const bankInput = document.getElementById('payBank');

        if (!cardInput.value && !bankInput.value) {
            cashInput.value = total > 0 ? total.toFixed(2) : '';
        }

        updatePaymentRemaining();
    }

    function setExactCash() {
        const subtotal = cart.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
        const discount = parseFloat(document.getElementById('inpDiscount').value || 0);
        const total = Math.max(0, subtotal - discount);

        document.getElementById('payCash').value = total > 0 ? total.toFixed(2) : '';
        document.getElementById('payCard').value = '';
        document.getElementById('payBank').value = '';
        updatePaymentRemaining();
    }

    function addCashPreset(amount) {
        const cur = parseFloat(document.getElementById('payCash').value || 0);
        document.getElementById('payCash').value = (cur + amount).toFixed(2);
        updatePaymentRemaining();
    }

    function updatePaymentRemaining() {
        const subtotal = cart.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
        const discount = parseFloat(document.getElementById('inpDiscount').value || 0);
        const total = Math.max(0, subtotal - discount);

        const cash = parseFloat(document.getElementById('payCash').value || 0);
        const card = parseFloat(document.getElementById('payCard').value || 0);
        const bank = parseFloat(document.getElementById('payBank').value || 0);
        const tendered = cash + card + bank;
        const diff = total - tendered;

        const notice = document.getElementById('paymentStatusNotice');
        if (Math.abs(diff) < 0.01) {
            notice.innerHTML = '<span style="color: #166534; font-size: 0.85rem; font-weight: 700;">✓ Exact payment tendered: LKR ' + tendered.toLocaleString('en-US', {minimumFractionDigits: 2}) + '</span>';
        } else if (diff < 0) {
            const change = Math.abs(diff);
            notice.innerHTML = '<div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 8px; padding: 0.6rem 0.85rem; margin-top: 0.4rem; text-align: center;">' +
                '<span style="color: #065f46; font-size: 0.72rem; text-transform: uppercase; font-weight: 800; display: block; letter-spacing: 0.05em; margin-bottom: 0.2rem;">💵 Return Change To Customer</span>' +
                '<span style="color: #047857; font-size: 1.35rem; font-weight: 900; letter-spacing: -0.02em;">LKR ' + change.toLocaleString('en-US', {minimumFractionDigits: 2}) + '</span>' +
                '</div>';
        } else {
            notice.innerHTML = '<span style="color: #b45309; font-weight: 700;">Remaining to settle: LKR ' + diff.toLocaleString('en-US', {minimumFractionDigits: 2}) + '</span>';
        }
    }

    async function processCheckout() {
        if (cart.length === 0) {
            alert('Your cart is empty. Add items before checking out.');
            return;
        }

        const subtotal = cart.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
        const discount = parseFloat(document.getElementById('inpDiscount').value || 0);
        const total = Math.max(0, subtotal - discount);

        const cash = parseFloat(document.getElementById('payCash').value || 0);
        const card = parseFloat(document.getElementById('payCard').value || 0);
        const bank = parseFloat(document.getElementById('payBank').value || 0);
        const tendered = cash + card + bank;
        const changeDue = tendered > total ? (tendered - total) : 0;

        const payments = [];
        // If cash was tendered in excess, the recorded payment is capped at net amount
        const netCash = (cash > 0 && changeDue > 0) ? Math.max(0, cash - changeDue) : cash;
        if (netCash > 0) payments.push({ payment_method: 'cash', amount: netCash });
        if (card > 0) payments.push({ payment_method: 'card', amount: card });
        if (bank > 0) payments.push({ payment_method: 'bank_transfer', amount: bank });

        if (payments.length === 0) {
            alert('Please enter a payment amount in Cash, Card, or Bank Transfer.');
            return;
        }

        const btn = document.getElementById('btnSubmitSale');
        btn.disabled = true;
        btn.innerText = 'Processing Transaction...';

        const payload = {
            customer_id: document.getElementById('customerSelect').value || null,
            discount_amount: discount,
            items: cart.map(i => ({
                product_id: i.product_id,
                phone_device_id: i.phone_device_id,
                quantity: i.quantity,
                unit_price: i.unit_price
            })),
            payments: payments
        };

        try {
            const res = await fetch("{{ route('shop.pos.checkout') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();
            if (!res.ok) {
                throw new Error(data.message || 'Error processing transaction.');
            }

            // Show Receipt Modal with change details
            showReceipt(data.sale, cash, changeDue);
            clearCart();
        } catch (err) {
            alert(err.message);
        } finally {
            btn.disabled = false;
            btn.innerText = 'Complete Sale & Generate Invoice';
        }
    }

    function showReceipt(sale, cashTendered = 0, changeAmount = 0) {
        const rc = document.getElementById('receiptContent');
        const items = sale.items || [];
        const payments = sale.payments || [];

        const itemsHtml = items.map(item => `
            <div style="display:flex; justify-content:space-between; margin-bottom: 0.35rem; font-size: 0.8125rem;">
                <div style="padding-right: 0.5rem;">
                    <strong>${item.item_name || (item.product ? item.product.name : 'Item')}</strong> x${item.quantity}
                    ${item.phone_device ? `<div style="font-size: 0.7rem; color: #0284c7; font-family: 'JetBrains Mono', monospace;">IMEI: ${item.phone_device.imei_1}</div>` : ''}
                </div>
                <div style="font-weight: 700;">LKR ${parseFloat(item.subtotal || (item.quantity * item.unit_price)).toFixed(2)}</div>
            </div>
        `).join('');

        const paymentsHtml = payments.map(p => `
            <div style="display:flex; justify-content:space-between; font-size: 0.75rem; color: #475569;">
                <span>${p.payment_method.toUpperCase().replace('_', ' ')}:</span>
                <span>LKR ${parseFloat(p.amount).toFixed(2)}</span>
            </div>
        `).join('');

        rc.innerHTML = `
            <div id="posReceiptPrintArea">
                <div style="text-align: center; margin-bottom: 0.75rem; border-bottom: 1px dashed #cbd5e1; padding-bottom: 0.75rem;">
                    <h4 style="margin: 0; font-size: 1.15rem; font-weight: 800;">{{ auth()->user()->shop->name ?? 'Phone Shop' }}</h4>
                    <div style="font-size: 0.75rem; color: #64748b;">Official POS Tax Invoice &amp; Receipt</div>
                    <div style="font-size: 0.8125rem; font-weight: 700; color: #4f46e5; margin-top: 0.35rem; font-family: 'JetBrains Mono', monospace;">Invoice: ${sale.invoice_number}</div>
                    <div style="font-size: 0.75rem; color: #64748b;">Date: ${sale.sale_date} | Cashier: ${sale.cashier ? sale.cashier.name : '{{ auth()->user()->name }}'}</div>
                    ${sale.customer ? `<div style="font-size: 0.75rem; color: #0f172a; margin-top: 0.2rem;">Customer: <strong>${sale.customer.name}</strong> (${sale.customer.phone})</div>` : '<div style="font-size: 0.75rem; color: #64748b; font-style: italic;">Walk-in Customer</div>'}
                </div>
                <div style="margin-bottom: 0.75rem;">
                    ${itemsHtml}
                </div>
                <div style="border-top: 1px dashed #cbd5e1; padding-top: 0.5rem; margin-bottom: 0.75rem;">
                    <div style="display:flex; justify-content:space-between; font-size: 0.8125rem;">
                        <span style="color: #64748b;">Subtotal:</span>
                        <span>LKR ${parseFloat(sale.subtotal).toFixed(2)}</span>
                    </div>
                    ${sale.discount_amount > 0 ? `
                        <div style="display:flex; justify-content:space-between; font-size: 0.8125rem; color: #b91c1c;">
                            <span>Discount:</span>
                            <span>-LKR ${parseFloat(sale.discount_amount).toFixed(2)}</span>
                        </div>
                    ` : ''}
                    <div style="display:flex; justify-content:space-between; font-weight:800; font-size: 1.1rem; color: #0f172a; border-top: 1px solid #e2e8f0; padding-top: 0.35rem; margin-top: 0.35rem;">
                        <span>Grand Total:</span>
                        <span style="color: #166534;">LKR ${parseFloat(sale.total_amount).toFixed(2)}</span>
                    </div>
                </div>
                <div style="background: #f8fafc; border-radius: 6px; padding: 0.5rem 0.75rem; margin-bottom: 0.75rem;">
                    ${paymentsHtml}
                    ${changeAmount > 0 ? `
                        <div style="display:flex; justify-content:space-between; font-size: 0.8125rem; font-weight: 800; color: #047857; border-top: 1px dashed #cbd5e1; padding-top: 0.35rem; margin-top: 0.35rem;">
                            <span>Change Returned:</span>
                            <span>LKR ${parseFloat(changeAmount).toFixed(2)}</span>
                        </div>
                    ` : ''}
                </div>
                <div style="font-size: 0.72rem; color: #64748b; text-align: center; margin-top: 0.75rem;">
                    Thank you for your purchase!<br>
                    Official Warranty applies per serial/IMEI record.
                </div>
            </div>
        `;
        document.getElementById('receiptFullViewBtn').href = `/shop/sales/${sale.id}`;
        openModal('receiptModal');
    }

    function printPosReceipt() {
        const printContent = document.getElementById('posReceiptPrintArea');
        if (!printContent) return;

        const printWindow = window.open('', '', 'width=450,height=600');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Receipt</title>
                    <style>
                        body { font-family: 'JetBrains Mono', monospace, Courier; margin: 15px; font-size: 12px; }
                        @media print { body { margin: 0; } }
                    </style>
                </head>
                <body>
                    ${printContent.innerHTML}
                </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 300);
    }
</script>
@endpush
@endsection
