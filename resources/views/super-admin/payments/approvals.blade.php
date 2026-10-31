@extends('layouts.super-admin')

@section('title', 'Bank Transfer Approvals')
@section('page_title', 'Bank Deposit Payment Approvals')

@section('content')
    <div class="data-card">
        <div class="data-card-header">
            <h2 class="data-card-title">Pending Bank Transfer Reviews ({{ $pendingPayments->total() }})</h2>
        </div>

        <table class="saas-table">
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Shop / Tenant</th>
                    <th>Plan</th>
                    <th>Amount</th>
                    <th>Deposit Reference</th>
                    <th>Submitted At</th>
                    <th>Review &amp; Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pendingPayments as $payment)
                    <tr>
                        <td><strong>#{{ $payment->id }}</strong></td>
                        <td>
                            <strong>{{ $payment->shop->name ?? 'N/A' }}</strong>
                            <div style="font-size: 0.75rem; color: #64748b;">ID #{{ $payment->shop_id }}</div>
                        </td>
                        <td>{{ $payment->plan->name ?? 'N/A' }}</td>
                        <td><strong style="color: var(--brand-indigo-600); font-size: 1rem;">LKR {{ number_format($payment->amount, 2) }}</strong></td>
                        <td>
                            <span style="font-family: monospace; font-size: 0.9375rem; background: #f1f5f9; padding: 0.2rem 0.5rem; border-radius: 5px;">
                                {{ $payment->reference_number ?? 'No Ref Provided' }}
                            </span>
                            @if ($payment->receipt_url)
                                <div style="margin-top: 0.35rem;">
                                    <a href="{{ $payment->receipt_url }}" target="_blank" style="font-size: 0.75rem; color: #4f46e5; font-weight: 700; text-decoration: underline; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                                        <span>View Slip / Receipt</span>
                                    </a>
                                </div>
                            @endif
                        </td>
                        <td>{{ $payment->created_at->format('M d, Y h:i A') }} ({{ $payment->created_at->diffForHumans() }})</td>
                        <td>
                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                <!-- Approve Form -->
                                <form method="POST" action="{{ route('superadmin.payments.approve', $payment->id) }}" onsubmit="return confirm('Confirm receipt and activate 30-day subscription for {{ $payment->shop->name ?? 'this shop' }}?');">
                                    @csrf
                                    <button type="submit" class="btn-action btn-success">✓ Approve</button>
                                </form>

                                <!-- Reject Form -->
                                <button type="button" class="btn-action btn-danger" onclick="toggleRejectModal({{ $payment->id }})">✕ Reject</button>
                            </div>

                            <!-- Inline Rejection Form Modal -->
                            <div id="reject-modal-{{ $payment->id }}" style="display: none; margin-top: 0.75rem; background: #fef2f2; border: 1px solid #fecaca; padding: 0.75rem; border-radius: 8px;">
                                <form method="POST" action="{{ route('superadmin.payments.reject', $payment->id) }}">
                                    @csrf
                                    <label style="font-size: 0.75rem; font-weight: 700; color: #991b1b; display: block; margin-bottom: 0.35rem;">Rejection Reason:</label>
                                    <input type="text" name="rejection_reason" required placeholder="e.g. Invalid bank slip or reference not matched" style="width: 100%; padding: 0.4rem; font-size: 0.8125rem; border: 1px solid #fca5a5; border-radius: 6px; margin-bottom: 0.5rem;">
                                    <button type="submit" class="btn-action btn-danger" style="font-size: 0.75rem;">Confirm Rejection</button>
                                    <button type="button" class="btn-action btn-outline" style="font-size: 0.75rem;" onclick="toggleRejectModal({{ $payment->id }})">Cancel</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: #94a3b8; padding: 3rem;">
                            <div style="margin-bottom: 0.75rem;">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="1.75" style="display: inline-block;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            </div>
                            <strong style="color: #475569;">No pending bank transfers awaiting review!</strong>
                            <p style="font-size: 0.8125rem; margin-top: 0.25rem;">All submitted payment slips have been verified and processed.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="padding: 1.25rem 1.5rem; border-top: 1px solid var(--surface-border);">
            {{ $pendingPayments->links() }}
        </div>
    </div>

    <script>
        function toggleRejectModal(id) {
            const el = document.getElementById('reject-modal-' + id);
            if (el) {
                el.style.display = el.style.display === 'none' ? 'block' : 'none';
            }
        }
    </script>
@endsection

