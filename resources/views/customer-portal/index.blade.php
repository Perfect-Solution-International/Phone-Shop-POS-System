@extends('layouts.customer')

@section('title', 'My Customer Portal')

@section('content')
<style>
    .portal-metrics {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    .p-metric {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        border-left: 4px solid var(--brand-primary);
    }

    .p-metric.cyan { border-left-color: #06b6d4; }
    .p-metric.green { border-left-color: var(--color-success); }
    .p-metric.amber { border-left-color: var(--color-warning); }

    .p-metric-label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted);
    }

    .p-metric-val {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--text-heading);
    }

    .portal-tabs {
        display: flex;
        gap: 0.5rem;
        border-bottom: 1px solid var(--border-color);
        margin-bottom: 1.5rem;
    }

    .portal-tab-btn {
        padding: 0.75rem 1.25rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-muted);
        background: none;
        border: none;
        border-bottom: 2px solid transparent;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .portal-tab-btn.active {
        color: var(--brand-primary);
        border-bottom-color: var(--brand-primary);
    }

    .tab-badge {
        background: #e0e7ff;
        color: var(--brand-primary);
        font-size: 0.7rem;
        padding: 0.15rem 0.5rem;
        border-radius: 9999px;
    }

    /* Modal */
    .modal-backdrop {
        display: none;
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(15, 23, 42, 0.6);
        z-index: 100;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .modal-backdrop.open { display: flex; }

    .modal-box {
        background: #ffffff;
        border-radius: 12px;
        max-width: 480px;
        width: 100%;
        padding: 1.5rem;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    }
</style>

<!-- Top Welcome Banner -->
<div style="background: linear-gradient(135deg, #4f46e5 0%, #06b6d4 100%); border-radius: 14px; padding: 1.75rem 2rem; color: #ffffff; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 0.25rem;">Welcome back, {{ auth()->user()->name }}!</h1>
        <p style="font-size: 0.875rem; opacity: 0.9;">Manage your device repairs, active warranties, and purchase invoices directly from your personal account.</p>
    </div>
    <div>
        <a href="{{ route('public.repair-track') }}" target="_blank" class="btn" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.4);">
            Public IMEI Search &rarr;
        </a>
    </div>
</div>

<!-- Metrics Cards -->
<div class="portal-metrics">
    <div class="p-metric">
        <span class="p-metric-label">Active Repairs</span>
        <span class="p-metric-val">{{ $metrics['active_repairs'] }}</span>
        <span style="font-size: 0.75rem; color: var(--text-muted);">Workshop tickets in progress</span>
    </div>

    <div class="p-metric cyan">
        <span class="p-metric-label">Active Warranties</span>
        <span class="p-metric-val">{{ $metrics['active_warranties'] }}</span>
        <span style="font-size: 0.75rem; color: var(--text-muted);">Protected mobile devices</span>
    </div>

    <div class="p-metric green">
        <span class="p-metric-label">Purchase Invoices</span>
        <span class="p-metric-val">{{ $metrics['total_invoices'] }}</span>
        <span style="font-size: 0.75rem; color: var(--text-muted);">Official shop sales receipts</span>
    </div>

    <div class="p-metric amber">
        <span class="p-metric-label">Account Balance</span>
        <span class="p-metric-val" style="font-size: 1.35rem; color: {{ $metrics['balance'] > 0 ? '#b91c1c' : '#15803d' }};">
            LKR {{ number_format($metrics['balance'], 2) }}
        </span>
        <span style="font-size: 0.75rem; color: var(--text-muted);">{{ $metrics['balance'] > 0 ? 'Payment due to shop' : 'No outstanding dues' }}</span>
    </div>
</div>

<!-- Navigation Tabs -->
<div class="portal-tabs">
    <button type="button" class="portal-tab-btn active" onclick="showTab('repairs', this)">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>
        <span>My Repair Jobs</span>
        <span class="tab-badge">{{ $repairs->count() }}</span>
    </button>
    <button type="button" class="portal-tab-btn" onclick="showTab('warranties', this)">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
        <span>Device Warranties</span>
        <span class="tab-badge">{{ $warranties->count() }}</span>
    </button>
    <button type="button" class="portal-tab-btn" onclick="showTab('invoices', this)">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
        <span>Invoices & Receipts</span>
        <span class="tab-badge">{{ $sales->count() }}</span>
    </button>
    <button type="button" class="portal-tab-btn" onclick="showTab('store', this)">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
        <span>Buy Devices & Accessories</span>
        <span class="tab-badge" style="background: #dcfce7; color: #166534;">{{ $availablePhones->count() + $availableProducts->count() }} In Stock</span>
    </button>
</div>

<!-- Tab 1: Repairs -->
<div id="tabContentRepairs" class="card">
    <div class="card-header">
        <span class="card-title">My Phone Repair Workshop Tickets</span>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Job Number</th>
                    <th>Device</th>
                    <th>Reported Problem</th>
                    <th>Current Stage</th>
                    <th>Estimated Ready</th>
                    <th>Total / Advance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($repairs as $r)
                <tr>
                    <td>
                        <span style="font-family: monospace; font-weight: 700; color: #4f46e5;">{{ $r->job_number }}</span>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Booked: {{ $r->created_at->toFormattedDateString() }}</div>
                    </td>
                    <td>
                        <strong>{{ $r->brand }} {{ $r->model }}</strong>
                        @if($r->imei)
                            <div style="font-family: monospace; font-size: 0.75rem; color: #64748b;">IMEI: {{ $r->imei }}</div>
                        @endif
                    </td>
                    <td style="max-width: 250px;">
                        <span style="font-size: 0.8125rem;">{{ $r->fault_description }}</span>
                    </td>
                    <td>
                        @php
                            $badgeClass = match($r->status) {
                                'delivered' => 'badge-success',
                                'ready_for_collection' => 'badge-info',
                                'in_repair', 'diagnosing' => 'badge-purple',
                                'waiting_for_parts', 'waiting_for_approval' => 'badge-warning',
                                default => 'badge-secondary',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}" style="font-size: 0.8rem; padding: 0.3rem 0.7rem;">
                            {{ ucwords(str_replace('_', ' ', $r->status)) }}
                        </span>
                        @if($r->status === 'ready_for_collection')
                            <div style="font-size: 0.7rem; color: #166534; font-weight: 700; margin-top: 4px;">Ready for Pickup!</div>
                        @elseif($r->status === 'waiting_for_approval')
                            <div style="margin-top: 6px;">
                                <form method="POST" action="{{ route('customer.repairs.estimate.approve', $r->id) }}" style="display:inline-block;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary" style="padding: 0.2rem 0.55rem; font-size: 0.72rem; font-weight: 700;">
                                        ✓ Approve Estimate
                                    </button>
                                </form>
                            </div>
                        @endif
                    </td>
                    <td>
                        @if($r->estimated_completion_date)
                            <div style="font-size: 0.8125rem; font-weight: 600; color: #0369a1;">{{ $r->estimated_completion_date->toFormattedDateString() }}</div>
                        @else
                            <span style="font-size: 0.75rem; color: var(--text-muted);">In diagnosis</span>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight: 700;">LKR {{ number_format($r->final_cost ?? $r->estimated_cost, 2) }}</div>
                        @if($r->advance_amount > 0)
                            <div style="font-size: 0.75rem; color: #166534;">Adv: LKR {{ number_format($r->advance_amount, 2) }}</div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 3rem;">
                        You have no repair jobs registered with the workshop yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Tab 2: Warranties -->
<div id="tabContentWarranties" class="card" style="display: none;">
    <div class="card-header">
        <span class="card-title">My Device Warranty Certificates</span>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Certificate Number</th>
                    <th>IMEI / Serial</th>
                    <th>Warranty Type</th>
                    <th>Coverage Period</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($warranties as $w)
                <tr>
                    <td>
                        <span style="font-family: monospace; font-weight: 700; color: #4f46e5;">{{ $w->warranty_number }}</span>
                    </td>
                    <td>
                        <span style="font-family: monospace; font-weight: 600; color: #0284c7;">{{ $w->imei ?? 'Standard Item' }}</span>
                    </td>
                    <td>
                        <span style="text-transform: uppercase; font-size: 0.75rem; font-weight: 600; color: #475569;">{{ $w->warranty_type }} warranty</span>
                    </td>
                    <td>
                        <div style="font-size: 0.8125rem;">{{ $w->starts_on->toFormattedDateString() }} &rarr; <strong>{{ $w->ends_on->toFormattedDateString() }}</strong></div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $w->duration_months }} Months duration</div>
                    </td>
                    <td>
                        @if($w->isValid())
                            <span class="badge badge-success">Active & Protected</span>
                        @else
                            <span class="badge badge-danger">Expired</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 3rem;">
                        No warranty certificates found under your account.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Tab 3: Invoices & Receipts -->
<div id="tabContentInvoices" class="card" style="display: none;">
    <div class="card-header">
        <span class="card-title">My Purchase Invoices & Receipts</span>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Date</th>
                    <th>Items Purchased</th>
                    <th>Payment Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $s)
                <tr>
                    <td>
                        <span style="font-family: monospace; font-weight: 700; color: #4f46e5;">{{ $s->invoice_number }}</span>
                    </td>
                    <td>
                        <div style="font-size: 0.8125rem;">{{ $s->created_at->toFormattedDateString() }}</div>
                    </td>
                    <td>
                        <div style="font-size: 0.8125rem;">
                            @foreach($s->items as $item)
                                <div>• {{ $item->item_name }} (x{{ $item->quantity }})</div>
                            @endforeach
                        </div>
                    </td>
                    <td>
                        <strong style="color: var(--text-heading); font-size: 0.9375rem;">LKR {{ number_format($s->total_amount, 2) }}</strong>
                    </td>
                    <td>
                        <span class="badge badge-success">{{ ucfirst($s->payment_status) }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 3rem;">
                        No purchase invoices recorded under your account yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Tab 4: Shop Store (Buy Phones & Accessories Online) -->
<div id="tabContentStore" class="card" style="display: none;">
    <div class="card-header" style="flex-wrap: wrap; gap: 0.5rem; justify-content: space-between;">
        <div>
            <span class="card-title" style="font-size: 1.1rem; font-weight: 800;">Available Devices & Accessories</span>
            <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">
                Direct online purchasing. Stock is reserved and deducted immediately upon purchase.
            </div>
        </div>
        <div style="display: flex; gap: 0.4rem;">
            <button type="button" class="btn btn-sm btn-secondary" onclick="filterStore('all')" id="btnStoreAll" style="font-weight: 700;">All Items</button>
            <button type="button" class="btn btn-sm btn-secondary" onclick="filterStore('phone')" id="btnStorePhones" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="5" y="2" width="14" height="20" rx="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                <span>Mobile Phones</span>
            </button>
            <button type="button" class="btn btn-sm btn-secondary" onclick="filterStore('accessory')" id="btnStoreAcc" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                <span>Accessories</span>
            </button>
        </div>
    </div>

    <!-- Store Items Grid -->
    <div style="padding: 1.5rem; display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1.25rem;">
        @forelse($availablePhones as $phone)
            <div class="store-item-card" data-category="phone" style="border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 1.15rem; background: #ffffff; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 2px 6px rgba(0,0,0,0.03); transition: transform 0.15s ease;">
                <div>
                    @if($phone->image_url)
                        <img src="{{ $phone->image_url }}" alt="{{ $phone->brand }} {{ $phone->model }}" style="width: 100%; height: 130px; object-fit: cover; border-radius: 8px; margin-bottom: 0.75rem; border: 1px solid #e2e8f0;">
                    @else
                        <div style="width: 100%; height: 110px; border-radius: 8px; background: #f8fafc; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center; margin-bottom: 0.75rem; color: #94a3b8;">
                            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="2" width="14" height="20" rx="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                        </div>
                    @endif
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem;">
                        <span style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: #4f46e5; background: #eef2ff; padding: 2px 8px; border-radius: 6px;">{{ $phone->brand }}</span>
                        <span class="badge badge-success" style="font-size: 0.68rem;">In Stock</span>
                    </div>
                    <h3 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 0.5rem 0 0.25rem;">{{ $phone->model }}</h3>
                    <div style="font-size: 0.8rem; color: #64748b; margin-bottom: 0.5rem;">
                        {{ $phone->color }} • {{ $phone->storage }} • <span style="text-transform: capitalize;">{{ $phone->condition }}</span>
                    </div>
                    <div style="font-family: monospace; font-size: 0.72rem; color: #0284c7; background: #f0f9ff; padding: 3px 6px; border-radius: 4px; border: 1px solid #bae6fd; display: inline-block;">
                        IMEI: {{ $phone->imei_1 }}
                    </div>
                </div>

                <div style="margin-top: 1.25rem; padding-top: 0.85rem; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 0.68rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Price</div>
                        <div style="font-size: 1.15rem; font-weight: 850; color: #166534;">LKR {{ number_format($phone->selling_price, 2) }}</div>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm" onclick="openBuyModal('phone', {{ $phone->id }}, '{{ addslashes($phone->brand.' '.$phone->model) }} (IMEI: {{ $phone->imei_1 }})', {{ $phone->selling_price }}, 1)" style="font-weight: 700; padding: 0.45rem 1rem; border-radius: 8px; display: inline-flex; align-items: center; gap: 0.35rem;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                        <span>Buy Now</span>
                    </button>
                </div>
            </div>
        @empty
        @endforelse

        @forelse($availableProducts as $product)
            <div class="store-item-card" data-category="accessory" style="border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 1.15rem; background: #ffffff; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 2px 6px rgba(0,0,0,0.03); transition: transform 0.15s ease;">
                <div>
                    @if($product->image_url)
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" style="width: 100%; height: 130px; object-fit: cover; border-radius: 8px; margin-bottom: 0.75rem; border: 1px solid #e2e8f0;">
                    @else
                        <div style="width: 100%; height: 110px; border-radius: 8px; background: #f8fafc; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center; margin-bottom: 0.75rem; color: #94a3b8;">
                            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                        </div>
                    @endif
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem;">
                        <span style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: #0891b2; background: #ecfeff; padding: 2px 8px; border-radius: 6px;">{{ $product->category->name ?? 'Accessory' }}</span>
                        <span class="badge badge-success" style="font-size: 0.68rem;">{{ $product->stock_quantity }} Available</span>
                    </div>
                    <h3 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 0.5rem 0 0.25rem;">{{ $product->name }}</h3>
                    <div style="font-size: 0.8rem; color: #64748b; margin-bottom: 0.5rem;">
                        SKU: {{ $product->sku ?? 'N/A' }} • Barcode: {{ $product->barcode }}
                    </div>
                </div>

                <div style="margin-top: 1.25rem; padding-top: 0.85rem; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 0.68rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Price</div>
                        <div style="font-size: 1.15rem; font-weight: 850; color: #166534;">LKR {{ number_format($product->selling_price, 2) }}</div>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm" onclick="openBuyModal('product', {{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->selling_price }}, {{ $product->stock_quantity }})" style="font-weight: 700; padding: 0.45rem 1rem; border-radius: 8px; display: inline-flex; align-items: center; gap: 0.35rem;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                        <span>Buy Now</span>
                    </button>
                </div>
            </div>
        @empty
        @endforelse

        @if($availablePhones->isEmpty() && $availableProducts->isEmpty())
            <div style="grid-column: 1/-1; text-align: center; color: var(--text-muted); padding: 4rem 1rem;">
                <div style="margin-bottom: 0.75rem; display: flex; justify-content: center;">
                    <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" style="color: #94a3b8;"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                </div>
                <div style="font-weight: 700; font-size: 1.1rem; color: #1e293b;">No products currently available</div>
                <div style="font-size: 0.85rem; margin-top: 0.25rem;">All devices and accessories are currently sold out. Please check back later!</div>
            </div>
        @endif
    </div>
</div>

<!-- Customer Direct Purchase Modal -->
<div id="customerBuyModal" class="modal-backdrop" onclick="if(event.target === this) closeBuyModal()">
    <div class="modal-box" style="max-width: 460px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.75rem;">
            <h3 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.45rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="color: #059669;"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                <span>Buy Product &amp; Confirm Stock</span>
            </h3>
            <button type="button" onclick="closeBuyModal()" style="background: none; border: none; font-size: 1.5rem; color: #94a3b8; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <form method="POST" action="{{ route('customer.buy') }}" id="formCustomerBuy">
            @csrf
            <input type="hidden" name="item_type" id="buyItemType">
            <input type="hidden" name="phone_device_id" id="buyPhoneId">
            <input type="hidden" name="product_id" id="buyProductId">

            <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 0.85rem 1rem; margin-bottom: 1rem;">
                <div style="font-size: 0.72rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Selected Item</div>
                <div id="buyItemTitle" style="font-weight: 800; color: #0f172a; font-size: 0.95rem; margin-top: 2px;"></div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px dashed #cbd5e1;">
                    <span style="font-size: 0.8rem; color: #475569; font-weight: 600;">Total Payable:</span>
                    <strong id="buyItemPrice" style="font-size: 1.15rem; color: #166534;"></strong>
                </div>
            </div>

            <div id="buyQtyRow" style="display: none; margin-bottom: 0.85rem;">
                <label style="font-size: 0.775rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem; display: block;">Quantity to Buy</label>
                <input type="number" name="quantity" id="buyQuantity" class="form-control" value="1" min="1" max="1" style="height: 38px; border-radius: 8px;" oninput="updateBuyPrice()">
                <div id="buyStockHint" style="font-size: 0.72rem; color: #64748b; margin-top: 3px;"></div>
            </div>

            <div style="margin-bottom: 0.85rem;">
                <label style="font-size: 0.775rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem; display: block;">Contact Phone *</label>
                <input type="text" name="contact_phone" class="form-control" value="{{ $customer->phone ?? '' }}" required style="height: 38px; border-radius: 8px;" placeholder="e.g. 0771234567">
            </div>

            <div style="margin-bottom: 0.85rem;">
                <label style="font-size: 0.775rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem; display: block;">Delivery Address / Notes</label>
                <input type="text" name="delivery_address" class="form-control" value="{{ $customer->address ?? '' }}" style="height: 38px; border-radius: 8px;" placeholder="Store Counter Pickup / Address">
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="font-size: 0.775rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem; display: block;">Payment Method *</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                    <label style="display: flex; align-items: center; gap: 0.45rem; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 0.5rem; cursor: pointer; font-size: 0.8rem; font-weight: 600;">
                        <input type="radio" name="payment_method" value="cash" checked>
                        <span style="display: inline-flex; align-items: center; gap: 0.35rem;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="2" y="6" width="20" height="12" rx="2"></rect><circle cx="12" cy="12" r="2"></circle></svg>
                            <span>Cash on Pickup</span>
                        </span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.45rem; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 0.5rem; cursor: pointer; font-size: 0.8rem; font-weight: 600;">
                        <input type="radio" name="payment_method" value="card">
                        <span style="display: inline-flex; align-items: center; gap: 0.35rem;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                            <span>Online Card</span>
                        </span>
                    </label>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeBuyModal()" style="border-radius: 8px;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700; border-radius: 8px; background: #166534; border-color: #166534;">
                    ✓ Confirm Purchase &amp; Deduct Stock
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    let currentItemUnitPrice = 0;

    function showTab(tab, el) {
        document.querySelectorAll('.portal-tab-btn').forEach(btn => btn.classList.remove('active'));
        if (el) el.classList.add('active');

        document.getElementById('tabContentRepairs').style.display = tab === 'repairs' ? 'block' : 'none';
        document.getElementById('tabContentWarranties').style.display = tab === 'warranties' ? 'block' : 'none';
        document.getElementById('tabContentInvoices').style.display = tab === 'invoices' ? 'block' : 'none';
        document.getElementById('tabContentStore').style.display = tab === 'store' ? 'block' : 'none';
    }

    function filterStore(type) {
        const items = document.querySelectorAll('.store-item-card');
        items.forEach(card => {
            if (type === 'all' || card.getAttribute('data-category') === type) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
        document.getElementById('btnStoreAll').className = type === 'all' ? 'btn btn-sm btn-primary' : 'btn btn-sm btn-secondary';
        document.getElementById('btnStorePhones').className = type === 'phone' ? 'btn btn-sm btn-primary' : 'btn btn-sm btn-secondary';
        document.getElementById('btnStoreAcc').className = type === 'accessory' ? 'btn btn-sm btn-primary' : 'btn btn-sm btn-secondary';
    }

    function openBuyModal(type, id, title, price, maxQty) {
        document.getElementById('buyItemType').value = type;
        document.getElementById('buyPhoneId').value = type === 'phone' ? id : '';
        document.getElementById('buyProductId').value = type === 'product' ? id : '';
        document.getElementById('buyItemTitle').innerText = title;
        currentItemUnitPrice = price;

        const qtyRow = document.getElementById('buyQtyRow');
        const qtyInp = document.getElementById('buyQuantity');
        if (type === 'product' && maxQty > 1) {
            qtyRow.style.display = 'block';
            qtyInp.max = maxQty;
            qtyInp.value = 1;
            document.getElementById('buyStockHint').innerText = 'Maximum ' + maxQty + ' units available in stock.';
        } else {
            qtyRow.style.display = 'none';
            qtyInp.value = 1;
        }

        updateBuyPrice();
        document.getElementById('customerBuyModal').classList.add('open');
    }

    function updateBuyPrice() {
        const qty = parseInt(document.getElementById('buyQuantity').value) || 1;
        const total = (currentItemUnitPrice * qty).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('buyItemPrice').innerText = 'LKR ' + total;
    }

    function closeBuyModal() {
        document.getElementById('customerBuyModal').classList.remove('open');
    }

    // Auto open tab if parameter is passed
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('tab') === 'store') {
        const storeBtn = document.querySelectorAll('.portal-tab-btn')[3];
        if (storeBtn) showTab('store', storeBtn);
    } else if (urlParams.get('tab') === 'invoices') {
        const invBtn = document.querySelectorAll('.portal-tab-btn')[2];
        if (invBtn) showTab('invoices', invBtn);
    }
</script>
@endpush
@endsection

