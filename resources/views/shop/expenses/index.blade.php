@extends('layouts.shop')

@section('title', 'Operating Expenses')
@section('page-title', 'Expense Tracking & Accounts')

@section('content')
<style>
    .combobox-item {
        padding: 0.5rem 0.75rem;
        font-size: 0.8125rem;
        color: #1e293b;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.12s ease;
    }
    .combobox-item:hover {
        background: #eef2ff;
        color: #4338ca;
        font-weight: 600;
    }
    .combobox-item.selected {
        background: #e0e7ff;
        color: #3730a3;
        font-weight: 700;
    }
</style>

<!-- Cash & Reports Unified Sub-Navigation Tabs -->
<div class="shop-subnav-tabs no-print" style="display: flex; gap: 0.5rem; margin-bottom: 1.1rem; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.75rem; overflow-x: auto; align-items: center;">
    <a href="{{ route('shop.cashbook') }}" class="btn btn-sm {{ request()->routeIs('shop.cashbook*') ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: 8px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.45rem 0.9rem; text-decoration: none; white-space: nowrap;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
        Daily Cashbook &amp; Drawer
    </a>
    <a href="{{ route('shop.reports') }}" class="btn btn-sm {{ request()->routeIs('shop.reports*') ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: 8px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.45rem 0.9rem; text-decoration: none; white-space: nowrap;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
        Financial &amp; Performance Reports
    </a>
    <a href="{{ route('shop.expenses') }}" class="btn btn-sm {{ request()->routeIs('shop.expenses*') ? 'btn-primary' : 'btn-secondary' }}" style="border-radius: 8px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.45rem 0.9rem; text-decoration: none; white-space: nowrap;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="2" y="4" width="20" height="16" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
        Operating Expenses
    </a>
</div>

<div class="card" style="margin-bottom: 1.5rem;">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; padding: 1.25rem 1.5rem;">
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1rem;">
            <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">Today's Expenses</span>
            <div style="font-size: 1.35rem; font-weight: 800; color: #b91c1c; margin-top: 0.25rem;">
                LKR {{ number_format($todayTotal, 2) }}
            </div>
        </div>
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1rem;">
            <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">This Month ({{ date('F Y') }})</span>
            <div style="font-size: 1.35rem; font-weight: 800; color: #c2410c; margin-top: 0.25rem;">
                LKR {{ number_format($thisMonthTotal, 2) }}
            </div>
        </div>
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1rem;">
            <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">All-Time Expenses</span>
            <div style="font-size: 1.35rem; font-weight: 800; color: #475569; margin-top: 0.25rem;">
                LKR {{ number_format($allTimeTotal, 2) }}
            </div>
        </div>
        <div style="display: flex; align-items: center; justify-content: flex-end;">
            <button type="button" class="btn btn-primary" onclick="openModal('newExpenseModal')" style="width: 100%; height: 100%; max-height: 52px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Record Expense
            </button>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <form method="GET" action="{{ route('shop.expenses') }}" style="display: flex; gap: 0.75rem; flex: 1; flex-wrap: wrap;">
            <input type="text" name="search" class="form-control" style="max-width: 260px;" placeholder="Search expense description..." value="{{ request('search') }}">
            <select name="category" class="form-control" style="max-width: 170px;" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <option value="rent" {{ request('category') === 'rent' ? 'selected' : '' }}>Shop Rent</option>
                <option value="electricity" {{ request('category') === 'electricity' ? 'selected' : '' }}>Electricity / Water</option>
                <option value="salaries" {{ request('category') === 'salaries' ? 'selected' : '' }}>Staff Salaries</option>
                <option value="transport" {{ request('category') === 'transport' ? 'selected' : '' }}>Transport / Courier</option>
                <option value="internet" {{ request('category') === 'internet' ? 'selected' : '' }}>Internet &amp; Phone</option>
                <option value="supplies" {{ request('category') === 'supplies' ? 'selected' : '' }}>Shop Supplies</option>
                <option value="maintenance" {{ request('category') === 'maintenance' ? 'selected' : '' }}>Repairs &amp; Maintenance</option>
                <option value="other" {{ request('category') === 'other' ? 'selected' : '' }}>Other Expense</option>
            </select>
            <input type="date" name="date" class="form-control" style="max-width: 150px;" value="{{ request('date') }}">
            <select name="payment_method" class="form-control" style="max-width: 150px;" onchange="this.form.submit()">
                <option value="">All Methods</option>
                <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                <option value="card" {{ request('payment_method') === 'card' ? 'selected' : '' }}>Card</option>
                <option value="bank_transfer" {{ request('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank / Online</option>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
            @if(request()->hasAny(['search', 'category', 'date', 'payment_method']))
                <a href="{{ route('shop.expenses') }}" class="btn btn-secondary btn-sm" style="color: #64748b;">Clear</a>
            @endif
        </form>

        <a href="{{ route('shop.cashbook') }}" class="btn btn-secondary btn-sm" style="font-weight: 600;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
            Daily Cashbook &amp; Drawer
        </a>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Expense Date</th>
                    <th>Category</th>
                    <th>Description / Details</th>
                    <th>Payment Method</th>
                    <th>Recorded By</th>
                    <th>Amount</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $exp)
                <tr>
                    <td style="font-size: 0.8125rem; color: #475569; white-space: nowrap;">
                        {{ $exp->expense_date->format('M d, Y') }}
                    </td>
                    <td>
                        <span class="badge" style="background: #f1f5f9; color: #334155; text-transform: uppercase; font-size: 0.7rem; font-weight: 700;">
                            {{ $exp->category }}
                        </span>
                    </td>
                    <td>
                        <strong style="color: #0f172a; font-size: 0.875rem;">{{ $exp->title }}</strong>
                        @if($exp->notes)
                            <div style="font-size: 0.72rem; color: #64748b; margin-top: 2px;">{{ $exp->notes }}</div>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $exp->payment_method === 'cash' ? 'badge-success' : 'badge-info' }}">
                            {{ strtoupper($exp->payment_method) }}
                        </span>
                    </td>
                    <td style="font-size: 0.8125rem; color: #64748b;">
                        {{ $exp->user->name ?? 'Staff' }}
                    </td>
                    <td>
                        <strong style="color: #b91c1c; font-size: 0.9375rem;">
                            LKR {{ number_format($exp->amount, 2) }}
                        </strong>
                    </td>
                    <td style="text-align: right;">
                        <form action="{{ route('shop.expenses.destroy', $exp) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Remove expense: {{ $exp->title }}?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm" style="background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; padding: 0.25rem 0.5rem; font-size: 0.75rem;">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 3rem; color: #94a3b8;">
                        No operating expenses recorded. Click "+ Record Expense" to log rent, electricity, courier, or supplies.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($expenses->hasPages())
    <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border);">
        {{ $expenses->links() }}
    </div>
    @endif
</div>

<!-- Modal: Record Expense -->
<div class="modal-backdrop" id="newExpenseModal">
    <div class="modal-box" style="max-width: 520px;">
        <div class="modal-header">
            <h3>Record Operating Expense</h3>
            <button type="button" class="modal-close" onclick="closeModal('newExpenseModal')">&times;</button>
        </div>
        <form action="{{ route('shop.expenses.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group" style="position: relative;">
                    <label>Expense Category *</label>
                    <div class="combobox-wrapper" style="position: relative;">
                        <input 
                            type="text" 
                            name="category" 
                            id="expense_category_input" 
                            class="form-control" 
                            required 
                            placeholder="Select from list or type custom category..."
                            value="Electricity / Water / Utilities"
                            autocomplete="off"
                            style="padding-right: 2.25rem;"
                        >
                        <button 
                            type="button" 
                            id="expense_category_toggle" 
                            tabindex="-1"
                            style="position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #64748b; padding: 0.25rem; display: flex; align-items: center; justify-content: center;"
                            title="Choose from list or type custom"
                        >
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </button>

                        <div id="expense_category_dropdown" class="combobox-dropdown" style="display: none; position: absolute; top: calc(100% + 4px); left: 0; right: 0; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.12), 0 8px 10px -6px rgba(0,0,0,0.06); max-height: 220px; overflow-y: auto; z-index: 1050; padding: 0.35rem;">
                            <div class="combobox-item" data-value="Shop Rent">Shop Rent</div>
                            <div class="combobox-item" data-value="Electricity / Water / Utilities">Electricity / Water / Utilities</div>
                            <div class="combobox-item" data-value="Staff Salaries &amp; Allowances">Staff Salaries &amp; Allowances</div>
                            <div class="combobox-item" data-value="Transport / Delivery &amp; Courier">Transport / Delivery &amp; Courier</div>
                            <div class="combobox-item" data-value="Internet, Telephone &amp; Software">Internet, Telephone &amp; Software</div>
                            <div class="combobox-item" data-value="Store Stationery &amp; Packaging">Store Stationery &amp; Packaging</div>
                            <div class="combobox-item" data-value="Equipment &amp; Shop Maintenance">Equipment &amp; Shop Maintenance</div>
                            <div class="combobox-item" data-value="Other Operating Expense">Other Operating Expense</div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Expense Title / Description *</label>
                    <input type="text" name="title" class="form-control" required placeholder="e.g. Monthly Electricity Bill - Main Branch">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Amount (LKR) *</label>
                        <input type="number" step="0.01" name="amount" class="form-control" required placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label>Expense Date *</label>
                        <input type="date" name="expense_date" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="form-group">
                    <label>Paid Out From *</label>
                    <select name="payment_method" class="form-control" required>
                        <option value="cash">Cash (Shop Drawer)</option>
                        <option value="card">Company Card</option>
                        <option value="bank_transfer">Bank Transfer / Online</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Notes / Voucher Reference</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Receipt voucher #9283 signed by manager"></textarea>
                </div>
            </div>
            <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('newExpenseModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Expense</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Expense Category Combobox (Dropdown + Free Custom Typing)
    const expCatInput = document.getElementById('expense_category_input');
    const expCatToggle = document.getElementById('expense_category_toggle');
    const expCatDropdown = document.getElementById('expense_category_dropdown');
    const expCatItems = expCatDropdown ? expCatDropdown.querySelectorAll('.combobox-item') : [];

    function openExpCatDropdown() {
        if (!expCatDropdown) return;
        expCatDropdown.style.display = 'block';
        expCatItems.forEach(item => {
            item.style.display = 'block';
            if (item.getAttribute('data-value') === (expCatInput ? expCatInput.value : '')) {
                item.classList.add('selected');
            } else {
                item.classList.remove('selected');
            }
        });
    }

    function closeExpCatDropdown() {
        if (expCatDropdown) expCatDropdown.style.display = 'none';
    }

    if (expCatToggle) {
        expCatToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            if (expCatDropdown && expCatDropdown.style.display === 'block') {
                closeExpCatDropdown();
            } else {
                openExpCatDropdown();
                if (expCatInput) expCatInput.focus();
            }
        });
    }

    if (expCatInput) {
        expCatInput.addEventListener('focus', function() {
            openExpCatDropdown();
        });

        expCatInput.addEventListener('input', function() {
            if (!expCatDropdown) return;
            const query = expCatInput.value.trim().toLowerCase();
            let matches = 0;
            expCatItems.forEach(item => {
                const text = item.getAttribute('data-value').toLowerCase();
                if (text.includes(query) || query === '') {
                    item.style.display = 'block';
                    matches++;
                } else {
                    item.style.display = 'none';
                }
            });
            expCatDropdown.style.display = matches > 0 ? 'block' : 'none';
        });
    }

    expCatItems.forEach(function(item) {
        item.addEventListener('click', function(e) {
            e.stopPropagation();
            if (expCatInput) {
                expCatInput.value = item.getAttribute('data-value');
                expCatInput.focus();
            }
            closeExpCatDropdown();
        });
    });

    window.addEventListener('click', function(e) {
        if (!e.target.closest('.combobox-wrapper')) {
            closeExpCatDropdown();
        }
    });
</script>
@endpush

