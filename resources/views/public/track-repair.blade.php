<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Track Your Phone Repair — Customer Status Portal</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <style>
        :root {
            --brand-indigo: #4f46e5;
            --brand-navy: #0f172a;
            --surface-bg: #f8fafc;
            --surface-card: #ffffff;
            --border-color: #e2e8f0;
            --text-heading: #0f172a;
            --text-muted: #64748b;
            --color-success: #10b981;
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--surface-bg);
            color: #334155;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2.5rem 1rem;
        }

        .tracking-container {
            max-width: 720px;
            width: 100%;
        }

        .header-brand {
            text-align: center;
            margin-bottom: 2rem;
        }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.35rem 0.85rem;
            background: #eef2ff;
            color: var(--brand-indigo);
            font-size: 0.8125rem;
            font-weight: 700;
            border-radius: 9999px;
            margin-bottom: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .header-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-heading);
            letter-spacing: -0.02em;
        }

        .header-desc {
            font-size: 0.9375rem;
            color: var(--text-muted);
            margin-top: 0.5rem;
        }

        .card {
            background: var(--surface-card);
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }

        .form-row {
            display: flex;
            gap: 0.75rem;
        }

        .input-track {
            flex: 1;
            padding: 0.85rem 1.15rem;
            font-size: 1rem;
            font-family: monospace;
            border: 1.5px solid var(--border-color);
            border-radius: 10px;
            color: var(--text-heading);
            outline: none;
            transition: border-color 0.15s ease;
        }

        .input-track:focus {
            border-color: var(--brand-indigo);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        }

        .btn-track {
            padding: 0.85rem 1.75rem;
            background: var(--brand-indigo);
            color: #ffffff;
            font-size: 1rem;
            font-weight: 600;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: opacity 0.15s ease;
        }

        .btn-track:hover {
            opacity: 0.92;
        }

        /* 8-Stage Steps Timeline */
        .timeline-steps {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.75rem;
            margin: 2rem 0 1.5rem;
        }

        .timeline-step {
            background: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 0.75rem 0.5rem;
            text-align: center;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-muted);
            position: relative;
        }

        .timeline-step.completed {
            background: #f0fdf4;
            border-color: #bbf7d0;
            color: #166534;
        }

        .timeline-step.current {
            background: #eef2ff;
            border-color: #c7d2fe;
            color: var(--brand-indigo);
            box-shadow: 0 0 0 2px var(--brand-indigo);
            font-weight: 700;
        }

        .step-num {
            display: block;
            font-size: 0.6875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.2rem;
            opacity: 0.8;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 0.6rem 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.875rem;
        }

        .detail-label {
            color: var(--text-muted);
        }

        .detail-val {
            font-weight: 600;
            color: var(--text-heading);
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            padding: 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            text-align: center;
        }

        @media (max-width: 600px) {
            .form-row { flex-direction: column; }
            .timeline-steps { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>
    <div class="tracking-container">
        <header class="header-brand">
            <span class="brand-badge">Customer Service Portal</span>
            <h1 class="header-title">Track Your Phone Repair</h1>
            <p class="header-desc">Enter your Job Number (from your receipt) or device IMEI to view real-time workshop progress.</p>
        </header>

        <div class="card">
            <form method="GET" action="{{ route('public.repair-track') }}">
                <div class="form-row">
                    <input type="text" name="tracking_id" class="input-track" placeholder="e.g. REP-20261022-XXXX or 15-digit IMEI" value="{{ $trackingId }}" required>
                    <button type="submit" class="btn-track">Check Status</button>
                </div>
            </form>
        </div>

        @if($notFound)
            <div class="card alert-error">
                <strong>No Repair Ticket Found</strong>
                <p style="margin-top: 0.35rem; font-size: 0.8125rem;">We couldn't locate any active repair job matching "{{ $trackingId }}". Please verify your receipt reference or contact the shop counter.</p>
            </div>
        @endif

        @if($job)
            @php
                $stages = [
                    'received' => '1. Received',
                    'diagnosing' => '2. Diagnosing',
                    'waiting_for_approval' => '3. Awaiting Approval',
                    'waiting_for_parts' => '4. Awaiting Parts',
                    'in_repair' => '5. In Repair',
                    'quality_check' => '6. Quality Testing',
                    'ready_for_collection' => '7. Ready for Pickup',
                    'delivered' => '8. Collected',
                ];

                $stageKeys = array_keys($stages);
                $currentIndex = array_search($job->status, $stageKeys);
                if ($currentIndex === false) $currentIndex = 0;
            @endphp

            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem; margin-bottom: 1.25rem;">
                    <div>
                        <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Job Number</span>
                        <div style="font-size: 1.25rem; font-weight: 800; font-family: monospace; color: var(--brand-indigo);">{{ $job->job_number }}</div>
                    </div>
                    <div style="text-align: right;">
                        <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Current Stage</span>
                        <div style="font-size: 1rem; font-weight: 700; color: #166534;">
                            {{ $stages[$job->status] ?? ucfirst($job->status) }}
                        </div>
                    </div>
                </div>

                <!-- 8-Stage Steps Grid -->
                <div class="timeline-steps">
                    @foreach($stages as $key => $label)
                        @php
                            $idx = array_search($key, $stageKeys);
                            $class = '';
                            if ($idx < $currentIndex) {
                                $class = 'completed';
                            } elseif ($idx === $currentIndex) {
                                $class = 'current';
                            }
                        @endphp
                        <div class="timeline-step {{ $class }}">
                            <span class="step-num">Step {{ $idx + 1 }}</span>
                            {{ $label }}
                        </div>
                    @endforeach
                </div>

                <!-- Job Details -->
                <div style="margin-top: 1.5rem;">
                    <div class="detail-row">
                        <span class="detail-label">Device</span>
                        <span class="detail-val">{{ $job->brand }} {{ $job->model }}</span>
                    </div>
                    @if($job->imei)
                    <div class="detail-row">
                        <span class="detail-label">IMEI</span>
                        <span class="detail-val" style="font-family: monospace;">{{ $job->imei }}</span>
                    </div>
                    @endif
                    <div class="detail-row">
                        <span class="detail-label">Reported Issue</span>
                        <span class="detail-val">{{ $job->fault_description }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Registered On</span>
                        <span class="detail-val">{{ $job->created_at->toFormattedDateString() }}</span>
                    </div>
                    @if($job->estimated_completion_date)
                    <div class="detail-row">
                        <span class="detail-label">Target Completion</span>
                        <span class="detail-val" style="color: #4f46e5;">{{ $job->estimated_completion_date->toFormattedDateString() }}</span>
                    </div>
                    @endif
                    @if($job->delivered_at)
                    <div class="detail-row">
                        <span class="detail-label">Delivered Date</span>
                        <span class="detail-val" style="color: #166534;">{{ $job->delivered_at->toFormattedDateString() }}</span>
                    </div>
                    @endif
                </div>

                @if($job->status === 'ready_for_collection')
                    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 1rem; margin-top: 1.5rem; text-align: center;">
                        <strong style="color: #166534;">🎉 Your device has passed quality tests and is ready for collection!</strong>
                        <p style="font-size: 0.8125rem; color: #166534; margin-top: 0.25rem;">Please bring your receipt or Job Ticket <code>{{ $job->job_number }}</code> to the shop counter to collect your device.</p>
                    </div>
                @endif
            </div>
        @endif

        <footer style="text-align: center; font-size: 0.8125rem; color: var(--text-muted); margin-top: 2rem;">
            Powered by {{ config('app.name', 'SaaS Phone Shop POS System') }} • Multi-Tenant Phone Retail Cloud Platform
        </footer>
    </div>
</body>
</html>

