@extends('layouts.super-admin')

@section('title', 'Registered Shops')
@section('page_title', 'Registered Shop Workspaces')

@section('content')
    <div class="data-card">
        <div class="data-card-header" style="flex-wrap: wrap; gap: 1rem;">
            <h2 class="data-card-title">All Shops ({{ $shops->total() }})</h2>

            <!-- Filter & Search Form -->
            <form method="GET" action="{{ route('superadmin.shops.index') }}" style="display: flex; gap: 0.75rem; align-items: center;">
                <input
                    type="text"
                    name="search"
                    placeholder="Search shop or owner..."
                    value="{{ request('search') }}"
                    style="padding: 0.45rem 0.85rem; border: 1.5px solid var(--surface-border); border-radius: 8px; font-size: 0.875rem;"
                >
                <select name="status" style="padding: 0.45rem 0.85rem; border: 1.5px solid var(--surface-border); border-radius: 8px; font-size: 0.875rem;">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Paid</option>
                    <option value="trial" {{ request('status') === 'trial' ? 'selected' : '' }}>Free Trial</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
                <button type="submit" class="btn-action btn-primary">Filter</button>
                @if (request()->hasAny(['search', 'status']))
                    <a href="{{ route('superadmin.shops.index') }}" class="btn-action btn-outline">Clear</a>
                @endif
            </form>
        </div>

        <table class="saas-table">
            <thead>
                <tr>
                    <th>Shop / Business</th>
                    <th>Primary Owner</th>
                    <th>Plan</th>
                    <th>Subscription Status</th>
                    <th>Current Period Ends</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($shops as $shop)
                    <tr>
                        <td>
                            <strong style="font-size: 0.9375rem;">{{ $shop->name }}</strong>
                            <div style="font-size: 0.75rem; color: #64748b;">ID #{{ $shop->id }} • Joined {{ $shop->created_at->format('M d, Y') }}</div>
                        </td>
                        <td>
                            <div>{{ $shop->users->first()->name ?? 'N/A' }}</div>
                            <div style="font-size: 0.75rem; color: #64748b;">{{ $shop->users->first()->email ?? '' }}</div>
                        </td>
                        <td>
                            <span style="font-weight: 600;">{{ $shop->subscription->plan->name ?? 'Standard Trial' }}</span>
                        </td>
                        <td>
                            <span class="status-pill status-{{ $shop->subscription->status ?? 'trial' }}">
                                {{ $shop->subscription->status ?? 'Trial' }}
                            </span>
                        </td>
                        <td>
                            @if ($shop->subscription?->current_period_ends_at)
                                {{ $shop->subscription->current_period_ends_at->format('Y-m-d') }}
                                <div style="font-size: 0.75rem; color: #64748b;">{{ $shop->subscription->current_period_ends_at->diffForHumans() }}</div>
                            @else
                                <span style="color: #94a3b8;">N/A</span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.4rem;">
                                <a href="{{ route('superadmin.shops.show', $shop->id) }}" class="btn-action btn-outline">Details</a>
                                <form method="POST" action="{{ route('superadmin.shops.toggle-suspension', $shop->id) }}" style="display: inline;" onsubmit="return confirm('Change access state for this shop?');">
                                    @csrf
                                    @if (($shop->subscription->status ?? '') === 'suspended')
                                        <button type="submit" class="btn-action btn-success" style="font-size: 0.775rem;">Activate</button>
                                    @else
                                        <button type="submit" class="btn-action btn-danger" style="font-size: 0.775rem;">Suspend</button>
                                    @endif
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #94a3b8; padding: 2.5rem;">No shops match the search criteria.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="padding: 1.25rem 1.5rem; border-top: 1px solid var(--surface-border);">
            {{ $shops->links() }}
        </div>
    </div>
@endsection

