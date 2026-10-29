@extends('layouts.shop')

@section('title', 'Subscription & Billing')
@section('page-title', 'Shop Subscription & SaaS Plan')

@section('content')
<div style="display: flex; flex-direction: column; gap: 1.5rem;">
    <!-- Active Subscription Status Banner -->
    <div class="card" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); color: white; padding: 1.5rem; border-radius: 12px; border: none;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div>
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                    <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; background: rgba(255,255,255,0.2); padding: 0.25rem 0.6rem; border-radius: 4px; letter-spacing: 0.05em;">
                        Current Plan
                    </span>
                    @if($subscription && $subscription->isActive())
                        <span class="badge" style="background: #22c55e; color: #fff; font-weight: 700;">● Active</span>
                    @elseif($subscription && $subscription->isTrial())
                        <span class="badge" style="background: #38bdf8; color: #fff; font-weight: 700;">● Trial Period</span>
                    @else
                        <span class="badge" style="background: #ef4444; color: #fff; font-weight: 700;">● Inactive / Expired</span>
                    @endif
                </div>

                <h2 style="font-size: 1.75rem; font-weight: 800; margin: 0 0 0.5rem 0; color: #ffffff;">
                    {{ $subscription && $subscription->plan ? $subscription->plan->name : 'No Active Subscription' }}
                </h2>

                <div style="display: flex; gap: 1.5rem; font-size: 0.875rem; opacity: 0.9; flex-wrap: wrap;">
                    @if($subscription && $subscription->current_period_ends_at)
                        <div>
                            Expires On: <strong>{{ $subscription->current_period_ends_at->format('M d, Y') }}</strong>
                            ({{ $daysRemaining }} days remaining)
                        </div>
                    @endif
                    @if($subscription)
                        <div>
                            Billing Cycle: <strong>{{ ucfirst($subscription->billing_cycle ?? 'Monthly') }}</strong>
                        </div>
                        <div>
                            Recurring: <strong>LKR {{ number_format($subscription->recurring_amount, 2) }}</strong>
                        </div>
                    @endif
                </div>
            </div>

            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                <button type="button" class="btn" onclick="openModal('cardCheckoutModal')" style="background: #4f46e5; color: white; font-weight: 700; padding: 0.75rem 1.25rem; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.2); display: inline-flex; align-items: center; gap: 0.5rem; border: none; cursor: pointer;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                    Pay Online by Card (Instant)
                </button>
                <button type="button" class="btn" onclick="openModal('bankTransferModal')" style="background: rgba(255,255,255,0.15); color: white; font-weight: 600; padding: 0.75rem 1.25rem; border-radius: 8px; border: 1px solid rgba(255,255,255,0.3); cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="21" x2="21" y2="21"></line><line x1="3" y1="10" x2="21" y2="10"></line><polyline points="5 6 12 3 19 6"></polyline><line x1="4" y1="10" x2="4" y2="21"></line><line x1="20" y1="10" x2="20" y2="21"></line><line x1="8" y1="14" x2="8" y2="17"></line><line x1="12" y1="14" x2="12" y2="17"></line><line x1="16" y1="14" x2="16" y2="17"></line></svg>
                    Upload Bank Slip
                </button>
            </div>
        </div>
    </div>

    <!-- Available Plans Pricing Grid -->
    <div>
        <h3 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin-bottom: 1rem;">
            Choose a Subscription Plan to Upgrade or Renew
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
            @foreach($plans as $plan)
                @php
                    $isCurrentPlan = $subscription && $subscription->plan_id == $plan->id && $subscription->isActive();
                @endphp
                <div class="card" style="padding: 1.5rem; border-radius: 12px; display: flex; flex-direction: column; justify-content: space-between; border: 2px solid {{ $isCurrentPlan ? '#4f46e5' : '#e2e8f0' }}; position: relative;">
                    @if($isCurrentPlan)
                        <div style="position: absolute; top: -12px; right: 20px; background: #4f46e5; color: white; font-size: 0.75rem; font-weight: 800; padding: 0.2rem 0.6rem; border-radius: 12px; text-transform: uppercase;">
                            Current Plan
                        </div>
                    @endif

                    <div>
                        <h4 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0 0 0.5rem 0;">
                            {{ $plan->name }}
                        </h4>
                        <div style="font-size: 0.85rem; color: #64748b; margin-bottom: 1.25rem; min-height: 40px;">
                            {{ $plan->description ?? 'Full-featured phone retail management for expanding shops.' }}
                        </div>

                        <!-- Price display -->
                        <div style="margin-bottom: 1.25rem; background: #f8fafc; padding: 1rem; border-radius: 8px;">
                            <div style="display: flex; align-items: baseline; gap: 0.25rem;">
                                <span style="font-size: 1.5rem; font-weight: 900; color: #0f172a;">Rs. {{ number_format($plan->monthly_price, 0) }}</span>
                                <span style="font-size: 0.8rem; color: #64748b;">/ month</span>
                            </div>
                            <div style="font-size: 0.75rem; color: #16a34a; font-weight: 700; margin-top: 0.25rem;">
                                or Rs. {{ number_format($plan->annual_price, 0) }} / year (Annual Savings)
                            </div>
                        </div>

                        <!-- Features -->
                        <ul style="list-style: none; padding: 0; margin: 0 0 1.5rem 0; font-size: 0.85rem; color: #334155; display: flex; flex-direction: column; gap: 0.5rem;">
                            <li style="display: flex; align-items: center; gap: 0.5rem;">
                                <span style="color: #22c55e; font-weight: bold;">✓</span>
                                Up to <strong>{{ $plan->max_branches }} Outlets / Branches</strong>
                            </li>
                            <li style="display: flex; align-items: center; gap: 0.5rem;">
                                <span style="color: #22c55e; font-weight: bold;">✓</span>
                                Up to <strong>{{ $plan->max_staff }} Staff Cashiers / Techs</strong>
                            </li>
                            <li style="display: flex; align-items: center; gap: 0.5rem;">
                                <span style="color: #22c55e; font-weight: bold;">✓</span>
                                Serialized <strong>Dual IMEI Phone Tracking</strong>
                            </li>
                            <li style="display: flex; align-items: center; gap: 0.5rem;">
                                <span style="color: #22c55e; font-weight: bold;">✓</span>
                                <strong>8-Stage Repair &amp; SMS Job Pipeline</strong>
                            </li>
                            <li style="display: flex; align-items: center; gap: 0.5rem;">
                                <span style="color: #22c55e; font-weight: bold;">✓</span>
                                Thermal Barcode Sticker Printing
                            </li>
                        </ul>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 0.4rem;">
                        <div style="display: flex; gap: 0.4rem;">
                            <button type="button" class="btn btn-primary" onclick="selectPlanForCheckout({{ $plan->id }}, '{{ addslashes($plan->name) }}', {{ $plan->monthly_price }}, {{ $plan->annual_price }})" style="flex: 1; justify-content: center; font-size: 0.8rem; padding: 0.55rem;">
                                Pay by Card
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="selectPlanForBank({{ $plan->id }}, '{{ addslashes($plan->name) }}', {{ $plan->monthly_price }}, {{ $plan->annual_price }})" style="font-size: 0.8rem; padding: 0.55rem;">
                                Bank Slip
                            </button>
                        </div>
                        <form method="POST" action="{{ route('shop.subscription.gateway.initiate') }}" style="margin: 0;">
                            @csrf
                            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                            <input type="hidden" name="billing_cycle" value="monthly">
                            <button type="submit" class="btn btn-info" style="width: 100%; justify-content: center; font-size: 0.78rem; padding: 0.45rem; background: #6366f1; border-color: #6366f1; color: white; display: inline-flex; align-items: center; gap: 0.35rem;">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                                Payment Gateway (Sandbox)
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Billing & Payment History -->
    <div class="card" style="padding: 1.5rem; border-radius: 12px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h4 style="font-size: 1rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                Subscription Payment Invoices
            </h4>
            <span class="badge badge-secondary">{{ $payments->total() }} payments</span>
        </div>

        <div class="table-responsive">
            <table class="table" style="font-size: 0.85rem;">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Plan</th>
                        <th>Billing Cycle</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>Transaction / Ref</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr>
                        <td style="white-space: nowrap; color: #64748b;">
                            {{ $payment->created_at->format('M d, Y h:i A') }}
                        </td>
                        <td style="font-weight: 700; color: #0f172a;">
                            {{ $payment->plan ? $payment->plan->name : 'Custom Plan' }}
                        </td>
                        <td style="text-transform: capitalize;">
                            {{ $payment->billing_cycle }}
                        </td>
                        <td style="font-weight: 800; color: #0f172a;">
                            LKR {{ number_format($payment->amount, 2) }}
                        </td>
                        <td>
                            @if($payment->payment_method === 'card')
                                <span class="badge" style="background: #e0e7ff; color: #4338ca; font-weight: 700; display: inline-flex; align-items: center; gap: 0.25rem;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                                    Online Card
                                </span>
                            @else
                                <span class="badge badge-secondary" style="display: inline-flex; align-items: center; gap: 0.25rem;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 2 7 22 7 12 2"></polygon><line x1="4" y1="22" x2="20" y2="22"></line><line x1="4" y1="11" x2="4" y2="18"></line><line x1="10" y1="11" x2="10" y2="18"></line><line x1="14" y1="11" x2="14" y2="18"></line><line x1="20" y1="11" x2="20" y2="18"></line></svg>
                                    Bank Transfer
                                </span>
                            @endif
                        </td>
                        <td style="font-family: monospace; font-size: 0.8rem;">
                            {{ $payment->transaction_id ?? $payment->reference_number }}
                        </td>
                        <td>
                            @if($payment->status === 'approved')
                                <span class="badge badge-success">Approved</span>
                            @elseif($payment->status === 'pending')
                                <span class="badge badge-warning">Awaiting Approval</span>
                            @elseif($payment->status === 'refunded')
                                <span class="badge badge-danger">Refunded</span>
                            @else
                                <span class="badge badge-secondary">{{ ucfirst($payment->status) }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: #94a3b8; padding: 2.5rem;">
                            No subscription payment records found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1rem;">
            {{ $payments->links() }}
        </div>
    </div>
</div>

<!-- Modal 1: Instant Card Checkout -->
<div class="modal-backdrop" id="cardCheckoutModal">
    <div class="modal-box" style="max-width: 500px;">
        <div class="modal-header">
            <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                Instant Card Checkout
            </h3>
            <button type="button" class="modal-close" onclick="closeModal('cardCheckoutModal')">&times;</button>
        </div>
        <form action="{{ route('shop.subscription.checkout') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div style="background: #eef2ff; border: 1px solid #c7d2fe; padding: 0.85rem; border-radius: 8px; margin-bottom: 1rem;">
                    <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #4338ca;">Selected Subscription</div>
                    <div id="checkoutPlanDisplay" style="font-size: 1.05rem; font-weight: 800; color: #1e1b4b; margin-top: 0.2rem;">Select a plan below</div>
                    <div id="checkoutAmountDisplay" style="font-size: 0.85rem; color: #4f46e5; font-weight: 600;"></div>
                </div>

                <div class="form-group" style="margin-bottom: 0.85rem;">
                    <label style="font-size: 0.8rem; font-weight: 600;">Select Plan *</label>
                    <select name="plan_id" id="checkoutPlanSelect" class="form-control" required onchange="updateCheckoutPrice()">
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" data-name="{{ $plan->name }}" data-monthly="{{ $plan->monthly_price }}" data-annual="{{ $plan->annual_price }}">
                                {{ $plan->name }} (Rs. {{ number_format($plan->monthly_price, 0) }}/mo)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0.85rem;">
                    <label style="font-size: 0.8rem; font-weight: 600;">Billing Cycle *</label>
                    <select name="billing_cycle" id="checkoutCycleSelect" class="form-control" required onchange="updateCheckoutPrice()">
                        <option value="monthly">Monthly (30 Days)</option>
                        <option value="annual">Annual (1 Year - Best Value)</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0.85rem;">
                    <label style="font-size: 0.8rem; font-weight: 600;">Cardholder Name *</label>
                    <input type="text" name="cardholder_name" class="form-control" placeholder="e.g. K. D. Perera" required value="{{ auth()->user()->name }}">
                </div>

                <div class="form-group" style="margin-bottom: 0.85rem;">
                    <label style="font-size: 0.8rem; font-weight: 600;">Credit / Debit Card Number *</label>
                    <input type="text" name="card_number" class="form-control" placeholder="4242 •••• •••• 4242" required maxlength="19">
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <div class="form-group">
                        <label style="font-size: 0.8rem; font-weight: 600;">Expiry Date *</label>
                        <input type="text" name="card_expiry" class="form-control" placeholder="MM/YY" required maxlength="7">
                    </div>
                    <div class="form-group">
                        <label style="font-size: 0.8rem; font-weight: 600;">CVV / CVC *</label>
                        <input type="password" name="card_cvv" class="form-control" placeholder="•••" required maxlength="4">
                    </div>
                </div>

                <div style="font-size: 0.72rem; color: #166534; background: #f0fdf4; padding: 0.6rem; border-radius: 6px; border: 1px solid #bbf7d0; margin-top: 0.5rem; display: flex; align-items: center; gap: 0.35rem;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    <span>256-bit encrypted simulated card checkout. Your plan will activate immediately upon confirmation.</span>
                </div>
            </div>

            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('cardCheckoutModal')">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding: 0.6rem 1.25rem;">
                    Confirm &amp; Pay Now
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Bank Slip Upload -->
<div class="modal-backdrop" id="bankTransferModal">
    <div class="modal-box" style="max-width: 500px;">
        <div class="modal-header">
            <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2"><line x1="3" y1="21" x2="21" y2="21"></line><line x1="3" y1="10" x2="21" y2="10"></line><polyline points="5 6 12 3 19 6"></polyline><line x1="4" y1="10" x2="4" y2="21"></line><line x1="20" y1="10" x2="20" y2="21"></line><line x1="8" y1="14" x2="8" y2="17"></line><line x1="12" y1="14" x2="12" y2="17"></line><line x1="16" y1="14" x2="16" y2="17"></line></svg>
                Bank Transfer Payment
            </h3>
            <button type="button" class="modal-close" onclick="closeModal('bankTransferModal')">&times;</button>
        </div>
        <form action="{{ route('shop.subscription.bank-transfer') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 0.85rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.8rem; color: #334155;">
                    <div style="font-weight: 700; color: #0f172a; margin-bottom: 0.25rem;">Bank Deposit Account:</div>
                    <div>Bank: <strong>Commercial Bank of Ceylon</strong></div>
                    <div>Account Name: <strong>Phone Shop SaaS (Pvt) Ltd</strong></div>
                    <div>Account Number: <strong>1000 2938 4920</strong></div>
                    <div>Branch: <strong>Colombo Main Branch</strong></div>
                </div>

                <div class="form-group" style="margin-bottom: 0.85rem;">
                    <label style="font-size: 0.8rem; font-weight: 600;">Select Plan *</label>
                    <select name="plan_id" id="bankPlanSelect" class="form-control" required>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}">
                                {{ $plan->name }} (Rs. {{ number_format($plan->monthly_price, 0) }}/mo)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0.85rem;">
                    <label style="font-size: 0.8rem; font-weight: 600;">Billing Cycle *</label>
                    <select name="billing_cycle" class="form-control" required>
                        <option value="monthly">Monthly</option>
                        <option value="annual">Annual</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0.85rem;">
                    <label style="font-size: 0.8rem; font-weight: 600;">Deposit Slip Ref / Transaction No *</label>
                    <input type="text" name="reference_number" class="form-control" placeholder="e.g. REF-928402" required>
                </div>

                <div class="form-group" style="margin-bottom: 0.5rem;">
                    <label style="font-size: 0.8rem; font-weight: 600;">Upload Receipt Slip (JPG / PNG / PDF)</label>
                    <input type="file" name="receipt" class="form-control" accept="image/*,application/pdf">
                </div>
            </div>

            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('bankTransferModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Submit Slip</button>
            </div>
        </form>
    </div>
</div>

<script>
function selectPlanForCheckout(planId, planName, monthlyPrice, annualPrice) {
    var select = document.getElementById('checkoutPlanSelect');
    if (select) {
        select.value = planId;
        updateCheckoutPrice();
    }
    openModal('cardCheckoutModal');
}

function selectPlanForBank(planId, planName, monthlyPrice, annualPrice) {
    var select = document.getElementById('bankPlanSelect');
    if (select) {
        select.value = planId;
    }
    openModal('bankTransferModal');
}

function updateCheckoutPrice() {
    var planSelect = document.getElementById('checkoutPlanSelect');
    var cycleSelect = document.getElementById('checkoutCycleSelect');
    if (!planSelect || !cycleSelect) return;

    var selectedOption = planSelect.options[planSelect.selectedIndex];
    var planName = selectedOption.getAttribute('data-name');
    var monthly = parseFloat(selectedOption.getAttribute('data-monthly') || 0);
    var annual = parseFloat(selectedOption.getAttribute('data-annual') || 0);

    var cycle = cycleSelect.value;
    var price = (cycle === 'annual') ? annual : monthly;

    document.getElementById('checkoutPlanDisplay').innerText = planName + ' (' + cycle.toUpperCase() + ')';
    document.getElementById('checkoutAmountDisplay').innerText = 'Total Due: LKR ' + price.toLocaleString('en-US', {minimumFractionDigits: 2});
}

document.addEventListener('DOMContentLoaded', function() {
    updateCheckoutPrice();
});
</script>
@endsection

