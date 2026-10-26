@extends('layouts.shop')

@section('title', 'Repair Center')
@section('page-title', 'Phone Repair & Service Management (8-Stage Workflow)')

@section('content')
<style>
    .stage-pills {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-bottom: 1.5rem;
    }

    .stage-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.45rem 0.85rem;
        border-radius: 9999px;
        font-size: 0.8125rem;
        font-weight: 600;
        text-decoration: none;
        background: #ffffff;
        border: 1px solid var(--surface-border);
        color: var(--text-body);
        transition: all 0.15s ease;
    }

    .stage-pill:hover, .stage-pill.active {
        background: var(--brand-navy-950);
        color: #ffffff;
        border-color: var(--brand-navy-950);
    }

    .stage-count {
        background: rgba(0, 0, 0, 0.08);
        padding: 0.1rem 0.45rem;
        border-radius: 9999px;
        font-size: 0.7rem;
    }

    .stage-pill.active .stage-count {
        background: rgba(255, 255, 255, 0.2);
    }
</style>

<!-- Stage Filter Pills -->
<div class="stage-pills">
    <a href="{{ route('shop.repairs') }}" class="stage-pill {{ !request('status') ? 'active' : '' }}">
        All Jobs <span class="stage-count">{{ $stageCounts['all'] }}</span>
    </a>
    <a href="{{ route('shop.repairs', ['status' => 'received']) }}" class="stage-pill {{ request('status') === 'received' ? 'active' : '' }}">
        1. Received <span class="stage-count">{{ $stageCounts['received'] }}</span>
    </a>
    <a href="{{ route('shop.repairs', ['status' => 'diagnosing']) }}" class="stage-pill {{ request('status') === 'diagnosing' ? 'active' : '' }}">
        2. Diagnosing <span class="stage-count">{{ $stageCounts['diagnosing'] }}</span>
    </a>
    <a href="{{ route('shop.repairs', ['status' => 'in_repair']) }}" class="stage-pill {{ request('status') === 'in_repair' ? 'active' : '' }}">
        5. In Repair <span class="stage-count">{{ $stageCounts['in_repair'] }}</span>
    </a>
    <a href="{{ route('shop.repairs', ['status' => 'ready_for_collection']) }}" class="stage-pill {{ request('status') === 'ready_for_collection' ? 'active' : '' }}">
        7. Ready for Collection <span class="stage-count">{{ $stageCounts['ready_for_collection'] }}</span>
    </a>
    <a href="{{ route('shop.repairs', ['status' => 'delivered']) }}" class="stage-pill {{ request('status') === 'delivered' ? 'active' : '' }}">
        8. Delivered <span class="stage-count">{{ $stageCounts['delivered'] }}</span>
    </a>
</div>

<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <form method="GET" action="{{ route('shop.repairs') }}" style="display: flex; gap: 0.75rem; flex: 1; flex-wrap: wrap;">
            <input type="text" name="search" class="form-control" style="max-width: 320px;" placeholder="Search Job #, IMEI, Model, Customer..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-secondary btn-sm">Search</button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('shop.repairs') }}" class="btn btn-sm btn-secondary" style="color: #64748b;">Clear</a>
            @endif
        </form>

        <button type="button" class="btn btn-primary" onclick="openModal('createRepairModal')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Book New Repair
        </button>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Job # / Customer</th>
                    <th>Device Details</th>
                    <th>Reported Fault</th>
                    <th>Stage Status</th>
                    <th>Cost / Advance</th>
                    <th>Parts Used</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($repairs as $repair)
                <tr>
                    <td>
                        <div style="font-family: monospace; font-weight: 700; color: #4f46e5; font-size: 0.875rem;">
                            {{ $repair->job_number }}
                        </div>
                        <div style="font-weight: 600; margin-top: 2px;">{{ $repair->customer->name ?? 'Direct Client' }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $repair->customer->phone ?? '' }}</div>
                    </td>
                    <td>
                        <strong>{{ $repair->brand }} {{ $repair->model }}</strong>
                        @if($repair->imei)
                            <div style="font-family: monospace; font-size: 0.75rem; color: #64748b;">IMEI: {{ $repair->imei }}</div>
                        @endif
                        @if($repair->technician)
                            <div style="font-size: 0.7rem; color: #0284c7; margin-top: 2px;">Tech: {{ $repair->technician->name }}</div>
                        @endif
                    </td>
                    <td style="max-width: 200px;">
                        <span style="font-size: 0.8125rem;">{{ Str::limit($repair->fault_description, 60) }}</span>
                    </td>
                    <td>
                        @php
                            $badgeClass = match($repair->status) {
                                'delivered' => 'badge-success',
                                'ready_for_collection' => 'badge-info',
                                'in_repair', 'diagnosing' => 'badge-purple',
                                'waiting_for_parts', 'waiting_for_approval' => 'badge-warning',
                                default => 'badge-secondary',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">
                            {{ ucwords(str_replace('_', ' ', $repair->status)) }}
                        </span>
                    </td>
                    <td>
                        <div style="font-weight: 700; color: var(--text-heading);">
                            LKR {{ number_format($repair->final_cost ?? $repair->estimated_cost, 2) }}
                        </div>
                        @if($repair->advance_amount > 0)
                            <div style="font-size: 0.75rem; color: #166534;">Adv: LKR {{ number_format($repair->advance_amount, 2) }}</div>
                        @endif
                    </td>
                    <td>
                        @if($repair->partsUsed->isNotEmpty())
                            <span class="badge badge-info">{{ $repair->partsUsed->count() }} parts</span>
                        @else
                            <span style="font-size: 0.75rem; color: var(--text-muted);">None</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 0.35rem; align-items: center;">
                            @if($repair->customer && $repair->customer->phone)
                                @php
                                    $rawPhone = preg_replace('/[^0-9]/', '', $repair->customer->phone);
                                    $waPhone = str_starts_with($rawPhone, '0') ? '94' . substr($rawPhone, 1) : $rawPhone;
                                    $waMsg = urlencode("Hello {$repair->customer->name}, update regarding repair #{$repair->job_number} ({$repair->device_model}): Status is " . ucwords(str_replace('_', ' ', $repair->status)) . ". Total Amount: LKR " . number_format($repair->final_cost ?? $repair->estimated_cost, 2) . ". Thank you!");
                                @endphp
                                <a href="https://wa.me/{{ $waPhone }}?text={{ $waMsg }}" target="_blank" class="btn btn-sm" title="Notify Customer via WhatsApp" style="background: #25D366; color: #ffffff; padding: 0.25rem 0.5rem; font-size: 0.75rem; display: inline-flex; align-items: center; text-decoration: none; border-radius: 4px; font-weight: 700;">
                                    💬 WA
                                </a>
                            @endif
                            <button class="btn btn-sm btn-secondary" onclick="openStatusModal({{ $repair->id }}, '{{ $repair->job_number }}', '{{ $repair->status }}', '{{ $repair->final_cost ?? $repair->estimated_cost }}', '{{ $repair->technician_id }}')">
                                Status
                            </button>
                            <button class="btn btn-sm btn-secondary" onclick="openPartsModal({{ $repair->id }}, '{{ $repair->job_number }}')">
                                + Part
                            </button>
                            <button class="btn btn-sm btn-secondary" onclick='openJobCardModal(@json($repair->load("customer", "technician")))' title="Print Repair Job Card Ticket">
                                🖨️ Job Card
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 3rem;">
                        No repair jobs found. Book a repair ticket above.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($repairs->hasPages())
    <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border);">
        {{ $repairs->links() }}
    </div>
    @endif
</div>

<!-- Modal: Create New Repair Job -->
<div class="modal-backdrop" id="createRepairModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Book New Repair Ticket</h3>
            <button type="button" class="modal-close" onclick="closeModal('createRepairModal')">&times;</button>
        </div>
        <form method="POST" action="{{ route('shop.repairs.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Customer *</label>
                    <select name="customer_id" class="form-control" required>
                        <option value="">Select Customer</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Device Brand *</label>
                        <input type="text" name="brand" class="form-control" placeholder="e.g. Apple, Samsung" required>
                    </div>
                    <div class="form-group">
                        <label>Device Model *</label>
                        <input type="text" name="model" class="form-control" placeholder="e.g. iPhone 13 Pro" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>IMEI / Serial Number</label>
                        <input type="text" name="imei" class="form-control" placeholder="15-digit IMEI (for customer tracking)" style="font-family: monospace;">
                    </div>
                    <div class="form-group">
                        <label>Assign Technician</label>
                        <select name="technician_id" class="form-control">
                            <option value="">Unassigned</option>
                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Reported Fault / Problem *</label>
                    <textarea name="fault_description" class="form-control" rows="2" placeholder="Describe customer's reported issues in detail..." required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Physical Condition Notes</label>
                        <input type="text" name="condition_notes" class="form-control" placeholder="Scratches, dents, water damage signs...">
                    </div>
                    <div class="form-group">
                        <label>Accessories Received</label>
                        <input type="text" name="accessories_received" class="form-control" placeholder="SIM tray, back cover, charger...">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Estimated Repair Cost (LKR) *</label>
                        <input type="number" step="0.01" name="estimated_cost" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="form-group">
                        <label>Advance Payment Paid (LKR)</label>
                        <input type="number" step="0.01" name="advance_amount" class="form-control" placeholder="0.00" value="0.00">
                    </div>
                </div>

                <div class="form-group">
                    <label>Estimated Completion Date</label>
                    <input type="date" name="estimated_completion_date" class="form-control">
                </div>
            </div>
            <div style="padding: 1.25rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('createRepairModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Ticket</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Update Status -->
<div class="modal-backdrop" id="updateStatusModal">
    <div class="modal-box" style="max-width: 500px;">
        <div class="modal-header">
            <h3>Update Repair Stage & Notes</h3>
            <button type="button" class="modal-close" onclick="closeModal('updateStatusModal')">&times;</button>
        </div>
        <form id="formUpdateStatus" method="POST">
            @csrf
            @method('PATCH')
            <div class="modal-body">
                <div style="margin-bottom: 1rem; font-size: 0.875rem;">
                    Repair Ticket: <strong id="lblJobNumber" style="font-family: monospace;"></strong>
                </div>

                <div class="form-group">
                    <label>8-Stage Lifecycle Status *</label>
                    <select name="status" id="selStatus" class="form-control" required>
                        <option value="received">1. Received</option>
                        <option value="diagnosing">2. Diagnosing</option>
                        <option value="waiting_for_approval">3. Waiting for Approval</option>
                        <option value="waiting_for_parts">4. Waiting for Parts</option>
                        <option value="in_repair">5. In Repair</option>
                        <option value="quality_check">6. Quality Check</option>
                        <option value="ready_for_collection">7. Ready for Collection</option>
                        <option value="delivered">8. Delivered</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Assign / Reassign Technician</label>
                    <select name="technician_id" id="selTechnician" class="form-control">
                        <option value="">-- Unassigned --</option>
                        @foreach($technicians as $tech)
                            <option value="{{ $tech->id }}">{{ $tech->name }} ({{ $tech->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Technician Service Notes</label>
                    <textarea name="technician_notes" class="form-control" rows="3" placeholder="Action taken, diagnostic findings, replaced components..."></textarea>
                </div>

                <div class="form-group">
                    <label>Final Cost (LKR)</label>
                    <input type="number" step="0.01" name="final_cost" id="inpFinalCost" class="form-control">
                </div>
            </div>
            <div style="padding: 1.25rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('updateStatusModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Status</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Allocate Spare Part -->
<div class="modal-backdrop" id="allocatePartsModal">
    <div class="modal-box" style="max-width: 500px;">
        <div class="modal-header">
            <h3>Allocate Spare Part from Stock</h3>
            <button type="button" class="modal-close" onclick="closeModal('allocatePartsModal')">&times;</button>
        </div>
        <form id="formAllocatePart" method="POST">
            @csrf
            <div class="modal-body">
                <div style="margin-bottom: 1rem; font-size: 0.875rem;">
                    Allocating part to ticket: <strong id="lblPartJobNumber" style="font-family: monospace;"></strong>
                </div>

                <div class="form-group">
                    <label>Select Spare Part *</label>
                    <select name="spare_part_id" class="form-control" required>
                        <option value="">Choose spare part from inventory</option>
                        @foreach($spareParts as $sp)
                            <option value="{{ $sp->id }}">{{ $sp->name }} (Available: {{ $sp->stock_quantity }} | LKR {{ number_format($sp->selling_price, 2) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Quantity *</label>
                    <input type="number" name="quantity" class="form-control" value="1" min="1" required>
                </div>
            </div>
            <div style="padding: 1.25rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('allocatePartsModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Deduct Stock & Allocate</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Printable Repair Job Card -->
<div class="modal-backdrop" id="jobCardModal">
    <div class="modal-box" style="max-width: 750px; padding: 0;">
        <div class="modal-header no-print" style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--surface-border);">
            <h3>Repair Job Card & Intake Ticket</h3>
            <button type="button" class="modal-close" onclick="closeModal('jobCardModal')">&times;</button>
        </div>
        <div class="modal-body" id="jobCardPrintArea" style="padding: 1.75rem;">
            <!-- Printable Job Sheet Box -->
            <div style="border: 2px solid #0f172a; padding: 1.5rem; border-radius: 8px; background: #ffffff;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px dashed #94a3b8; padding-bottom: 1rem; margin-bottom: 1rem;">
                    <div>
                        <h2 style="margin: 0; font-size: 1.35rem; font-weight: 800; color: #0f172a;">{{ auth()->user()->shop->name ?? 'Phone Shop' }}</h2>
                        <div style="font-size: 0.8rem; color: #475569; margin-top: 0.2rem;">
                            Branch: {{ session('active_outlet_name', auth()->user()->shop->mainOutlet->name ?? 'Main Branch') }}<br>
                            Phone: {{ auth()->user()->shop->phone ?? 'Shop Counter' }}
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <span style="display: inline-block; background: #0f172a; color: #ffffff; padding: 0.25rem 0.65rem; border-radius: 4px; font-weight: 800; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em;">
                            WORKSHOP JOB CARD
                        </span>
                        <div id="jcJobNumber" style="font-size: 1.25rem; font-weight: 800; color: #4f46e5; font-family: monospace; margin-top: 0.35rem;"></div>
                        <div id="jcDate" style="font-size: 0.75rem; color: #64748b;"></div>
                    </div>
                </div>

                <!-- Customer & Device Grid -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; background: #f8fafc; padding: 0.85rem 1rem; border-radius: 6px; border: 1px solid #e2e8f0;">
                    <div>
                        <div style="font-size: 0.72rem; text-transform: uppercase; color: #64748b; font-weight: 700;">Customer Info</div>
                        <div id="jcCustomerName" style="font-weight: 700; font-size: 0.95rem; color: #0f172a; margin-top: 2px;"></div>
                        <div id="jcCustomerPhone" style="font-size: 0.825rem; color: #334155;"></div>
                    </div>
                    <div>
                        <div style="font-size: 0.72rem; text-transform: uppercase; color: #64748b; font-weight: 700;">Device Details</div>
                        <div id="jcDevice" style="font-weight: 700; font-size: 0.95rem; color: #0f172a; margin-top: 2px;"></div>
                        <div id="jcImei" style="font-size: 0.825rem; font-family: monospace; color: #334155;"></div>
                        <div id="jcTech" style="font-size: 0.75rem; color: #0284c7; margin-top: 2px;"></div>
                    </div>
                </div>

                <!-- Fault & Notes -->
                <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.75rem;">
                        <div style="font-size: 0.72rem; text-transform: uppercase; color: #64748b; font-weight: 700;">Reported Fault / Symptoms</div>
                        <div id="jcFault" style="font-size: 0.85rem; color: #0f172a; margin-top: 4px; white-space: pre-wrap;"></div>
                    </div>
                    <div style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.75rem;">
                        <div style="font-size: 0.72rem; text-transform: uppercase; color: #64748b; font-weight: 700;">Intake Condition & Accessories</div>
                        <div style="font-size: 0.8rem; margin-top: 4px;">
                            <strong>Condition:</strong> <span id="jcCondition"></span><br>
                            <strong>Accessories:</strong> <span id="jcAccessories"></span>
                        </div>
                    </div>
                </div>

                <!-- Billing & Estimates -->
                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; padding: 0.75rem 0.5rem; margin-bottom: 1rem;">
                    <div>
                        <span style="font-size: 0.75rem; color: #64748b;">Est. Completion:</span>
                        <strong id="jcEstDate" style="font-size: 0.85rem; color: #0f172a; margin-left: 0.25rem;"></strong>
                    </div>
                    <div style="display: flex; gap: 1.5rem; text-align: right;">
                        <div>
                            <div style="font-size: 0.7rem; color: #64748b; text-transform: uppercase;">Estimated Cost</div>
                            <div id="jcEstCost" style="font-weight: 700; font-size: 0.9rem; color: #0f172a;"></div>
                        </div>
                        <div>
                            <div style="font-size: 0.7rem; color: #64748b; text-transform: uppercase;">Advance Paid</div>
                            <div id="jcAdvance" style="font-weight: 700; font-size: 0.9rem; color: #166534;"></div>
                        </div>
                        <div>
                            <div style="font-size: 0.7rem; color: #64748b; text-transform: uppercase;">Balance Due</div>
                            <div id="jcBalance" style="font-weight: 800; font-size: 0.95rem; color: #dc2626;"></div>
                        </div>
                    </div>
                </div>

                <!-- Signatures & Notice -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 1.5rem; padding-top: 0.5rem;">
                    <div style="border-top: 1px dashed #94a3b8; padding-top: 0.4rem; font-size: 0.75rem; color: #64748b; text-align: center;">
                        Customer Signature &amp; Acceptance
                    </div>
                    <div style="border-top: 1px dashed #94a3b8; padding-top: 0.4rem; font-size: 0.75rem; color: #64748b; text-align: center;">
                        Authorized Workshop Intake Signature
                    </div>
                </div>
                <div style="font-size: 0.68rem; color: #94a3b8; text-align: center; margin-top: 1rem; border-top: 1px dotted #cbd5e1; padding-top: 0.5rem;">
                    Track online anytime at: <span id="jcTrackUrl" style="color: #4f46e5; font-family: monospace;"></span> | Presentation of this ticket required for device collection.
                </div>
            </div>
        </div>
        <div class="modal-footer no-print" style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.75rem;">
            <button type="button" class="btn btn-secondary" onclick="closeModal('jobCardModal')">Close</button>
            <button type="button" class="btn btn-primary" onclick="printJobCard()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 4px;"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                Print Job Card
            </button>
        </div>
    </div>
</div>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #jobCardModal, #jobCardModal * {
            visibility: visible;
        }
        #jobCardModal {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: auto;
            background: #ffffff !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .modal-box {
            max-width: 100% !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .no-print {
            display: none !important;
        }
    }
</style>

@push('scripts')
<script>
    function openStatusModal(id, jobNumber, currentStatus, cost, technicianId) {
        document.getElementById('lblJobNumber').innerText = jobNumber;
        document.getElementById('selStatus').value = currentStatus;
        document.getElementById('inpFinalCost').value = cost;
        document.getElementById('selTechnician').value = technicianId || '';
        document.getElementById('formUpdateStatus').action = '/shop/repairs/' + id + '/status';
        openModal('updateStatusModal');
    }

    function openPartsModal(id, jobNumber) {
        document.getElementById('lblPartJobNumber').innerText = jobNumber;
        document.getElementById('formAllocatePart').action = '/shop/repairs/' + id + '/parts';
        openModal('allocatePartsModal');
    }

    function openJobCardModal(repair) {
        document.getElementById('jcJobNumber').innerText = repair.job_number;
        document.getElementById('jcDate').innerText = 'Intake: ' + (repair.created_at ? new Date(repair.created_at).toLocaleDateString() : 'Today');
        document.getElementById('jcCustomerName').innerText = (repair.customer && repair.customer.name) ? repair.customer.name : 'Walk-in Customer';
        document.getElementById('jcCustomerPhone').innerText = (repair.customer && repair.customer.phone) ? 'Phone: ' + repair.customer.phone : 'No phone';
        document.getElementById('jcDevice').innerText = repair.brand + ' ' + repair.model;
        document.getElementById('jcImei').innerText = repair.imei ? 'IMEI: ' + repair.imei : 'IMEI: Not Provided';
        document.getElementById('jcTech').innerText = (repair.technician && repair.technician.name) ? 'Assigned Tech: ' + repair.technician.name : 'Tech: Unassigned';
        document.getElementById('jcFault').innerText = repair.fault_description || 'General Service';
        document.getElementById('jcCondition').innerText = repair.condition_notes || 'Normal Wear';
        document.getElementById('jcAccessories').innerText = repair.accessories_received || 'Device Only';
        document.getElementById('jcEstDate').innerText = repair.estimated_completion_date ? new Date(repair.estimated_completion_date).toLocaleDateString() : 'To be notified';

        const est = parseFloat(repair.estimated_cost || 0);
        const adv = parseFloat(repair.advance_amount || 0);
        const bal = Math.max(0, est - adv);

        document.getElementById('jcEstCost').innerText = 'LKR ' + est.toFixed(2);
        document.getElementById('jcAdvance').innerText = 'LKR ' + adv.toFixed(2);
        document.getElementById('jcBalance').innerText = 'LKR ' + bal.toFixed(2);

        const trackUrl = window.location.origin + '/track-repair?id=' + encodeURIComponent(repair.job_number);
        document.getElementById('jcTrackUrl').innerText = trackUrl;

        openModal('jobCardModal');
    }

    function printJobCard() {
        window.print();
    }
</script>
@endpush
@endsection

