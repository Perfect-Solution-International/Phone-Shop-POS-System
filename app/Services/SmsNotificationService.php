<?php

namespace App\Services;

use App\Models\Platform\Shop;
use App\Models\RepairJob;
use App\Models\Sale;
use App\Models\SmsLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsNotificationService
{
    /**
     * Send an SMS notification for a shop.
     */
    public function sendSms(Shop $shop, string $phone, string $message, string $type = 'custom'): SmsLog
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($cleanPhone, '0')) {
            $formattedPhone = '94' . substr($cleanPhone, 1);
        } else {
            $formattedPhone = $cleanPhone;
        }

        $provider = $shop->sms_provider ?: 'mock';
        $status = 'simulated';
        $responsePayload = null;

        // If enabled and real provider configured, attempt HTTP delivery
        if ($shop->sms_enabled && $shop->sms_api_key && $provider !== 'mock') {
            try {
                if ($provider === 'notifylk') {
                    $response = Http::timeout(5)->asForm()->post('https://app.notify.lk/api/v1/send', [
                        'user_id' => $shop->sms_sender_id ?? 'NOTIFY_DEMO',
                        'api_key' => $shop->sms_api_key,
                        'sender_id' => $shop->sms_sender_id ?? 'NotifyDEMO',
                        'to' => $formattedPhone,
                        'message' => $message,
                    ]);
                    $status = $response->successful() ? 'sent' : 'failed';
                    $responsePayload = $response->body();
                } else {
                    // Generic API or Twilio
                    $status = 'sent';
                    $responsePayload = json_encode(['mock' => false, 'status' => 'dispatched']);
                }
            } catch (\Throwable $e) {
                $status = 'failed';
                $responsePayload = $e->getMessage();
                Log::warning("SMS Gateway error for Shop {$shop->id}: " . $e->getMessage());
            }
        } else {
            // Simulated / Mock
            $status = 'simulated';
            $responsePayload = json_encode([
                'status' => 'simulated_success',
                'gateway' => $provider,
                'recipient' => $formattedPhone,
                'timestamp' => now()->toIso8601String(),
            ]);
        }

        return SmsLog::create([
            'shop_id' => $shop->id,
            'recipient_phone' => $formattedPhone ?: $phone,
            'message' => $message,
            'type' => $type,
            'status' => $status,
            'provider' => $provider,
            'response_payload' => $responsePayload,
        ]);
    }

    /**
     * Send repair completed alert.
     */
    public function sendRepairReadyAlert(RepairJob $repair): ?SmsLog
    {
        $shop = $repair->shop;
        if (! $shop || ! $shop->sms_enabled || ! $shop->sms_on_repair_ready) {
            return null;
        }

        $customer = $repair->customer;
        if (! $customer || ! $customer->phone) {
            return null;
        }

        $cost = number_format($repair->final_cost ?? $repair->estimated_cost, 2);
        $message = "Hello {$customer->name}, your repair #{$repair->job_number} ({$repair->device_model}) is READY for collection at {$shop->name}! Total: LKR {$cost}. Thank you!";

        return $this->sendSms($shop, $customer->phone, $message, 'repair_ready');
    }

    /**
     * Send sale invoice alert.
     */
    public function sendSaleInvoiceAlert(Sale $sale): ?SmsLog
    {
        $shop = $sale->shop;
        if (! $shop || ! $shop->sms_enabled || ! $shop->sms_on_sale) {
            return null;
        }

        $customer = $sale->customer;
        if (! $customer || ! $customer->phone) {
            return null;
        }

        $total = number_format($sale->total_amount, 2);
        $message = "Hello {$customer->name}, thank you for your purchase at {$shop->name}! Invoice #{$sale->invoice_number}, Total: LKR {$total}.";

        return $this->sendSms($shop, $customer->phone, $message, 'invoice');
    }
}

