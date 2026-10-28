<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OutletController extends Controller
{
    /**
     * Display the outlets / branches management page for the current shop.
     */
    public function index(Request $request): View
    {
        $shop = $request->user()->shop;

        // Auto-provision initial Main Outlet if the shop has 0 outlets
        if ($shop && Outlet::count() === 0) {
            Outlet::create([
                'shop_id' => $shop->id,
                'name' => 'Main Branch',
                'code' => 'OUT-01',
                'phone' => $request->user()->phone,
                'email' => $request->user()->email,
                'address' => $shop->address ?? 'Main Retail Store',
                'city' => 'Colombo',
                'manager_name' => $request->user()->name,
                'is_main' => true,
                'is_active' => true,
            ]);
        }

        $outlets = Outlet::orderByDesc('is_main')->orderBy('name')->get();

        // Calculate branch quota from subscription plan
        $plan = $shop?->activeSubscription?->plan;
        $maxBranches = $plan ? (int) $plan->max_branches : 3;
        $usedBranches = $outlets->count();
        $canAddMore = $usedBranches < $maxBranches;

        $activeOutletId = session('active_outlet_id', $outlets->firstWhere('is_main', true)?->id ?? $outlets->first()?->id);

        return view('shop.outlets.index', compact('outlets', 'maxBranches', 'usedBranches', 'canAddMore', 'activeOutletId'));
    }

    /**
     * Store a new outlet for the current shop.
     */
    public function store(Request $request): RedirectResponse
    {
        $shop = $request->user()->shop;
        $plan = $shop?->activeSubscription?->plan;
        $maxBranches = $plan ? (int) $plan->max_branches : 3;

        if (Outlet::count() >= $maxBranches) {
            return redirect()->back()->with('error', "You have reached your subscription plan limit of {$maxBranches} branch outlet(s). Upgrade your subscription plan to add more outlets.");
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'address' => 'required|string|max:255',
            'city' => 'nullable|string|max:100',
            'manager_name' => 'nullable|string|max:150',
            'is_main' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'receipt_header' => 'nullable|string|max:255',
            'receipt_footer' => 'nullable|string|max:255',
        ]);

        if (empty($validated['code'])) {
            $nextNum = Outlet::count() + 1;
            $validated['code'] = 'OUT-' . str_pad((string) $nextNum, 2, '0', STR_PAD_LEFT);
        }

        $validated['code'] = strtoupper(trim($validated['code']));

        // Check if code is already used within this shop
        if (Outlet::where('code', $validated['code'])->exists()) {
            return redirect()->back()->with('error', "An outlet with code '{$validated['code']}' already exists in your shop.");
        }

        $isMain = $request->boolean('is_main');
        if ($isMain) {
            Outlet::where('is_main', true)->update(['is_main' => false]);
            $validated['is_main'] = true;
        } else {
            $validated['is_main'] = false;
        }

        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;
        $validated['shop_id'] = $shop->id;

        $outlet = Outlet::create($validated);

        return redirect()->route('shop.outlets.index')->with('success', "Outlet '{$outlet->name}' ({$outlet->code}) created successfully!");
    }

    /**
     * Update an existing outlet.
     */
    public function update(Request $request, Outlet $outlet): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:50',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'address' => 'required|string|max:255',
            'city' => 'nullable|string|max:100',
            'manager_name' => 'nullable|string|max:150',
            'is_main' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'receipt_header' => 'nullable|string|max:255',
            'receipt_footer' => 'nullable|string|max:255',
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));

        // Ensure code uniqueness for other outlets in this shop
        $codeExists = Outlet::where('id', '!=', $outlet->id)
            ->where('code', $validated['code'])
            ->exists();

        if ($codeExists) {
            return redirect()->back()->with('error', "Branch code '{$validated['code']}' is already used by another outlet.");
        }

        $isMain = $request->boolean('is_main');
        if ($isMain) {
            Outlet::where('id', '!=', $outlet->id)->where('is_main', true)->update(['is_main' => false]);
            $validated['is_main'] = true;
        } else {
            // If this is currently the only main outlet and the owner is trying to uncheck main without another main
            if ($outlet->is_main && Outlet::where('is_main', true)->count() <= 1) {
                $validated['is_main'] = true; // Retain main branch status
            } else {
                $validated['is_main'] = false;
            }
        }

        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        $outlet->update($validated);

        // Update active session name if currently active
        if (session('active_outlet_id') == $outlet->id) {
            session(['active_outlet_name' => $outlet->name]);
        }

        return redirect()->route('shop.outlets.index')->with('success', "Outlet '{$outlet->name}' updated successfully!");
    }

    /**
     * Delete an outlet.
     */
    public function destroy(Outlet $outlet): RedirectResponse
    {
        if ($outlet->is_main) {
            return redirect()->back()->with('error', 'Cannot delete the primary Main Branch of your shop.');
        }

        $name = $outlet->name;

        if (session('active_outlet_id') == $outlet->id) {
            session()->forget(['active_outlet_id', 'active_outlet_name']);
        }

        $outlet->delete();

        return redirect()->route('shop.outlets.index')->with('success', "Outlet '{$name}' removed from your branches.");
    }

    /**
     * Switch the active outlet session context.
     */
    public function switchOutlet(Request $request, Outlet $outlet): JsonResponse|RedirectResponse
    {
        session([
            'active_outlet_id' => $outlet->id,
            'active_outlet_name' => $outlet->name,
            'active_outlet_code' => $outlet->code,
        ]);

        $msg = "Switched active workspace branch to '{$outlet->name}'.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $msg, 'outlet' => $outlet]);
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Set this outlet as the primary/main branch.
     */
    public function toggleMain(Outlet $outlet): RedirectResponse
    {
        Outlet::where('is_main', true)->update(['is_main' => false]);
        $outlet->update(['is_main' => true]);

        return redirect()->route('shop.outlets.index')->with('success', "Outlet '{$outlet->name}' is now set as the Main Branch.");
    }
}
