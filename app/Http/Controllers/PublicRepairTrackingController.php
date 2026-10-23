<?php

namespace App\Http\Controllers;

use App\Models\RepairJob;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicRepairTrackingController extends Controller
{
    public function show(Request $request): View
    {
        $trackingId = trim($request->input('tracking_id', ''));
        $job = null;
        $notFound = false;

        if ($trackingId !== '') {
            $job = RepairJob::withoutGlobalScope(\App\Models\Scopes\BelongsToTenantScope::class)
                ->where(function ($q) use ($trackingId): void {
                    $q->where('job_number', $trackingId)
                        ->orWhere('imei', $trackingId);
                })
                ->first();

            if (! $job) {
                $notFound = true;
            }
        }

        return view('public.track-repair', compact('job', 'trackingId', 'notFound'));
    }
}

