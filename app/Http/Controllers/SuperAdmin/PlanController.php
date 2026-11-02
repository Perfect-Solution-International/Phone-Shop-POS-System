<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Platform\SubscriptionPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $plans = SubscriptionPlan::withCount('subscriptions')->get();

        if ($request->wantsJson()) {
            return response()->json($plans);
        }

        return view('super-admin.plans.index', compact('plans'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'monthly_price' => 'required|numeric|min:0',
            'annual_price' => 'nullable|numeric|min:0',
            'trial_days' => 'required|integer|min:0|max:90',
            'max_branches' => 'required|integer|min:1',
            'max_staff' => 'required|integer|min:1',
            'max_products' => 'nullable|integer|min:1',
            'has_imei_tracking' => 'boolean',
            'has_repair_management' => 'boolean',
            'has_warranty_management' => 'boolean',
            'has_accounting_reports' => 'boolean',
            'has_multi_branch' => 'boolean',
            'has_customer_portal' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']).'-'.Str::random(4);
        $plan = SubscriptionPlan::create($validated);

        if ($request->wantsJson()) {
            return response()->json($plan, 201);
        }

        return back()->with('status', "Plan '{$plan->name}' created successfully.");
    }

    public function update(Request $request, SubscriptionPlan $plan): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'monthly_price' => 'required|numeric|min:0',
            'annual_price' => 'nullable|numeric|min:0',
            'trial_days' => 'required|integer|min:0|max:90',
            'max_branches' => 'required|integer|min:1',
            'max_staff' => 'required|integer|min:1',
            'max_products' => 'nullable|integer|min:1',
            'has_imei_tracking' => 'boolean',
            'has_repair_management' => 'boolean',
            'has_warranty_management' => 'boolean',
            'has_accounting_reports' => 'boolean',
            'has_multi_branch' => 'boolean',
            'has_customer_portal' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $booleanFields = [
            'has_imei_tracking',
            'has_repair_management',
            'has_warranty_management',
            'has_accounting_reports',
            'has_multi_branch',
            'has_customer_portal',
            'is_active',
        ];
        foreach ($booleanFields as $field) {
            $validated[$field] = $request->boolean($field);
        }

        $plan->update($validated);

        if ($request->wantsJson()) {
            return response()->json($plan);
        }

        return back()->with('status', "Plan '{$plan->name}' updated successfully.");
    }

    public function toggleActive(SubscriptionPlan $plan): RedirectResponse|JsonResponse
    {
        $plan->update(['is_active' => ! $plan->is_active]);

        $msg = $plan->is_active ? "Plan '{$plan->name}' activated." : "Plan '{$plan->name}' deactivated.";

        if (request()->wantsJson()) {
            return response()->json(['message' => $msg, 'is_active' => $plan->is_active]);
        }

        return back()->with('status', $msg);
    }
}

