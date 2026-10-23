<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Warranty;
use App\Models\WarrantyClaim;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WarrantyController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:warranties.view')->only(['index', 'show', 'verify']);
        $this->middleware('can:warranties.manage')->only(['store']);
        $this->middleware('can:warranties.claim')->only(['claim']);
    }

    public function index(Request $request): JsonResponse
    {
        $query = Warranty::with(['customer', 'product', 'phoneDevice']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('warranty_number', 'like', "%{$search}%")
                    ->orWhere('imei', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->input('warranty_type')) {
            $query->where('warranty_type', $type);
        }

        $warranties = $query->latest()->paginate($request->integer('per_page', 20));

        return response()->json($warranties);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'product_id' => 'nullable|exists:products,id',
            'phone_device_id' => 'nullable|exists:phone_devices,id',
            'repair_job_id' => 'nullable|exists:repair_jobs,id',
            'warranty_type' => 'required|string|in:manufacturer,shop,supplier,repair',
            'imei' => 'nullable|string|max:50',
            'duration_months' => 'required|integer|min:1|max:60',
            'starts_on' => 'nullable|date',
            'terms' => 'nullable|string|max:1000',
        ]);

        $startsOn = isset($validated['starts_on']) ? now()->parse($validated['starts_on']) : now();
        $endsOn = $startsOn->copy()->addMonths((int) $validated['duration_months']);

        $validated['starts_on'] = $startsOn->toDateString();
        $validated['ends_on'] = $endsOn->toDateString();
        $validated['warranty_number'] = 'WAR-'.date('Y').'-'.strtoupper(Str::random(6));
        $validated['status'] = 'active';

        $warranty = Warranty::create($validated);

        return response()->json($warranty->load(['customer', 'product', 'phoneDevice']), 201);
    }

    public function show(Warranty $warranty): JsonResponse
    {
        return response()->json($warranty->load(['customer', 'product', 'phoneDevice', 'repairJob', 'claims']));
    }

    /**
     * Check warranty status by IMEI or warranty number.
     */
    public function verify(Request $request): JsonResponse
    {
        $query = trim($request->input('query', ''));
        abort_if(! $query, 400, 'IMEI or warranty number is required.');

        $warranty = Warranty::with(['customer', 'claims'])
            ->where('warranty_number', $query)
            ->orWhere('imei', $query)
            ->first();

        if (! $warranty) {
            return response()->json(['message' => 'No warranty found for this device or reference.'], 404);
        }

        return response()->json([
            'warranty_number' => $warranty->warranty_number,
            'warranty_type' => $warranty->warranty_type,
            'imei' => $warranty->imei,
            'status' => $warranty->isValid() ? 'active' : 'expired',
            'starts_on' => $warranty->starts_on->toDateString(),
            'ends_on' => $warranty->ends_on->toDateString(),
            'duration_months' => $warranty->duration_months,
            'claims_count' => $warranty->claims->count(),
            'customer_name' => $warranty->customer->name ?? 'Anonymous',
        ]);
    }

    /**
     * Register a warranty claim.
     */
    public function claim(Request $request, Warranty $warranty): JsonResponse
    {
        abort_unless($warranty->isValid(), 422, 'Cannot register claim against an expired or voided warranty.');

        $validated = $request->validate([
            'issue_description' => 'required|string|max:1000',
            'settlement_type' => 'nullable|string|in:repair,replacement,refund',
            'action_notes' => 'nullable|string|max:1000',
        ]);

        $validated['warranty_id'] = $warranty->id;
        $validated['claim_number'] = 'CLM-'.date('Ymd').'-'.strtoupper(Str::random(4));
        $validated['claim_date'] = now()->toDateString();
        $validated['status'] = 'pending';

        $claim = WarrantyClaim::create($validated);

        return response()->json($claim, 201);
    }
}

