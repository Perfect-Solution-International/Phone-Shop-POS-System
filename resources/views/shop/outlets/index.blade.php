@extends('layouts.shop')

@section('title', 'Outlets & Branches')
@section('page-title', 'Shop Outlets & Multi-Branch Management')

@section('content')
<style>
    .outlets-grid {
        padding: 1.5rem;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(min(100%, 310px), 1fr));
        gap: 1.25rem;
    }
    .outlet-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 2px 6px rgba(0,0,0,0.03);
        position: relative;
        transition: all 0.2s ease;
    }
    .outlet-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
    }
    @media (max-width: 640px) {
        .outlets-grid {
            padding: 1rem;
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        .outlet-card {
            padding: 1rem;
        }
    }
</style>
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem; justify-content: space-between; align-items: center;">
        <div>
            <h3 style="margin: 0; font-size: 1.125rem; font-weight: 700; color: var(--text-heading); display: flex; align-items: center; gap: 0.5rem;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2.2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                Registered Outlets &amp; Showrooms
            </h3>
            <p style="margin: 0.25rem 0 0; font-size: 0.8125rem; color: var(--text-muted);">
                Configure your retail branches, contact information, and physical locations.
            </p>
        </div>

        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            <!-- Quota Badge -->
            <div style="background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.4rem 0.85rem; font-size: 0.8125rem; font-weight: 600; color: #334155; display: inline-flex; align-items: center; gap: 0.4rem;">
                <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: {{ $canAddMore ? '#10b981' : '#f59e0b' }};"></span>
                <span>Quota: <strong>{{ $usedBranches }} / {{ $maxBranches }}</strong> Outlets</span>
            </div>

            @if($canAddMore)
                <button type="button" class="btn btn-primary" onclick="openModal('addOutletModal')">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Add New Outlet
                </button>
            @else
                <button type="button" class="btn btn-secondary" onclick="alert('You have reached the maximum number of outlets ({{ $maxBranches }}) allowed by your current subscription plan. Please upgrade your plan to unlock more branches.')" style="opacity: 0.85;" title="Plan limit reached">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    Upgrade for More Outlets
                </button>
            @endif
        </div>
    </div>

    <!-- Outlets Grid -->
    <div class="outlets-grid">
        @forelse($outlets as $outlet)
            <div class="outlet-card" style="border: 1.5px solid {{ $outlet->is_main ? '#818cf8' : 'var(--surface-border)' }};">
                <div>
                    <!-- Card Top Header -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.75rem;">
                        <div style="min-width: 0;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                <h4 style="margin: 0; font-size: 1rem; font-weight: 700; color: var(--text-heading); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ $outlet->name }}
                                </h4>
                                <span style="font-family: monospace; font-size: 0.72rem; font-weight: 700; background: #e0e7ff; color: #4338ca; padding: 0.15rem 0.45rem; border-radius: 4px;">
                                    {{ $outlet->code }}
                                </span>
                            </div>
                            @if($outlet->city)
                                <span style="font-size: 0.75rem; color: #64748b; font-weight: 500;">
                                    📍 {{ $outlet->city }}
                                </span>
                            @endif
                        </div>

                        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.25rem;">
                            @if($outlet->is_main)
                                <span class="badge" style="background: #4f46e5; color: #ffffff; font-size: 0.68rem; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.25rem;">
                                    ★ Main Branch
                                </span>
                            @endif

                            @if($outlet->is_active)
                                <span class="badge badge-success" style="font-size: 0.65rem;">Active</span>
                            @else
                                <span class="badge badge-secondary" style="font-size: 0.65rem;">Inactive</span>
                            @endif
                        </div>
                    </div>

                    <!-- Outlet Meta Details -->
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 0.85rem; font-size: 0.8125rem; color: #334155; margin-bottom: 1rem; display: flex; flex-direction: column; gap: 0.4rem;">
                        <div style="display: flex; align-items: flex-start; gap: 0.4rem;">
                            <span style="color: #64748b; min-width: 65px; font-weight: 600; flex-shrink: 0;">Address:</span>
                            <span style="font-weight: 500; color: #1e293b; word-break: break-word;">{{ $outlet->address }}</span>
                        </div>
                        @if($outlet->phone)
                            <div style="display: flex; align-items: center; gap: 0.4rem;">
                                <span style="color: #64748b; min-width: 65px; font-weight: 600; flex-shrink: 0;">Phone:</span>
                                <span style="font-weight: 500; color: #0284c7;">{{ $outlet->phone }}</span>
                            </div>
                        @endif
                        @if($outlet->email)
                            <div style="display: flex; align-items: center; gap: 0.4rem; overflow: hidden;">
                                <span style="color: #64748b; min-width: 65px; font-weight: 600; flex-shrink: 0;">Email:</span>
                                <span style="font-weight: 500; color: #475569; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $outlet->email }}">{{ $outlet->email }}</span>
                            </div>
                        @endif
                        @if($outlet->manager_name)
                            <div style="display: flex; align-items: center; gap: 0.4rem;">
                                <span style="color: #64748b; min-width: 65px; font-weight: 600; flex-shrink: 0;">Manager:</span>
                                <span style="font-weight: 600; color: #1e293b;">{{ $outlet->manager_name }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Card Actions -->
                <div style="border-top: 1px solid var(--surface-border); padding-top: 0.85rem; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <div>
                        @if($activeOutletId == $outlet->id)
                            <span style="display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.75rem; font-weight: 700; color: #059669; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 0.25rem 0.55rem; border-radius: 6px;">
                                <span style="width: 6px; height: 6px; border-radius: 50%; background: #10b981;"></span>
                                Active Branch
                            </span>
                        @else
                            <form method="POST" action="{{ route('shop.outlets.switch', $outlet) }}" style="display: inline; margin: 0;">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-secondary" style="font-size: 0.75rem; padding: 0.25rem 0.6rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 2v6h-6"></path><path d="M3 12a9 9 0 0 1 15-6.7L21 8"></path><path d="M3 22v-6h6"></path><path d="M21 12a9 9 0 0 1-15 6.7L3 16"></path></svg>
                                    Switch Branch
                                </button>
                            </form>
                        @endif
                    </div>

                    <div style="display: flex; align-items: center; gap: 0.35rem;">
                        <!-- Edit Button -->
                        <button type="button" 
                                class="btn btn-sm btn-secondary" 
                                onclick='openEditOutletModal(@json($outlet))' 
                                title="Edit outlet details" 
                                style="padding: 0.3rem 0.6rem; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                            Edit
                        </button>

                        @if(! $outlet->is_main)
                            <!-- Set as Main Button -->
                            <form method="POST" action="{{ route('shop.outlets.toggle-main', $outlet) }}" style="display: inline; margin: 0;">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-secondary" title="Set as primary Main Branch" style="padding: 0.3rem 0.55rem; font-size: 0.75rem;">
                                    ★ Make Main
                                </button>
                            </form>

                            <!-- Delete Button -->
                            <form method="POST" action="{{ route('shop.outlets.destroy', $outlet) }}" onsubmit="return confirm('Are you sure you want to delete outlet \'{{ addslashes($outlet->name) }}\' ({{ $outlet->code }})?');" style="display: inline; margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Delete outlet" style="padding: 0.3rem 0.55rem; font-size: 0.75rem;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: 3rem;">
                No branch outlets found. Click "+ Add New Outlet" to register your first branch.
            </div>
        @endforelse
    </div>
</div>

<!-- Modal: Add Outlet -->
<div class="modal-backdrop" id="addOutletModal">
    <div class="modal-box" style="max-width: 580px;">
        <div class="modal-header">
            <h3>Add New Branch / Outlet</h3>
            <button type="button" class="modal-close" onclick="closeModal('addOutletModal')">&times;</button>
        </div>
        <form method="POST" action="{{ route('shop.outlets.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group" style="flex: 2;">
                        <label>Outlet / Branch Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Kandy City Showroom" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Branch Code</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. KND-01" style="font-family: monospace; text-transform: uppercase;">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Contact Phone</label>
                        <input type="text" name="phone" class="form-control" placeholder="e.g. 081 223 4567">
                    </div>
                    <div class="form-group">
                        <label>Contact Email (Optional)</label>
                        <input type="email" name="email" class="form-control" placeholder="e.g. kandy@store.com">
                    </div>
                </div>

                <div class="form-group">
                    <label>Address *</label>
                    <input type="text" name="address" class="form-control" placeholder="e.g. Level 2, KCC, Dalada Veediya" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>City / District</label>
                        <input type="text" name="city" class="form-control" placeholder="e.g. Kandy">
                    </div>
                    <div class="form-group">
                        <label>Branch Manager / Person in Charge</label>
                        <input type="text" name="manager_name" class="form-control" placeholder="e.g. Nuwan Perera">
                    </div>
                </div>

                <div class="form-row" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 1rem; margin-top: 0.5rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; margin-bottom: 0; font-weight: 600;">
                            <input type="checkbox" name="is_main" value="1">
                            <span>Set as primary Main Branch</span>
                        </label>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; margin-bottom: 0; font-weight: 600;">
                            <input type="checkbox" name="is_active" value="1" checked>
                            <span>Branch is active &amp; open</span>
                        </label>
                    </div>
                </div>
            </div>
            <div style="padding: 1.25rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addOutletModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Outlet</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Outlet -->
<div class="modal-backdrop" id="editOutletModal">
    <div class="modal-box" style="max-width: 580px;">
        <div class="modal-header">
            <h3>Edit Outlet / Branch</h3>
            <button type="button" class="modal-close" onclick="closeModal('editOutletModal')">&times;</button>
        </div>
        <form id="editOutletForm" method="POST" action="">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group" style="flex: 2;">
                        <label>Outlet / Branch Name *</label>
                        <input type="text" name="name" id="edit_outlet_name" class="form-control" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Branch Code *</label>
                        <input type="text" name="code" id="edit_outlet_code" class="form-control" required style="font-family: monospace; text-transform: uppercase;">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Contact Phone</label>
                        <input type="text" name="phone" id="edit_outlet_phone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Contact Email (Optional)</label>
                        <input type="email" name="email" id="edit_outlet_email" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label>Address *</label>
                    <input type="text" name="address" id="edit_outlet_address" class="form-control" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>City / District</label>
                        <input type="text" name="city" id="edit_outlet_city" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Branch Manager / Person in Charge</label>
                        <input type="text" name="manager_name" id="edit_outlet_manager_name" class="form-control">
                    </div>
                </div>

                <div class="form-row" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 1rem; margin-top: 0.5rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; margin-bottom: 0; font-weight: 600;">
                            <input type="checkbox" name="is_main" id="edit_outlet_is_main" value="1">
                            <span>Primary Main Branch</span>
                        </label>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; margin-bottom: 0; font-weight: 600;">
                            <input type="checkbox" name="is_active" id="edit_outlet_is_active" value="1">
                            <span>Branch is active &amp; open</span>
                        </label>
                    </div>
                </div>
            </div>
            <div style="padding: 1.25rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editOutletModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openEditOutletModal(outlet) {
        const form = document.getElementById('editOutletForm');
        form.action = `/shop/outlets/${outlet.id}`;

        document.getElementById('edit_outlet_name').value = outlet.name || '';
        document.getElementById('edit_outlet_code').value = outlet.code || '';
        document.getElementById('edit_outlet_phone').value = outlet.phone || '';
        document.getElementById('edit_outlet_email').value = outlet.email || '';
        document.getElementById('edit_outlet_address').value = outlet.address || '';
        document.getElementById('edit_outlet_city').value = outlet.city || '';
        document.getElementById('edit_outlet_manager_name').value = outlet.manager_name || '';
        document.getElementById('edit_outlet_is_main').checked = !!outlet.is_main;
        document.getElementById('edit_outlet_is_active').checked = !!outlet.is_active;

        openModal('editOutletModal');
    }
</script>
@endpush
@endsection

