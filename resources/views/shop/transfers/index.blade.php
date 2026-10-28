@extends('layouts.shop')

@section('title', 'Branch Stock Transfers')
@section('page-title', 'Branch-to-Branch Stock Transfers')

@section('content')
<!-- Metric Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">Total Transfers Logged</span>
        <div style="font-size: 1.6rem; font-weight: 800; color: #0f172a; margin-top: 0.35rem;">
            {{ number_format($transfers->total()) }}
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">Inter-branch movements</div>
    </div>

    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">Currently In Transit</span>
        <div style="font-size: 1.6rem; font-weight: 800; color: #d97706; margin-top: 0.35rem;">
            {{ number_format($inTransitCount) }}
        </div>
        <div style="font-size: 0.75rem; color: #d97706; margin-top: 0.25rem;">Awaiting receiving confirmation</div>
    </div>

    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">Received &amp; Completed</span>
        <div style="font-size: 1.6rem; font-weight: 800; color: #166534; margin-top: 0.35rem;">
            {{ number_format($completedCount) }}
        </div>
        <div style="font-size: 0.75rem; color: #166534; margin-top: 0.25rem;">Stock updated at target branch</div>
    </div>

    <div style="display: flex; align-items: center; justify-content: flex-end;">
        <button type="button" class="btn btn-primary" onclick="openModal('transferModal')" style="width: 100%; height: 100%; max-height: 52px; font-weight: 700;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="17 1 21 5 17 9"></polyline><path d="M3 11V9a4 4 0 0 1 4-4h14"></path><polyline points="7 23 3 19 7 15"></polyline><path d="M21 13v2a4 4 0 0 1-4 4H3"></path></svg>
            Dispatch Branch Transfer
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <form method="GET" action="{{ route('shop.transfers.index') }}" style="display: flex; gap: 0.75rem; flex: 1; flex-wrap: wrap;">
            <select name="status" class="form-control" style="max-width: 170px;" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="in_transit" {{ request('status') === 'in_transit' ? 'selected' : '' }}>In Transit (Pending)</option>
                <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>Received / Confirmed</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
            @if(request()->has('status'))
                <a href="{{ route('shop.transfers.index') }}" class="btn btn-secondary btn-sm" style="color: #64748b;">Clear</a>
            @endif
        </form>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Transfer #</th>
                    <th>Date Dispatched</th>
                    <th>Source &rarr; Destination</th>
                    <th>Transferred Items</th>
                    <th>Dispatched By</th>
                    <th>Status</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transfers as $trf)
                <tr>
                    <td>
                        <strong style="color: #4f46e5; font-family: monospace; font-size: 0.9375rem;">
                            {{ $trf->transfer_number }}
                        </strong>
                    </td>
                    <td style="font-size: 0.8125rem; color: #64748b; white-space: nowrap;">
                        {{ $trf->created_at->format('M d, Y H:i') }}
                    </td>
                    <td>
                        <div style="font-size: 0.875rem; font-weight: 700; color: #0f172a;">
                            {{ $trf->fromOutlet->name ?? 'Main' }}
                            <span style="color: #64748b; font-weight: 400;">&rarr;</span>
                            <span style="color: #4f46e5;">{{ $trf->toOutlet->name ?? 'Branch' }}</span>
                        </div>
                    </td>
                    <td>
                        @foreach($trf->items as $item)
                            <div style="font-size: 0.8125rem; color: #334155;">
                                &bull; {{ $item->item_name }} (x{{ $item->quantity }})
                            </div>
                        @endforeach
                    </td>
                    <td style="font-size: 0.8125rem; color: #64748b;">
                        {{ $trf->dispatchedByUser->name ?? 'Staff' }}
                    </td>
                    <td>
                        @if($trf->status === 'in_transit')
                            <span class="badge badge-warning">🚚 In Transit</span>
                        @elseif($trf->status === 'received')
                            <span class="badge badge-success">✓ Received</span>
                        @else
                            <span class="badge" style="background: #f1f5f9; color: #64748b;">Cancelled</span>
                        @endif
                    </td>
                    <td style="text-align: right; white-space: nowrap;">
                        @if($trf->status === 'in_transit')
                            <form action="{{ route('shop.transfers.receive', $trf) }}" method="POST" style="display: inline-block;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-primary" style="padding: 0.25rem 0.6rem; font-size: 0.75rem;" onclick="return confirm('Confirm receipt of transfer {{ $trf->transfer_number }} into your branch stock?')">
                                    ✓ Confirm Receipt
                                </button>
                            </form>
                            <form action="{{ route('shop.transfers.cancel', $trf) }}" method="POST" style="display: inline-block;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-secondary" style="padding: 0.25rem 0.6rem; font-size: 0.75rem;" onclick="return confirm('Cancel this transfer and revert items?')">
                                    ✕ Cancel
                                </button>
                            </form>
                        @endif
                        <button type="button" class="btn btn-secondary btn-sm" onclick='viewTransferChallan(@json($trf))' style="padding: 0.25rem 0.6rem; font-size: 0.75rem;">
                            🖨️ Challan
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 3rem; color: #94a3b8;">
                        No branch stock transfers recorded. Click "+ Dispatch Branch Transfer" to move serialized phones or accessories between shop branches.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($transfers->hasPages())
    <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border);">
        {{ $transfers->links() }}
    </div>
    @endif
</div>

<!-- Modal: Dispatch Transfer -->
<div class="modal-backdrop" id="transferModal">
    <div class="modal-box" style="max-width: 540px;">
        <div class="modal-header">
            <h3>Dispatch Branch Stock Transfer</h3>
            <button type="button" class="modal-close" onclick="closeModal('transferModal')">&times;</button>
        </div>
        <form action="{{ route('shop.transfers.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Source Branch (From) *</label>
                        <select name="from_outlet_id" id="selFromOutlet" class="form-control" required>
                            @foreach($outlets as $out)
                                <option value="{{ $out->id }}" {{ (session('active_outlet_id') == $out->id) ? 'selected' : '' }}>{{ $out->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Destination Branch (To) *</label>
                        <select name="to_outlet_id" class="form-control" required>
                            <option value="">— Select Destination —</option>
                            @foreach($outlets as $out)
                                <option value="{{ $out->id }}">{{ $out->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Transfer Category *</label>
                    <select name="item_type" id="selTransferType" class="form-control" required onchange="toggleTransferItemType(this.value)">
                        <option value="phone" selected>Serialized Mobile Phone (By IMEI)</option>
                        <option value="product">Accessories / Products (By Quantity)</option>
                    </select>
                </div>

                <!-- Phone Item Selection -->
                <div class="form-group" id="divTransferPhone">
                    <label>Select In-Stock Phone *</label>
                    <select name="phone_device_id" class="form-control">
                        <option value="">— Select Available Phone —</option>
                        @foreach($availablePhones as $p)
                            <option value="{{ $p->id }}">{{ $p->brand }} {{ $p->model }} (IMEI: {{ $p->imei_1 }}) &bull; {{ $p->color }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Product Item Selection -->
                <div id="divTransferProduct" style="display: none;">
                    <div class="form-group">
                        <label>Select Accessory / Product *</label>
                        <select name="product_id" class="form-control">
                            <option value="">— Select Product —</option>
                            @foreach($availableProducts as $prod)
                                <option value="{{ $prod->id }}">{{ $prod->name }} (Available: {{ $prod->quantity }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Quantity to Transfer *</label>
                        <input type="number" name="quantity" class="form-control" value="1" min="1">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label>Transfer Instructions / Notes</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Dispatched via shop driver Kamal for customer reservation."></textarea>
                </div>
            </div>
            <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('transferModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Dispatch Stock Transfer</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Transfer Challan Print -->
<div class="modal-backdrop" id="challanModal">
    <div class="modal-box" style="max-width: 520px; font-family: monospace;">
        <div class="modal-header">
            <h3>Branch Transfer Dispatch Challan</h3>
            <button type="button" class="modal-close" onclick="closeModal('challanModal')">&times;</button>
        </div>
        <div class="modal-body" id="challanContent">
            <!-- Dynamically populated -->
        </div>
        <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.5rem;">
            <button type="button" class="btn btn-secondary" onclick="closeModal('challanModal')">Close</button>
            <button type="button" class="btn btn-primary" onclick="window.print()">🖨️ Print Challan</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleTransferItemType(type) {
        document.getElementById('divTransferPhone').style.display = type === 'phone' ? 'block' : 'none';
        document.getElementById('divTransferProduct').style.display = type === 'product' ? 'block' : 'none';
    }

    function viewTransferChallan(trf) {
        const c = document.getElementById('challanContent');
        c.innerHTML = `
            <div style="text-align: center; border-bottom: 2px dashed #0f172a; padding-bottom: 0.75rem; margin-bottom: 1rem;">
                <h2 style="margin: 0; font-size: 1.25rem; font-weight: 800; color: #0f172a;">{{ auth()->user()->shop->name ?? 'Phone Shop' }}</h2>
                <div style="font-size: 0.75rem; color: #64748b;">Inter-Branch Stock Transfer Challan</div>
                <div style="font-size: 0.8125rem; font-weight: 700; color: #4f46e5; margin-top: 0.25rem;">Transfer Ref: ${trf.transfer_number}</div>
                <div style="font-size: 0.75rem; color: #64748b;">Date: ${new Date(trf.created_at).toLocaleDateString()}</div>
            </div>

            <div style="display: flex; justify-content: space-between; font-size: 0.8125rem; background: #f8fafc; padding: 0.75rem; border-radius: 6px; margin-bottom: 1rem;">
                <div>
                    <strong>Dispatched From:</strong><br>
                    ${trf.from_outlet ? trf.from_outlet.name : 'Origin Branch'}
                </div>
                <div style="text-align: right;">
                    <strong>Destination:</strong><br>
                    ${trf.to_outlet ? trf.to_outlet.name : 'Target Branch'}
                </div>
            </div>

            <table style="width: 100%; border-collapse: collapse; font-size: 0.8125rem; margin-bottom: 1rem;">
                <thead>
                    <tr style="border-bottom: 1px solid #cbd5e1; text-align: left; background: #f1f5f9;">
                        <th style="padding: 6px 8px;">Item Description</th>
                        <th style="padding: 6px 8px; text-align: right;">Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    ${trf.items.map(it => `
                        <tr style="border-bottom: 1px dashed #e2e8f0;">
                            <td style="padding: 6px 8px;">${it.item_name}</td>
                            <td style="padding: 6px 8px; text-align: right; font-weight: 700;">${it.quantity}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>

            <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 1.5rem;">
                Status: <strong>${trf.status.toUpperCase()}</strong> &bull; Dispatched by: ${trf.dispatched_by_user ? trf.dispatched_by_user.name : 'Staff'}
            </div>

            <div style="display: flex; justify-content: space-between; margin-top: 2.5rem; font-size: 0.75rem;">
                <div style="text-align: center; border-top: 1px solid #94a3b8; width: 140px; padding-top: 4px;">Dispatched By</div>
                <div style="text-align: center; border-top: 1px solid #94a3b8; width: 140px; padding-top: 4px;">Received By</div>
            </div>
        `;
        openModal('challanModal');
    }
</script>
@endpush
@endsection

