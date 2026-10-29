@extends('layouts.super-admin')

@section('title', 'Edit Shop Workspace — ' . $shop->name)
@section('page_title', 'Edit Shop: ' . $shop->name)

@section('content')
    <div style="margin-bottom: 1.5rem;">
        <a href="{{ route('superadmin.shops.index') }}" class="btn-action btn-outline">&larr; Back to Shops</a>
    </div>

    @php
        $primaryUser = $shop->users->first();
    @endphp

    <div class="data-card" style="max-width: 800px; margin: 0 auto;">
        <div class="data-card-header">
            <h2 class="data-card-title">Edit Shop Workspace (#{{ $shop->id }})</h2>
        </div>
        <form method="POST" action="{{ route('superadmin.shops.update', $shop->id) }}" style="padding: 1.75rem;">
            @csrf
            @method('PUT')

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;">
                <div class="shop-form-group">
                    <label>Shop / Business Name *</label>
                    <input type="text" name="name" class="shop-form-control" value="{{ old('name', $shop->name) }}" required>
                </div>
                <div class="shop-form-group">
                    <label>Business Reg Number (BRN)</label>
                    <input type="text" name="business_reg_number" class="shop-form-control" value="{{ old('business_reg_number', $shop->business_reg_number) }}" placeholder="e.g. PV-123456">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;">
                <div class="shop-form-group">
                    <label>Shop Contact Phone</label>
                    <input type="text" name="phone" class="shop-form-control" value="{{ old('phone', $shop->phone) }}">
                </div>
                <div class="shop-form-group">
                    <label>Shop Email Address</label>
                    <input type="email" name="email" class="shop-form-control" value="{{ old('email', $shop->email) }}">
                </div>
            </div>

            <div class="shop-form-group" style="margin-bottom: 1.25rem;">
                <label>Physical Address / Location</label>
                <input type="text" name="address" class="shop-form-control" value="{{ old('address', $shop->address) }}" placeholder="Street, City, Province">
            </div>

            <!-- Primary Owner Information -->
            <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 1.25rem; margin-bottom: 1.25rem;">
                <label style="font-weight: 700; color: #1e293b; display: block; margin-bottom: 0.25rem;">Primary Owner Details</label>
                <p style="font-size: 0.75rem; color: #64748b; margin-bottom: 0.85rem;">Updates the primary user account attached to this store workspace.</p>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.85rem;">
                    <div class="shop-form-group">
                        <label style="font-size: 0.8125rem;">Owner Name</label>
                        <input type="text" name="owner_name" class="shop-form-control" value="{{ old('owner_name', $primaryUser->name ?? '') }}">
                    </div>
                    <div class="shop-form-group">
                        <label style="font-size: 0.8125rem;">Owner Email</label>
                        <input type="email" name="owner_email" class="shop-form-control" value="{{ old('owner_email', $primaryUser->email ?? '') }}">
                    </div>
                    <div class="shop-form-group">
                        <label style="font-size: 0.8125rem;">Owner Phone</label>
                        <input type="text" name="owner_phone" class="shop-form-control" value="{{ old('owner_phone', $primaryUser->phone ?? '') }}">
                    </div>
                </div>
            </div>

            <!-- Subscription & License Controls -->
            <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 1.25rem; margin-bottom: 1.25rem;">
                <label style="font-weight: 700; color: #1e293b; display: block; margin-bottom: 0.25rem;">Subscription &amp; Platform Plan</label>
                <p style="font-size: 0.75rem; color: #64748b; margin-bottom: 0.85rem;">Directly adjust tenant plan, license status, or expiry date.</p>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.85rem;">
                    <div class="shop-form-group">
                        <label style="font-size: 0.8125rem;">Plan</label>
                        <select name="plan_id" class="shop-form-control">
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}" {{ ($shop->subscription->plan_id ?? '') == $plan->id ? 'selected' : '' }}>
                                    {{ $plan->name }} (LKR {{ number_format($plan->monthly_price, 2) }}/mo)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="shop-form-group">
                        <label style="font-size: 0.8125rem;">License Status</label>
                        <select name="subscription_status" class="shop-form-control">
                            @php $currentStatus = $shop->subscription->status ?? 'trial'; @endphp
                            <option value="trial" {{ $currentStatus === 'trial' ? 'selected' : '' }}>Free Trial</option>
                            <option value="active" {{ $currentStatus === 'active' ? 'selected' : '' }}>Active Paid</option>
                            <option value="suspended" {{ $currentStatus === 'suspended' ? 'selected' : '' }}>Suspended</option>
                            <option value="expired" {{ $currentStatus === 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="cancelled" {{ $currentStatus === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="shop-form-group">
                        <label style="font-size: 0.8125rem;">Period Ends At</label>
                        <input type="date" name="current_period_ends_at" class="shop-form-control" value="{{ old('current_period_ends_at', $shop->subscription?->current_period_ends_at ? $shop->subscription->current_period_ends_at->format('Y-m-d') : '') }}">
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.5rem;">
                <div class="shop-form-group">
                    <label>Default Currency</label>
                    <input type="text" name="currency" class="shop-form-control" value="{{ old('currency', $shop->setting->currency ?? 'LKR') }}">
                </div>
                <div class="shop-form-group">
                    <label>Timezone</label>
                    <input type="text" name="timezone" class="shop-form-control" value="{{ old('timezone', $shop->setting->timezone ?? 'Asia/Colombo') }}">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; border-top: 1px solid #e2e8f0; padding-top: 1.25rem;">
                <a href="{{ route('superadmin.shops.index') }}" class="btn-action btn-outline">Cancel</a>
                <button type="submit" class="btn-action btn-primary" style="padding: 0.55rem 1.5rem;">Save Changes</button>
            </div>
        </form>
    </div>

    <style>
        .shop-form-group {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }
        .shop-form-group label {
            font-size: 0.8125rem;
            font-weight: 700;
            color: #475569;
        }
        .shop-form-control {
            padding: 0.5rem 0.85rem;
            border: 1.5px solid var(--surface-border);
            border-radius: 8px;
            font-size: 0.875rem;
            color: #1e293b;
            width: 100%;
        }
        .shop-form-control:focus {
            outline: none;
            border-color: #4f46e5;
        }
    </style>
@endsection

