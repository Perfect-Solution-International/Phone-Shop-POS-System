<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\Platform\SystemActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OutletApprovalController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->input('status', 'pending');

        $query = Outlet::withoutGlobalScopes()
            ->with(['shop', 'approver'])
            ->latest();

        if ($status !== 'all') {
            $query->where('approval_status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhereHas('shop', fn ($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        $outlets = $query->paginate(15)->withQueryString();

        $counts = [
            'pending' => Outlet::withoutGlobalScopes()->where('approval_status', 'pending')->count(),
            'approved' => Outlet::withoutGlobalScopes()->where('approval_status', 'approved')->count(),
            'rejected' => Outlet::withoutGlobalScopes()->where('approval_status', 'rejected')->count(),
            'all' => Outlet::withoutGlobalScopes()->count(),
        ];

        return view('super-admin.outlets.approvals', compact('outlets', 'status', 'counts'));
    }

    public function approve(Request $request, $outlet): RedirectResponse
    {
        $outlet = Outlet::withoutGlobalScopes()->with(['shop.subscription.plan', 'shop.activeSubscription.plan'])->findOrFail($outlet);

        $isCovered = $outlet->isCoveredByPlan();

        // If not covered by current subscription plan quota:
        if (! $isCovered) {
            $fee = (float) ($outlet->additional_fee ?: 2500.00);

            // Block approval if unpaid and not explicitly waived/confirmed by Super Admin
            if ($outlet->payment_status === 'unpaid' && ! $request->boolean('confirm_unpaid')) {
                return redirect()->back()->with('error', "Cannot approve branch '{$outlet->name}'. This store has reached their plan quota limit and the additional branch fee (LKR " . number_format($fee, 2) . ") is unpaid. Payment must be verified before approval.");
            }

            if ($outlet->payment_status !== 'paid') {
                $outlet->update([
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                    'payment_reference' => $outlet->payment_reference ?: 'ADMIN-CONFIRMED',
                ]);
            }
        }

        $outlet->update([
            'approval_status' => 'approved',
            'is_active' => true,
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'rejection_reason' => null,
        ]);

        SystemActivity::log(
            'outlet.approved',
            "Approved branch '{$outlet->name}' ({$outlet->code}) for shop '{$outlet->shop->name}'" . ($isCovered ? " (Covered by plan quota)" : " (Additional branch fee verified)"),
            $outlet->shop_id,
            auth()->id(),
            ['outlet_id' => $outlet->id, 'outlet_name' => $outlet->name, 'covered_by_plan' => $isCovered]
        );

        $msg = "Branch '{$outlet->name}' ({$outlet->code}) for shop '{$outlet->shop->name}' has been approved successfully!";
        if ($isCovered) {
            $msg .= " (Covered under their plan quota - no extra payment required).";
        } else {
            $msg .= " (Additional branch approved with payment verified).";
        }

        return redirect()->back()->with('success', $msg);
    }

    public function reject(Request $request, $outlet): RedirectResponse
    {
        $outlet = Outlet::withoutGlobalScopes()->with('shop')->findOrFail($outlet);

        $validated = $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        $reason = $validated['reason'] ?? 'Branch request declined by Super Administrator.';

        $outlet->update([
            'approval_status' => 'rejected',
            'is_active' => false,
            'rejection_reason' => $reason,
        ]);

        SystemActivity::log(
            'outlet.rejected',
            "Rejected branch '{$outlet->name}' ({$outlet->code}) for shop '{$outlet->shop->name}'",
            $outlet->shop_id,
            auth()->id(),
            ['outlet_id' => $outlet->id, 'reason' => $reason]
        );

        return redirect()->back()->with('success', "Branch '{$outlet->name}' has been rejected.");
    }
}

