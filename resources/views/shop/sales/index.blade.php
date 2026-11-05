@extends('layouts.shop')

@section('title', 'Sales & Customer Purchases')
@section('page-title', 'Sales & Customer Purchases Directory')

@section('content')
@include('shop.partials.pos-subnav')

<!-- Sales Metric Summary Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.04em; display: block;">Today's Sales Revenue</span>
        <div style="font-size: 1.5rem; font-weight: 850; color: #047857; margin-top: 0.35rem;">
            LKR {{ number_format($todayTotal, 2) }}
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">{{ $todayCount }} transactions recorded today</div>
    </div>
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.04em; display: block;">Total Completed Sales</span>
        <div style="font-size: 1.5rem; font-weight: 850; color: #4338ca; margin-top: 0.35rem;">
            {{ number_format($totalSalesCount) }}
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">Lifetime completed transactions</div>
    </div>
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
        <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.04em; display: block;">All-Time Sales Revenue</span>
        <div style="font-size: 1.5rem; font-weight: 850; color: #0f172a; margin-top: 0.35rem;">
            LKR {{ number_format($totalSalesAmount, 2) }}
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">Gross sales ledger amount</div>
    </div>
</div>

<style>
    @media (max-width: 768px) {
        .sales-desktop-table-wrap {
            display: none !important;
        }
        .sales-mobile-cards-wrap {
            display: flex !important;
            flex-direction: column;
            gap: 0.85rem;
            padding: 1rem;
        }
    }
    @media (min-width: 769px) {
        .sales-mobile-cards-wrap {
            display: none !important;
        }
    }
    .sale-mobile-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
    }
</style>
<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <form method="GET" action="{{ route('shop.sales') }}" style="display: flex; gap: 0.75rem; flex: 1; flex-wrap: wrap;">
            <input type="text" name="search" class="form-control" style="max-width: 280px;" placeholder="Search Invoice #, Customer, IMEI, Item..." value="{{ request('search') }}">
            <input type="date" name="date" class="form-control" style="max-width: 160px;" value="{{ request('date') }}">
            <select name="payment_method" class="form-control" style="max-width: 160px;" onchange="this.form.submit()">
                <option value="">All Payment Methods</option>
                <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                <option value="card" {{ request('payment_method') === 'card' ? 'selected' : '' }}>Card</option>
            </select>
            <select name="status" class="form-control" style="max-width: 140px;" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial</option>
                <option value="credit" {{ request('status') === 'credit' ? 'selected' : '' }}>Credit</option>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
            @if(request()->hasAny(['search', 'date', 'payment_method', 'status']))
                <a href="{{ route('shop.sales') }}" class="btn btn-sm btn-secondary" style="color: #64748b;">Clear</a>
            @endif
        </form>
    </div>

    <div class="table-responsive sales-desktop-table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Date & Time</th>
                    <th>Customer</th>
                    <th>Items Sold (Summary & IMEI)</th>
                    <th>Payment Method</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                <tr>
                    <td>
                        <strong style="color: #4f46e5; font-family: monospace; font-size: 0.9375rem;">
                            {{ $sale->invoice_number }}
                        </strong>
                        <div style="font-size: 0.72rem; color: var(--text-muted);">
                            Cashier: {{ $sale->cashier->name ?? 'Staff' }}
                        </div>
                    </td>
                    <td>
                        <div style="font-size: 0.875rem; font-weight: 600;">
                            {{ \Carbon\Carbon::parse($sale->created_at)->format('Y-m-d') }}
                        </div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">
                            {{ \Carbon\Carbon::parse($sale->created_at)->format('h:i A') }}
                        </div>
                    </td>
                    <td>
                        @if($sale->customer)
                            <strong style="color: var(--text-heading); font-size: 0.875rem;">{{ $sale->customer->name }}</strong>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $sale->customer->phone }}</div>
                        @else
                            <span style="color: var(--text-muted); font-style: italic; font-size: 0.8125rem;">Walk-in Customer</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; flex-direction: column; gap: 0.25rem; max-width: 320px;">
                            @foreach($sale->items as $item)
                                <div style="font-size: 0.8125rem;">
                                    <span style="font-weight: 600;">{{ $item->item_name }}</span>
                                    <span style="color: var(--text-muted);">x{{ $item->quantity }}</span>
                                    @if($item->phoneDevice)
                                        <span class="badge badge-info" style="font-size: 0.68rem; font-family: monospace; padding: 0.1rem 0.35rem;">
                                            IMEI: {{ $item->phoneDevice->imei_1 }}
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </td>
                    <td>
                        @if($sale->payment_method === 'split')
                            <span class="badge badge-purple">Split Tender</span>
                            <div style="font-size: 0.72rem; color: #64748b; margin-top: 2px;">
                                @foreach($sale->payments as $p)
                                    <div>{{ ucfirst(str_replace('_', ' ', $p->payment_method)) }}: {{ number_format($p->amount, 2) }}</div>
                                @endforeach
                            </div>
                        @elseif($sale->payment_method === 'cash')
                            <span class="badge badge-success">Cash</span>
                        @elseif($sale->payment_method === 'card')
                            <span class="badge badge-info">Card</span>
                        @else
                            <span class="badge badge-warning">{{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }}</span>
                        @endif
                    </td>
                    <td>
                        <strong style="color: #166534; font-size: 0.9375rem;">
                            LKR {{ number_format($sale->total_amount, 2) }}
                        </strong>
                        @if($sale->discount_amount > 0)
                            <div style="font-size: 0.72rem; color: #b91c1c;">
                                Disc: -LKR {{ number_format($sale->discount_amount, 2) }}
                            </div>
                        @endif
                    </td>
                    <td>
                        @if($sale->status === 'completed')
                            <span class="badge badge-success">Completed</span>
                        @elseif($sale->status === 'partial')
                            <span class="badge badge-warning">Partial</span>
                        @else
                            <span class="badge badge-purple">{{ ucfirst($sale->status) }}</span>
                        @endif
                    </td>
                    <td style="text-align: right; white-space: nowrap;">
                        <div style="display: inline-flex; align-items: center; gap: 0.35rem;">
                            @if($sale->customer && $sale->customer->phone)
                                @php
                                    $rawPhone = preg_replace('/[^0-9]/', '', $sale->customer->phone);
                                    $waPhone = str_starts_with($rawPhone, '0') ? '94' . substr($rawPhone, 1) : $rawPhone;
                                    $waMsg = urlencode("Hello {$sale->customer->name}, thank you for your purchase! Invoice #{$sale->invoice_number}, Total Amount: LKR " . number_format($sale->total_amount, 2) . ". Thank you!");
                                @endphp
                                <a href="https://wa.me/{{ $waPhone }}?text={{ $waMsg }}" target="_blank" class="btn btn-sm" title="Send Receipt via WhatsApp" style="background: #25D366; color: #ffffff; padding: 0.35rem 0.55rem; font-size: 0.75rem; display: inline-flex; align-items: center; text-decoration: none; border-radius: 4px; font-weight: 700;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 0.2rem;"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                                    WA
                                </a>
                            @endif
                            @if(in_array($sale->status, ['credit', 'partial']) && $sale->customer && (float) $sale->customer->balance > 0)
                                <button type="button" 
                                        class="btn btn-sm" 
                                        onclick="openSettleDebtModal({{ $sale->customer->id }}, '{{ addslashes($sale->customer->name) }}', {{ (float) $sale->customer->balance }}, '{{ $sale->invoice_number }}')" 
                                        title="Settle Customer Debt" 
                                        style="background: #10b981; color: #ffffff; padding: 0.35rem 0.55rem; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 700; border-radius: 4px;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                                    <span>Settle</span>
                                </button>
                            @endif
                            <button type="button" 
                                    class="btn btn-secondary btn-sm" 
                                    onclick='viewInvoiceModal({{ $sale->id }})' 
                                    title="View Invoice & Details" 
                                    style="padding: 0.35rem 0.65rem; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.3rem;">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                Details
                            </button>
                            <a href="{{ route('shop.sales.show', $sale) }}" 
                               class="btn btn-primary btn-sm" 
                               target="_blank"
                               title="Full Invoice / Print" 
                               style="padding: 0.35rem 0.65rem; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.3rem;">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                                Print
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 3rem;">
                        No sales transactions recorded yet. Completed POS sales will appear here with full line-item details.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Card View (Auto-enabled on screens <= 768px) -->
    <div class="sales-mobile-cards-wrap">
        @forelse($sales as $sale)
        <div class="sale-mobile-card">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong style="color: #4f46e5; font-family: monospace; font-size: 0.95rem;">
                        {{ $sale->invoice_number }}
                    </strong>
                    <div style="font-size: 0.72rem; color: #64748b;">
                        {{ \Carbon\Carbon::parse($sale->created_at)->format('Y-m-d h:i A') }} &bull; Cashier: {{ $sale->cashier->name ?? 'Staff' }}
                    </div>
                </div>
                <div>
                    @if($sale->status === 'completed')
                        <span class="badge badge-success">Completed</span>
                    @elseif($sale->status === 'partial')
                        <span class="badge badge-warning">Partial</span>
                    @else
                        <span class="badge badge-purple">{{ ucfirst($sale->status) }}</span>
                    @endif
                </div>
            </div>

            <div style="font-size: 0.8125rem; background: #f8fafc; border-radius: 8px; padding: 0.5rem 0.75rem;">
                <div style="font-weight: 700; color: #0f172a;">
                    {{ $sale->customer ? $sale->customer->name : 'Walk-in Customer' }}
                    @if($sale->customer && $sale->customer->phone)
                        <span style="font-size: 0.75rem; color: #64748b; font-weight: normal;">({{ $sale->customer->phone }})</span>
                    @endif
                </div>
                <div style="margin-top: 0.35rem; color: #475569; font-size: 0.78rem;">
                    @foreach($sale->items as $item)
                        <div>
                            &bull; {{ $item->item_name }} &times; {{ $item->quantity }}
                            @if($item->phoneDevice)
                                <span class="badge badge-info" style="font-size: 0.65rem; font-family: monospace;">IMEI: {{ $item->phoneDevice->imei_1 }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px dashed #e2e8f0; padding-top: 0.5rem;">
                <div>
                    <span style="font-size: 0.72rem; color: #64748b; text-transform: uppercase; font-weight: 700; display: block;">Total Amount</span>
                    <strong style="color: #166534; font-size: 1.05rem;">
                        LKR {{ number_format($sale->total_amount, 2) }}
                    </strong>
                    <div style="font-size: 0.7rem; color: #64748b;">
                        {{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }}
                    </div>
                </div>
                <div style="display: flex; gap: 0.4rem; align-items: center;">
                    @if($sale->customer && $sale->customer->phone)
                        @php
                            $rawPhone = preg_replace('/[^0-9]/', '', $sale->customer->phone);
                            $waPhone = str_starts_with($rawPhone, '0') ? '94' . substr($rawPhone, 1) : $rawPhone;
                            $waMsg = urlencode("Hello {$sale->customer->name}, thank you for your purchase! Invoice #{$sale->invoice_number}, Total Amount: LKR " . number_format($sale->total_amount, 2) . ". Thank you!");
                        @endphp
                        <a href="https://wa.me/{{ $waPhone }}?text={{ $waMsg }}" target="_blank" class="btn btn-sm" title="WhatsApp Receipt" style="background: #25D366; color: #ffffff; padding: 0.4rem 0.6rem; font-size: 0.75rem; border-radius: 6px; font-weight: 700;">
                            WA
                        </a>
                    @endif
                    @if(in_array($sale->status, ['credit', 'partial']) && $sale->customer && (float) $sale->customer->balance > 0)
                        <button type="button" 
                                class="btn btn-sm" 
                                onclick="openSettleDebtModal({{ $sale->customer->id }}, '{{ addslashes($sale->customer->name) }}', {{ (float) $sale->customer->balance }}, '{{ $sale->invoice_number }}')" 
                                style="background: #10b981; color: #ffffff; padding: 0.4rem 0.6rem; font-size: 0.75rem; border-radius: 6px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                            <span>Settle</span>
                        </button>
                    @endif
                    <button type="button" class="btn btn-secondary btn-sm" onclick='viewInvoiceModal({{ $sale->id }})' style="font-size: 0.75rem; padding: 0.4rem 0.65rem; border-radius: 6px;">
                        Details
                    </button>
                    <a href="{{ route('shop.sales.show', $sale) }}" class="btn btn-primary btn-sm" target="_blank" style="font-size: 0.75rem; padding: 0.4rem 0.65rem; border-radius: 6px;">
                        Print
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div style="text-align: center; color: #94a3b8; padding: 2rem;">
            No sales transactions recorded yet.
        </div>
        @endforelse
    </div>

    @if($sales->hasPages())
    <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border);">
        {{ $sales->links() }}
    </div>
    @endif
</div>

<!-- Modal: Invoice Details Quick View -->
<div class="modal-backdrop" id="invoiceDetailsModal">
    <div class="modal-box" style="max-width: 580px;">
        <div class="modal-header">
            <h3 id="invModalTitle">Sale Invoice Details</h3>
            <button type="button" class="modal-close" onclick="closeModal('invoiceDetailsModal')">&times;</button>
        </div>
        <div class="modal-body" id="invModalBody" style="font-family: inherit;">
            <div style="text-align: center; padding: 2rem; color: #64748b;">Loading sale details...</div>
        </div>
        <div style="padding: 1.25rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: space-between; align-items: center;">
            <a href="#" id="btnFullInvoiceLink" target="_blank" class="btn btn-secondary btn-sm">Open Full Invoice</a>
            <div style="display: flex; gap: 0.5rem;">
                <button type="button" class="btn btn-primary btn-sm" onclick="printModalInvoice()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                    Print Receipt
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('invoiceDetailsModal')">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Settle Customer Debt -->
<div class="modal-backdrop" id="settleDebtModal">
    <div class="modal-box" style="max-width: 480px;">
        <div class="modal-header">
            <h3 style="display: flex; align-items: center; gap: 0.5rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="color: #059669;"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                <span>Settle Customer Outstanding Debt</span>
            </h3>
            <button type="button" class="modal-close" onclick="closeModal('settleDebtModal')">&times;</button>
        </div>
        <form method="POST" id="settleDebtForm" action="">
            @csrf
            <div class="modal-body" style="display: flex; flex-direction: column; gap: 1rem;">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.85rem;">
                    <div style="font-size: 0.75rem; color: #64748b; font-weight: 650; text-transform: uppercase;">Customer Name</div>
                    <div id="debtCustomerName" style="font-size: 1rem; font-weight: 750; color: #0f172a;">-</div>
                    <div style="margin-top: 0.4rem; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.75rem; color: #64748b;">Current Outstanding Debt:</span>
                        <strong id="debtCurrentBalance" style="color: #b91c1c; font-size: 1.05rem;">LKR 0.00</strong>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Payment Amount (LKR) *</label>
                    <input type="number" step="0.01" name="amount" id="debtAmountInput" class="form-control" required min="0.01" placeholder="Enter amount to settle">
                </div>

                <div class="form-group">
                    <label class="form-label">Payment Method *</label>
                    <select name="payment_method" class="form-control" required>
                        <option value="cash">Cash (Updates Daily Cashbook)</option>
                        <option value="card">Card Payment</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Reference / Slip Number (Optional)</label>
                    <input type="text" name="reference" id="debtReferenceInput" class="form-control" placeholder="e.g. Counter Payment, Receipt #">
                </div>

                <div class="form-group">
                    <label class="form-label">Notes (Optional)</label>
                    <input type="text" name="notes" class="form-control" placeholder="Settlement notes">
                </div>
            </div>
            <div class="modal-footer" style="padding: 1rem 1.5rem; display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid #e2e8f0;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('settleDebtModal')">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background: #10b981; border-color: #10b981;">Confirm Settlement</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    async function viewInvoiceModal(saleId) {
        openModal('invoiceDetailsModal');
        const body = document.getElementById('invModalBody');
        body.innerHTML = '<div style="text-align: center; padding: 2rem; color: #64748b;">Loading sale details...</div>';
        document.getElementById('btnFullInvoiceLink').href = `/shop/sales/${saleId}`;

        try {
            const res = await fetch(`/shop/sales/${saleId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (!res.ok) throw new Error('Failed to load invoice details.');
            const sale = await res.json();
            renderModalInvoice(sale);
        } catch (err) {
            body.innerHTML = `<div style="text-align: center; color: #b91c1c; padding: 2rem;">${err.message}</div>`;
        }
    }

    function renderModalInvoice(sale) {
        document.getElementById('invModalTitle').innerText = `Invoice #${sale.invoice_number}`;
        const body = document.getElementById('invModalBody');

        const itemsHtml = sale.items.map(item => `
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 0.65rem 0.5rem;">
                    <strong style="color: #0f172a; font-size: 0.875rem;">${item.item_name}</strong>
                    ${item.phone_device ? `
                        <div style="font-family: monospace; font-size: 0.72rem; color: #0284c7; margin-top: 2px;">
                            IMEI 1: ${item.phone_device.imei_1} ${item.phone_device.imei_2 ? ' | IMEI 2: ' + item.phone_device.imei_2 : ''}
                        </div>
                    ` : ''}
                </td>
                <td style="padding: 0.65rem 0.5rem; text-align: center; color: #475569;">${item.quantity}</td>
                <td style="padding: 0.65rem 0.5rem; text-align: right; color: #475569;">${parseFloat(item.unit_price).toFixed(2)}</td>
                <td style="padding: 0.65rem 0.5rem; text-align: right; font-weight: 700; color: #0f172a;">${parseFloat(item.subtotal).toFixed(2)}</td>
            </tr>
        `).join('');

        const paymentsHtml = sale.payments.map(p => `
            <div style="display:flex; justify-content:space-between; font-size: 0.8125rem; color: #475569; margin-bottom: 0.2rem;">
                <span>${p.payment_method.toUpperCase().replace('_', ' ')} ${p.reference ? '(' + p.reference + ')' : ''}</span>
                <span style="font-weight: 600;">LKR ${parseFloat(p.amount).toFixed(2)}</span>
            </div>
        `).join('');

        body.innerHTML = `
            <div id="printableReceiptArea">
                <div style="text-align: center; border-bottom: 1px dashed #cbd5e1; padding-bottom: 0.75rem; margin-bottom: 1rem;">
                    <h3 style="margin: 0; font-size: 1.15rem; font-weight: 800; color: #0f172a;">{{ auth()->user()->shop->name ?? 'Phone Shop' }}</h3>
                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;">Official Tax Invoice & Receipt</div>
                    <div style="font-size: 0.8125rem; margin-top: 0.35rem; font-weight: 700; color: #4f46e5; font-family: monospace;">Invoice: ${sale.invoice_number}</div>
                    <div style="font-size: 0.75rem; color: #64748b;">Date: ${sale.sale_date} | Cashier: ${sale.cashier ? sale.cashier.name : 'Staff'}</div>
                </div>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.65rem 0.85rem; margin-bottom: 1rem; font-size: 0.8125rem;">
                    <strong>Customer:</strong> ${sale.customer ? sale.customer.name + ' (' + sale.customer.phone + ')' : 'Walk-in Customer'}<br>
                    ${sale.customer && sale.customer.address ? '<span style="color:#64748b;">Address: ' + sale.customer.address + '</span>' : ''}
                </div>

                <table style="width: 100%; border-collapse: collapse; font-size: 0.8125rem; margin-bottom: 1rem;">
                    <thead>
                        <tr style="border-bottom: 2px solid #cbd5e1; text-align: left; color: #64748b; font-size: 0.75rem; text-transform: uppercase;">
                            <th style="padding: 0.4rem 0.5rem;">Item & Serial</th>
                            <th style="padding: 0.4rem 0.5rem; text-align: center;">Qty</th>
                            <th style="padding: 0.4rem 0.5rem; text-align: right;">Unit (LKR)</th>
                            <th style="padding: 0.4rem 0.5rem; text-align: right;">Total (LKR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${itemsHtml}
                    </tbody>
                </table>

                <div style="border-top: 1.5px dashed #cbd5e1; padding-top: 0.75rem; margin-bottom: 1rem;">
                    <div style="display:flex; justify-content:space-between; font-size: 0.8125rem; margin-bottom: 0.25rem;">
                        <span style="color: #64748b;">Subtotal:</span>
                        <span style="font-weight: 600;">LKR ${parseFloat(sale.subtotal).toFixed(2)}</span>
                    </div>
                    ${sale.discount_amount > 0 ? `
                        <div style="display:flex; justify-content:space-between; font-size: 0.8125rem; margin-bottom: 0.25rem; color: #b91c1c;">
                            <span>Discount:</span>
                            <span>-LKR ${parseFloat(sale.discount_amount).toFixed(2)}</span>
                        </div>
                    ` : ''}
                    <div style="display:flex; justify-content:space-between; font-size: 1.05rem; font-weight: 800; color: #0f172a; border-top: 1px solid #e2e8f0; padding-top: 0.4rem; margin-top: 0.4rem;">
                        <span>Net Total:</span>
                        <span style="color: #166534;">LKR ${parseFloat(sale.total_amount).toFixed(2)}</span>
                    </div>
                </div>

                <div style="background: #f8fafc; border-radius: 6px; padding: 0.65rem 0.85rem; margin-bottom: 0.75rem;">
                    <div style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 0.35rem;">Payment Tender Breakdown</div>
                    ${paymentsHtml}
                </div>

                <div style="font-size: 0.72rem; color: #64748b; text-align: center; margin-top: 0.75rem; border-top: 1px dashed #cbd5e1; padding-top: 0.5rem;">
                    Thank you for your business! Serialized phones come with warranty protection as stamped.
                </div>
            </div>
        `;
    }

    function printModalInvoice() {
        const printContent = document.getElementById('printableReceiptArea');
        if (!printContent) return;

        const printWindow = window.open('', '', 'width=650,height=700');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Print Invoice</title>
                    <style>
                        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, monospace; margin: 20px; font-size: 12px; color: #0f172a; }
                        table { width: 100%; border-collapse: collapse; }
                        th, td { padding: 5px; }
                        @media print {
                            body { margin: 0; }
                        }
                    </style>
                </head>
                <body>
                    ${printContent.innerHTML}
                </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 300);
    }

    function openSettleDebtModal(customerId, customerName, currentBalance, invoiceNumber) {
        document.getElementById('settleDebtForm').action = `/shop/customers/${customerId}/settle-debt`;
        document.getElementById('debtCustomerName').innerText = customerName;
        document.getElementById('debtCurrentBalance').innerText = 'LKR ' + parseFloat(currentBalance).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('debtAmountInput').value = parseFloat(currentBalance).toFixed(2);
        document.getElementById('debtAmountInput').max = parseFloat(currentBalance).toFixed(2);
        document.getElementById('debtReferenceInput').value = `Settlement for ${invoiceNumber}`;
        openModal('settleDebtModal');
    }
</script>
@endpush
@endsection

