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
                    <th style="text-align: right;">Action</th>
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
                    <td style="text-align: right; white-space: nowrap;">
                        @if($c->balance > 0)
                            <button type="button" class="btn btn-sm" onclick='openSettleDebtModal(@json($c))' title="Receive payment towards outstanding debt balance" style="background: #16a34a; color: #ffffff; border: none; padding: 0.3rem 0.65rem; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.25rem;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
                                Settle Debt
                            </button>
                        @endif
                        <button type="button" class="btn btn-sm btn-secondary" onclick='openEditCustomerModal(@json($c))' title="Edit customer details" style="padding: 0.3rem 0.6rem; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                            Edit
                        </button>
                        <form method="POST" action="{{ route('shop.customers.destroy', $c) }}" style="display: inline; margin: 0 0 0 0.25rem;" onsubmit="return confirm('Are you sure you want to delete customer {{ $c->name }}?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm" title="Delete customer profile" style="background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; padding: 0.3rem 0.6rem; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 3rem;">
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

                <div class="form-row">
                    <div class="form-group" style="position: relative;">
                        <label>Portal Login Password</label>
                        <div style="position: relative;">
                            <input type="password" name="password" id="addCustPassword" class="form-control" placeholder="e.g. Pass1234" style="padding-right: 2.25rem;">
                            <button type="button" onclick="togglePasswordVisibility('addCustPassword', this)" style="position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #94a3b8; padding: 0.2rem;" title="Show/Hide Password">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </button>
                        </div>
                        <span style="font-size: 0.72rem; color: #64748b; margin-top: 0.2rem; display: block;">Optional for customer portal login</span>
                    </div>
                    <div class="form-group" style="position: relative;">
                        <label>Confirm Password</label>
                        <div style="position: relative;">
                            <input type="password" name="password_confirmation" id="addCustPasswordConfirm" class="form-control" placeholder="Repeat password" style="padding-right: 2.25rem;">
                            <button type="button" onclick="togglePasswordVisibility('addCustPasswordConfirm', this)" style="position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #94a3b8; padding: 0.2rem;" title="Show/Hide Password">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </button>
                        </div>
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

<!-- Modal: Edit Customer -->
<div class="modal-backdrop" id="editCustomerModal">
    <div class="modal-box" style="max-width: 520px;">
        <div class="modal-header">
            <h3 id="editCustomerTitle">Edit Customer Profile</h3>
            <button type="button" class="modal-close" onclick="closeModal('editCustomerModal')">&times;</button>
        </div>
        <form method="POST" id="editCustomerForm" action="">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label>Full Customer Name *</label>
                    <input type="text" name="name" id="editCustName" class="form-control" placeholder="e.g. Kasun Chamara" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Phone Number *</label>
                        <input type="text" name="phone" id="editCustPhone" class="form-control" placeholder="e.g. 0771234567" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" id="editCustEmail" class="form-control" placeholder="name@example.com">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>NIC / ID Card Number</label>
                        <input type="text" name="nic" id="editCustNic" class="form-control" placeholder="e.g. 199512345678">
                    </div>
                    <div class="form-group">
                        <label>Credit Limit (LKR)</label>
                        <input type="number" step="0.01" name="credit_limit" id="editCustCreditLimit" class="form-control" value="0.00">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="position: relative;">
                        <label>New Portal Password</label>
                        <div style="position: relative;">
                            <input type="password" name="password" id="editCustPassword" class="form-control" placeholder="Leave blank to keep unchanged" style="padding-right: 2.25rem;">
                            <button type="button" onclick="togglePasswordVisibility('editCustPassword', this)" style="position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #94a3b8; padding: 0.2rem;" title="Show/Hide Password">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </button>
                        </div>
                        <span style="font-size: 0.72rem; color: #64748b; margin-top: 0.2rem; display: block;">Leave blank if not changing</span>
                    </div>
                    <div class="form-group" style="position: relative;">
                        <label>Confirm New Password</label>
                        <div style="position: relative;">
                            <input type="password" name="password_confirmation" id="editCustPasswordConfirm" class="form-control" placeholder="Repeat new password" style="padding-right: 2.25rem;">
                            <button type="button" onclick="togglePasswordVisibility('editCustPasswordConfirm', this)" style="position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #94a3b8; padding: 0.2rem;" title="Show/Hide Password">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Postal / Residence Address</label>
                    <textarea name="address" id="editCustAddress" class="form-control" rows="2" placeholder="Street, City"></textarea>
                </div>
            </div>
            <div style="padding: 1.25rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editCustomerModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Settle Customer Debt -->
<div class="modal-backdrop" id="settleDebtModal">
    <div class="modal-box" style="max-width: 480px;">
        <div class="modal-header">
            <h3 id="settleDebtTitle" style="display: flex; align-items: center; gap: 0.5rem; color: #166534; font-size: 1.1rem; margin: 0;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
                Receive Customer Debt Payment
            </h3>
            <button type="button" class="modal-close" onclick="closeModal('settleDebtModal')">&times;</button>
        </div>
        <form method="POST" id="settleDebtForm">
            @csrf
            <div class="modal-body">
                <!-- Balance Banner -->
                <div style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #991b1b;">Current Outstanding Debt</div>
                        <div id="settleCustName" style="font-size: 0.85rem; font-weight: 600; color: #7f1d1d; margin-top: 0.15rem;"></div>
                    </div>
                    <div id="settleCustBalance" style="font-size: 1.4rem; font-weight: 900; color: #dc2626;">LKR 0.00</div>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                        <label style="font-size: 0.825rem; font-weight: 700; color: #1e293b; margin: 0;">Payment Amount (LKR) *</label>
                        <button type="button" onclick="payFullDebt()" style="background: none; border: none; color: #2563eb; font-size: 0.75rem; font-weight: 700; cursor: pointer; text-decoration: underline;">
                            Pay Full Balance
                        </button>
                    </div>
                    <input type="number" step="0.01" name="amount" id="settleDebtAmount" class="form-control" placeholder="0.00" required min="0.01" style="font-size: 1.1rem; font-weight: 700;">
                </div>

                <div class="form-row" style="margin-bottom: 1rem;">
                    <div class="form-group">
                        <label style="font-size: 0.825rem; font-weight: 600;">Payment Method *</label>
                        <select name="payment_method" class="form-control" required>
                            <option value="cash">Cash In-Hand</option>
                            <option value="card">Card Terminal / POS</option>
                            <option value="bank_transfer">Bank Transfer / Online</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="font-size: 0.825rem; font-weight: 600;">Payment Date *</label>
                        <input type="date" name="payment_date" class="form-control" value="{{ now()->toDateString() }}" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label style="font-size: 0.825rem; font-weight: 600;">Reference / Slip # (Optional)</label>
                    <input type="text" name="reference" class="form-control" placeholder="e.g. SLIP-10293 or Card Ref">
                </div>

                <div class="form-group">
                    <label style="font-size: 0.825rem; font-weight: 600;">Cashier Notes (Optional)</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Settle partial debt for repairs/sales"></textarea>
                </div>
            </div>
            <div style="padding: 1.25rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('settleDebtModal')">Cancel</button>
                <button type="submit" class="btn" style="background: #16a34a; color: white; font-weight: 700; padding: 0.6rem 1.25rem; border: none; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    Confirm &amp; Record Payment
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentCustomerDebt = 0;

    function openEditCustomerModal(c) {
        document.getElementById('editCustomerTitle').innerText = 'Edit Customer: ' + (c.name || '');
        document.getElementById('editCustomerForm').action = '/shop/customers/' + c.id;
        document.getElementById('editCustName').value = c.name || '';
        document.getElementById('editCustPhone').value = c.phone || '';
        document.getElementById('editCustEmail').value = c.email || '';
        document.getElementById('editCustNic').value = c.nic || '';
        document.getElementById('editCustCreditLimit').value = c.credit_limit || '0.00';
        document.getElementById('editCustAddress').value = c.address || '';
        if (document.getElementById('editCustPassword')) document.getElementById('editCustPassword').value = '';
        if (document.getElementById('editCustPasswordConfirm')) document.getElementById('editCustPasswordConfirm').value = '';
        openModal('editCustomerModal');
    }

    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;
        if (input.type === 'password') {
            input.type = 'text';
            btn.style.color = '#4f46e5';
        } else {
            input.type = 'password';
            btn.style.color = '#94a3b8';
        }
    }

    function openSettleDebtModal(c) {
        currentCustomerDebt = parseFloat(c.balance || 0);
        document.getElementById('settleDebtTitle').innerText = 'Receive Debt: ' + (c.name || '');
        document.getElementById('settleDebtForm').action = '/shop/customers/' + c.id + '/settle-debt';
        document.getElementById('settleCustName').innerText = (c.name || 'Customer') + (c.phone ? ' (' + c.phone + ')' : '');
        document.getElementById('settleCustBalance').innerText = 'LKR ' + currentCustomerDebt.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const amtInput = document.getElementById('settleDebtAmount');
        amtInput.value = currentCustomerDebt.toFixed(2);
        amtInput.max = currentCustomerDebt;
        openModal('settleDebtModal');
    }

    function payFullDebt() {
        document.getElementById('settleDebtAmount').value = currentCustomerDebt.toFixed(2);
    }
</script>
@endpush

