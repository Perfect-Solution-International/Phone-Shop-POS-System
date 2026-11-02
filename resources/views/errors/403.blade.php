@extends('layouts.auth')

@section('title', 'Action Unauthorized — 403')
@section('heading', 'Verification Request Failed')
@section('subheading', 'We could not verify your email address with this link.')

@section('content')
<div style="text-align: center; margin-bottom: 2rem;">
    <div style="display: inline-flex; align-items: center; justify-content: center; width: 56px; height: 56px; background-color: #fef2f2; border: 1.5px solid #fecaca; border-radius: 50%; color: #ef4444; margin-bottom: 1rem;">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
    </div>

    @php
        $isExpired = request()->has('expires') && (int) request()->query('expires') < now()->getTimestamp();
    @endphp

    <h2 style="font-size: 1.125rem; font-weight: 700; color: #0f172a; margin-bottom: 0.5rem;">
        {{ $isExpired ? 'Verification Link Expired' : 'Invalid or Unauthorized Link' }}
    </h2>

    <p style="font-size: 0.875rem; color: #64748b; line-height: 1.55; max-width: 400px; margin: 0 auto;">
        @if ($isExpired)
            This verification link has expired for your workspace security. Verification links are time-limited to 60 minutes. Please request a new verification email.
        @elseif (!empty($exception) && $exception->getMessage())
            {{ $exception->getMessage() }}
        @else
            This verification link is invalid, has been altered, or belongs to a different shop owner account.
        @endif
    </p>
</div>

<div style="display: flex; flex-direction: column; gap: 0.75rem;">
    @auth
        <a href="{{ route('verification.notice') }}" class="btn-submit" style="text-decoration: none; text-align: center; margin-top: 0;">
            <span>Go to Email Verification</span>
        </a>
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn-submit" style="background: #f8fafc; color: #334155; border: 1.5px solid #cbd5e1; box-shadow: none; margin-top: 0;">
                <span>Resend Fresh Verification Link</span>
            </button>
        </form>
    @else
        <a href="{{ route('login') }}" class="btn-submit" style="text-decoration: none; text-align: center; margin-top: 0;">
            <span>Login to Your Workspace</span>
        </a>
    @endauth
</div>

<div class="auth-footer-links">
    Need assistance? <a href="{{ route('login') }}" class="auth-link">Return to workspace login</a>
</div>
@endsection

