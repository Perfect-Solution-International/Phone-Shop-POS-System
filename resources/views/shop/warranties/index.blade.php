@extends('layouts.shop')

@section('title', 'Warranty & Claims Management')
@section('page-title', 'Warranty Certificates & IMEI Coverage Claims')

@section('content')
<style>
    @media (max-width: 576px) {
        .warranty-verify-box {
            flex-direction: column !important;
        }
        .warranty-verify-box button {
            width: 100%;
        }
    }
</style>
<!-- Quick IMEI Warranty Verification Box -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <span class="card-title">Instant IMEI Warranty Verification</span>
    </div>
    <div class="card-body">
        <div class="warranty-verify-box" style="display: flex; gap: 0.75rem; max-width: 650px;">
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
                            <button type="button" 
                                    class="badge badge-warning" 
                                    style="cursor: pointer; border: none; padding: 0.35rem 0.6rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.25rem;" 
                                    data-claims="{{ json_encode($w->claims) }}" 
                                    data-warranty-number="{{ $w->warranty_number }}"
                                    onclick="openClaimsHistoryModalFromButton(this)"
                                    title="Click to view claims history & review">
                                <span>📋</span> {{ $w->claims->count() }} Claims
                            </button>
                        @else
                            <span style="font-size: 0.75rem; color: var(--text-muted);">0</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 0.35rem; align-items: center;">
                            @if($w->customer && $w->customer->phone)
                                @php
                                    $rawPhone = preg_replace('/[^0-9]/', '', $w->customer->phone);
                                    $waPhone = str_starts_with($rawPhone, '0') ? '94' . substr($rawPhone, 1) : $rawPhone;
                                    $waEndDate = $w->ends_on ? $w->ends_on->format('Y-m-d') : 'Active';
                                    $waMsg = urlencode("Hello {$w->customer->name}, your warranty certificate #{$w->warranty_number} (IMEI/Serial: {$w->imei}) is valid until {$waEndDate}. Thank you!");
                                @endphp
                                <a href="https://wa.me/{{ $waPhone }}?text={{ $waMsg }}" target="_blank" class="btn btn-sm" title="Send Warranty Details via WhatsApp" style="background: #25D366; color: #ffffff; padding: 0.25rem 0.5rem; font-size: 0.75rem; display: inline-flex; align-items: center; text-decoration: none; border-radius: 4px; font-weight: 700;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 0.2rem;"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                                    WA
                                </a>
                            @endif
                            @if($w->isValid())
                                <button type="button" class="btn btn-sm btn-secondary" onclick="openClaimModal({{ $w->id }}, '{{ $w->warranty_number }}', '{{ $w->imei }}')">
                                    File Claim
                                </button>
                            @else
                                <span style="font-size: 0.75rem; color: var(--text-muted);">Expired</span>
                            @endif
                        </div>
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

<!-- Modal: Claims History & Decision Review -->
<div class="modal-backdrop" id="claimsHistoryModal">
    <div class="modal-box" style="max-width: 650px;">
        <div class="modal-header">
            <h3>Claims History &amp; Decision Review</h3>
            <button type="button" class="modal-close" onclick="closeModal('claimsHistoryModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div style="margin-bottom: 1rem; font-size: 0.875rem;">
                Certificate: <strong id="lblHistoryWarranty" style="font-family: monospace; color:#4f46e5;"></strong>
            </div>

            <div id="claimsHistoryContainer" style="display: flex; flex-direction: column; gap: 0.85rem;">
                <!-- Populated via JS -->
            </div>
        </div>
        <div style="padding: 1.25rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end;">
            <button type="button" class="btn btn-secondary" onclick="closeModal('claimsHistoryModal')">Close</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openClaimModal(id, warrantyNumber, imei) {
        document.getElementById('lblClaimWarranty').innerText = warrantyNumber + (imei ? ' (IMEI: ' + imei + ')' : '');
        document.getElementById('formClaim').action = '/shop/warranties/' + id + '/claim';
        openModal('claimModal');
    }

    function openClaimsHistoryModalFromButton(btn) {
        const claims = JSON.parse(btn.getAttribute('data-claims') || '[]');
        const warrantyNum = btn.getAttribute('data-warranty-number');
        
        document.getElementById('lblHistoryWarranty').innerText = warrantyNum;
        const container = document.getElementById('claimsHistoryContainer');
        container.innerHTML = '';

        if (!claims || claims.length === 0) {
            container.innerHTML = '<p style="color: var(--text-muted); text-align: center; padding: 1.5rem 0;">No claims recorded for this warranty certificate.</p>';
        } else {
            claims.forEach(function(c) {
                const card = document.createElement('div');
                card.style.cssText = 'border: 1px solid var(--surface-border); border-radius: 8px; padding: 1rem; background: #f8fafc;';
                
                let statusBadge = '<span class="badge badge-warning">Pending</span>';
                if (c.status === 'inspecting') statusBadge = '<span class="badge badge-info">Inspecting</span>';
                if (c.status === 'approved') statusBadge = '<span class="badge badge-success">Approved</span>';
                if (c.status === 'rejected') statusBadge = '<span class="badge badge-danger">Rejected</span>';
                if (c.status === 'settled') statusBadge = '<span class="badge badge-success" style="background:#059669; color:#ffffff;">Settled</span>';

                const csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';

                card.innerHTML = `
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                        <div>
                            <strong style="font-family: monospace; color: #0284c7; font-size: 0.9375rem;">${c.claim_number}</strong>
                            <span style="font-size: 0.75rem; color: var(--text-muted); margin-left: 0.5rem;">Filed on ${c.claim_date}</span>
                        </div>
                        ${statusBadge}
                    </div>
                    <div style="font-size: 0.8125rem; color: #334155; margin-bottom: 0.5rem;">
                        <strong>Problem Claimed:</strong> ${c.issue_description || 'N/A'}
                    </div>
                    <div style="font-size: 0.8125rem; color: #475569; margin-bottom: 0.5rem;">
                        <strong>Proposed Settlement:</strong> <span style="text-transform: capitalize;">${c.settlement_type || 'Repair'}</span>
                        ${c.action_notes ? '<br><strong>Inspector Action Notes:</strong> ' + c.action_notes : ''}
                    </div>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="toggleReviewForm(${c.id})" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                        ✎ Review / Decision Update
                    </button>
                    <div id="review-form-${c.id}" style="display: none; margin-top: 0.75rem; background: #ffffff; border: 1px solid #cbd5e1; padding: 0.75rem; border-radius: 6px;">
                        <form method="POST" action="/shop/warranties/claims/${c.id}/review">
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <div style="margin-bottom: 0.5rem;">
                                <label style="font-size: 0.75rem; font-weight: 700; display: block; margin-bottom: 0.25rem;">Review Status *</label>
                                <select name="status" class="form-control" style="font-size: 0.8125rem; padding: 0.35rem;" required>
                                    <option value="pending" ${c.status === 'pending' ? 'selected' : ''}>Pending</option>
                                    <option value="inspecting" ${c.status === 'inspecting' ? 'selected' : ''}>Under Inspection</option>
                                    <option value="approved" ${c.status === 'approved' ? 'selected' : ''}>Approved</option>
                                    <option value="rejected" ${c.status === 'rejected' ? 'selected' : ''}>Rejected</option>
                                    <option value="settled" ${c.status === 'settled' ? 'selected' : ''}>Settled (Complete)</option>
                                </select>
                            </div>
                            <div style="margin-bottom: 0.5rem;">
                                <label style="font-size: 0.75rem; font-weight: 700; display: block; margin-bottom: 0.25rem;">Settlement Decision</label>
                                <select name="settlement_type" class="form-control" style="font-size: 0.8125rem; padding: 0.35rem;">
                                    <option value="repair" ${c.settlement_type === 'repair' ? 'selected' : ''}>Warranty Repair / Part Fix</option>
                                    <option value="replacement" ${c.settlement_type === 'replacement' ? 'selected' : ''}>Replacement Unit</option>
                                    <option value="refund" ${c.settlement_type === 'refund' ? 'selected' : ''}>Refund / Store Credit</option>
                                </select>
                            </div>
                            <div style="margin-bottom: 0.5rem;">
                                <label style="font-size: 0.75rem; font-weight: 700; display: block; margin-bottom: 0.25rem;">Inspector Action Notes</label>
                                <textarea name="action_notes" class="form-control" rows="2" style="font-size: 0.8125rem;" placeholder="Verification details, inspection outcome...">${c.action_notes || ''}</textarea>
                            </div>
                            <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                <button type="button" class="btn btn-sm btn-secondary" onclick="toggleReviewForm(${c.id})">Cancel</button>
                                <button type="submit" class="btn btn-sm btn-primary">Save Decision</button>
                            </div>
                        </form>
                    </div>
                `;
                container.appendChild(card);
            });
        }
        openModal('claimsHistoryModal');
    }

    function toggleReviewForm(claimId) {
        const el = document.getElementById('review-form-' + claimId);
        if (el) {
            el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
        }
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

