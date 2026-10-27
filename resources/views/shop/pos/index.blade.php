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
        grid-template-columns: 1.35fr 1fr;
        gap: 1.5rem;
        height: calc(100vh - 190px);
        min-height: 580px;
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
        width: 24px;
        height: 24px;
        border-radius: 6px;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #475569;
        font-weight: 800;
        font-size: 1rem;
        transition: all 0.15s ease;
    }

    .pos-item-card:hover .pos-item-add-btn {
        background: #4f46e5;
        color: #ffffff;
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
    }

    .pos-cart-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--surface-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fafbfc;
    }

    .pos-cart-title {
        font-size: 0.95rem;
        font-weight: 800;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 0.45rem;
    }

    .pos-cart-items {
        flex: 1;
        overflow-y: auto;
        padding: 0.75rem 1.25rem;
        background: #ffffff;
    }

    .pos-cart-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f1f5f9;
        gap: 0.5rem;
    }

    .pos-cart-row:last-child {
        border-bottom: none;
    }

    .pos-cart-summary {
        padding: 1.25rem 1.35rem;
        border-top: 1px solid var(--surface-border);
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
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
                     onclick="addPhoneToCart({{ $phone->id }}, '{{ addslashes($phone->brand.' '.$phone->model) }}', {{ $phone->selling_price }}, '{{ $phone->imei_1 }}')">
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
                        <div class="pos-item-add-btn">+</div>
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
                     onclick="addProductToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->selling_price }}, {{ $product->stock_quantity }})">
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
                        <div class="pos-item-add-btn">+</div>
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
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                <span>Active Register Cart</span>
            </span>
            <button class="btn btn-sm btn-secondary" onclick="clearCart()" style="border-radius: 8px; color: #ef4444; font-size: 0.75rem;">Clear Cart</button>
        </div>

        <!-- Customer selection -->
        <div style="padding: 0.85rem 1.25rem; border-bottom: 1px solid var(--surface-border); background: #fdfdfe;">
            <label style="font-size: 0.72rem; font-weight: 700; color: #64748b; display: block; margin-bottom: 0.35rem; text-transform: uppercase;">Customer Selection</label>
            <select id="customerSelect" class="form-control" style="height: 38px; border-radius: 8px; font-size: 0.85rem;">
                <option value="">Walk-in Customer (Cash / Default)</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->phone }})</option>
                @endforeach
            </select>
        </div>

        <!-- Cart Items List -->
        <div class="pos-cart-items" id="cartContainer">
            <div id="emptyCartNotice" style="text-align: center; color: var(--text-muted); padding: 3rem 1rem;">
                <div style="width: 44px; height: 44px; border-radius: 50%; background: #f1f5f9; color: #94a3b8; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.75rem;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                </div>
                <div style="font-weight: 600; color: #475569;">Cart is currently empty</div>
                <div style="font-size: 0.78rem; margin-top: 0.25rem;">Scan a barcode or click items on the left to add them to this sale.</div>
            </div>
            <div id="cartItemsList"></div>
        </div>

        <!-- Checkout Form & Split Payment -->
        <div class="pos-cart-summary">
            <div style="display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 0.35rem;">
                <span style="color: var(--text-muted);">Subtotal</span>
                <span id="lblSubtotal" style="font-weight: 700; color: #1e293b;">LKR 0.00</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem; margin-bottom: 0.45rem;">
                <span style="color: var(--text-muted);">Discount (LKR)</span>
                <input type="number" id="inpDiscount" class="form-control" style="width: 110px; text-align: right; padding: 0.25rem 0.5rem; height: 32px; border-radius: 6px; font-weight: 600;" value="0" min="0" oninput="recalc()">
            </div>
            
            <div class="summary-total-banner">
                <span style="font-size: 0.95rem; font-weight: 800; color: #0f172a;">Total Payable</span>
                <span id="lblGrandTotal" style="font-size: 1.35rem; font-weight: 800; color: #4f46e5; letter-spacing: -0.02em;">LKR 0.00</span>
            </div>

            <!-- Split Payments Entry -->
            <label style="font-size: 0.72rem; font-weight: 750; text-transform: uppercase; color: #64748b; display: block; letter-spacing: 0.05em;">Payment Tender (Split Allowed)</label>
            <div class="split-row">
                <div class="split-tender-box">
                    <label style="color: #166534;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                        <span>Cash</span>
                    </label>
                    <input type="number" id="payCash" placeholder="0.00" oninput="updatePaymentRemaining()">
                </div>
                <div class="split-tender-box">
                    <label style="color: #1e40af;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                        <span>Card</span>
                    </label>
                    <input type="number" id="payCard" placeholder="0.00" oninput="updatePaymentRemaining()">
                </div>
                <div class="split-tender-box">
                    <label style="color: #6b21a8;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                        <span>Bank / QR</span>
                    </label>
                    <input type="number" id="payBank" placeholder="0.00" oninput="updatePaymentRemaining()">
                </div>
            </div>

            <!-- Quick Cash Presets -->
            <div class="quick-cash-row">
                <button type="button" class="quick-cash-btn" onclick="setExactCash()">Exact</button>
                <button type="button" class="quick-cash-btn" onclick="addCashPreset(1000)">+1,000</button>
                <button type="button" class="quick-cash-btn" onclick="addCashPreset(5000)">+5,000</button>
                <button type="button" class="quick-cash-btn" onclick="addCashPreset(10000)">+10,000</button>
            </div>

            <div id="paymentStatusNotice" style="font-size: 0.78rem; font-weight: 700; margin-top: 0.65rem; color: #b45309; text-align: center;">
                Remaining to settle: LKR 0.00
            </div>

            <button type="button" id="btnSubmitSale" class="btn btn-primary" style="width: 100%; margin-top: 0.75rem; padding: 0.85rem; border-radius: 10px; font-weight: 800; font-size: 0.95rem; box-shadow: 0 4px 14px rgba(79, 70, 229, 0.4);" onclick="processCheckout()">
                Complete Sale &amp; Generate Invoice
            </button>
        </div>
    </div>
</div>

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

    function addPhoneToCart(id, name, price, imei) {
        if (cart.some(item => item.phone_device_id === id)) {
            alert('This specific serialized phone (IMEI ' + imei + ') is already in your cart.');
            return;
        }
        cart.push({
            type: 'phone',
            phone_device_id: id,
            product_id: null,
            name: name,
            imei: imei,
            quantity: 1,
            unit_price: parseFloat(price)
        });
        renderCart();
    }

    function addProductToCart(id, name, price, maxStock) {
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
                imei: null,
                quantity: 1,
                unit_price: parseFloat(price)
            });
        }
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

        if (cart.length === 0) {
            list.innerHTML = '';
            emptyNotice.style.display = 'block';
        } else {
            emptyNotice.style.display = 'none';
            list.innerHTML = cart.map((item, idx) => `
                <div class="pos-cart-row">
                    <div style="flex: 1; padding-right: 0.5rem;">
                        <div style="font-weight: 700; font-size: 0.85rem; color: #0f172a;">${item.name}</div>
                        ${item.imei ? `<div style="font-size: 0.7rem; color: #0284c7; font-family: 'JetBrains Mono', monospace; font-weight: 600;">IMEI: ${item.imei}</div>` : ''}
                        <div style="font-size: 0.75rem; color: var(--text-muted);">${item.quantity} x LKR ${item.unit_price.toFixed(2)}</div>
                    </div>
                    <div style="font-weight: 800; font-size: 0.9rem; margin-right: 0.75rem; color: #1e293b;">
                        LKR ${(item.quantity * item.unit_price).toFixed(2)}
                    </div>
                    <button type="button" onclick="removeFromCart(${idx})" style="background:none; border:none; color: #ef4444; font-size: 1.25rem; cursor:pointer; line-height: 1; padding: 2px 6px;">&times;</button>
                </div>
            `).join('');
        }
        recalc();
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
            notice.innerText = '✓ Exact payment tendered (LKR ' + tendered.toFixed(2) + ')';
            notice.style.color = '#166534';
        } else if (diff < 0) {
            notice.innerText = 'Change due to customer: LKR ' + Math.abs(diff).toFixed(2);
            notice.style.color = '#0284c7';
        } else {
            notice.innerText = 'Remaining to settle: LKR ' + diff.toFixed(2);
            notice.style.color = '#b45309';
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

        const payments = [];
        if (cash > 0) payments.push({ payment_method: 'cash', amount: cash });
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

            // Show Receipt Modal
            showReceipt(data.sale);
            clearCart();
        } catch (err) {
            alert(err.message);
        } finally {
            btn.disabled = false;
            btn.innerText = 'Complete Sale & Generate Invoice';
        }
    }

    function showReceipt(sale) {
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
