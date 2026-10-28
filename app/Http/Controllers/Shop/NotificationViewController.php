<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Platform\Shop;
use App\Models\SmsLog;
use App\Services\SmsNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationViewController extends Controller
{
    public function __construct(
        protected SmsNotificationService $smsService
    ) {}

    public function index(): View
    {
        $shopId = auth()->user()->shop_id ?? session('superadmin_active_shop_id');
        $shop = Shop::findOrFail($shopId);

        $logs = SmsLog::where('shop_id', $shop->id)
            ->latest()
            ->paginate(15);

        $metrics = [
            'total_messages' => SmsLog::where('shop_id', $shop->id)->count(),
            'sent_delivered' => SmsLog::where('shop_id', $shop->id)->whereIn('status', ['sent', 'simulated'])->count(),
            'repair_alerts' => SmsLog::where('shop_id', $shop->id)->where('type', 'repair_ready')->count(),
            'failed' => SmsLog::where('shop_id', $shop->id)->where('status', 'failed')->count(),
        ];

        return view('shop.notifications.index', compact('shop', 'logs', 'metrics'));
    }

    public function sendCustomSms(Request $request): RedirectResponse
    {
        $shopId = auth()->user()->shop_id ?? session('superadmin_active_shop_id');
        $shop = Shop::findOrFail($shopId);

        $data = $request->validate([
            'recipient_phone' => ['required', 'string', 'max:25'],
            'message' => ['required', 'string', 'max:450'],
        ]);

        $log = $this->smsService->sendSms($shop, $data['recipient_phone'], $data['message'], 'custom');

        return redirect()->route('shop.notifications.index')
            ->with('success', "SMS {$log->status}: Message queued to {$log->recipient_phone} successfully.");
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $shopId = auth()->user()->shop_id ?? session('superadmin_active_shop_id');
        $shop = Shop::findOrFail($shopId);

        $data = $request->validate([
            'sms_enabled' => ['nullable', 'boolean'],
            'sms_provider' => ['required', 'string', 'in:mock,notifylk,twilio'],
            'sms_api_key' => ['nullable', 'string', 'max:255'],
            'sms_sender_id' => ['nullable', 'string', 'max:50'],
            'sms_on_repair_ready' => ['nullable', 'boolean'],
            'sms_on_sale' => ['nullable', 'boolean'],
        ]);

        $shop->update([
            'sms_enabled' => $request->boolean('sms_enabled'),
            'sms_provider' => $data['sms_provider'],
            'sms_api_key' => $data['sms_api_key'] ?? null,
            'sms_sender_id' => $data['sms_sender_id'] ?? null,
            'sms_on_repair_ready' => $request->boolean('sms_on_repair_ready'),
            'sms_on_sale' => $request->boolean('sms_on_sale'),
        ]);

        return redirect()->route('shop.notifications.index')
            ->with('success', 'SMS gateway configuration updated successfully.');
    }
}

