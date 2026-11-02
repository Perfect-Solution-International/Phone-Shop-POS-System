<?php

namespace App\Http\Controllers;

use App\Models\RepairJob;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicRepairTrackingController extends Controller
{
    public function show(Request $request): View
    {
        $trackingId = trim($request->input('tracking_id', $request->input('id', '')));
        $job = null;
        $notFound = false;

        if ($trackingId !== '') {
            $job = $this->findRepairJob($trackingId);

            if (! $job) {
                $notFound = true;
            }
        }

        return view('public.track-repair', compact('job', 'trackingId', 'notFound'));
    }

    public function approveEstimate(Request $request): \Illuminate\Http\RedirectResponse
    {
        $trackingId = trim($request->input('tracking_id', $request->input('id', '')));
        abort_if(! $trackingId, 400, 'Tracking reference is required.');

        $job = $this->findRepairJob($trackingId);
        abort_if(! $job, 404, 'Repair ticket not found.');

        $approvalNote = '[Customer Approved Estimate Online on ' . now()->toDayDateTimeString() . ']';

        $job->update([
            'status' => 'in_repair',
            'technician_notes' => trim(($job->technician_notes ? $job->technician_notes . "\n" : '') . $approvalNote),
        ]);

        return redirect()->route('public.repair-track', ['tracking_id' => $job->job_number])
            ->with('success', "Estimate for repair ticket #{$job->job_number} approved! Our technicians will proceed with the service.");
    }

    private function findRepairJob(string $trackingId): ?RepairJob
    {
        $cleanId = trim($trackingId);
        if ($cleanId === '') {
            return null;
        }

        $base = RepairJob::withoutGlobalScope(\App\Models\Scopes\BelongsToTenantScope::class)
            ->with(['customer' => function ($q): void {
                $q->withoutGlobalScope('tenant');
            }, 'technician']);

        // Collect search candidate tokens (raw, stripped of punctuation/spaces, and phone variations)
        $candidates = array_unique(array_filter([
            $cleanId,
            preg_replace('/[^a-zA-Z0-9]/', '', $cleanId),
        ]));

        // Sri Lankan phone variations: e.g. +94748156923 -> 0748156923, or 748156923 -> 0748156923
        foreach ($candidates as $cand) {
            if (preg_match('/^(?:\+?94|0)?([1-9][0-9]{8})$/', $cand, $m)) {
                $candidates[] = '0' . $m[1];
                $candidates[] = $m[1];
            }
        }
        $candidates = array_unique(array_filter($candidates));

        // Find customer IDs matching any candidate NIC / Phone / ID without tenant scope
        $customerIds = \App\Models\Customer::withoutGlobalScope('tenant')
            ->where(function ($cq) use ($candidates): void {
                foreach ($candidates as $cand) {
                    $cq->orWhere('nic', $cand)
                       ->orWhere('phone', $cand);

                    if (is_numeric($cand)) {
                        $cq->orWhere('id', (int) $cand);
                    }
                }
            })
            ->pluck('id');

        // 1. Exact match against candidates on Job Number, IMEI, Customer NIC, Ticket ID, or Customer ID
        $job = (clone $base)->where(function ($q) use ($candidates, $customerIds): void {
            foreach ($candidates as $cand) {
                $q->orWhere('job_number', $cand)
                  ->orWhere('imei', $cand)
                  ->orWhere('customer_nic', $cand);

                if (is_numeric($cand)) {
                    $q->orWhere('id', (int) $cand);
                }
            }

            if ($customerIds->isNotEmpty()) {
                $q->orWhereIn('customer_id', $customerIds);
            }
        })->latest('id')->first();

        if ($job) {
            return $job;
        }

        // 2. Substring / Prefix match if query has at least 4 characters
        if (strlen($cleanId) >= 4) {
            $fuzzyCustomerIds = \App\Models\Customer::withoutGlobalScope('tenant')
                ->where(function ($cq) use ($candidates): void {
                    foreach ($candidates as $cand) {
                        if (strlen($cand) >= 4) {
                            $cq->orWhere('nic', 'like', "%{$cand}%")
                               ->orWhere('phone', 'like', "%{$cand}%");
                        }
                    }
                })
                ->pluck('id');

            $job = (clone $base)->where(function ($q) use ($candidates, $fuzzyCustomerIds): void {
                foreach ($candidates as $cand) {
                    if (strlen($cand) >= 4) {
                        $q->orWhere('job_number', 'like', "%{$cand}%")
                          ->orWhere('imei', 'like', "%{$cand}%")
                          ->orWhere('customer_nic', 'like', "%{$cand}%");
                    }
                }

                if ($fuzzyCustomerIds->isNotEmpty()) {
                    $q->orWhereIn('customer_id', $fuzzyCustomerIds);
                }
            })->latest('id')->first();
        }

        return $job;
    }
}


