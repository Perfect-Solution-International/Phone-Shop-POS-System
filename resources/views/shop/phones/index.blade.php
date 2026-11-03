@extends('layouts.shop')

@section('title', request('status') === 'sold' ? 'Sold Phones & IMEI Sales Archive' : 'Phone Devices & Dual IMEI Inventory')
@section('page-title', request('status') === 'sold' ? 'Sold Phone Devices (IMEI Sales Archive)' : 'Serialized Phone Inventory (Dual IMEI)')

@section('content')
@include('shop.partials.inventory-subnav')

<!-- Summary Stat Cards -->
<style>
    .phone-stat-card {
        background: #ffffff;
        border-radius: 14px;
        padding: 1rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        text-decoration: none;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    .phone-stat-card:hover {
        transform: translateY(-2px);
    }
    .btn-copy-imei {
        background: none;
        border: 1px solid transparent;
        cursor: pointer;
        padding: 2px 4px;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        gap: 2px;
        transition: all 0.15s ease;
    }
    .btn-copy-imei:hover {
        background: rgba(0, 0, 0, 0.06);
    }
    .phone-table-row {
        transition: background-color 0.15s ease;
    }
    .phone-table-row:hover {
        background-color: #f8fafc;
    }
</style>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
    <!-- Available In Stock Filter Card -->
    <a href="{{ request('status') === 'in_stock' ? route('shop.phones') : route('shop.phones', ['status' => 'in_stock']) }}" 
       class="phone-stat-card"
       style="border: 1.5px solid {{ request('status') === 'in_stock' ? '#10b981' : '#bbf7d0' }}; box-shadow: {{ request('status') === 'in_stock' ? '0 0 0 3px rgba(16, 185, 129, 0.15), 0 4px 12px rgba(0,0,0,0.04)' : '0 1px 3px rgba(0,0,0,0.03)' }}; background: {{ request('status') === 'in_stock' ? '#f0fdf4' : '#ffffff' }};"
       title="{{ request('status') === 'in_stock' ? 'Showing in-stock phones (Click to clear filter)' : 'Click to filter only in-stock phones' }}">
        <div>
            <div style="font-size: 0.72rem; font-weight: 750; color: #166534; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 0.4rem;">
                <span>Available In Stock</span>
                @if(request('status') === 'in_stock')
                    <span style="font-size: 0.62rem; padding: 1px 6px; border-radius: 9999px; background: #166534; color: #ffffff; font-weight: 800;">ACTIVE</span>
                @endif
            </div>
            <div style="font-size: 1.55rem; font-weight: 850; color: #15803d; margin-top: 2px; display: flex; align-items: baseline; gap: 0.35rem;">
                <span>{{ number_format($inStockCount ?? 0) }}</span>
                <span style="font-size: 0.8rem; font-weight: 600; color: #16a34a;">{{ ($inStockCount ?? 0) === 1 ? 'unit' : 'units' }}</span>
            </div>
        </div>
        <div style="width: 42px; height: 42px; border-radius: 10px; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
        </div>
    </a>

    <!-- Sold Out Devices Filter Card -->
    <a href="{{ request('status') === 'sold' ? route('shop.phones') : route('shop.phones', ['status' => 'sold']) }}" 
       class="phone-stat-card"
       style="border: 1.5px solid {{ request('status') === 'sold' ? '#f43f5e' : '#fecaca' }}; box-shadow: {{ request('status') === 'sold' ? '0 0 0 3px rgba(244, 63, 94, 0.15), 0 4px 12px rgba(0,0,0,0.04)' : '0 1px 3px rgba(0,0,0,0.03)' }}; background: {{ request('status') === 'sold' ? '#fff1f2' : '#ffffff' }};"
       title="{{ request('status') === 'sold' ? 'Showing sold devices (Click to clear filter)' : 'Click to filter only sold devices' }}">
        <div>
            <div style="font-size: 0.72rem; font-weight: 750; color: #991b1b; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 0.4rem;">
                <span>Sold Out Devices</span>
                @if(request('status') === 'sold')
                    <span style="font-size: 0.62rem; padding: 1px 6px; border-radius: 9999px; background: #991b1b; color: #ffffff; font-weight: 800;">ACTIVE</span>
                @endif
            </div>
            <div style="font-size: 1.55rem; font-weight: 850; color: #b91c1c; margin-top: 2px; display: flex; align-items: baseline; gap: 0.35rem;">
                <span>{{ number_format($soldCount ?? 0) }}</span>
                <span style="font-size: 0.8rem; font-weight: 600; color: #dc2626;">{{ ($soldCount ?? 0) === 1 ? 'unit' : 'units' }}</span>
            </div>
        </div>
        <div style="width: 42px; height: 42px; border-radius: 10px; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
        </div>
    </a>

    <!-- Total Registered Phones Card -->
    <a href="{{ route('shop.phones') }}" 
       class="phone-stat-card"
       style="border: 1.5px solid {{ (!request('status') && !request('condition') && !request('search')) ? '#6366f1' : '#e2e8f0' }}; box-shadow: {{ (!request('status') && !request('condition') && !request('search')) ? '0 0 0 3px rgba(99, 102, 241, 0.12), 0 4px 12px rgba(0,0,0,0.04)' : '0 1px 3px rgba(0,0,0,0.03)' }}; background: {{ (!request('status') && !request('condition') && !request('search')) ? '#f5f3ff' : '#ffffff' }};"
       title="Click to view all registered devices">
        <div>
            <div style="font-size: 0.72rem; font-weight: 750; color: #4338ca; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 0.4rem;">
                <span>Total Registered Phones</span>
                @if(!request('status') && !request('condition') && !request('search'))
                    <span style="font-size: 0.62rem; padding: 1px 6px; border-radius: 9999px; background: #4338ca; color: #ffffff; font-weight: 800;">ALL</span>
                @endif
            </div>
            <div style="font-size: 1.55rem; font-weight: 850; color: #0f172a; margin-top: 2px; display: flex; align-items: baseline; gap: 0.35rem;">
                <span>{{ number_format($totalCount ?? 0) }}</span>
                <span style="font-size: 0.8rem; font-weight: 600; color: #64748b;">{{ ($totalCount ?? 0) === 1 ? 'device' : 'devices' }}</span>
            </div>
        </div>
        <div style="width: 42px; height: 42px; border-radius: 10px; background: #ede9fe; color: #6366f1; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="5" y="2" width="14" height="20" rx="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
        </div>
    </a>
</div>

<div class="card" style="border-radius: 14px; overflow: visible; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
    <div class="card-header" style="flex-wrap: wrap; gap: 0.85rem; padding: 1rem 1.25rem; background: #ffffff; border-bottom: 1px solid var(--surface-border); display: flex; align-items: center; justify-content: space-between;">
        <form method="GET" action="{{ route('shop.phones') }}" style="display: flex; gap: 0.65rem; flex: 1; flex-wrap: wrap; align-items: center;">
            <div style="position: relative; flex: 1; min-width: 240px; max-width: 340px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.3" style="position: absolute; left: 0.85rem; top: 50%; transform: translateY(-50%); pointer-events: none;">
                    <circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" name="search" class="form-control" style="padding-left: 2.35rem; border-radius: 9px; font-size: 0.85rem; height: 38px;" placeholder="Search IMEI 1, IMEI 2, Model, S/N..." value="{{ request('search') }}">
            </div>
            <select name="status" class="form-control" style="width: auto; min-width: 135px; border-radius: 9px; font-size: 0.85rem; height: 38px;" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="in_stock" {{ request('status') === 'in_stock' ? 'selected' : '' }}>● In Stock</option>
                <option value="sold" {{ request('status') === 'sold' ? 'selected' : '' }}>● Sold</option>
                <option value="reserved" {{ request('status') === 'reserved' ? 'selected' : '' }}>● Reserved</option>
                <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>● Returned</option>
            </select>
            <select name="condition" class="form-control" style="width: auto; min-width: 145px; border-radius: 9px; font-size: 0.85rem; height: 38px;" onchange="this.form.submit()">
                <option value="">All Conditions</option>
                <option value="new" {{ request('condition') === 'new' ? 'selected' : '' }}>✨ Brand New</option>
                <option value="used" {{ request('condition') === 'used' ? 'selected' : '' }}>🔄 Pre-Owned</option>
                <option value="refurbished" {{ request('condition') === 'refurbished' ? 'selected' : '' }}>🛠️ Refurbished</option>
            </select>
            <button type="submit" class="btn btn-secondary" style="height: 38px; padding: 0 1rem; border-radius: 9px; font-weight: 600; font-size: 0.825rem;">
                Filter
            </button>
            @if(request()->hasAny(['search', 'status', 'condition']))
                <a href="{{ route('shop.phones') }}" class="btn btn-secondary" style="height: 38px; padding: 0 0.85rem; border-radius: 9px; font-weight: 600; font-size: 0.825rem; color: #ef4444; border-color: #fecaca; background: #fff5f5; display: inline-flex; align-items: center; gap: 0.3rem;" title="Clear all filters">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    Clear
                </a>
            @endif
        </form>

        <button type="button" class="btn btn-primary" onclick="openModal('addPhoneModal')" style="height: 38px; padding: 0 1.15rem; border-radius: 9px; font-weight: 700; font-size: 0.875rem; box-shadow: 0 2px 8px rgba(79, 70, 229, 0.25); display: inline-flex; align-items: center; gap: 0.4rem;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Register Phone
        </button>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1.5px solid var(--surface-border);">
                    <th style="padding: 0.85rem 1rem; font-size: 0.72rem; font-weight: 750; color: #64748b; letter-spacing: 0.05em; text-transform: uppercase;">Device &amp; Model</th>
                    <th style="padding: 0.85rem 1rem; font-size: 0.72rem; font-weight: 750; color: #64748b; letter-spacing: 0.05em; text-transform: uppercase;">IMEI 1 &amp; IMEI 2</th>
                    <th style="padding: 0.85rem 1rem; font-size: 0.72rem; font-weight: 750; color: #64748b; letter-spacing: 0.05em; text-transform: uppercase;">Specification</th>
                    <th style="padding: 0.85rem 1rem; font-size: 0.72rem; font-weight: 750; color: #64748b; letter-spacing: 0.05em; text-transform: uppercase;">In Stock</th>
                    <th style="padding: 0.85rem 1rem; font-size: 0.72rem; font-weight: 750; color: #64748b; letter-spacing: 0.05em; text-transform: uppercase;">Condition</th>
                    <th style="padding: 0.85rem 1rem; font-size: 0.72rem; font-weight: 750; color: #64748b; letter-spacing: 0.05em; text-transform: uppercase;">Purchase Cost</th>
                    <th style="padding: 0.85rem 1rem; font-size: 0.72rem; font-weight: 750; color: #64748b; letter-spacing: 0.05em; text-transform: uppercase;">Selling Price</th>
                    <th style="padding: 0.85rem 1rem; font-size: 0.72rem; font-weight: 750; color: #64748b; letter-spacing: 0.05em; text-transform: uppercase;">Status</th>
                    <th style="padding: 0.85rem 1rem; font-size: 0.72rem; font-weight: 750; color: #64748b; letter-spacing: 0.05em; text-transform: uppercase; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($phones as $phone)
                <tr class="phone-table-row">
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.85rem;">
                            @if($phone->image_url)
                                <div style="position: relative; display: inline-block; flex-shrink: 0;">
                                    <img src="{{ $phone->image_url }}" 
                                         alt="{{ $phone->brand }} {{ $phone->model }}"
                                         data-title="{{ $phone->brand }} {{ $phone->model }} (IMEI: {{ $phone->imei_1 }})"
                                         data-delete-url="{{ route('shop.phones.delete-image', $phone) }}"
                                         style="width: 48px; height: 48px; min-width: 48px; object-fit: cover; border-radius: 10px; border: 1.5px solid #e2e8f0; box-shadow: 0 1px 4px rgba(0,0,0,0.06); cursor: pointer; transition: transform 0.15s ease;"
                                         onmouseover="this.style.transform='scale(1.05)'"
                                         onmouseout="this.style.transform='scale(1)'"
                                         onclick="handlePhoneLightboxClick(this)"
                                         title="Click to view full photo">
                                    <form method="POST" action="{{ route('shop.phones.delete-image', $phone) }}" data-confirm="Delete photo for {{ $phone->brand }} {{ $phone->model }}?" onsubmit="return confirm(this.getAttribute('data-confirm'));" style="margin: 0; position: absolute; top: -5px; right: -5px;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Delete image" style="width: 18px; height: 18px; border-radius: 50%; background: #ef4444; color: #ffffff; border: 2px solid #ffffff; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 11px; line-height: 1; padding: 0; box-shadow: 0 1px 3px rgba(0,0,0,0.2);">&times;</button>
                                    </form>
                                </div>
                            @else
                                <div style="width: 48px; height: 48px; min-width: 48px; border-radius: 10px; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border: 1.5px solid #e2e8f0; display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: #94a3b8;" title="No image uploaded">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="2" width="14" height="20" rx="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                                </div>
                            @endif
                            <div style="min-width: 0;">
                                <div style="color: var(--text-heading); font-size: 0.9375rem; font-weight: 750; line-height: 1.25;">{{ $phone->brand }} {{ $phone->model }}</div>
                                <div style="display: flex; flex-wrap: wrap; gap: 0.35rem; align-items: center; margin-top: 0.3rem;">
                                    @if($phone->serial_number)
                                        <span style="display: inline-flex; align-items: center; gap: 0.2rem; font-family: monospace; font-size: 0.7rem; background: #f1f5f9; color: #475569; padding: 0.12rem 0.4rem; border-radius: 4px; border: 1px solid #e2e8f0;" title="Serial Number">
                                            SN: {{ $phone->serial_number }}
                                        </span>
                                    @endif
                                    @if($phone->supplier)
                                        <span style="display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.7rem; background: #eef2ff; color: #4f46e5; padding: 0.12rem 0.45rem; border-radius: 4px; border: 1px solid #c7d2fe; font-weight: 600;" title="Supplier">
                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                            {{ $phone->supplier->name }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="display: flex; flex-direction: column; gap: 0.35rem;">
                            <div style="display: inline-flex; align-items: center; gap: 0.35rem; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 6px; padding: 0.18rem 0.45rem; width: fit-content;">
                                <span style="font-size: 0.68rem; font-weight: 800; color: #0284c7; text-transform: uppercase;">IMEI 1:</span>
                                <span style="font-family: 'JetBrains Mono', monospace; font-size: 0.8125rem; font-weight: 700; color: #0369a1; letter-spacing: 0.02em;">{{ $phone->imei_1 }}</span>
                                <button type="button" class="btn-copy-imei" onclick="copyImei('{{ $phone->imei_1 }}', this)" title="Copy IMEI 1">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.3"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                </button>
                            </div>
                            @if($phone->imei_2)
                                <div style="display: inline-flex; align-items: center; gap: 0.35rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.18rem 0.45rem; width: fit-content;">
                                    <span style="font-size: 0.68rem; font-weight: 800; color: #64748b; text-transform: uppercase;">IMEI 2:</span>
                                    <span style="font-family: 'JetBrains Mono', monospace; font-size: 0.775rem; font-weight: 600; color: #475569; letter-spacing: 0.02em;">{{ $phone->imei_2 }}</span>
                                    <button type="button" class="btn-copy-imei" onclick="copyImei('{{ $phone->imei_2 }}', this)" title="Copy IMEI 2">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.3"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div>
                            <div style="font-size: 0.8125rem; font-weight: 600; color: var(--text-heading);">{{ $phone->color ?? 'Standard Color' }}</div>
                            @if($phone->storage || $phone->ram)
                                <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px; font-weight: 500;">
                                    {{ $phone->ram ? $phone->ram.' RAM • ' : '' }}{{ $phone->storage ?? '' }}
                                </div>
                            @endif
                        </div>
                    </td>
                    <td>
                        @if($phone->status === 'in_stock')
                            <span style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.28rem 0.65rem; border-radius: 9999px; background: #ecfdf5; color: #047857; font-size: 0.78rem; font-weight: 750; border: 1px solid #a7f3d0; white-space: nowrap;">
                                <span style="width: 7px; height: 7px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                                1 in stock
                            </span>
                        @elseif($phone->status === 'sold')
                            <span style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.28rem 0.65rem; border-radius: 9999px; background: #fff1f2; color: #be123c; font-size: 0.78rem; font-weight: 750; border: 1px solid #fecdd3; white-space: nowrap;">
                                <span style="width: 7px; height: 7px; border-radius: 50%; background: #f43f5e; display: inline-block;"></span>
                                0 in stock
                            </span>
                        @else
                            <span style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.28rem 0.65rem; border-radius: 9999px; background: #fffbeb; color: #b45309; font-size: 0.78rem; font-weight: 750; border: 1px solid #fde68a; white-space: nowrap;">
                                <span style="width: 7px; height: 7px; border-radius: 50%; background: #f59e0b; display: inline-block;"></span>
                                0 units
                            </span>
                        @endif
                    </td>
                    <td>
                        @if($phone->condition === 'new')
                            <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.75rem; font-weight: 700; background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;">
                                ✨ New
                            </span>
                        @elseif($phone->condition === 'used')
                            <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.75rem; font-weight: 700; background: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd;">
                                🔄 Used
                            </span>
                        @else
                            <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.75rem; font-weight: 700; background: #f5f3ff; color: #7c3aed; border: 1px solid #ddd6fe;">
                                🛠️ Refurbished
                            </span>
                        @endif
                    </td>
                    <td style="color: #64748b; font-size: 0.8125rem; font-weight: 600;">LKR {{ number_format($phone->purchase_cost, 2) }}</td>
                    <td>
                        <span style="font-weight: 800; color: #047857; font-size: 0.9375rem; letter-spacing: -0.01em;">
                            LKR {{ number_format($phone->selling_price, 2) }}
                        </span>
                    </td>
                    <td>
                        @if($phone->status === 'in_stock')
                            <span style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.25rem 0.6rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                In Stock
                            </span>
                        @elseif($phone->status === 'sold')
                            <span style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.25rem 0.6rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 750; background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Sold (Stock Deducted)
                            </span>
                            @if($phone->sale && $phone->sale->customer)
                                <div style="margin-top: 6px; padding: 0.45rem 0.6rem; background: #eff6ff; border-radius: 8px; border: 1px solid #bfdbfe; font-size: 0.75rem; line-height: 1.35; display: flex; flex-direction: column; gap: 0.2rem;">
                                    <div style="font-weight: 800; color: #1e40af; display: inline-flex; align-items: center; gap: 0.3rem;">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                        <span>{{ $phone->sale->customer->name }}</span>
                                    </div>
                                    <div style="font-size: 0.72rem; color: #2563eb; font-weight: 600; display: inline-flex; align-items: center; gap: 0.3rem;">
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                        <a href="tel:{{ $phone->sale->customer->phone }}" style="color: inherit; text-decoration: none;">{{ $phone->sale->customer->phone }}</a>
                                    </div>
                                    @if($phone->sale->customer->nic)
                                        <div style="font-size: 0.68rem; color: #64748b; display: inline-flex; align-items: center; gap: 0.3rem;">
                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="16" rx="2"></rect><line x1="7" y1="8" x2="17" y2="8"></line><line x1="7" y1="12" x2="13" y2="12"></line></svg>
                                            <span>{{ $phone->sale->customer->nic }}</span>
                                        </div>
                                    @endif
                                    <div style="font-size: 0.7rem; color: #4338ca; font-family: monospace; font-weight: 700; margin-top: 2px;">
                                        <a href="{{ route('shop.sales.show', $phone->sale) }}" style="text-decoration: underline; color: inherit; display: inline-flex; align-items: center; gap: 0.25rem;">
                                            #{{ $phone->sale->invoice_number }}
                                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                        </a>
                                    </div>
                                </div>
                            @elseif($phone->sale)
                                <div style="font-size: 0.72rem; color: #4338ca; margin-top: 4px; font-weight: 700;">
                                    <a href="{{ route('shop.sales.show', $phone->sale) }}" style="text-decoration: underline; color: inherit; font-family: monospace; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        #{{ $phone->sale->invoice_number }}
                                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                    </a>
                                </div>
                            @endif
                            @if($phone->sold_at)
                                <div style="font-size: 0.7rem; color: #64748b; margin-top: 4px; display: inline-flex; align-items: center; gap: 0.3rem;">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                    <span>{{ \Carbon\Carbon::parse($phone->sold_at)->format('M d, Y') }}</span>
                                </div>
                            @endif
                        @else
                            <span class="badge badge-warning">{{ ucfirst($phone->status) }}</span>
                        @endif
                    </td>
                    <td style="text-align: right; white-space: nowrap;">
                        <div style="display: inline-flex; align-items: center; gap: 0.4rem; justify-content: flex-end;">
                            <a href="{{ route('shop.labels.index', ['type' => 'phone', 'id' => $phone->id]) }}" 
                               class="btn btn-secondary btn-sm" 
                               title="Print IMEI Barcode Label" 
                               style="padding: 0.32rem 0.65rem; font-size: 0.75rem; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 0.25rem; color: #4338ca; background: #eef2ff; border-color: #c7d2fe;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
                                Label
                            </a>
                            <button type="button" 
                                    class="btn btn-secondary btn-sm" 
                                    data-phone="{{ json_encode($phone) }}"
                                    onclick="handleEditPhoneClick(this)" 
                                    title="Edit phone details" 
                                    style="padding: 0.32rem 0.65rem; font-size: 0.75rem; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 0.3rem;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                Edit
                            </button>
                            @if(! $phone->isSold() && ! $phone->sale_id)
                            <form method="POST" action="{{ route('shop.phones.destroy', $phone) }}" onsubmit="return confirm('Delete {{ addslashes($phone->brand . ' ' . $phone->model) }} (IMEI: {{ $phone->imei_1 }}) from inventory?');" style="display: inline; margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="btn btn-danger btn-sm" 
                                        title="Delete device" 
                                        style="padding: 0.32rem 0.55rem; font-size: 0.75rem; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </button>
                            </form>
                            @else
                            <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.32rem 0.6rem; border-radius: 8px; background: #f8fafc; color: #94a3b8; font-size: 0.75rem; font-weight: 600; border: 1px solid #e2e8f0;" title="Sold devices cannot be deleted from inventory">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                Sold
                            </span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 3.5rem 1rem;">
                        <div style="width: 48px; height: 48px; border-radius: 50%; background: #f1f5f9; color: #94a3b8; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.75rem;">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                        </div>
                        <div style="font-weight: 750; color: #334155; font-size: 1rem;">No serialized phone devices found</div>
                        <div style="font-size: 0.8125rem; color: #94a3b8; margin: 0.35rem 0 1.25rem;">Register new or pre-owned phones with IMEI 1 &amp; IMEI 2 to start selling on POS.</div>
                        <button type="button" class="btn btn-primary btn-sm" onclick="openModal('addPhoneModal')" style="display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 700; border-radius: 8px; padding: 0.5rem 1rem;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
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
                        <select name="status" id="edit_phone_status" class="form-control" required onchange="updateEditPhoneStockQty(this.value)">
                            <option value="in_stock">In Stock</option>
                            <option value="reserved">Reserved</option>
                            <option value="sold">Sold</option>
                            <option value="defective">Defective</option>
                            <option value="returned">Returned</option>
                            <option value="in_transfer">In Transfer</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Current Stock Quantity</label>
                        <input type="text" id="edit_phone_stock_display" class="form-control" readonly style="background: #f8fafc; font-weight: 750;" value="1 unit (In Stock)">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Condition *</label>
                        <select name="condition" id="edit_phone_condition" class="form-control" required>
                            <option value="new">Brand New (Sealed/Official)</option>
                            <option value="used">Used / Pre-Owned</option>
                            <option value="refurbished">Factory Refurbished</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Color</label>
                        <input type="text" name="color" id="edit_phone_color" class="form-control" placeholder="e.g. Space Black, Titanium">
                    </div>
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
    function copyImei(text, btn) {
        if (!text) return;
        const doSuccess = () => {
            const originalHTML = btn.innerHTML;
            btn.innerHTML = `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>`;
            btn.setAttribute('title', 'Copied!');
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.setAttribute('title', 'Copy IMEI');
            }, 1800);
        };

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(doSuccess).catch(() => {
                fallbackCopyText(text, doSuccess);
            });
        } else {
            fallbackCopyText(text, doSuccess);
        }
    }

    function fallbackCopyText(text, cb) {
        const temp = document.createElement('textarea');
        temp.value = text;
        temp.style.position = 'fixed';
        temp.style.opacity = '0';
        document.body.appendChild(temp);
        temp.focus();
        temp.select();
        try {
            document.execCommand('copy');
            if (cb) cb();
        } catch (e) {
            console.warn('Copy command failed', e);
        }
        document.body.removeChild(temp);
    }

    function handleEditPhoneClick(btn) {
        try {
            const phone = JSON.parse(btn.getAttribute('data-phone'));
            openEditPhoneModal(phone);
        } catch (e) {
            console.error('Failed to parse phone data for edit modal', e);
        }
    }

    function openEditPhoneModal(phone) {
        const form = document.getElementById('editPhoneForm');
        form.action = `/shop/phones/${phone.id}`;

        const setVal = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.value = val ?? '';
        };

        setVal('edit_phone_brand', phone.brand);
        setVal('edit_phone_model', phone.model);
        setVal('edit_phone_imei_1', phone.imei_1);
        setVal('edit_phone_imei_2', phone.imei_2);
        setVal('edit_phone_status', phone.status || 'in_stock');
        setVal('edit_phone_condition', phone.condition || 'new');
        setVal('edit_phone_color', phone.color);
        setVal('edit_phone_storage', phone.storage);
        setVal('edit_phone_ram', phone.ram);
        setVal('edit_phone_serial_number', phone.serial_number);
        setVal('edit_phone_purchase_cost', phone.purchase_cost || '0.00');
        setVal('edit_phone_selling_price', phone.selling_price || '0.00');
        setVal('edit_phone_supplier_id', phone.supplier_id);
        setVal('edit_phone_notes', phone.notes);
        updateEditPhoneStockQty(phone.status || 'in_stock');

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

    function updateEditPhoneStockQty(status) {
        const stockDisplay = document.getElementById('edit_phone_stock_display');
        if (!stockDisplay) return;
        if (status === 'in_stock') {
            stockDisplay.value = '1 unit (In Stock)';
            stockDisplay.style.color = '#15803d';
        } else if (status === 'sold') {
            stockDisplay.value = '0 units (Sold)';
            stockDisplay.style.color = '#b91c1c';
        } else {
            stockDisplay.value = '0 units (' + (status || '') + ')';
            stockDisplay.style.color = '#d97706';
        }
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

