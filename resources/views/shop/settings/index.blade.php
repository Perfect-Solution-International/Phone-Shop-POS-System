@extends('layouts.shop')

@section('title', 'Shop Configuration')
@section('page-title', 'Shop Profile & POS Settings')

@section('content')
<div class="card" style="max-width: 860px; margin: 0 auto;">
    <div class="card-header">
        <div>
            <strong style="font-size: 1.05rem; color: #0f172a;">Store Identity &amp; Receipt Configuration</strong>
            <div style="font-size: 0.8125rem; color: #64748b; margin-top: 2px;">
                Configure your business credentials, contact numbers, and printed receipt notices.
            </div>
        </div>
        <span class="badge badge-info">Tenant Scoped</span>
    </div>

    <div style="padding: 1rem 1.5rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; gap: 0.75rem; flex-wrap: wrap;">
        <a href="{{ route('shop.subscription.index') }}" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 0.45rem; color: #4338ca; background: #eef2ff; border-color: #c7d2fe; font-weight: 600; border-radius: 8px;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                <line x1="1" y1="10" x2="23" y2="10"></line>
            </svg>
            <span>Manage Subscription &amp; Billing</span>
        </a>
        <a href="{{ route('shop.notifications.index') }}" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 0.45rem; color: #166534; background: #f0fdf4; border-color: #bbf7d0; font-weight: 600; border-radius: 8px;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            <span>SMS Gateway &amp; Automated Alerts</span>
        </a>
        <a href="{{ route('shop.labels.index') }}" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 0.45rem; color: #9a3412; background: #fff7ed; border-color: #fed7aa; font-weight: 600; border-radius: 8px;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                <line x1="7" y1="7" x2="7.01" y2="7"></line>
            </svg>
            <span>Barcode &amp; Thermal Sticker Printer</span>
        </a>
    </div>

    <form action="{{ route('shop.settings.update') }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card-body">
            <!-- Business Identity -->
            <div style="margin-bottom: 1.5rem;">
                <h4 style="font-size: 0.875rem; text-transform: uppercase; color: var(--brand-indigo-600); font-weight: 700; margin-bottom: 0.85rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.4rem;">
                    1. Business Identity
                </h4>
                <div class="form-row">
                    <div class="form-group">
                        <label>Business / Shop Legal Name *</label>
                        <input type="text" name="name" class="form-control" required value="{{ old('name', $shop->name) }}">
                    </div>
                    <div class="form-group">
                        <label>Business Registration Number (BRN)</label>
                        <input type="text" name="business_reg_number" class="form-control" placeholder="e.g. PV-102948" value="{{ old('business_reg_number', $shop->business_reg_number) }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Contact Phone (Printed on Receipts)</label>
                        <input type="text" name="phone" class="form-control" placeholder="e.g. +94 11 234 5678" value="{{ old('phone', $shop->phone) }}">
                    </div>
                    <div class="form-group">
                        <label>Official Support Email</label>
                        <input type="email" name="email" class="form-control" placeholder="e.g. contact@phoneshop.lk" value="{{ old('email', $shop->email) }}">
                    </div>
                </div>
                <div class="form-group">
                    <label>Store Physical Address</label>
                    <input type="text" name="address" class="form-control" placeholder="e.g. 142 Galle Road, Bambalapitiya, Colombo 04" value="{{ old('address', $shop->address) }}">
                </div>
            </div>

            <!-- Regional & Currency -->
            <div style="margin-bottom: 1.5rem;">
                <h4 style="font-size: 0.875rem; text-transform: uppercase; color: var(--brand-indigo-600); font-weight: 700; margin-bottom: 0.85rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.4rem;">
                    2. Regional &amp; Currency Settings
                </h4>
                <div class="form-row">
                    <div class="form-group">
                        <label>Operating Currency *</label>
                        <input type="text" name="currency" class="form-control" required value="{{ old('currency', $setting->currency ?? 'LKR') }}" style="max-width: 140px;">
                        <span style="font-size: 0.72rem; color: #64748b;">Default currency symbol for prices and sales (e.g. LKR).</span>
                    </div>
                    <div class="form-group">
                        <label>Store Timezone *</label>
                        <input type="text" name="timezone" class="form-control" required value="{{ old('timezone', $setting->timezone ?? 'Asia/Colombo') }}">
                        <span style="font-size: 0.72rem; color: #64748b;">Timezone used for daily closing and receipt timestamps.</span>
                    </div>
                </div>
                <div class="form-group">
                    <label>Country</label>
                    <input type="text" name="country" class="form-control" value="{{ old('country', $shop->country ?? 'Sri Lanka') }}">
                </div>
            </div>

            <!-- Invoicing & Receipt Notes -->
            <div style="margin-bottom: 1rem;">
                <h4 style="font-size: 0.875rem; text-transform: uppercase; color: var(--brand-indigo-600); font-weight: 700; margin-bottom: 0.85rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.4rem;">
                    3. POS Invoicing &amp; Warranty Disclaimers
                </h4>
                <div class="form-group">
                    <label>Invoice Header Greeting Note</label>
                    <input type="text" name="invoice_header_note" class="form-control" placeholder="e.g. Genuine Smartphones, Apple & Samsung Accessories, Expert Chip-Level Repairs" value="{{ old('invoice_header_note', $shop->invoice_header_note) }}">
                </div>
                <div class="form-group">
                    <label>Invoice Footer Thank You &amp; Return Policy Note</label>
                    <textarea name="invoice_footer_note" class="form-control" rows="2" placeholder="e.g. Thank you for choosing us! Goods sold with receipt only. 7 days exchange for manufacturing defects.">{{ old('invoice_footer_note', $shop->invoice_footer_note) }}</textarea>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Default Warranty Terms Disclaimer</label>
                    <textarea name="warranty_terms" class="form-control" rows="3" placeholder="e.g. 1 Year Software & Hardware warranty. Water damage, physical drop, and display crack void warranty.">{{ old('warranty_terms', $shop->warranty_terms) }}</textarea>
                </div>
            </div>
        </div>

        <div style="padding: 1.25rem 1.5rem; background: #f8fafc; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.75rem;">
            <button type="submit" class="btn btn-primary">Save Shop Settings</button>
        </div>
    </form>
</div>
@endsection

