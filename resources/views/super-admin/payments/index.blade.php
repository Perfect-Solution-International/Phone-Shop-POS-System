@extends('layouts.super-admin')

@section('title', 'All Payments')
@section('page_title', 'All Subscription Payments & Invoices')

@section('content')
    <div class="data-card">
        <div class="data-card-header" style="flex-wrap: wrap; gap: 1rem;">
            <h2 class="data-card-title">Payments Ledger ({{ $payments->total() }})</h2>

            <!-- Filter -->
            <form method="GET" action="{{ route('superadmin.payments.index') }}" style="display: flex; gap: 0.75rem; align-items: center;">
                <select name="status" style="padding: 0.45rem 0.85rem; border: 1.5px solid var(--surface-border); border-radius: 8px; font-size: 0.875rem;">
                    <option value="">All Statuses</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>Under Review</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>

                <select name="method" style="padding: 0.45rem 0.85rem; border: 1.5px solid var(--surface-border); border-radius: 8px; font-size: 0.875rem;">
                    <option value="">All Methods</option>
                    <option value="bank_transfer" {{ request('method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="online" {{ request('method') === 'online' ? 'selected' : '' }}>Online Gateway</option>
                </select>

                <button type="submit" class="btn-action btn-primary">Filter</button>
            </form>
        </div>

        <table class="saas-table">
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Shop</th>
                    <th>Plan</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th>Reference / Details</th>
                    <th>Verified By</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payments as $payment)
                    <tr>
                        <td>#{{ $payment->id }}</td>
                        <td>
                            <strong>{{ $payment->shop->name ?? 'N/A' }}</strong>
                            <div style="font-size: 0.75rem; color: #64748b;">ID #{{ $payment->shop_id }}</div>
                        </td>
                        <td>{{ $payment->plan->name ?? 'N/A' }}</td>
                        <td><strong>LKR {{ number_format($payment->amount, 2) }}</strong></td>
                        <td>
                            <span style="font-size: 0.775rem; text-transform: uppercase;">
                                {{ str_replace('_', ' ', $payment->payment_method) }}
                            </span>
                        </td>
                        <td>
                            <span class="status-pill status-{{ $payment->status }}">
                                {{ $payment->status }}
                            </span>
                        </td>
                        <td>
                            <div style="font-size: 0.8125rem;">{{ $payment->reference_number ?? ($payment->transaction_id ?? 'None') }}</div>
                            @if ($payment->rejection_reason)
                                <div style="font-size: 0.75rem; color: #ef4444;">Reason: {{ $payment->rejection_reason }}</div>
                            @endif
                        </td>
                        <td>{{ $payment->verifier->name ?? 'System' }}</td>
                        <td>{{ $payment->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; color: #94a3b8; padding: 2.5rem;">No payments match your filter.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="padding: 1.25rem 1.5rem; border-top: 1px solid var(--surface-border);">
            {{ $payments->links() }}
        </div>
    </div>
@endsection

