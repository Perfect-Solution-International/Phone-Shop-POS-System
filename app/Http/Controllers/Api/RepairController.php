<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RepairJob;
use App\Models\RepairPartUsed;
use App\Models\SparePart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RepairController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:repairs.view')->only(['index', 'show']);
        $this->middleware('can:repairs.manage')->only(['store', 'updateStatus', 'addPart']);
    }

    public function index(Request $request): JsonResponse
    {
        $query = RepairJob::with(['customer', 'technician']);

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

        if ($technicianId = $request->input('technician_id')) {
            $query->where('technician_id', $technicianId);
        }

        $repairs = $query->latest()->paginate($request->integer('per_page', 20));

        return response()->json($repairs);
    }

    public function store(Request $request): JsonResponse
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

        return response()->json($job->load(['customer', 'technician']), 201);
    }

    public function show(RepairJob $repair): JsonResponse
    {
        return response()->json($repair->load(['customer', 'technician', 'partsUsed.sparePart']));
    }

    public function updateStatus(Request $request, RepairJob $repair): JsonResponse
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

        return response()->json($repair->fresh());
    }

    /**
     * Allocate a spare part to a repair job and automatically decrement stock.
     */
    public function addPart(Request $request, RepairJob $repair): JsonResponse
    {
        $validated = $request->validate([
            'spare_part_id' => 'required|exists:spare_parts,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'nullable|numeric|min:0',
        ]);

        $partUsed = DB::transaction(function () use ($repair, $validated) {
            /** @var SparePart $part */
            $part = SparePart::where('id', $validated['spare_part_id'])->lockForUpdate()->firstOrFail();

            if ($part->stock_quantity < $validated['quantity']) {
                abort(422, "Insufficient stock for part '{$part->name}'. Available: {$part->stock_quantity}");
            }

            $part->decrement('stock_quantity', $validated['quantity']);

            return RepairPartUsed::create([
                'repair_job_id' => $repair->id,
                'spare_part_id' => $part->id,
                'quantity' => $validated['quantity'],
                'unit_cost' => $part->cost_price,
                'unit_price' => $validated['unit_price'] ?? $part->selling_price,
            ]);
        });

        return response()->json($partUsed->load('sparePart'), 201);
    }

    /**
     * Public tracking page (Customer portal / public tracking without revealing private shop details).
     */
    public function trackPublic(Request $request): JsonResponse
    {
        $query = trim($request->input('tracking_id', ''));
        abort_if(! $query, 400, 'Tracking number or IMEI is required.');

        $job = RepairJob::withoutGlobalScope(\App\Models\Scopes\BelongsToTenantScope::class)
            ->where(function ($q) use ($query): void {
                $q->where('job_number', $query)
                    ->orWhere('imei', $query);
            })
            ->first();

        abort_unless($job, 404, 'No repair job found with this reference.');

        // Sanitize response for public view
        return response()->json([
            'job_number' => $job->job_number,
            'brand' => $job->brand,
            'model' => $job->model,
            'status' => $job->status,
            'fault_description' => $job->fault_description,
            'estimated_completion_date' => $job->estimated_completion_date?->toDateString(),
            'delivered_at' => $job->delivered_at?->toDateString(),
        ]);
    }
}
