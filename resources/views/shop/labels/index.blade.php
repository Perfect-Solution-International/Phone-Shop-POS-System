@extends('layouts.shop')

@section('title', 'Barcode & IMEI Label Generator')
@section('page-title', 'Thermal Barcode & IMEI Label Generator')

@section('content')
@include('shop.partials.inventory-subnav')

<style>
    .labels-layout-grid {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 1.5rem;
        align-items: start;
    }
    @media (max-width: 992px) {
        .labels-layout-grid {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 640px) {
        .inventory-type-selector {
            flex-direction: column !important;
        }
        .template-selector {
            grid-template-columns: 1fr !important;
        }
    }
</style>
<div style="display: flex; flex-direction: column; gap: 1.5rem;">
    <!-- Header Banner -->
    <div class="card" style="background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); color: white; padding: 1.25rem 1.5rem; border: none; border-radius: 12px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h3 style="font-size: 1.25rem; font-weight: 700; margin: 0 0 0.25rem 0; color: white; display: flex; align-items: center; gap: 0.5rem;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
                    Barcode &amp; Thermal Sticker Printer
                </h3>
                <p style="margin: 0; font-size: 0.85rem; opacity: 0.9;">
                    Generate instant Code128 barcode &amp; IMEI labels for thermal printers (Xprinter, Zebra, TSC) and standard sticker sheets.
                </p>
            </div>
            @if($labelData)
                <a href="{{ route('shop.labels.print', ['type' => $selectedType, 'id' => $labelData['id'], 'quantity' => $quantity, 'template' => $template]) }}"
                   target="_blank"
                   class="btn"
                   style="background: #ffffff; color: #4338ca; font-weight: 700; padding: 0.65rem 1.25rem; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                    Print Label Now
                </a>
            @endif
        </div>
    </div>

    <!-- Main Grid: Configuration Form & Live Preview -->
    <div class="labels-layout-grid">
        <!-- Left: Label Setup Form -->
        <div class="card" style="padding: 1.5rem; border-radius: 12px;">
            <h4 style="font-size: 1rem; font-weight: 700; margin-bottom: 1.25rem; color: #1e293b; display: flex; align-items: center; gap: 0.5rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                Label Configuration
            </h4>

            <form action="{{ route('shop.labels.index') }}" method="GET" id="labelForm">
                <!-- Select Inventory Type -->
                <div style="margin-bottom: 1.25rem;">
                    <label style="display: block; font-size: 0.825rem; font-weight: 600; color: #475569; margin-bottom: 0.4rem;">
                        Inventory Category
                    </label>
                    <div class="inventory-type-selector" style="display: flex; gap: 0.75rem;">
                        <label style="flex: 1; border: 2px solid {{ $selectedType === 'phone' ? 'var(--brand-indigo-600)' : '#e2e8f0' }}; background: {{ $selectedType === 'phone' ? '#eef2ff' : '#f8fafc' }}; padding: 0.75rem 1rem; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
                            <input type="radio" name="type" value="phone" {{ $selectedType === 'phone' ? 'checked' : '' }} onchange="document.getElementById('labelForm').submit();">
                            <div>
                                <div style="font-weight: 700; font-size: 0.875rem; color: #0f172a; display: flex; align-items: center; gap: 0.35rem;">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                                    Serialized Phones
                                </div>
                                <div style="font-size: 0.75rem; color: #64748b;">IMEI 1 &amp; IMEI 2 Barcode</div>
                            </div>
                        </label>
                        <label style="flex: 1; border: 2px solid {{ $selectedType === 'product' ? 'var(--brand-indigo-600)' : '#e2e8f0' }}; background: {{ $selectedType === 'product' ? '#eef2ff' : '#f8fafc' }}; padding: 0.75rem 1rem; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
                            <input type="radio" name="type" value="product" {{ $selectedType === 'product' ? 'checked' : '' }} onchange="document.getElementById('labelForm').submit();">
                            <div>
                                <div style="font-weight: 700; font-size: 0.875rem; color: #0f172a; display: flex; align-items: center; gap: 0.35rem;">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 18v-6a9 9 0 0 1 18 0v6"></path><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path></svg>
                                    Accessories &amp; Items
                                </div>
                                <div style="font-size: 0.75rem; color: #64748b;">Product SKU / Barcode</div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Select Item -->
                <div style="margin-bottom: 1.25rem;">
                    <label style="display: block; font-size: 0.825rem; font-weight: 600; color: #475569; margin-bottom: 0.4rem;">
                        Select {{ $selectedType === 'phone' ? 'Device' : 'Product' }} *
                    </label>
                    <select name="id" class="form-control" onchange="document.getElementById('labelForm').submit();" style="width: 100%; padding: 0.6rem 0.75rem; border-radius: 8px; font-size: 0.9rem;">
                        @if($selectedType === 'phone')
                            @foreach($phones as $phone)
                                <option value="{{ $phone->id }}" {{ $selectedId == $phone->id ? 'selected' : '' }}>
                                    {{ is_object($phone->brand) ? $phone->brand->name . ' ' : ($phone->brand ? $phone->brand . ' ' : '') }}{{ $phone->model }}
                                    ({{ $phone->storage ?? 'N/A' }} / {{ $phone->color ?? 'Standard' }}) -
                                    IMEI: {{ $phone->imei_1 ?? $phone->imei1 }} - Rs. {{ number_format($phone->selling_price, 2) }}
                                </option>
                            @endforeach
                        @else
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ $selectedId == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }} (Code: {{ $product->code ?? $product->barcode }}) - Rs. {{ number_format($product->retail_price ?? $product->selling_price, 2) }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <!-- Template Selection -->
                <div style="margin-bottom: 1.25rem;">
                    <label style="display: block; font-size: 0.825rem; font-weight: 600; color: #475569; margin-bottom: 0.4rem;">
                        Printer Label Format / Dimensions
                    </label>
                    <div class="template-selector" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.6rem;">
                        <label style="border: 1px solid {{ $template === 'thermal_50x30' ? 'var(--brand-indigo-600)' : '#cbd5e1' }}; background: {{ $template === 'thermal_50x30' ? '#eef2ff' : '#ffffff' }}; padding: 0.6rem; border-radius: 6px; cursor: pointer; text-align: center;">
                            <input type="radio" name="template" value="thermal_50x30" {{ $template === 'thermal_50x30' ? 'checked' : '' }} onchange="document.getElementById('labelForm').submit();">
                            <div style="font-weight: 700; font-size: 0.82rem; margin-top: 0.2rem;">50 x 30 mm</div>
                            <div style="font-size: 0.7rem; color: #64748b;">Thermal Roll</div>
                        </label>
                        <label style="border: 1px solid {{ $template === 'thermal_40x25' ? 'var(--brand-indigo-600)' : '#cbd5e1' }}; background: {{ $template === 'thermal_40x25' ? '#eef2ff' : '#ffffff' }}; padding: 0.6rem; border-radius: 6px; cursor: pointer; text-align: center;">
                            <input type="radio" name="template" value="thermal_40x25" {{ $template === 'thermal_40x25' ? 'checked' : '' }} onchange="document.getElementById('labelForm').submit();">
                            <div style="font-weight: 700; font-size: 0.82rem; margin-top: 0.2rem;">40 x 25 mm</div>
                            <div style="font-size: 0.7rem; color: #64748b;">Compact Roll</div>
                        </label>
                        <label style="border: 1px solid {{ $template === 'sheet_a4' ? 'var(--brand-indigo-600)' : '#cbd5e1' }}; background: {{ $template === 'sheet_a4' ? '#eef2ff' : '#ffffff' }}; padding: 0.6rem; border-radius: 6px; cursor: pointer; text-align: center;">
                            <input type="radio" name="template" value="sheet_a4" {{ $template === 'sheet_a4' ? 'checked' : '' }} onchange="document.getElementById('labelForm').submit();">
                            <div style="font-weight: 700; font-size: 0.82rem; margin-top: 0.2rem;">A4 Sticker</div>
                            <div style="font-size: 0.7rem; color: #64748b;">24-up Sheet</div>
                        </label>
                    </div>
                </div>

                <!-- Print Quantity -->
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-size: 0.825rem; font-weight: 600; color: #475569; margin-bottom: 0.4rem;">
                        Print Quantity (Copies)
                    </label>
                    <input type="number" name="quantity" class="form-control" min="1" max="100" value="{{ $quantity }}" style="max-width: 140px; padding: 0.5rem 0.75rem; border-radius: 6px;">
                    <span style="font-size: 0.72rem; color: #64748b;">For thermal rolls, will feed sequential labels. For A4 sheet, prints multiple stickers.</span>
                </div>

                <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 0.65rem 0.85rem; margin-bottom: 1.25rem; font-size: 0.775rem; color: #1e40af; display: flex; align-items: center; gap: 0.5rem;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    <div>
                        <strong>Thermal Print Tip:</strong> In your browser print dialog, set <strong>Margins to "None"</strong> (or Custom: 0mm) and ensure paper size matches your label (e.g., 50x30mm) for edge-to-edge alignment.
                    </div>
                </div>

                <div style="display: flex; gap: 0.75rem;">
                    <button type="submit" class="btn btn-secondary" style="padding: 0.6rem 1.25rem;">
                        Refresh Preview
                    </button>
                    @if($labelData)
                        <a href="{{ route('shop.labels.print', ['type' => $selectedType, 'id' => $labelData['id'], 'quantity' => $quantity, 'template' => $template]) }}"
                           target="_blank"
                           class="btn btn-primary"
                           style="padding: 0.6rem 1.5rem; display: inline-flex; align-items: center; gap: 0.5rem;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                            Print Labels
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Right: Live Label Preview -->
        <div class="card" style="padding: 1.5rem; border-radius: 12px; background: #f8fafc; border: 1px dashed #cbd5e1; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 380px;">
            <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #64748b; letter-spacing: 0.05em; margin-bottom: 1rem;">
                Live Thermal Sticker Preview
            </div>

            @if($labelData)
                <!-- Label Container Rendered to Physical Scale -->
                <div style="width: 280px; background: #ffffff; border: 1px solid #1e293b; border-radius: 6px; padding: 10px 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); text-align: center; color: #000000; font-family: monospace, sans-serif;">
                    <!-- Shop Name -->
                    <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px dashed #94a3b8; padding-bottom: 3px; margin-bottom: 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        {{ $shop->name ?? 'PHONE SHOP POS' }}
                    </div>

                    <!-- Item Title & Spec -->
                    <div style="font-size: 12px; font-weight: 700; line-height: 1.2; margin-bottom: 2px;">
                        {{ $labelData['title'] }}
                    </div>
                    @if(!empty($labelData['subtitle']))
                        <div style="font-size: 10px; color: #334155; margin-bottom: 4px;">
                            {{ $labelData['subtitle'] }}
                        </div>
                    @endif

                    <!-- Code128 Barcode Graphic -->
                    <div style="margin: 6px 0 2px 0;">
                        {!! $labelData['barcode_svg'] !!}
                    </div>

                    <!-- Barcode Code / IMEI Text -->
                    <div style="font-size: 11px; font-weight: 700; letter-spacing: 1px; margin-bottom: 3px;">
                        {{ $labelData['code'] }}
                    </div>

                    @if(!empty($labelData['secondary_code']))
                        <div style="font-size: 9px; color: #475569; margin-bottom: 3px;">
                            {{ $labelData['secondary_code'] }}
                        </div>
                    @endif

                    <!-- Price -->
                    <div style="font-size: 13px; font-weight: 900; border-top: 1px solid #000000; padding-top: 3px; margin-top: 4px;">
                        LKR {{ number_format($labelData['price'], 2) }}
                    </div>
                </div>

                <div style="font-size: 0.75rem; color: #64748b; margin-top: 1rem; text-align: center;">
                    Format: <strong>{{ strtoupper(str_replace('_', ' ', $template)) }}</strong> • Quantity: <strong>{{ $quantity }} pcs</strong>
                </div>
            @else
                <div style="color: #94a3b8; font-size: 0.875rem;">
                    No inventory items available to generate barcode.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
