@extends('layouts.shop')

@section('title', 'SMS & WhatsApp Notifications')
@section('page-title', 'Customer SMS & WhatsApp Communication')

@section('content')
<div style="display: flex; flex-direction: column; gap: 1.5rem;">
    <!-- Metrics Row -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem;">
        <div class="card" style="padding: 1.15rem 1.25rem; border-radius: 10px;">
            <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #64748b; margin-bottom: 0.35rem;">
                Total Dispatches
            </div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #0f172a;">
                {{ number_format($metrics['total_messages']) }}
            </div>
            <div style="font-size: 0.72rem; color: #94a3b8; margin-top: 0.25rem;">
                SMS logs recorded
            </div>
        </div>
        <div class="card" style="padding: 1.15rem 1.25rem; border-radius: 10px;">
            <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #166534; margin-bottom: 0.35rem;">
                Delivered / Simulated
            </div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #16a34a;">
                {{ number_format($metrics['sent_delivered']) }}
            </div>
            <div style="font-size: 0.72rem; color: #16a34a; margin-top: 0.25rem;">
                Successful customer alerts
            </div>
        </div>
        <div class="card" style="padding: 1.15rem 1.25rem; border-radius: 10px;">
            <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #4338ca; margin-bottom: 0.35rem;">
                Repair Ready Alerts
            </div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #6366f1;">
                {{ number_format($metrics['repair_alerts']) }}
            </div>
            <div style="font-size: 0.72rem; color: #6366f1; margin-top: 0.25rem;">
                Collection notices to customers
            </div>
        </div>
        <div class="card" style="padding: 1.15rem 1.25rem; border-radius: 10px;">
            <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #991b1b; margin-bottom: 0.35rem;">
                Failed Deliveries
            </div>
            <div style="font-size: 1.6rem; font-weight: 800; color: {{ $metrics['failed'] > 0 ? '#dc2626' : '#64748b' }};">
                {{ number_format($metrics['failed']) }}
            </div>
            <div style="font-size: 0.72rem; color: #94a3b8; margin-top: 0.25rem;">
                Gateway or invalid numbers
            </div>
        </div>
    </div>

    <!-- Main Content: Forms & Logs -->
    <div style="display: grid; grid-template-columns: 360px 1fr; gap: 1.5rem; align-items: start;">
        <!-- Left Column: Quick SMS & Gateway Config -->
        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
            <!-- Send Quick Customer SMS -->
            <div class="card" style="padding: 1.25rem; border-radius: 10px;">
                <h4 style="font-size: 0.95rem; font-weight: 700; color: #0f172a; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.4rem;">
                    <span>📲</span> Send Quick Customer SMS
                </h4>
                <form action="{{ route('shop.notifications.send') }}" method="POST">
                    @csrf
                    <div class="form-group" style="margin-bottom: 0.85rem;">
                        <label style="font-size: 0.8rem; font-weight: 600;">Recipient Mobile No *</label>
                        <input type="text" name="recipient_phone" class="form-control" placeholder="e.g. 0771234567 or +9477..." required>
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label style="font-size: 0.8rem; font-weight: 600;">SMS Content (Max 450 chars) *</label>
                        <textarea name="message" class="form-control" rows="3" placeholder="Type SMS message..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; display: flex; justify-content: center; align-items: center; gap: 0.4rem;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                        Send SMS
                    </button>
                </form>
            </div>

            <!-- Gateway Settings Form -->
            <div class="card" style="padding: 1.25rem; border-radius: 10px;">
                <h4 style="font-size: 0.95rem; font-weight: 700; color: #0f172a; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem;">
                    <span>⚙️</span> SMS Gateway Settings
                </h4>
                <form action="{{ route('shop.notifications.settings') }}" method="POST">
                    @csrf
                    <div style="margin-bottom: 0.85rem; background: #f8fafc; padding: 0.6rem 0.75rem; border-radius: 6px; border: 1px solid #e2e8f0;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; margin: 0; font-size: 0.85rem; font-weight: 600;">
                            <input type="checkbox" name="sms_enabled" value="1" {{ $shop->sms_enabled ? 'checked' : '' }}>
                            Enable SMS Gateway
                        </label>
                    </div>

                    <div class="form-group" style="margin-bottom: 0.85rem;">
                        <label style="font-size: 0.8rem; font-weight: 600;">SMS Provider</label>
                        <select name="sms_provider" class="form-control">
                            <option value="mock" {{ $shop->sms_provider === 'mock' ? 'selected' : '' }}>Simulator / Mock (Zero Cost)</option>
                            <option value="notifylk" {{ $shop->sms_provider === 'notifylk' ? 'selected' : '' }}>NotifyLK (Sri Lanka SMS)</option>
                            <option value="twilio" {{ $shop->sms_provider === 'twilio' ? 'selected' : '' }}>Twilio / International</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 0.85rem;">
                        <label style="font-size: 0.8rem; font-weight: 600;">API Key / Secret Token</label>
                        <input type="password" name="sms_api_key" class="form-control" placeholder="Paste API token..." value="{{ $shop->sms_api_key }}">
                    </div>

                    <div class="form-group" style="margin-bottom: 0.85rem;">
                        <label style="font-size: 0.8rem; font-weight: 600;">Sender ID / Mask Name</label>
                        <input type="text" name="sms_sender_id" class="form-control" placeholder="e.g. NotifyDEMO or SHOPNAME" value="{{ $shop->sms_sender_id }}">
                    </div>

                    <div style="border-top: 1px solid #f1f5f9; padding-top: 0.75rem; margin-bottom: 1rem;">
                        <div style="font-size: 0.75rem; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 0.5rem;">
                            Automated SMS Triggers
                        </div>
                        <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.825rem; margin-bottom: 0.4rem; cursor: pointer;">
                            <input type="checkbox" name="sms_on_repair_ready" value="1" {{ $shop->sms_on_repair_ready ? 'checked' : '' }}>
                            Auto-alert when Repair is Ready
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.825rem; cursor: pointer;">
                            <input type="checkbox" name="sms_on_sale" value="1" {{ $shop->sms_on_sale ? 'checked' : '' }}>
                            Auto-alert e-receipt on POS Sale
                        </label>
                    </div>

                    <button type="submit" class="btn btn-secondary" style="width: 100%;">
                        Save Gateway Settings
                    </button>
                </form>
            </div>
        </div>

        <!-- Right Column: SMS Logs Table -->
        <div class="card" style="padding: 1.25rem; border-radius: 10px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h4 style="font-size: 1rem; font-weight: 700; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.4rem;">
                    <span>📋</span> Notification Log History
                </h4>
                <span class="badge badge-secondary">{{ $logs->total() }} records</span>
            </div>

            <div class="table-responsive">
                <table class="table" style="font-size: 0.85rem;">
                    <thead>
                        <tr>
                            <th>Date / Time</th>
                            <th>Recipient</th>
                            <th>Message</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Provider</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td style="white-space: nowrap; font-size: 0.78rem; color: #64748b;">
                                {{ $log->created_at->format('M d, Y h:i A') }}
                            </td>
                            <td style="font-weight: 700; font-family: monospace;">
                                {{ $log->recipient_phone }}
                            </td>
                            <td style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $log->message }}">
                                {{ $log->message }}
                            </td>
                            <td>
                                @php
                                    $typeBadge = match($log->type) {
                                        'repair_ready' => 'badge-info',
                                        'invoice' => 'badge-success',
                                        'warranty' => 'badge-purple',
                                        default => 'badge-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $typeBadge }}" style="font-size: 0.72rem;">
                                    {{ ucwords(str_replace('_', ' ', $log->type)) }}
                                </span>
                            </td>
                            <td>
                                @if($log->status === 'sent')
                                    <span class="badge badge-success" style="font-size: 0.72rem;">Sent</span>
                                @elseif($log->status === 'simulated')
                                    <span class="badge badge-info" style="font-size: 0.72rem;" title="Simulated successfully">Simulated</span>
                                @else
                                    <span class="badge badge-danger" style="font-size: 0.72rem;">Failed</span>
                                @endif
                            </td>
                            <td style="font-size: 0.75rem; color: #64748b; text-transform: uppercase;">
                                {{ $log->provider }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: #94a3b8; padding: 3rem;">
                                No notifications dispatched yet. Try sending a quick test SMS above.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 1rem;">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

