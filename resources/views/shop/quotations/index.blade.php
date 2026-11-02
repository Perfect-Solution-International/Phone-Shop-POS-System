@extends('layouts.shop')

@section('title', 'Quotations & Proforma Invoices')
@section('page-title', 'Quotations & Price Estimates')

@section('content')
<!-- Metric Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">Active Pending Quotes</span>
        <div style="font-size: 1.6rem; font-weight: 800; color: #d97706; margin-top: 0.35rem;">
            {{ number_format($pendingCount) }}
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">Awaiting customer purchase decision</div>
    </div>

    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">Potential Pipeline Value</span>
        <div style="font-size: 1.6rem; font-weight: 800; color: #166534; margin-top: 0.35rem;">
            LKR {{ number_format($totalQuotationValue, 2) }}
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">Total value of pending estimates</div>
    </div>

    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; display: block;">Converted to Invoices</span>
        <div style="font-size: 1.6rem; font-weight: 800; color: #4f46e5; margin-top: 0.35rem;">
            {{ number_format($convertedCount) }}
        </div>
        <div style="font-size: 0.75rem; color: #166534; margin-top: 0.25rem;">Directly billed &amp; paid</div>
    </div>

    <div style="display: flex; align-items: center; justify-content: flex-end;">
        <button type="button" class="btn btn-primary" onclick="openModal('quotationModal')" style="width: 100%; height: 100%; max-height: 52px; font-weight: 700;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Create Price Quotation
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <form method="GET" action="{{ route('shop.quotations.index') }}" style="display: flex; gap: 0.75rem; flex: 1; flex-wrap: wrap;">
            <input type="text" name="search" class="form-control" style="max-width: 280px;" placeholder="Search Quotation #, Customer..." value="{{ request('search') }}">
            <select name="status" class="form-control" style="max-width: 170px;" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="converted" {{ request('status') === 'converted' ? 'selected' : '' }}>Converted to Sale</option>
                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('shop.quotations.index') }}" class="btn btn-secondary btn-sm" style="color: #64748b;">Clear</a>
            @endif
        </form>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Quote #</th>
                    <th>Date &amp; Validity</th>
                    <th>Customer</th>
                    <th>Quoted Items</th>
                    <th>Subtotal</th>
                    <th>Total Estimate</th>
                    <th>Status</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($quotations as $qt)
                <tr>
                    <td>
                        <strong style="color: #4f46e5; font-family: monospace; font-size: 0.9375rem;">
                            {{ $qt->quotation_number }}
                        </strong>
                    </td>
                    <td>
                        <div style="font-size: 0.8125rem; color: #0f172a;">Issued: {{ $qt->quotation_date->format('M d, Y') }}</div>
                        <div style="font-size: 0.72rem; color: {{ $qt->valid_until->isPast() ? '#b91c1c' : '#64748b' }};">
                            Valid until: {{ $qt->valid_until->format('M d, Y') }}
                        </div>
                    </td>
                    <td>
                        <strong style="color: #0f172a; font-size: 0.875rem;">{{ $qt->customer_name }}</strong>
                        <div style="font-size: 0.72rem; color: #64748b;">{{ $qt->customer_phone ?? 'No phone' }}</div>
                    </td>
                    <td>
                        @foreach($qt->items as $it)
                            <div style="font-size: 0.8125rem; color: #334155;">
                                &bull; {{ $it->item_name }} (x{{ $it->quantity }})
                            </div>
                        @endforeach
                    </td>
                    <td style="font-size: 0.875rem; color: #475569;">
                        LKR {{ number_format($qt->subtotal, 2) }}
                    </td>
                    <td>
                        <strong style="color: #166534; font-size: 0.9375rem;">
                            LKR {{ number_format($qt->total_amount, 2) }}
                        </strong>
                    </td>
                    <td>
                        @if($qt->status === 'pending')
                            <span class="badge badge-warning">Pending Decision</span>
                        @elseif($qt->status === 'converted')
                            <span class="badge badge-success">✓ Converted to Invoice</span>
                        @else
                            <span class="badge" style="background: #f1f5f9; color: #64748b;">Expired</span>
                        @endif
                    </td>
                    <td style="text-align: right; white-space: nowrap;">
                        @if($qt->status === 'pending')
                            <button type="button" class="btn btn-sm btn-primary" onclick='openConvertModal(@json($qt))' style="padding: 0.25rem 0.6rem; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                                Convert to Sale
                            </button>
                        @elseif($qt->convertedSale)
                            <a href="{{ route('shop.sales.show', $qt->convertedSale) }}" class="btn btn-sm btn-secondary" style="padding: 0.25rem 0.6rem; font-size: 0.75rem;">
                                View Invoice
                            </a>
                        @endif
                        <button type="button" class="btn btn-secondary btn-sm" onclick='viewQuotationDocument(@json($qt))' style="padding: 0.25rem 0.6rem; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                            Print Quote
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 3rem; color: #94a3b8;">
                        No price quotations issued yet. Click "+ Create Price Quotation" to generate formal customer estimates.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($quotations->hasPages())
    <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border);">
        {{ $quotations->links() }}
    </div>
    @endif
</div>

<!-- Modal: Create Quotation -->
<div class="modal-backdrop" id="quotationModal">
    <div class="modal-box" style="max-width: 620px;">
        <div class="modal-header">
            <h3>Create Customer Price Quotation</h3>
            <button type="button" class="modal-close" onclick="closeModal('quotationModal')">&times;</button>
        </div>
        <form action="{{ route('shop.quotations.store') }}" method="POST">
            @csrf
            <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
                <div class="form-row">
                    <div class="form-group">
                        <label>Customer Name *</label>
                        <input type="text" name="customer_name" class="form-control" required placeholder="e.g. Kasun Fernando">
                    </div>
                    <div class="form-group">
                        <label>Customer Mobile Phone</label>
                        <input type="text" name="customer_phone" class="form-control" placeholder="077xxxxxxx">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Customer Email</label>
                        <input type="email" name="customer_email" class="form-control" placeholder="client@example.com">
                    </div>
                    <div class="form-group">
                        <label>Validity (Valid Until) *</label>
                        <input type="date" name="valid_until" class="form-control" required value="{{ date('Y-m-d', strtotime('+14 days')) }}">
                    </div>
                </div>

                <div style="font-size: 0.8125rem; font-weight: 700; text-transform: uppercase; color: #4f46e5; margin-top: 1rem; margin-bottom: 0.5rem; border-top: 1px dashed #cbd5e1; padding-top: 0.75rem;">
                    Quotation Line Items
                </div>

                <div id="quotationItemsContainer">
                    <div class="quote-item-row" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem; margin-bottom: 0.75rem;">
                        <input type="hidden" name="items[0][item_type]" value="custom">
                        <div class="form-row">
                            <div class="form-group" style="flex: 2;">
                                <label>Item Name / Phone / Accessory *</label>
                                <input type="text" name="items[0][item_name]" class="form-control quote-name" required placeholder="e.g. Samsung Galaxy S24 Ultra 256GB">
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <label>Qty *</label>
                                <input type="number" name="items[0][quantity]" class="form-control quote-qty" required value="1" min="1" oninput="calculateQuoteTotal()">
                            </div>
                            <div class="form-group" style="flex: 1.2;">
                                <label>Unit Price (LKR) *</label>
                                <input type="number" step="0.01" name="items[0][unit_price]" class="form-control quote-price" required placeholder="0.00" oninput="calculateQuoteTotal()">
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-secondary btn-sm" onclick="addQuoteItemRow()" style="margin-bottom: 1rem;">
                    + Add Another Item
                </button>

                <div class="form-group">
                    <label>Overall Discount (LKR)</label>
                    <input type="number" step="0.01" name="discount" id="inpQuoteDiscount" class="form-control" placeholder="0.00" value="0.00" oninput="calculateQuoteTotal()">
                </div>

                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 1rem; display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <span style="font-weight: 700; color: #166534; font-size: 0.875rem;">Total Estimated Quotation:</span>
                    <strong id="lblQuoteTotal" style="color: #166534; font-size: 1.25rem;">LKR 0.00</strong>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label>Terms &amp; Warranty Conditions</label>
                    <textarea name="terms" class="form-control" rows="2">1 Year company warranty included. Prices valid for 14 days. Subject to stock availability.</textarea>
                </div>
            </div>
            <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('quotationModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save &amp; Generate Quotation</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Convert to Sale -->
<div class="modal-backdrop" id="convertModal">
    <div class="modal-box" style="max-width: 460px;">
        <div class="modal-header">
            <h3>Convert Quotation to Invoice</h3>
            <button type="button" class="modal-close" onclick="closeModal('convertModal')">&times;</button>
        </div>
        <form id="convertForm" method="POST">
            @csrf
            <div class="modal-body">
                <div style="background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                    <div style="font-size: 0.8125rem; color: #4338ca;">Quotation Reference:</div>
                    <strong id="lblConvertRef" style="font-size: 1.1rem; color: #312e81; display: block;">—</strong>
                    <div style="display: flex; justify-content: space-between; margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px dashed #c7d2fe;">
                        <span style="font-size: 0.8125rem; color: #4338ca;">Invoice Total Amount:</span>
                        <strong id="lblConvertTotal" style="font-size: 1.15rem; color: #166534;">LKR 0.00</strong>
                    </div>
                </div>

                <div class="form-group">
                    <label>Select Payment Tender *</label>
                    <select name="payment_method" class="form-control" required>
                        <option value="cash" selected>Cash Payment</option>
                        <option value="card">Card Payment</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </div>
                <div style="font-size: 0.75rem; color: #64748b;">
                    * Converting will create an official invoice, decrement inventory stock, and log payment.
                </div>
            </div>
            <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('convertModal')">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background: #166534; border-color: #166534;">✓ Confirm &amp; Issue Invoice</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Printable Quotation Document -->
<div class="modal-backdrop" id="quoteDocModal">
    <div class="modal-box" style="max-width: 540px; font-family: monospace;">
        <div class="modal-header">
            <h3>Price Quotation Document</h3>
            <button type="button" class="modal-close" onclick="closeModal('quoteDocModal')">&times;</button>
        </div>
        <div class="modal-body" id="quoteDocContent">
            <!-- Dynamically populated -->
        </div>
        <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.5rem;">
            <button type="button" class="btn btn-secondary" onclick="closeModal('quoteDocModal')">Close</button>
            <button type="button" class="btn btn-primary" onclick="window.print()" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                Print Quotation
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let quoteItemIndex = 1;

    function addQuoteItemRow() {
        const container = document.getElementById('quotationItemsContainer');
        const div = document.createElement('div');
        div.className = 'quote-item-row';
        div.style = 'background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem; margin-bottom: 0.75rem;';
        div.innerHTML = `
            <input type="hidden" name="items[${quoteItemIndex}][item_type]" value="custom">
            <div class="form-row">
                <div class="form-group" style="flex: 2;">
                    <label>Item Name *</label>
                    <input type="text" name="items[${quoteItemIndex}][item_name]" class="form-control quote-name" required placeholder="e.g. 20W USB-C Fast Charger">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>Qty *</label>
                    <input type="number" name="items[${quoteItemIndex}][quantity]" class="form-control quote-qty" required value="1" min="1" oninput="calculateQuoteTotal()">
                </div>
                <div class="form-group" style="flex: 1.2;">
                    <label>Unit Price *</label>
                    <input type="number" step="0.01" name="items[${quoteItemIndex}][unit_price]" class="form-control quote-price" required placeholder="0.00" oninput="calculateQuoteTotal()">
                </div>
            </div>
            <div style="text-align: right;">
                <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.quote-item-row').remove(); calculateQuoteTotal();" style="padding: 0.2rem 0.5rem; font-size: 0.72rem;">Remove</button>
            </div>
        `;
        container.appendChild(div);
        quoteItemIndex++;
    }

    function calculateQuoteTotal() {
        const rows = document.querySelectorAll('.quote-item-row');
        let subtotal = 0;
        rows.forEach(r => {
            const qty = parseFloat(r.querySelector('.quote-qty').value || 0);
            const price = parseFloat(r.querySelector('.quote-price').value || 0);
            subtotal += (qty * price);
        });

        const discount = parseFloat(document.getElementById('inpQuoteDiscount').value || 0);
        const total = Math.max(0, subtotal - discount);
        document.getElementById('lblQuoteTotal').innerText = 'LKR ' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function openConvertModal(qt) {
        document.getElementById('lblConvertRef').innerText = qt.quotation_number + ' — ' + qt.customer_name;
        document.getElementById('lblConvertTotal').innerText = 'LKR ' + parseFloat(qt.total_amount).toLocaleString('en-US', { minimumFractionDigits: 2 });
        document.getElementById('convertForm').action = `/shop/quotations/${qt.id}/convert`;
        openModal('convertModal');
    }

    function viewQuotationDocument(qt) {
        const c = document.getElementById('quoteDocContent');
        c.innerHTML = `
            <div style="text-align: center; border-bottom: 2px dashed #0f172a; padding-bottom: 0.75rem; margin-bottom: 1rem;">
                <h2 style="margin: 0; font-size: 1.3rem; font-weight: 800; color: #0f172a;">{{ auth()->user()->shop->name ?? 'Phone Shop' }}</h2>
                <div style="font-size: 0.75rem; color: #64748b;">Official Price Quotation / Proforma Estimate</div>
                <div style="font-size: 0.8125rem; font-weight: 700; color: #4f46e5; margin-top: 0.25rem;">Quotation: ${qt.quotation_number}</div>
            </div>

            <div style="display: flex; justify-content: space-between; font-size: 0.8125rem; background: #f8fafc; padding: 0.75rem; border-radius: 6px; margin-bottom: 1rem;">
                <div>
                    <strong>Quote To:</strong> ${qt.customer_name}<br>
                    <strong>Contact:</strong> ${qt.customer_phone || '—'}<br>
                    <strong>Email:</strong> ${qt.customer_email || '—'}
                </div>
                <div style="text-align: right;">
                    <strong>Date:</strong> ${qt.quotation_date}<br>
                    <strong>Valid Until:</strong> ${qt.valid_until}<br>
                    <strong>Status:</strong> ${qt.status.toUpperCase()}
                </div>
            </div>

            <table style="width: 100%; border-collapse: collapse; font-size: 0.8125rem; margin-bottom: 1rem;">
                <thead>
                    <tr style="border-bottom: 1px solid #cbd5e1; text-align: left; background: #f1f5f9;">
                        <th style="padding: 6px 8px;">Item Description</th>
                        <th style="padding: 6px 8px; text-align: center;">Qty</th>
                        <th style="padding: 6px 8px; text-align: right;">Price</th>
                        <th style="padding: 6px 8px; text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    ${qt.items.map(it => `
                        <tr style="border-bottom: 1px dashed #e2e8f0;">
                            <td style="padding: 6px 8px;">${it.item_name}</td>
                            <td style="padding: 6px 8px; text-align: center;">${it.quantity}</td>
                            <td style="padding: 6px 8px; text-align: right;">${parseFloat(it.unit_price).toFixed(2)}</td>
                            <td style="padding: 6px 8px; text-align: right; font-weight: 700;">${parseFloat(it.subtotal).toFixed(2)}</td>
                        </tr>
                    `).join('')}
                    ${qt.discount > 0 ? `
                        <tr style="color: #b91c1c;">
                            <td colspan="3" style="padding: 6px 8px; text-align: right;">Discount:</td>
                            <td style="padding: 6px 8px; text-align: right;">-LKR ${parseFloat(qt.discount).toFixed(2)}</td>
                        </tr>
                    ` : ''}
                    <tr style="border-top: 2px solid #0f172a; font-weight: 800; font-size: 0.95rem; color: #166534; background: #f0fdf4;">
                        <td colspan="3" style="padding: 8px;">ESTIMATED TOTAL:</td>
                        <td style="padding: 8px; text-align: right;">LKR ${parseFloat(qt.total_amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                    </tr>
                </tbody>
            </table>

            ${qt.terms ? `
                <div style="font-size: 0.72rem; color: #64748b; line-height: 1.4; margin-bottom: 1.5rem; background: #f8fafc; padding: 0.5rem; border-radius: 4px;">
                    <strong>Terms &amp; Conditions:</strong> ${qt.terms}
                </div>
            ` : ''}

            <div style="display: flex; justify-content: space-between; margin-top: 2.5rem; font-size: 0.75rem;">
                <div style="text-align: center; border-top: 1px solid #94a3b8; width: 140px; padding-top: 4px;">Prepared By</div>
                <div style="text-align: center; border-top: 1px solid #94a3b8; width: 140px; padding-top: 4px;">Customer Acceptance</div>
            </div>
        `;
        openModal('quoteDocModal');
    }
</script>
@endpush
@endsection

