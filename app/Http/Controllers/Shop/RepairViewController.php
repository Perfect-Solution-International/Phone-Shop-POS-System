<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\RepairJob;
use App\Models\RepairPartUsed;
use App\Models\SparePart;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RepairViewController extends Controller
{
    public function index(Request $request): View
    {
        $query = RepairJob::with(['customer', 'technician', 'partsUsed.sparePart']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('job_number', 'like', "%{$search}%")
                    ->orWhere('imei', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $repairs = $query->latest()->paginate(15)->withQueryString();
        $customers = Customer::latest()->limit(100)->get();
        $technicians = User::all();
        $spareParts = SparePart::where('stock_quantity', '>', 0)->get();

        $stageCounts = [
            'all' => RepairJob::count(),
            'received' => RepairJob::where('status', 'received')->count(),
            'diagnosing' => RepairJob::where('status', 'diagnosing')->count(),
            'in_repair' => RepairJob::where('status', 'in_repair')->count(),
            'ready_for_collection' => RepairJob::where('status', 'ready_for_collection')->count(),
            'delivered' => RepairJob::where('status', 'delivered')->count(),
        ];

        return view('shop.repairs.index', compact('repairs', 'customers', 'technicians', 'spareParts', 'stageCounts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'technician_id' => 'nullable|exists:users,id',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'imei' => 'nullable|string|max:50',
            'fault_description' => 'required|string|max:1000',
            'condition_notes' => 'nullable|string|max:1000',
            'accessories_received' => 'nullable|string|max:255',
            'estimated_cost' => 'required|numeric|min:0',
            'advance_amount' => 'nullable|numeric|min:0',
            'estimated_completion_date' => 'nullable|date',
        ]);

        $validated['job_number'] = 'REP-'.date('Ymd').'-'.strtoupper(Str::random(4));
        $validated['status'] = 'received';

        $job = RepairJob::create($validated);

        return redirect()->route('shop.repairs')->with('success', "Repair ticket #{$job->job_number} created successfully!");
    }

    public function updateStatus(Request $request, RepairJob $repair): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:received,diagnosing,waiting_for_approval,waiting_for_parts,in_repair,quality_check,ready_for_collection,delivered',
            'technician_notes' => 'nullable|string|max:1000',
            'final_cost' => 'nullable|numeric|min:0',
        ]);

        if ($validated['status'] === 'delivered' && ! $repair->delivered_at) {
            $validated['delivered_at'] = now();
        }

        $repair->update($validated);

        if ($validated['status'] === 'ready_for_collection') {
            try {
                app(\App\Services\SmsNotificationService::class)->sendRepairReadyAlert($repair);
            } catch (\Throwable $e) {
                // Silently log or ignore delivery error to prevent blocking repair workflow
            }
        }

        return redirect()->route('shop.repairs')->with('success', "Repair #{$repair->job_number} status updated to ".ucfirst(str_replace('_', ' ', $repair->status)));
    }

    public function addPart(Request $request, RepairJob $repair): RedirectResponse
    {
        $validated = $request->validate([
            'spare_part_id' => 'required|exists:spare_parts,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($repair, $validated): void {
                /** @var SparePart $part */
                $part = SparePart::where('id', $validated['spare_part_id'])->lockForUpdate()->firstOrFail();

                if ($part->stock_quantity < $validated['quantity']) {
                    throw new \RuntimeException("Insufficient stock for {$part->name}. Available: {$part->stock_quantity}");
                }

                $part->decrement('stock_quantity', $validated['quantity']);

                RepairPartUsed::create([
                    'repair_job_id' => $repair->id,
                    'spare_part_id' => $part->id,
                    'quantity' => $validated['quantity'],
                    'unit_cost' => $part->cost_price,
                    'unit_price' => $validated['unit_price'] ?? $part->selling_price,
                ]);
            });

            return redirect()->route('shop.repairs')->with('success', 'Spare part allocated and stock deducted successfully!');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}

