@extends('layouts.shop')

@section('title', 'My Profile & Security')
@section('page-title', 'My Profile & Account Security')

@section('content')
<div style="max-width: 900px; margin: 0 auto; display: flex; flex-direction: column; gap: 1.5rem;">

    @if(session('success'))
        <div class="alert alert-success" style="display: flex; align-items: center; gap: 0.65rem; border-radius: 10px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 0.85rem 1.25rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span style="font-weight: 600; font-size: 0.875rem;">{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" style="border-radius: 10px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.85rem 1.25rem;">
            <div style="font-weight: 700; margin-bottom: 0.35rem; font-size: 0.875rem;">Please review the following errors:</div>
            <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.825rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Profile Overview Card -->
    <div class="card" style="overflow: hidden; border-radius: 12px; border: 1px solid var(--surface-border); box-shadow: 0 4px 15px -3px rgba(0, 0, 0, 0.05);">
        <div style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0c4a6e 100%); padding: 2rem 1.75rem; color: #ffffff; position: relative;">
            <div style="display: flex; align-items: center; gap: 1.25rem; flex-wrap: wrap;">
                <div style="width: 68px; height: 68px; border-radius: 16px; background: linear-gradient(135deg, #06b6d4 0%, #4f46e5 100%); display: flex; align-items: center; justify-content: center; font-size: 1.85rem; font-weight: 800; color: #ffffff; border: 3px solid rgba(255, 255, 255, 0.25); box-shadow: 0 10px 25px -5px rgba(6, 182, 212, 0.5); flex-shrink: 0;">
                    {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                </div>
                <div style="flex: 1; min-width: 220px;">
                    <div style="display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap; margin-bottom: 0.35rem;">
                        <h2 style="font-size: 1.35rem; font-weight: 800; margin: 0; color: #ffffff; letter-spacing: -0.01em;">{{ $user->name }}</h2>
                        <span class="badge" style="background: rgba(6, 182, 212, 0.25); color: #38bdf8; border: 1px solid rgba(6, 182, 212, 0.45); font-size: 0.75rem; text-transform: uppercase; font-weight: 750; padding: 0.2rem 0.65rem;">
                            {{ $user->roles->first()->name ?? 'Staff Member' }}
                        </span>
                        <span class="badge" style="background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35); font-size: 0.72rem; font-weight: 650; padding: 0.2rem 0.55rem;">
                            ● Active
                        </span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 1.25rem; flex-wrap: wrap; font-size: 0.8125rem; color: #94a3b8;">
                        <span style="display: inline-flex; align-items: center; gap: 0.35rem;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            {{ $user->email }}
                        </span>
                        @if($user->phone)
                        <span style="display: inline-flex; align-items: center; gap: 0.35rem;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                            {{ $user->phone }}
                        </span>
                        @endif
                        <span style="display: inline-flex; align-items: center; gap: 0.35rem;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                            Store: <strong>{{ $user->shop->name ?? 'Phone Shop' }}</strong>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div style="background: #f8fafc; padding: 0.85rem 1.75rem; border-top: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;">
            <div style="font-size: 0.8rem; color: #64748b;">
                Branch: <strong>{{ session('active_outlet_name', $user->shop->mainOutlet->name ?? 'Main Branch') }}</strong> &bull; Member Since: <strong>{{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}</strong>
            </div>
            @if(auth()->user()->hasRole('Super Administrator') || auth()->user()->hasRole('Administrator') || auth()->user()->can('shop.manage'))
            <a href="{{ route('shop.settings') }}" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.78rem; font-weight: 650;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                <span>Store Profile &amp; Settings &rarr;</span>
            </a>
            @endif
        </div>
    </div>

    <!-- Personal Profile Edit Form -->
    <div class="card" style="border-radius: 12px; border: 1px solid var(--surface-border);">
        <div class="card-header" style="border-bottom: 1px solid var(--surface-border); padding: 1.15rem 1.5rem; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <strong style="font-size: 1rem; color: var(--text-heading); display: block;">Personal Information</strong>
                <span style="font-size: 0.8rem; color: var(--text-muted);">Update your personal name and contact details across the shop.</span>
            </div>
            <span class="badge badge-info" style="font-size: 0.72rem;">User Account</span>
        </div>
        <form action="{{ route('shop.profile.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body" style="padding: 1.5rem;">
                <div class="form-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="font-size: 0.8125rem; font-weight: 650; color: #334155; margin-bottom: 0.4rem; display: block;">Full Name *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required placeholder="e.g. Kasun Chamara">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="font-size: 0.8125rem; font-weight: 650; color: #334155; margin-bottom: 0.4rem; display: block;">Email Address *</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required placeholder="e.g. staff@shop.lk">
                    </div>
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="font-size: 0.8125rem; font-weight: 650; color: #334155; margin-bottom: 0.4rem; display: block;">Mobile Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="e.g. 0771234567">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="font-size: 0.8125rem; font-weight: 650; color: #334155; margin-bottom: 0.4rem; display: block;">Assigned System Role</label>
                        <input type="text" class="form-control" value="{{ $user->roles->first()->name ?? 'Staff' }}" readonly style="background: #f1f5f9; cursor: not-allowed; color: #475569;">
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; padding-top: 1rem; border-top: 1px solid #f1f5f9;">
                    <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.55rem 1.35rem; font-weight: 700;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        <span>Save Profile Changes</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Security & Password Change Form -->
    <div class="card" style="border-radius: 12px; border: 1px solid var(--surface-border);">
        <div class="card-header" style="border-bottom: 1px solid var(--surface-border); padding: 1.15rem 1.5rem; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <strong style="font-size: 1rem; color: var(--text-heading); display: block;">Account Security &amp; Password</strong>
                <span style="font-size: 0.8rem; color: var(--text-muted);">Ensure your account uses a secure password of at least 8 characters.</span>
            </div>
            <span class="badge" style="background: #eef2ff; color: #4f46e5; border: 1px solid #c7d2fe; font-size: 0.72rem;">Credentials</span>
        </div>
        <form action="{{ route('shop.profile.password') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body" style="padding: 1.5rem;">
                <div class="form-group" style="margin-bottom: 1rem; max-width: 420px;">
                    <label style="font-size: 0.8125rem; font-weight: 650; color: #334155; margin-bottom: 0.4rem; display: block;">Current Password *</label>
                    <input type="password" name="current_password" class="form-control" required placeholder="Enter your current password">
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="font-size: 0.8125rem; font-weight: 650; color: #334155; margin-bottom: 0.4rem; display: block;">New Password * (Min 8 Characters)</label>
                        <input type="password" name="password" class="form-control" required placeholder="Enter new strong password">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="font-size: 0.8125rem; font-weight: 650; color: #334155; margin-bottom: 0.4rem; display: block;">Confirm New Password *</label>
                        <input type="password" name="password_confirmation" class="form-control" required placeholder="Re-type new password">
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; padding-top: 1rem; border-top: 1px solid #f1f5f9;">
                    <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.55rem 1.35rem; font-weight: 700; background: #4f46e5; border-color: #4f46e5;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        <span>Update Password</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

</div>
@endsection

