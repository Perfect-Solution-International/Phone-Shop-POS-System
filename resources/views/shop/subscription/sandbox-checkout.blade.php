@extends('layouts.shop')

@section('title', 'Sandbox Payment Gateway')
@section('page_title', 'Sandbox Payment Gateway Simulator')

@section('content')
<div style="max-width: 800px; margin: 0 auto; padding: 1.5rem 0;">
    <!-- Order Summary Card -->
    <div class="card" style="margin-bottom: 2rem; border-top: 4px solid #6366f1; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.06); padding: 1.75rem;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem;">
            <div>
                <span class="badge badge-info" style="font-size: 0.75rem; letter-spacing: 0.05em; text-transform: uppercase;">Sandbox Environment</span>
                <h2 style="font-size: 1.5rem; font-weight: 800; color: #1e293b; margin: 0.5rem 0 0.25rem;">Online Gateway Checkout</h2>
                <p style="color: #64748b; font-size: 0.875rem; margin: 0;">Order Reference: <strong style="font-family: monospace; color: #0284c7;">{{ $payment->reference_number }}</strong></p>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 0.8125rem; color: #64748b;">Amount Payable</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: #059669;">LKR {{ number_format($payment->amount, 2) }}</div>
                <div style="font-size: 0.75rem; color: #94a3b8; text-transform: capitalize;">Billing: {{ $payment->billing_cycle }}</div>
            </div>
        </div>

        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem 1.25rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; font-size: 0.875rem;">
            <div>
                <span style="color: #64748b; display: block; font-size: 0.75rem;">Shop / Tenant:</span>
                <strong style="color: #1e293b;">{{ $payment->shop->name }}</strong>
            </div>
            <div>
                <span style="color: #64748b; display: block; font-size: 0.75rem;">Selected Plan:</span>
                <strong style="color: #1e293b;">{{ $payment->plan->name }}</strong>
            </div>
            <div>
                <span style="color: #64748b; display: block; font-size: 0.75rem;">Gateway Driver:</span>
                <span class="badge badge-success">{{ strtoupper($payment->payment_method) }}</span>
            </div>
        </div>
    </div>

    <!-- Simulator Action Triggers -->
    <h3 style="font-size: 1.125rem; font-weight: 700; color: #1e293b; margin-bottom: 1rem;">Simulate Gateway Response</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
        
        <!-- 1. Success Simulation -->
        <div class="card" style="border: 1.5px solid #bbf7d0; background: #f0fdf4; border-radius: 10px; padding: 1.25rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                <span style="width: 32px; height: 32px; border-radius: 50%; background: #22c55e; color: white; display: flex; align-items: center; justify-content: center; font-weight: 700;">✓</span>
                <h4 style="margin: 0; font-size: 1rem; color: #166534;">Simulate Successful Payment</h4>
            </div>
            <p style="font-size: 0.8125rem; color: #15803d; margin-bottom: 1rem;">
                Simulates an approved payment from the gateway with valid HMAC-SHA256 signature, triggers subscription renewal and updates periods.
            </p>
            <form method="POST" action="{{ route('shop.subscription.gateway.simulate-action') }}">
                @csrf
                <input type="hidden" name="order_id" value="{{ $payment->reference_number }}">
                <input type="hidden" name="action" value="success">
                <button type="submit" class="btn btn-success" style="width: 100%; justify-content: center;">Approve &amp; Renew Subscription</button>
            </form>
        </div>

        <!-- 2. Failure Simulation -->
        <div class="card" style="border: 1.5px solid #fecaca; background: #fef2f2; border-radius: 10px; padding: 1.25rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                <span style="width: 32px; height: 32px; border-radius: 50%; background: #ef4444; color: white; display: flex; align-items: center; justify-content: center; font-weight: 700;">✕</span>
                <h4 style="margin: 0; font-size: 1rem; color: #991b1b;">Simulate Gateway Failure</h4>
            </div>
            <p style="font-size: 0.8125rem; color: #b91c1c; margin-bottom: 1rem;">
                Simulates a declined payment (e.g. insufficient funds, card error). Records payment failure and preserves original subscription status.
            </p>
            <form method="POST" action="{{ route('shop.subscription.gateway.simulate-action') }}">
                @csrf
                <input type="hidden" name="order_id" value="{{ $payment->reference_number }}">
                <input type="hidden" name="action" value="failure">
                <button type="submit" class="btn btn-danger" style="width: 100%; justify-content: center;">Decline Payment (Failure Path)</button>
            </form>
        </div>

        <!-- 3. Security Tampered Signature -->
        <div class="card" style="border: 1.5px solid #fed7aa; background: #fff7ed; border-radius: 10px; padding: 1.25rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                <span style="width: 32px; height: 32px; border-radius: 50%; background: #f97316; color: white; display: flex; align-items: center; justify-content: center; font-weight: 700;">⚠</span>
                <h4 style="margin: 0; font-size: 1rem; color: #9a3412;">Tampered Signature Attack</h4>
            </div>
            <p style="font-size: 0.8125rem; color: #c2410c; margin-bottom: 1rem;">
                Simulates a forged or tampered webhook payload. Rejects the request with HTTP 400 and logs a security audit event.
            </p>
            <form method="POST" action="{{ route('shop.subscription.gateway.simulate-action') }}">
                @csrf
                <input type="hidden" name="order_id" value="{{ $payment->reference_number }}">
                <input type="hidden" name="action" value="tampered_signature">
                <button type="submit" class="btn btn-warning" style="width: 100%; justify-content: center;">Trigger Tampered Webhook</button>
            </form>
        </div>

        <!-- 4. Duplicate Webhook Simulation -->
        <div class="card" style="border: 1.5px solid #e2e8f0; background: #f8fafc; border-radius: 10px; padding: 1.25rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                <span style="width: 32px; height: 32px; border-radius: 50%; background: #64748b; color: white; display: flex; align-items: center; justify-content: center; font-weight: 700;">↺</span>
                <h4 style="margin: 0; font-size: 1rem; color: #334155;">Simulate Duplicate Callback</h4>
            </div>
            <p style="font-size: 0.8125rem; color: #475569; margin-bottom: 1rem;">
                Tests idempotency by firing a callback for an already-approved transaction. Verifies that duplicate renewal is prevented.
            </p>
            <form method="POST" action="{{ route('shop.subscription.gateway.simulate-action') }}">
                @csrf
                <input type="hidden" name="order_id" value="{{ $payment->reference_number }}">
                <input type="hidden" name="action" value="duplicate">
                <button type="submit" class="btn btn-secondary" style="width: 100%; justify-content: center;">Send Duplicate Callback</button>
            </form>
        </div>
    </div>

    <div style="text-align: center;">
        <a href="{{ route('shop.subscription.gateway.cancel') }}" class="btn btn-outline" style="color: #64748b;">
            Cancel Checkout &amp; Return to Subscriptions
        </a>
    </div>
</div>
@endsection

