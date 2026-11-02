@extends('layouts.shop')

@section('title', 'SMS & WhatsApp Notifications')
@section('page-title', 'Customer SMS & WhatsApp Communication')

@section('content')
<style>
    .notifications-layout-grid {
        display: grid;
        grid-template-columns: 360px 1fr;
        gap: 1.5rem;
        align-items: start;
    }
    @media (max-width: 992px) {
        .notifications-layout-grid {
            grid-template-columns: 1fr;
        }
    }

    .sms-log-table {
        width: 100%;
        border-collapse: collapse;
    }
    .sms-log-table th {
        font-size: 0.7rem !important;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        font-weight: 700 !important;
        color: #475569 !important;
        background-color: #f8fafc !important;
        padding: 0.85rem 1rem !important;
        border-bottom: 1px solid var(--surface-border) !important;
    }
    .sms-log-table td {
        padding: 0.85rem 1rem !important;
        font-size: 0.835rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--surface-border) !important;
    }
    .sms-log-table tr:hover td {
        background-color: #f8fafc;
    }

    .sms-bubble-preview {
        max-width: 250px;
        color: #334155;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.4rem 0.65rem;
        font-size: 0.8125rem;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        word-break: break-word;
    }

    .phone-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 0.25rem 0.55rem;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.82rem;
        font-weight: 700;
        color: #0f172a;
        white-space: nowrap;
    }

    .sms-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem 0.65rem;
        border-radius: 9999px;
        font-size: 0.725rem;
        font-weight: 700;
        white-space: nowrap;
    }
</style>

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
    <div class="notifications-layout-grid">
        <!-- Left Column: Quick SMS & Gateway Config -->
        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
            <!-- Send Quick Customer SMS -->
            <div class="card" style="padding: 1.25rem; border-radius: 10px;">
                <h4 style="font-size: 0.95rem; font-weight: 700; color: #0f172a; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                    Send Quick Customer SMS
                </h4>
                <form action="{{ route('shop.notifications.send') }}" method="POST">
                    @csrf
                    <div class="form-group" style="margin-bottom: 0.85rem;">
                        <label style="font-size: 0.8rem; font-weight: 600;">Recipient Mobile No *</label>
                        <input type="text" name="recipient_phone" class="form-control" placeholder="e.g. 0771234567 or +9477..." required>
                    </div>
                    <div class="form-group" style="margin-bottom: 0.4rem;">
                        <label style="font-size: 0.8rem; font-weight: 600;">SMS Content (Max 450 chars) *</label>
                        <textarea name="message" id="quickSmsMessageInput" class="form-control" rows="3" placeholder="Type SMS message..." maxlength="450" required oninput="document.getElementById('charCountDisplay').innerText = this.value.length"></textarea>
                    </div>
                    <div style="display: flex; justify-content: flex-end; font-size: 0.72rem; color: #64748b; margin-bottom: 1rem;">
                        <span id="charCountDisplay">0</span>&nbsp;/ 450 characters
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; display: flex; justify-content: center; align-items: center; gap: 0.4rem;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                        Send SMS
                    </button>
                </form>
            </div>

            <!-- Gateway Settings Form -->
            <div class="card" style="padding: 1.25rem; border-radius: 10px;">
                <h4 style="font-size: 0.95rem; font-weight: 700; color: #0f172a; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                    SMS Gateway Settings
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
        <div class="card" style="border-radius: 12px; overflow: hidden;">
            <div style="padding: 1.15rem 1.25rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; background: #ffffff;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; background: #eef2ff; color: #4f46e5;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect><path d="M9 12h6"></path><path d="M9 16h6"></path></svg>
                    </span>
                    <div>
                        <h4 style="font-size: 0.975rem; font-weight: 750; color: #0f172a; margin: 0;">
                            Notification Log History
                        </h4>
                        <div style="font-size: 0.725rem; color: #64748b; margin-top: 0.15rem;">
                            Live audit history of SMS alerts sent to customers
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: #e2e8f0; color: #334155; font-size: 0.75rem; font-weight: 700; padding: 0.25rem 0.65rem;">
                    {{ number_format($logs->total()) }} {{ Str::plural('record', $logs->total()) }}
                </span>
            </div>

            <div class="table-responsive">
                <table class="data-table sms-log-table">
                    <thead>
                        <tr>
                            <th style="min-width: 140px;">Date & Time</th>
                            <th style="min-width: 140px;">Recipient</th>
                            <th style="min-width: 220px;">Message Content</th>
                            <th style="min-width: 110px; text-align: center;">Trigger</th>
                            <th style="min-width: 110px; text-align: center;">Delivery</th>
                            <th style="min-width: 90px; text-align: center;">Provider</th>
                            <th style="min-width: 70px; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        @php
                            $typeConfig = match($log->type) {
                                'repair_ready' => ['bg' => '#e0f2fe', 'color' => '#0369a1', 'label' => 'Repair Ready'],
                                'invoice' => ['bg' => '#dcfce7', 'color' => '#15803d', 'label' => 'POS Invoice'],
                                'warranty' => ['bg' => '#f3e8ff', 'color' => '#7e22ce', 'label' => 'Warranty Alert'],
                                'custom' => ['bg' => '#f1f5f9', 'color' => '#475569', 'label' => 'Custom SMS'],
                                default => ['bg' => '#f1f5f9', 'color' => '#475569', 'label' => ucwords(str_replace('_', ' ', $log->type))],
                            };
                        @endphp
                        <tr>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 0.15rem;">
                                    <span style="font-weight: 650; color: #0f172a; font-size: 0.8125rem; display: flex; align-items: center; gap: 0.35rem;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                        {{ $log->created_at->format('M d, Y') }}
                                    </span>
                                    <span style="font-size: 0.72rem; color: #64748b; padding-left: 1.1rem;">
                                        {{ $log->created_at->format('h:i A') }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="phone-badge">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                    {{ $log->recipient_phone }}
                                </div>
                            </td>
                            <td>
                                <div class="sms-bubble-preview" title="{{ $log->message }}">
                                    {{ $log->message }}
                                </div>
                            </td>
                            <td>
                                <div style="text-align: center;">
                                    <span style="display: inline-block; padding: 0.22rem 0.6rem; border-radius: 9999px; font-size: 0.72rem; font-weight: 700; background: {{ $typeConfig['bg'] }}; color: {{ $typeConfig['color'] }};">
                                        {{ $typeConfig['label'] }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div style="text-align: center;">
                                    @if($log->status === 'sent')
                                        <span class="sms-status-pill" style="background: #dcfce7; color: #166534;">
                                            <span style="width: 6px; height: 6px; border-radius: 50%; background: #22c55e;"></span>
                                            Delivered
                                        </span>
                                    @elseif($log->status === 'simulated')
                                        <span class="sms-status-pill" style="background: #e0f2fe; color: #0369a1;" title="Simulated successfully in Mock Mode">
                                            <span style="width: 6px; height: 6px; border-radius: 50%; background: #0284c7;"></span>
                                            Simulated
                                        </span>
                                    @else
                                        <span class="sms-status-pill" style="background: #fee2e2; color: #991b1b;">
                                            <span style="width: 6px; height: 6px; border-radius: 50%; background: #ef4444;"></span>
                                            Failed
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div style="text-align: center;">
                                    <span style="display: inline-block; font-size: 0.7rem; font-weight: 750; color: #475569; background: #f8fafc; border: 1px solid #e2e8f0; padding: 0.18rem 0.5rem; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.04em;">
                                        {{ $log->provider }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div style="text-align: right;">
                                    <button type="button" 
                                            class="btn btn-secondary btn-sm"
                                            style="padding: 0.25rem 0.55rem; font-size: 0.75rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.3rem;"
                                            data-phone="{{ $log->recipient_phone }}"
                                            data-date="{{ $log->created_at->format('M d, Y h:i A') }}"
                                            data-type="{{ $typeConfig['label'] }}"
                                            data-status="{{ $log->status }}"
                                            data-provider="{{ strtoupper($log->provider) }}"
                                            data-message="{{ $log->message }}"
                                            data-payload="{{ $log->response_payload }}"
                                            onclick="openSmsModalFromBtn(this)">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                        View
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 3.5rem 1rem;">
                                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.75rem;">
                                    <div style="width: 48px; height: 48px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                                    </div>
                                    <div style="font-size: 0.95rem; font-weight: 700; color: #1e293b;">No SMS Dispatches Recorded Yet</div>
                                    <div style="font-size: 0.8125rem; color: #64748b; max-width: 360px; line-height: 1.4;">
                                        When customer SMS alerts are dispatched (via Quick SMS, POS invoices, or repair readiness), their full audit history will appear here.
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
            <div style="padding: 1rem 1.25rem; border-top: 1px solid #f1f5f9;">
                {{ $logs->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- SMS Message Details Modal -->
<div id="smsDetailModal" class="modal-backdrop">
    <div class="modal-box" style="max-width: 520px;">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1.15rem 1.25rem; border-bottom: 1px solid #f1f5f9;">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <div style="width: 32px; height: 32px; border-radius: 8px; background: #e0e7ff; color: #4338ca; display: flex; align-items: center; justify-content: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                </div>
                <div>
                    <h3 style="font-size: 1rem; font-weight: 750; color: #0f172a; margin: 0;">Notification Details</h3>
                    <div id="modalSmsDate" style="font-size: 0.75rem; color: #64748b;"></div>
                </div>
            </div>
            <button type="button" onclick="closeModal('smsDetailModal')" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>
        <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
            <!-- Meta Grid -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.85rem;">
                <div>
                    <div style="font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Recipient</div>
                    <div id="modalSmsPhone" style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 0.875rem; font-weight: 700; color: #0f172a; margin-top: 0.2rem;"></div>
                </div>
                <div>
                    <div style="font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Trigger Type</div>
                    <div id="modalSmsType" style="font-size: 0.825rem; font-weight: 600; color: #334155; margin-top: 0.2rem;"></div>
                </div>
                <div>
                    <div style="font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Delivery Status</div>
                    <div id="modalSmsStatus" style="margin-top: 0.2rem;"></div>
                </div>
                <div>
                    <div style="font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase;">SMS Provider</div>
                    <div id="modalSmsProvider" style="font-size: 0.825rem; font-weight: 700; color: #475569; margin-top: 0.2rem;"></div>
                </div>
            </div>

            <!-- Message Full Text -->
            <div>
                <div style="font-size: 0.75rem; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 0.4rem;">Message Content</div>
                <div id="modalSmsMessage" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.85rem 1rem; font-size: 0.875rem; line-height: 1.5; color: #1e293b; white-space: pre-wrap; word-break: break-word;"></div>
            </div>

            <!-- Response Payload (if any) -->
            <div id="modalSmsPayloadWrapper" style="display: none;">
                <div style="font-size: 0.72rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 0.35rem;">Gateway Response Log</div>
                <pre id="modalSmsPayload" style="background: #0f172a; color: #38bdf8; padding: 0.6rem; border-radius: 6px; font-size: 0.72rem; overflow-x: auto; max-height: 120px;"></pre>
            </div>
        </div>
        <div style="padding: 0.85rem 1.25rem; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; background: #f8fafc;">
            <button type="button" class="btn btn-secondary" onclick="closeModal('smsDetailModal')" style="font-size: 0.825rem; padding: 0.4rem 1rem;">Close</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openSmsModalFromBtn(btn) {
        openSmsModal({
            phone: btn.dataset.phone,
            date: btn.dataset.date,
            type: btn.dataset.type,
            status: btn.dataset.status,
            provider: btn.dataset.provider,
            message: btn.dataset.message,
            payload: btn.dataset.payload
        });
    }

    function openSmsModal(data) {
        document.getElementById('modalSmsPhone').innerText = data.phone || '-';
        document.getElementById('modalSmsDate').innerText = data.date || '-';
        document.getElementById('modalSmsType').innerText = data.type || '-';
        document.getElementById('modalSmsProvider').innerText = data.provider || '-';
        document.getElementById('modalSmsMessage').innerText = data.message || '';

        const statusEl = document.getElementById('modalSmsStatus');
        if (data.status === 'sent') {
            statusEl.innerHTML = '<span class="sms-status-pill" style="background: #dcfce7; color: #166534;"><span style="width: 6px; height: 6px; border-radius: 50%; background: #22c55e;"></span> Delivered</span>';
        } else if (data.status === 'simulated') {
            statusEl.innerHTML = '<span class="sms-status-pill" style="background: #e0f2fe; color: #0369a1;"><span style="width: 6px; height: 6px; border-radius: 50%; background: #0284c7;"></span> Simulated (Mock)</span>';
        } else {
            statusEl.innerHTML = '<span class="sms-status-pill" style="background: #fee2e2; color: #991b1b;"><span style="width: 6px; height: 6px; border-radius: 50%; background: #ef4444;"></span> Failed</span>';
        }

        const payloadWrapper = document.getElementById('modalSmsPayloadWrapper');
        const payloadEl = document.getElementById('modalSmsPayload');
        if (data.payload) {
            try {
                const parsed = typeof data.payload === 'string' ? JSON.parse(data.payload) : data.payload;
                payloadEl.textContent = JSON.stringify(parsed, null, 2);
            } catch(e) {
                payloadEl.textContent = String(data.payload);
            }
            payloadWrapper.style.display = 'block';
        } else {
            payloadWrapper.style.display = 'none';
        }

        openModal('smsDetailModal');
    }
</script>
@endpush
