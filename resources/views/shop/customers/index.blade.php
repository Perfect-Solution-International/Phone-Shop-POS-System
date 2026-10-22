@extends('layouts.shop')

@section('title', 'Customers Directory')
@section('page-title', 'Customer Directory & Credit Balances')

@section('content')
<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <form method="GET" action="{{ route('shop.customers') }}" style="display: flex; gap: 0.75rem; flex: 1; flex-wrap: wrap;">
            <input type="text" name="search" class="form-control" style="max-width: 320px;" placeholder="Search name, phone, email, NIC..." value="{{ request('search') }}">
            <label style="display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.8125rem; font-weight: 600; cursor: pointer;">
                <input type="checkbox" name="has_balance" value="1" {{ request('has_balance') ? 'checked' : '' }} onchange="this.form.submit()">
                Show Outstanding Debt Only
            </label>
            <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
            @if(request()->hasAny(['search', 'has_balance']))
                <a href="{{ route('shop.customers') }}" class="btn btn-sm btn-secondary" style="color: #64748b;">Clear</a>
            @endif
        </form>

        <button type="button" class="btn btn-primary" onclick="openModal('addCustomerModal')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Add Customer
        </button>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Phone Number</th>
                    <th>Email Address</th>
                    <th>NIC / ID</th>
                    <th>Credit Limit</th>
                    <th>Current Balance (Debt)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $c)
                <tr>
                    <td>
                        <strong style="color: var(--text-heading); font-size: 0.9375rem;">{{ $c->name }}</strong>
                        @if($c->address)
                            <div style="font-size: 0.75rem; color: var(--text-muted);">{{ Str::limit($c->address, 35) }}</div>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight: 600; color: #0284c7;">{{ $c->phone }}</div>
                    </td>
                    <td>
                        <span style="font-size: 0.8125rem; color: var(--text-muted);">{{ $c->email ?? '—' }}</span>
                    </td>
                    <td>
                        <span style="font-family: monospace; font-size: 0.8125rem;">{{ $c->nic ?? '—' }}</span>
                    </td>
                    <td>
                        <span style="font-size: 0.8125rem; color: var(--text-muted);">
                            LKR {{ number_format($c->credit_limit, 2) }}
                        </span>
                    </td>
                    <td>
                        @if($c->balance > 0)
                            <span class="badge badge-danger">LKR {{ number_format($c->balance, 2) }} Due</span>
                        @else
                            <span class="badge badge-success">Clear (0.00)</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 3rem;">
                        No customer profiles found. Add a customer above.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($customers->hasPages())
    <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border);">
        {{ $customers->links() }}
    </div>
    @endif
</div>

<!-- Modal: Add Customer -->
<div class="modal-backdrop" id="addCustomerModal">
    <div class="modal-box" style="max-width: 520px;">
        <div class="modal-header">
            <h3>Add Customer Profile</h3>
            <button type="button" class="modal-close" onclick="closeModal('addCustomerModal')">&times;</button>
        </div>
        <form method="POST" action="{{ route('shop.customers.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Full Customer Name *</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Kasun Chamara" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Phone Number *</label>
                        <input type="text" name="phone" class="form-control" placeholder="e.g. 0771234567" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="name@example.com">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>NIC / ID Card Number</label>
                        <input type="text" name="nic" class="form-control" placeholder="e.g. 199512345678">
                    </div>
                    <div class="form-group">
                        <label>Credit Limit (LKR)</label>
                        <input type="number" step="0.01" name="credit_limit" class="form-control" value="0.00">
                    </div>
                </div>

                <div class="form-group">
                    <label>Postal / Residence Address</label>
                    <textarea name="address" class="form-control" rows="2" placeholder="Street, City"></textarea>
                </div>
            </div>
            <div style="padding: 1.25rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addCustomerModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Profile</button>
            </div>
        </form>
    </div>
</div>
@endsection

