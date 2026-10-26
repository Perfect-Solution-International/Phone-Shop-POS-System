@extends('layouts.shop')

@section('title', 'Used Phone Buying & Trade-Ins')
@section('page-title', 'Used Phone Buying & Trade-Ins')

@section('content')
<style>
    @media (max-width: 640px) {
        .tradein-checklist-grid {
            grid-template-columns: 1fr !important;
        }
    }
</style>
<!-- Metric Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">Total Used Devices Bought</span>
        <div style="font-size: 1.6rem; font-weight: 800; color: #0f172a; margin-top: 0.35rem;">
            {{ number_format($totalDevices) }}
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">Customer acquisitions &amp; trade-ins</div>
    </div>

    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">Total Valuation Paid</span>
        <div style="font-size: 1.6rem; font-weight: 800; color: #166534; margin-top: 0.35rem;">
            LKR {{ number_format($totalValuation, 2) }}
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">Cash payouts &amp; store credits</div>
    </div>

    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">Available Used Phones In Stock</span>
        <div style="font-size: 1.6rem; font-weight: 800; color: #4f46e5; margin-top: 0.35rem;">
            {{ number_format($inStockUsed) }}
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">Ready for immediate resale</div>
    </div>

    <div style="display: flex; align-items: center; justify-content: flex-end;">
        <button type="button" class="btn btn-primary" onclick="openModal('tradeInModal')" style="width: 100%; height: 100%; max-height: 52px; font-weight: 700;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            + Buy Used Phone / Trade-In
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <form method="GET" action="{{ route('shop.trade-ins.index') }}" style="display: flex; gap: 0.75rem; flex: 1; flex-wrap: wrap;">
            <input type="text" name="search" class="form-control" style="max-width: 280px;" placeholder="Search Customer, NIC, IMEI, Model..." value="{{ request('search') }}">
            <select name="status" class="form-control" style="max-width: 160px;" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('shop.trade-ins.index') }}" class="btn btn-secondary btn-sm" style="color: #64748b;">Clear</a>
            @endif
        </form>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Customer (Seller)</th>
                    <th>Device Details</th>
                    <th>IMEI 1 &amp; 2</th>
                    <th>Condition Audit</th>
                    <th>Valuation Payout</th>
                    <th>Payout Mode</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tradeIns as $ti)
                <tr>
                    <td style="font-size: 0.8125rem; color: #64748b; white-space: nowrap;">
                        {{ $ti->created_at->format('M d, Y') }}
                    </td>
                    <td>
                        <strong style="color: #0f172a; font-size: 0.875rem;">{{ $ti->customer_name }}</strong>
                        <div style="font-size: 0.72rem; color: #64748b;">
                            {{ $ti->customer_phone }} @if($ti->customer_nic)&bull; NIC: {{ $ti->customer_nic }}@endif
                        </div>
                    </td>
                    <td>
                        <strong style="color: #0f172a; font-size: 0.875rem;">{{ $ti->brand }} {{ $ti->model }}</strong>
                        <div style="font-size: 0.72rem; color: #64748b;">
                            {{ $ti->storage ?? '—' }} &bull; {{ $ti->color ?? 'Standard' }}
                        </div>
                    </td>
                    <td style="font-family: monospace; font-size: 0.8125rem; color: #334155;">
                        <div>IMEI 1: {{ $ti->imei_1 }}</div>
                        @if($ti->imei_2)<div style="color: #64748b; font-size: 0.75rem;">IMEI 2: {{ $ti->imei_2 }}</div>@endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 0.3rem; flex-wrap: wrap;">
                            <span class="badge" style="background: #f1f5f9; color: #334155; font-size: 0.7rem;">
                                Battery: {{ $ti->condition_checklist['battery_health'] ?? 'N/A' }}
                            </span>
                            <span class="badge" style="background: #ecfdf5; color: #047857; font-size: 0.7rem;">
                                {{ $ti->condition_checklist['account_lock'] ?? 'Unlocked' }}
                            </span>
                        </div>
                    </td>
                    <td>
                        <strong style="color: #166534; font-size: 0.9375rem;">
                            LKR {{ number_format($ti->valuation_amount, 2) }}
                        </strong>
                    </td>
                    <td>
                        @if($ti->payout_type === 'cash')
                            <span class="badge" style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0;">Cash Handover</span>
                        @elseif($ti->payout_type === 'trade_in_credit')
                            <span class="badge" style="background: #eef2ff; color: #3730a3; border: 1px solid #c7d2fe;">Trade-In Credit</span>
                        @else
                            <span class="badge" style="background: #f8fafc; color: #475569; border: 1px solid #cbd5e1;">Bank Transfer</span>
                        @endif
                    </td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick='viewTradeInSlip(@json($ti))' style="padding: 0.25rem 0.6rem; font-size: 0.75rem;">
                            🖨️ Receipt
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 3rem; color: #94a3b8;">
                        No used phone trade-in transactions recorded yet. Click "+ Buy Used Phone / Trade-In" to purchase a customer device.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($tradeIns->hasPages())
    <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border);">
        {{ $tradeIns->links() }}
    </div>
    @endif
</div>

<!-- Modal: Evaluate & Buy Used Phone -->
<div class="modal-backdrop" id="tradeInModal">
    <div class="modal-box" style="max-width: 640px;">
        <div class="modal-header">
            <h3>Used Phone Evaluation &amp; Trade-In</h3>
            <button type="button" class="modal-close" onclick="closeModal('tradeInModal')">&times;</button>
        </div>
        <form action="{{ route('shop.trade-ins.store') }}" method="POST">
            @csrf
            <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
                <div style="font-size: 0.8125rem; font-weight: 700; text-transform: uppercase; color: #4f46e5; margin-bottom: 0.5rem;">
                    1. Customer &amp; Seller Identification
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Customer Full Name *</label>
                        <input type="text" name="customer_name" class="form-control" required placeholder="e.g. Ruwan Jayasuriya">
                    </div>
                    <div class="form-group">
                        <label>Mobile Number *</label>
                        <input type="text" name="customer_phone" class="form-control" required placeholder="077xxxxxxx">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>NIC / Passport Number</label>
                        <input type="text" name="customer_nic" class="form-control" placeholder="e.g. 199512345678 or 951234567V">
                    </div>
                    <div class="form-group">
                        <label>Receiving Branch Outlet *</label>
                        <select name="outlet_id" class="form-control" required>
                            @foreach($outlets as $out)
                                <option value="{{ $out->id }}" {{ (session('active_outlet_id') == $out->id || $out->is_main) ? 'selected' : '' }}>{{ $out->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="font-size: 0.8125rem; font-weight: 700; text-transform: uppercase; color: #4f46e5; margin-top: 1rem; margin-bottom: 0.5rem; border-top: 1px dashed #cbd5e1; padding-top: 0.75rem;">
                    2. Device Specifications &amp; Dual IMEI
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Brand *</label>
                        <input type="text" name="brand" class="form-control" required placeholder="e.g. Apple / Samsung / Xiaomi">
                    </div>
                    <div class="form-group">
                        <label>Model *</label>
                        <input type="text" name="model" class="form-control" required placeholder="e.g. iPhone 13 Pro Max">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Storage</label>
                        <input type="text" name="storage" class="form-control" placeholder="e.g. 128GB / 256GB">
                    </div>
                    <div class="form-group">
                        <label>Color</label>
                        <input type="text" name="color" class="form-control" placeholder="e.g. Sierra Blue">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>IMEI 1 (Primary) *</label>
                        <input type="text" name="imei_1" class="form-control" required placeholder="15-digit IMEI 1" maxlength="50">
                    </div>
                    <div class="form-group">
                        <label>IMEI 2 (Secondary / eSIM)</label>
                        <input type="text" name="imei_2" class="form-control" placeholder="Optional 15-digit IMEI 2" maxlength="50">
                    </div>
                </div>

                <div style="font-size: 0.8125rem; font-weight: 700; text-transform: uppercase; color: #4f46e5; margin-top: 1rem; margin-bottom: 0.5rem; border-top: 1px dashed #cbd5e1; padding-top: 0.75rem;">
                    3. Condition Audit Checklist
                </div>
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Screen Condition</label>
                            <select name="screen_condition" class="form-control">
                                <option value="Flawless / Mint">Flawless / Mint (No scratches)</option>
                                <option value="Minor Scratches" selected>Minor Scratches (Normal wear)</option>
                                <option value="Deep Scratches">Deep Scratches</option>
                                <option value="Cracked Glass">Cracked Glass</option>
                                <option value="Display Defect (Lines/Dots)">Display Defect (Lines/Black spots)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Battery Health %</label>
                            <input type="text" name="battery_health" class="form-control" placeholder="e.g. 87%">
                        </div>
                    </div>
                    <div class="tradein-checklist-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-top: 0.5rem;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8125rem; cursor: pointer;">
                            <input type="checkbox" name="cameras_working" value="1" checked>
                            <span>Front &amp; Rear Cameras Functional</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8125rem; cursor: pointer;">
                            <input type="checkbox" name="biometrics_working" value="1" checked>
                            <span>Face ID / Fingerprint Working</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8125rem; cursor: pointer;">
                            <input type="checkbox" name="icloud_unlocked" value="1" checked>
                            <span>iCloud / Google FRP Account Removed</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8125rem; cursor: pointer;">
                            <input type="checkbox" name="accessories_included" value="Original Box & Charger">
                            <span>Includes Original Box &amp; Cable</span>
                        </label>
                    </div>
                </div>

                <div style="font-size: 0.8125rem; font-weight: 700; text-transform: uppercase; color: #4f46e5; margin-top: 1rem; margin-bottom: 0.5rem; border-top: 1px dashed #cbd5e1; padding-top: 0.75rem;">
                    4. Valuation &amp; Payout Terms
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Agreed Valuation Amount (LKR) *</label>
                        <input type="number" step="0.01" name="valuation_amount" class="form-control" required placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label>Target Resale Selling Price (LKR)</label>
                        <input type="number" step="0.01" name="resale_price" class="form-control" placeholder="Optional target price">
                    </div>
                </div>
                <div class="form-group">
                    <label>Payment Settlement Mode *</label>
                    <select name="payout_type" class="form-control" required>
                        <option value="cash" selected>Cash Handover (Direct purchase)</option>
                        <option value="trade_in_credit">Trade-In Store Credit (Deducted from new sale)</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Inspection Notes</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Tested charging port and speakers, verified clean IMEI on TRC."></textarea>
                </div>
            </div>
            <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('tradeInModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Complete Valuation &amp; Stock-In</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Printable Receipt -->
<div class="modal-backdrop" id="tradeInReceiptModal">
    <div class="modal-box" style="max-width: 520px; font-family: monospace;">
        <div class="modal-header">
            <h3>Used Phone Acquisition Receipt</h3>
            <button type="button" class="modal-close" onclick="closeModal('tradeInReceiptModal')">&times;</button>
        </div>
        <div class="modal-body" id="tradeInReceiptContent">
            <!-- Dynamically populated -->
        </div>
        <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.5rem;">
            <button type="button" class="btn btn-secondary" onclick="closeModal('tradeInReceiptModal')">Close</button>
            <button type="button" class="btn btn-primary" onclick="window.print()">🖨️ Print Document</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function viewTradeInSlip(ti) {
        const c = document.getElementById('tradeInReceiptContent');
        c.innerHTML = `
            <div style="text-align: center; border-bottom: 2px dashed #0f172a; padding-bottom: 0.75rem; margin-bottom: 1rem;">
                <h2 style="margin: 0; font-size: 1.25rem; font-weight: 800; color: #0f172a;">{{ auth()->user()->shop->name ?? 'Phone Shop' }}</h2>
                <div style="font-size: 0.75rem; color: #64748b;">Customer Device Acquisition &amp; Trade-In Certificate</div>
                <div style="font-size: 0.8125rem; font-weight: 700; color: #4f46e5; margin-top: 0.25rem;">Date: ${new Date(ti.created_at).toLocaleDateString()}</div>
            </div>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.75rem; font-size: 0.8125rem; margin-bottom: 1rem;">
                <strong>Seller Name:</strong> ${ti.customer_name}<br>
                <strong>Contact Phone:</strong> ${ti.customer_phone}<br>
                <strong>NIC / ID:</strong> ${ti.customer_nic || 'N/A'}
            </div>

            <div style="font-size: 0.8125rem; margin-bottom: 1rem;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr style="border-bottom: 1px solid #e2e8f0;"><td style="padding: 4px 0; color: #64748b;">Device:</td><td style="padding: 4px 0; font-weight: 700; text-align: right;">${ti.brand} ${ti.model}</td></tr>
                    <tr style="border-bottom: 1px solid #e2e8f0;"><td style="padding: 4px 0; color: #64748b;">IMEI 1:</td><td style="padding: 4px 0; font-family: monospace; text-align: right;">${ti.imei_1}</td></tr>
                    ${ti.imei_2 ? `<tr style="border-bottom: 1px solid #e2e8f0;"><td style="padding: 4px 0; color: #64748b;">IMEI 2:</td><td style="padding: 4px 0; font-family: monospace; text-align: right;">${ti.imei_2}</td></tr>` : ''}
                    <tr style="border-bottom: 1px solid #e2e8f0;"><td style="padding: 4px 0; color: #64748b;">Specs:</td><td style="padding: 4px 0; text-align: right;">${ti.storage || '—'} / ${ti.color || '—'}</td></tr>
                    <tr style="border-bottom: 1px solid #0f172a; font-weight: 800; font-size: 1rem; color: #166534;"><td style="padding: 8px 0;">Valuation Amount:</td><td style="padding: 8px 0; text-align: right;">LKR ${parseFloat(ti.valuation_amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</td></tr>
                </table>
            </div>

            <div style="font-size: 0.72rem; color: #64748b; line-height: 1.4; margin-bottom: 1.5rem; background: #fffbeb; padding: 0.5rem; border-radius: 4px; border: 1px solid #fef3c7;">
                <strong>Declaration:</strong> The seller hereby certifies that they are the lawful owner of the device specified above, with zero outstanding carrier contracts, iCloud locks, or illegal claims. Ownership is transferred to the shop upon payment.
            </div>

            <div style="display: flex; justify-content: space-between; margin-top: 2rem; font-size: 0.75rem;">
                <div style="text-align: center; border-top: 1px solid #94a3b8; width: 140px; padding-top: 4px;">Seller Signature</div>
                <div style="text-align: center; border-top: 1px solid #94a3b8; width: 140px; padding-top: 4px;">Authorized Agent</div>
            </div>
        `;
        openModal('tradeInReceiptModal');
    }
</script>
@endpush
@endsection

