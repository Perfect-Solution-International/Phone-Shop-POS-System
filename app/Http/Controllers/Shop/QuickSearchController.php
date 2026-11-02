<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\PhoneDevice;
use App\Models\RepairJob;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuickSearchController extends Controller
{
    /**
     * Unified multi-entity search for phones, repairs, invoices, and customers.
     */
    public function search(Request $request): JsonResponse
    {
        $query = trim($request->input('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'total_count' => 0,
                'query' => $query,
                'data' => [
                    'repairs' => [],
                    'phones' => [],
                    'sales' => [],
                    'customers' => [],
                ],
            ]);
        }

        // 1. Repair Jobs
        $repairs = RepairJob::with('customer')
            ->where(function ($q) use ($query): void {
                $q->where('job_number', 'like', "%{$query}%")
                    ->orWhere('imei', 'like', "%{$query}%")
                    ->orWhere('customer_nic', 'like', "%{$query}%")
                    ->orWhere('brand', 'like', "%{$query}%")
                    ->orWhere('model', 'like', "%{$query}%")
                    ->orWhereHas('customer', function ($cq) use ($query): void {
                        $cq->where('name', 'like', "%{$query}%")
                            ->orWhere('phone', 'like', "%{$query}%");
                    });
            })
            ->latest()
            ->take(6)
            ->get()
            ->map(function ($job) {
                return [
                    'id' => $job->id,
                    'job_number' => $job->job_number,
                    'device' => trim("{$job->brand} {$job->model}"),
                    'imei' => $job->imei,
                    'customer_name' => $job->customer->name ?? 'Walk-in',
                    'customer_phone' => $job->customer->phone ?? '',
                    'status' => $job->status,
                    'status_label' => ucfirst(str_replace('_', ' ', $job->status)),
                    'estimated_cost' => number_format((float) $job->estimated_cost, 2),
                    'date' => $job->created_at ? $job->created_at->format('M d, Y') : '',
                    'url' => route('shop.repairs', ['search' => $job->job_number]),
                    'public_track_url' => route('public.repair-track', ['tracking_id' => $job->job_number]),
                ];
            });

        // 2. Phones in Stock / Inventory
        $phones = PhoneDevice::where(function ($q) use ($query): void {
                $q->where('imei_1', 'like', "%{$query}%")
                    ->orWhere('imei_2', 'like', "%{$query}%")
                    ->orWhere('serial_number', 'like', "%{$query}%")
                    ->orWhere('brand', 'like', "%{$query}%")
                    ->orWhere('model', 'like', "%{$query}%");
            })
            ->latest()
            ->take(6)
            ->get()
            ->map(function ($phone) {
                return [
                    'id' => $phone->id,
                    'title' => trim("{$phone->brand} {$phone->model}"),
                    'specs' => trim(($phone->storage ? "{$phone->storage} " : '') . ($phone->color ?? '')),
                    'imei_1' => $phone->imei_1,
                    'imei_2' => $phone->imei_2,
                    'selling_price' => number_format((float) $phone->selling_price, 2),
                    'status' => $phone->status,
                    'url' => route('shop.phones', ['search' => $phone->imei_1 ?: $phone->brand]),
                ];
            });

        // 3. Sales Invoices
        $sales = Sale::with('customer')
            ->where(function ($q) use ($query): void {
                $q->where('invoice_number', 'like', "%{$query}%")
                    ->orWhereHas('customer', function ($cq) use ($query): void {
                        $cq->where('name', 'like', "%{$query}%")
                            ->orWhere('phone', 'like', "%{$query}%");
                    });
            })
            ->latest()
            ->take(6)
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'invoice_number' => $sale->invoice_number,
                    'customer_name' => $sale->customer->name ?? 'Walk-in Customer',
                    'customer_phone' => $sale->customer->phone ?? '',
                    'total_amount' => number_format((float) $sale->total_amount, 2),
                    'payment_method' => ucfirst(str_replace('_', ' ', $sale->payment_method ?? 'Cash')),
                    'date' => $sale->sale_date ? $sale->sale_date->format('M d, Y') : ($sale->created_at ? $sale->created_at->format('M d, Y') : ''),
                    'url' => route('shop.sales', ['search' => $sale->invoice_number]),
                ];
            });

        // 4. Customers Directory
        $customers = Customer::where(function ($q) use ($query): void {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%")
                    ->orWhere('nic', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->latest()
            ->take(6)
            ->get()
            ->map(function ($cust) {
                return [
                    'id' => $cust->id,
                    'name' => $cust->name,
                    'phone' => $cust->phone,
                    'nic' => $cust->nic,
                    'balance' => number_format((float) ($cust->outstanding_balance ?? 0), 2),
                    'url' => route('shop.customers', ['search' => $cust->phone ?: $cust->name]),
                ];
            });

        $totalCount = $repairs->count() + $phones->count() + $sales->count() + $customers->count();

        return response()->json([
            'success' => true,
            'total_count' => $totalCount,
            'query' => $query,
            'data' => [
                'repairs' => $repairs,
                'phones' => $phones,
                'sales' => $sales,
                'customers' => $customers,
            ],
        ]);
    }

    /**
     * Live repair tracking status lookup.
     */
    public function track(Request $request): JsonResponse
    {
        $trackingId = trim($request->input('tracking_id', ''));

        if ($trackingId === '') {
            return response()->json([
                'found' => false,
                'message' => 'Please provide a repair Job #, IMEI, Customer Phone or NIC.',
            ]);
        }

        $cleanId = preg_replace('/[^a-zA-Z0-9]/', '', $trackingId);

        $job = RepairJob::with(['customer', 'technician'])
            ->where(function ($q) use ($trackingId, $cleanId): void {
                $q->where('job_number', $trackingId)
                    ->orWhere('imei', $trackingId)
                    ->orWhere('customer_nic', $trackingId)
                    ->orWhere('id', is_numeric($trackingId) ? (int) $trackingId : 0)
                    ->orWhereHas('customer', function ($cq) use ($trackingId, $cleanId): void {
                        $cq->where('phone', 'like', "%{$trackingId}%")
                            ->orWhere('phone', 'like', "%{$cleanId}%")
                            ->orWhere('nic', $trackingId);
                    });
            })
            ->latest()
            ->first();

        if (! $job) {
            return response()->json([
                'found' => false,
                'message' => "No repair job found matching '{$trackingId}'. Please check the reference and try again.",
            ]);
        }

        $stages = [
            'received' => ['label' => 'Received', 'percent' => 15, 'color' => '#3b82f6'],
            'diagnosing' => ['label' => 'Diagnosing', 'percent' => 30, 'color' => '#6366f1'],
            'waiting_for_approval' => ['label' => 'Awaiting Approval', 'percent' => 45, 'color' => '#f59e0b'],
            'waiting_for_parts' => ['label' => 'Waiting for Parts', 'percent' => 50, 'color' => '#f97316'],
            'in_repair' => ['label' => 'In Repair', 'percent' => 70, 'color' => '#8b5cf6'],
            'quality_check' => ['label' => 'Quality Check', 'percent' => 85, 'color' => '#ec4899'],
            'ready_for_collection' => ['label' => 'Ready for Pickup', 'percent' => 95, 'color' => '#10b981'],
            'delivered' => ['label' => 'Delivered / Completed', 'percent' => 100, 'color' => '#059669'],
        ];

        $currentStage = $stages[$job->status] ?? [
            'label' => ucfirst(str_replace('_', ' ', $job->status)),
            'percent' => 50,
            'color' => '#64748b',
        ];

        return response()->json([
            'found' => true,
            'job' => [
                'id' => $job->id,
                'job_number' => $job->job_number,
                'brand' => $job->brand,
                'model' => $job->model,
                'device' => trim("{$job->brand} {$job->model}"),
                'imei' => $job->imei ?: 'N/A',
                'customer_name' => $job->customer->name ?? 'Walk-in Customer',
                'customer_phone' => $job->customer->phone ?? 'N/A',
                'fault_description' => $job->fault_description ?: 'No description provided.',
                'status' => $job->status,
                'status_label' => $currentStage['label'],
                'progress_percent' => $currentStage['percent'],
                'status_color' => $currentStage['color'],
                'estimated_cost' => number_format((float) $job->estimated_cost, 2),
                'advance_amount' => number_format((float) $job->advance_amount, 2),
                'balance_due' => number_format((float) $job->balance_due, 2),
                'technician_notes' => $job->technician_notes ?: 'No notes updated yet.',
                'technician_name' => $job->technician->name ?? 'Assigned Staff',
                'received_date' => $job->created_at ? $job->created_at->format('M d, Y • h:i A') : 'N/A',
                'estimated_completion' => $job->estimated_completion_date ? $job->estimated_completion_date->format('M d, Y') : 'Pending estimate',
                'delivered_at' => $job->delivered_at ? $job->delivered_at->format('M d, Y • h:i A') : null,
                'shop_url' => route('shop.repairs', ['search' => $job->job_number]),
                'public_url' => route('public.repair-track', ['tracking_id' => $job->job_number]),
            ],
        ]);
    }
}

