@extends('layouts.super-admin')

@section('title', 'Shop Details — ' . $shop->name)
@section('page_title', 'Shop Workspace: ' . $shop->name)

@section('content')
    <div style="margin-bottom: 1.5rem;">
        <a href="{{ route('superadmin.shops.index') }}" class="btn-action btn-outline">&larr; Back to Shops</a>
    </div>

    <!-- Shop Overview & Settings -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
        <div class="data-card" style="margin-bottom: 0;">
            <div class="data-card-header">
                <h2 class="data-card-title">Shop Information</h2>
                <form method="POST" action="{{ route('superadmin.shops.toggle-suspension', $shop->id) }}">
                    @csrf
                    @if (($shop->subscription->status ?? '') === 'suspended')
                        <button type="submit" class="btn-action btn-success">Activate Shop</button>
                    @else
                        <button type="submit" class="btn-action btn-danger">Suspend Shop</button>
                    @endif
                </form>
            </div>
            <div style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
                <div>
                    <span style="font-size: 0.775rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Shop Name</span>
                    <p style="font-size: 1.15rem; font-weight: 750; color: #0f172a;">{{ $shop->name }}</p>
                </div>
                <div>
                    <span style="font-size: 0.775rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Tenant Workspace ID</span>
                    <p style="font-family: monospace; font-size: 1rem; color: #4338ca;">#{{ $shop->id }}</p>
                </div>
                <div>
                    <span style="font-size: 0.775rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Currency &amp; Timezone</span>
                    <p>{{ $shop->setting->currency ?? 'LKR' }} • {{ $shop->setting->timezone ?? 'Asia/Colombo' }}</p>
                </div>
                <div>
                    <span style="font-size: 0.775rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Registered On</span>
                    <p>{{ $shop->created_at->format('M d, Y h:i A') }} ({{ $shop->created_at->diffForHumans() }})</p>
                </div>
            </div>
        </div>

        <div class="data-card" style="margin-bottom: 0;">
            <div class="data-card-header">
                <h2 class="data-card-title">Current Subscription</h2>
                <span class="status-pill status-{{ $shop->subscription->status ?? 'trial' }}">
                    {{ $shop->subscription->status ?? 'Trial' }}
                </span>
            </div>
            <div style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
                <div>
                    <span style="font-size: 0.775rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Plan</span>
                    <p style="font-size: 1.15rem; font-weight: 750; color: #0f172a;">{{ $shop->subscription->plan->name ?? 'Standard Trial' }}</p>
                </div>
                <div>
                    <span style="font-size: 0.775rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Billing Period</span>
                    <p>
                        {{ $shop->subscription?->current_period_starts_at ? $shop->subscription->current_period_starts_at->format('Y-m-d') : 'N/A' }} 
                        to 
                        {{ $shop->subscription?->current_period_ends_at ? $shop->subscription->current_period_ends_at->format('Y-m-d') : 'N/A' }}
                    </p>
                </div>
                @if ($shop->subscription?->trial_ends_at)
                    <div>
                        <span style="font-size: 0.775rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Trial Expiration</span>
                        <p style="color: #2563eb; font-weight: 600;">{{ $shop->subscription->trial_ends_at->format('Y-m-d') }} ({{ $shop->subscription->trial_ends_at->diffForHumans() }})</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Shop Users Table -->
    <div class="data-card">
        <div class="data-card-header">
            <h2 class="data-card-title">Assigned Users &amp; Staff ({{ $shop->users->count() }})</h2>
        </div>
        <table class="saas-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($shop->users as $u)
                    <tr>
                        <td><strong>{{ $u->name }}</strong></td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $u->phone ?? 'N/A' }}</td>
                        <td>
                            @foreach ($u->roles as $r)
                                <span class="status-pill status-trial">{{ $r->name }}</span>
                            @endforeach
                        </td>
                        <td>{{ $u->created_at->format('M d, Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Payments Ledger -->
    <div class="data-card">
        <div class="data-card-header">
            <h2 class="data-card-title">Subscription Payment History</h2>
        </div>
        <table class="saas-table">
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th>Reference / Transaction</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($shop->subscriptionPayments as $p)
                    <tr>
                        <td>#{{ $p->id }}</td>
                        <td><strong>LKR {{ number_format($p->amount, 2) }}</strong></td>
                        <td><span style="text-transform: uppercase;">{{ str_replace('_', ' ', $p->payment_method) }}</span></td>
                        <td><span class="status-pill status-{{ $p->status }}">{{ $p->status }}</span></td>
                        <td>{{ $p->reference_number ?? ($p->transaction_id ?? 'N/A') }}</td>
                        <td>{{ $p->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #94a3b8; padding: 2rem;">No payments on record for this workspace.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

