@extends('layouts.shop')

@section('title', 'Warranty & Claims Management')
@section('page-title', 'Warranty Certificates & IMEI Coverage Claims')

@section('content')
<!-- Quick IMEI Warranty Verification Box -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <span class="card-title">Instant IMEI Warranty Verification</span>
    </div>
    <div class="card-body">
        <div style="display: flex; gap: 0.75rem; max-width: 650px;">
            <input type="text" id="verifyQuery" class="form-control" placeholder="Enter 15-digit IMEI or Warranty Number (e.g. WAR-2026-XXXXXX)..." style="font-family: monospace;">
            <button type="button" class="btn btn-primary" onclick="verifyWarranty()">Verify Status</button>
        </div>
        <div id="verifyResult" style="display: none; margin-top: 1rem; padding: 1rem; border-radius: 8px; border: 1px solid var(--surface-border);">
            <!-- Populated via JS -->
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <form method="GET" action="{{ route('shop.warranties') }}" style="display: flex; gap: 0.75rem; flex: 1; flex-wrap: wrap;">
            <input type="text" name="search" class="form-control" style="max-width: 300px;" placeholder="Search Warranty #, IMEI, Customer..." value="{{ request('search') }}">
            <select name="warranty_type" class="form-control" style="max-width: 170px;" onchange="this.form.submit()">
                <option value="">All Types</option>
                <option value="shop" {{ request('warranty_type') === 'shop' ? 'selected' : '' }}>Shop Warranty</option>
                <option value="manufacturer" {{ request('warranty_type') === 'manufacturer' ? 'selected' : '' }}>Manufacturer</option>
                <option value="repair" {{ request('warranty_type') === 'repair' ? 'selected' : '' }}>Repair Service</option>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
            @if(request()->hasAny(['search', 'warranty_type']))
                <a href="{{ route('shop.warranties') }}" class="btn btn-sm btn-secondary" style="color: #64748b;">Clear</a>
            @endif
        </form>

        <button type="button" class="btn btn-primary" onclick="openModal('issueWarrantyModal')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Issue Warranty
        </button>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Warranty # / Type</th>
                    <th>IMEI / Serial</th>
                    <th>Customer</th>
                    <th>Coverage Period</th>
                    <th>Status</th>
                    <th>Claims</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($warranties as $w)
                <tr>
                    <td>
                        <div style="font-family: monospace; font-weight: 700; color: #4f46e5;">{{ $w->warranty_number }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">{{ $w->warranty_type }} warranty</div>
                    </td>
                    <td>
                        @if($w->imei)
                            <span style="font-family: monospace; font-size: 0.8125rem; font-weight: 600; color: #0284c7;">{{ $w->imei }}</span>
                        @else
                            <span style="font-size: 0.75rem; color: var(--text-muted);">Standard item</span>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $w->customer->name ?? 'Walk-in Client' }}</strong>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $w->customer->phone ?? '' }}</div>
                    </td>
                    <td>
                        <div style="font-size: 0.8125rem;">
                            {{ $w->starts_on->toFormattedDateString() }} &rarr; <strong>{{ $w->ends_on->toFormattedDateString() }}</strong>
                        </div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $w->duration_months }} Months duration</div>
                    </td>
                    <td>
                        @if($w->isValid())
                            <span class="badge badge-success">Valid / Active</span>
                        @else
                            <span class="badge badge-danger">Expired</span>
                        @endif
                    </td>
                    <td>
                        @if($w->claims->isNotEmpty())
                            <span class="badge badge-warning">{{ $w->claims->count() }} Claims</span>
                        @else
                            <span style="font-size: 0.75rem; color: var(--text-muted);">0</span>
                        @endif
                    </td>
                    <td>
                        @if($w->isValid())
                            <button type="button" class="btn btn-sm btn-secondary" onclick="openClaimModal({{ $w->id }}, '{{ $w->warranty_number }}', '{{ $w->imei }}')">
                                File Claim
                            </button>
                        @else
                            <span style="font-size: 0.75rem; color: var(--text-muted);">Expired</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 3rem;">
                        No warranty certificates registered yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($warranties->hasPages())
    <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border);">
        {{ $warranties->links() }}
    </div>
    @endif
</div>

<!-- Modal: Issue Warranty -->
<div class="modal-backdrop" id="issueWarrantyModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Issue Warranty Certificate</h3>
            <button type="button" class="modal-close" onclick="closeModal('issueWarrantyModal')">&times;</button>
        </div>
        <form method="POST" action="{{ route('shop.warranties.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Customer</label>
                    <select name="customer_id" class="form-control">
                        <option value="">Select Customer (Optional)</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Warranty Type *</label>
                        <select name="warranty_type" class="form-control" required>
                            <option value="shop">Shop Warranty (Hardware/Motherboard)</option>
                            <option value="manufacturer">Official Company / Agent Warranty</option>
                            <option value="repair">Repair Service Guarantee (Display/Battery)</option>
                            <option value="supplier">Supplier Warranty</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Coverage Duration (Months) *</label>
                        <input type="number" name="duration_months" class="form-control" value="12" min="1" max="60" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Device IMEI (If Mobile Phone)</label>
                        <input type="text" name="imei" class="form-control" placeholder="15-digit IMEI" style="font-family: monospace;">
                    </div>
                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" name="starts_on" class="form-control" value="{{ now()->toDateString() }}">
                    </div>
                </div>

                <div class="form-group">
                    <label>Warranty Terms & Scope</label>
                    <textarea name="terms" class="form-control" rows="2" placeholder="e.g. 1 Year Motherboard & Display warranty. Liquid or accidental drop damage excluded."></textarea>
                </div>
            </div>
            <div style="padding: 1.25rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('issueWarrantyModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Certificate</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: File Warranty Claim -->
<div class="modal-backdrop" id="claimModal">
    <div class="modal-box" style="max-width: 500px;">
        <div class="modal-header">
            <h3>Record Warranty Claim</h3>
            <button type="button" class="modal-close" onclick="closeModal('claimModal')">&times;</button>
        </div>
        <form id="formClaim" method="POST">
            @csrf
            <div class="modal-body">
                <div style="margin-bottom: 1rem; font-size: 0.875rem;">
                    Certificate: <strong id="lblClaimWarranty" style="font-family: monospace; color:#4f46e5;"></strong>
                </div>

                <div class="form-group">
                    <label>Claim Problem / Defect Description *</label>
                    <textarea name="issue_description" class="form-control" rows="3" placeholder="Describe issue claimed by customer..." required></textarea>
                </div>

                <div class="form-group">
                    <label>Proposed Settlement Type</label>
                    <select name="settlement_type" class="form-control">
                        <option value="repair">Warranty Repair / Part Replacement</option>
                        <option value="replacement">Device Unit Replacement</option>
                        <option value="refund">Refund / Credit Note</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Inspector Action Notes</label>
                    <textarea name="action_notes" class="form-control" rows="2" placeholder="Inspection verification details..."></textarea>
                </div>
            </div>
            <div style="padding: 1.25rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('claimModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Submit Claim</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openClaimModal(id, warrantyNumber, imei) {
        document.getElementById('lblClaimWarranty').innerText = warrantyNumber + (imei ? ' (IMEI: ' + imei + ')' : '');
        document.getElementById('formClaim').action = '/shop/warranties/' + id + '/claim';
        openModal('claimModal');
    }

    async function verifyWarranty() {
        const query = document.getElementById('verifyQuery').value.trim();
        if (!query) {
            alert('Please enter an IMEI or Warranty Number.');
            return;
        }

        const resBox = document.getElementById('verifyResult');
        resBox.style.display = 'block';
        resBox.innerHTML = '<span style="color: var(--text-muted);">Checking warranty database...</span>';

        try {
            const res = await fetch('/api/v1/warranties/verify?query=' + encodeURIComponent(query), {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (!res.ok) {
                resBox.style.background = '#fee2e2';
                resBox.innerHTML = '<strong style="color: #991b1b;">No active warranty record found for: ' + query + '</strong>';
                return;
            }

            const data = await res.json();
            const isActive = data.status === 'active';

            resBox.style.background = isActive ? '#dcfce7' : '#fef3c7';
            resBox.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <strong style="color: ${isActive ? '#15803d' : '#b45309'}; font-size: 1rem;">
                        ${isActive ? '✓ Valid Warranty Found' : '⚠️ Warranty Has Expired'}
                    </strong>
                    <span class="badge ${isActive ? 'badge-success' : 'badge-warning'}">${data.status.toUpperCase()}</span>
                </div>
                <div style="font-size: 0.8125rem; color: #334155; line-height: 1.6;">
                    <div>Certificate: <strong>${data.warranty_number}</strong> | Type: <strong>${data.warranty_type}</strong></div>
                    <div>Customer: <strong>${data.customer_name}</strong> | IMEI: <code>${data.imei || 'N/A'}</code></div>
                    <div>Coverage: <strong>${data.starts_on}</strong> to <strong>${data.ends_on}</strong> (${data.duration_months} Months)</div>
                    <div>Total Prior Claims: <strong>${data.claims_count}</strong></div>
                </div>
            `;
        } catch (err) {
            resBox.style.background = '#fee2e2';
            resBox.innerHTML = '<strong style="color: #991b1b;">Error checking warranty status.</strong>';
        }
    }
</script>
@endpush
@endsection

