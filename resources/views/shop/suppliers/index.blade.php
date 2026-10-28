@extends('layouts.shop')

@section('title', 'Suppliers Management')
@section('page-title', 'Suppliers Directory')

@section('content')
<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <form method="GET" action="{{ route('shop.suppliers') }}" style="display: flex; gap: 0.75rem; flex: 1; max-width: 480px;">
            <input type="text" name="search" class="form-control" placeholder="Search supplier by name, company, phone..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-secondary btn-sm">Search</button>
            @if(request('search'))
                <a href="{{ route('shop.suppliers') }}" class="btn btn-secondary btn-sm" style="color: #64748b;">Clear</a>
            @endif
        </form>

        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="font-size: 0.8125rem; background: #fff1f2; color: #be123c; padding: 0.45rem 0.85rem; border-radius: 8px; font-weight: 700; border: 1px solid #fecdd3;">
                Total Outstanding: LKR {{ number_format($totalBalanceOwed, 2) }}
            </div>
            <button type="button" class="btn btn-primary" onclick="openAddSupplierModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Register Supplier
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Supplier / Company</th>
                    <th>Contact Phone</th>
                    <th>Email Address</th>
                    <th>Physical Address</th>
                    <th>Balance Owed</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $supplier)
                <tr>
                    <td>
                        <strong style="color: #0f172a; font-size: 0.9375rem;">{{ $supplier->name }}</strong>
                        @if($supplier->company_name)
                            <div style="font-size: 0.75rem; color: #64748b;">{{ $supplier->company_name }}</div>
                        @endif
                    </td>
                    <td>
                        <span style="font-family: monospace; font-size: 0.85rem; color: #0369a1; font-weight: 600;">
                            {{ $supplier->phone ?? '—' }}
                        </span>
                    </td>
                    <td style="color: #64748b; font-size: 0.8125rem;">
                        {{ $supplier->email ?? '—' }}
                    </td>
                    <td style="color: #64748b; font-size: 0.8125rem; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ $supplier->address ?? '—' }}
                    </td>
                    <td>
                        @if($supplier->balance > 0)
                            <span class="badge badge-danger" style="font-weight: 700;">
                                LKR {{ number_format($supplier->balance, 2) }}
                            </span>
                        @else
                            <span class="badge badge-success">Settled (LKR 0.00)</span>
                        @endif
                    </td>
                    <td style="text-align: right; white-space: nowrap;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick='openEditSupplierModal(@json($supplier))' style="padding: 0.3rem 0.6rem;">Edit</button>
                        <form action="{{ route('shop.suppliers.destroy', $supplier) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete supplier {{ $supplier->name }}?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm" style="background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; padding: 0.3rem 0.6rem;">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 3rem; color: #94a3b8;">
                        No suppliers found. Click "+ Register Supplier" to add phone and spare parts vendors.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($suppliers->hasPages())
    <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border);">
        {{ $suppliers->links() }}
    </div>
    @endif
</div>

<!-- Modal Supplier (Add / Edit) -->
<div class="modal-backdrop" id="supplierModal">
    <div class="modal-box" style="max-width: 520px;">
        <div class="modal-header">
            <h3 id="supplierModalTitle">Register Supplier</h3>
            <button type="button" class="modal-close" onclick="closeModal('supplierModal')">&times;</button>
        </div>
        <form id="supplierForm" method="POST" action="{{ route('shop.suppliers.store') }}">
            @csrf
            <div id="supplierMethodPut"></div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Contact Person / Supplier Name *</label>
                    <input type="text" name="name" id="supName" class="form-control" required placeholder="e.g. Sunil Perera">
                </div>
                <div class="form-group">
                    <label>Company / Distributorship Name</label>
                    <input type="text" name="company_name" id="supCompany" class="form-control" placeholder="e.g. Apex Cellular Distributors Ltd">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" id="supPhone" class="form-control" placeholder="0771234567">
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" id="supEmail" class="form-control" placeholder="supplier@example.com">
                    </div>
                </div>
                <div class="form-group">
                    <label>Address / Warehouse Location</label>
                    <input type="text" name="address" id="supAddress" class="form-control" placeholder="123 1st Cross St, Colombo 11">
                </div>
                <div class="form-group">
                    <label>Current Outstanding Balance (LKR)</label>
                    <input type="number" step="0.01" name="balance" id="supBalance" class="form-control" placeholder="0.00" value="0.00">
                    <span style="font-size: 0.72rem; color: #64748b;">Amount your shop currently owes this supplier.</span>
                </div>
            </div>
            <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('supplierModal')">Cancel</button>
                <button type="submit" class="btn btn-primary" id="btnSaveSupplier">Save Supplier</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openAddSupplierModal() {
        document.getElementById('supplierModalTitle').innerText = 'Register Supplier';
        const form = document.getElementById('supplierForm');
        form.action = "{{ route('shop.suppliers.store') }}";
        document.getElementById('supplierMethodPut').innerHTML = '';
        form.reset();
        document.getElementById('supBalance').value = '0.00';
        openModal('supplierModal');
    }

    function openEditSupplierModal(supplier) {
        document.getElementById('supplierModalTitle').innerText = 'Edit Supplier: ' + supplier.name;
        const form = document.getElementById('supplierForm');
        form.action = `/shop/suppliers/${supplier.id}`;
        document.getElementById('supplierMethodPut').innerHTML = '@method("PUT")';
        document.getElementById('supName').value = supplier.name || '';
        document.getElementById('supCompany').value = supplier.company_name || '';
        document.getElementById('supPhone').value = supplier.phone || '';
        document.getElementById('supEmail').value = supplier.email || '';
        document.getElementById('supAddress').value = supplier.address || '';
        document.getElementById('supBalance').value = supplier.balance || 0;
        openModal('supplierModal');
    }
</script>
@endpush
@endsection

