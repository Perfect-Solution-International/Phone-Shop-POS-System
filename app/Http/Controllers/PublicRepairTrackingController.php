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

    public function approveEstimate(Request $request): \Illuminate\Http\RedirectResponse
    {
        $trackingId = trim($request->input('tracking_id', ''));
        abort_if(! $trackingId, 400, 'Tracking reference is required.');

        $job = RepairJob::withoutGlobalScope(\App\Models\Scopes\BelongsToTenantScope::class)
            ->where(function ($q) use ($trackingId): void {
                $q->where('job_number', $trackingId)
                    ->orWhere('imei', $trackingId);
            })
            ->firstOrFail();

        $approvalNote = '[Customer Approved Estimate Online on ' . now()->toDayDateTimeString() . ']';

        $job->update([
            'status' => 'in_repair',
            'technician_notes' => trim(($job->technician_notes ? $job->technician_notes . "\n" : '') . $approvalNote),
        ]);

        return redirect()->route('public.repair-track', ['tracking_id' => $job->job_number])
            ->with('success', "Estimate for repair ticket #{$job->job_number} approved! Our technicians will proceed with the service.");
    }
}

