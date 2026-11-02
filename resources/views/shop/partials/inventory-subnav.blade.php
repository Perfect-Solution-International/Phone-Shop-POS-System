<!-- Inventory & Stock Unified Sub-Navigation Tabs -->
<div class="shop-subnav-tabs no-print" style="display: flex; gap: 0.45rem; margin-bottom: 1rem; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.75rem; overflow-x: auto; align-items: center;">
    <a href="{{ route('shop.phones') }}" class="btn btn-sm {{ request()->routeIs('shop.phones*') && request('status') !== 'sold' ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: 8px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.45rem 0.85rem; text-decoration: none; white-space: nowrap;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="5" y="2" width="14" height="20" rx="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
        <span>Dual IMEI Phones</span>
    </a>
    <a href="{{ route('shop.products') }}" class="btn btn-sm {{ request()->routeIs('shop.products*') ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: 8px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.45rem 0.85rem; text-decoration: none; white-space: nowrap;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
        <span>Products &amp; Accessories</span>
    </a>
    <a href="{{ route('shop.spare-parts') }}" class="btn btn-sm {{ request()->routeIs('shop.spare-parts*') ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: 8px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.45rem 0.85rem; text-decoration: none; white-space: nowrap;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>
        <span>Spare Parts</span>
    </a>
    <a href="{{ route('shop.trade-ins.index') }}" class="btn btn-sm {{ request()->routeIs('shop.trade-ins*') ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: 8px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.45rem 0.85rem; text-decoration: none; white-space: nowrap;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
        <span>Used Trade-Ins</span>
    </a>
    <a href="{{ route('shop.labels.index') }}" class="btn btn-sm {{ request()->routeIs('shop.labels*') ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: 8px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.45rem 0.85rem; text-decoration: none; white-space: nowrap;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
        <span>Barcode &amp; IMEI Labels</span>
    </a>
    <a href="{{ route('shop.phones', ['status' => 'sold']) }}" class="btn btn-sm {{ request()->routeIs('shop.phones*') && request('status') === 'sold' ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: 8px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.45rem 0.85rem; text-decoration: none; white-space: nowrap;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
        <span>Sold Phones Archive</span>
    </a>
    <a href="{{ route('shop.purchases') }}" class="btn btn-sm {{ request()->routeIs('shop.purchases*') || request()->routeIs('shop.inventory.grns*') || request()->routeIs('shop.suppliers*') ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: 8px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.45rem 0.85rem; text-decoration: none; white-space: nowrap;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
        <span>Purchases &amp; Suppliers</span>
    </a>
</div>

@if(request()->routeIs('shop.purchases*') || request()->routeIs('shop.inventory.grns*') || request()->routeIs('shop.suppliers*'))
<div class="shop-subnav-secondary no-print" style="display: flex; gap: 0.45rem; margin-bottom: 1.1rem; padding: 0.4rem 0.65rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; align-items: center; flex-wrap: wrap;">
    <span style="font-size: 0.72rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin-right: 0.35rem; letter-spacing: 0.04em;">Purchasing &amp; Vendors:</span>
    <a href="{{ route('shop.purchases') }}" class="btn btn-sm {{ request()->routeIs('shop.purchases*') ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: 6px; font-size: 0.78rem; font-weight: 650; padding: 0.3rem 0.75rem; text-decoration: none;">
        Inbound Purchases
    </a>
    <a href="{{ route('shop.inventory.grns') }}" class="btn btn-sm {{ request()->routeIs('shop.inventory.grns*') ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: 6px; font-size: 0.78rem; font-weight: 650; padding: 0.3rem 0.75rem; text-decoration: none;">
        Goods Received Notes (GRN)
    </a>
    <a href="{{ route('shop.suppliers') }}" class="btn btn-sm {{ request()->routeIs('shop.suppliers*') ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: 6px; font-size: 0.78rem; font-weight: 650; padding: 0.3rem 0.75rem; text-decoration: none;">
        Suppliers Directory
    </a>
</div>
@endif
