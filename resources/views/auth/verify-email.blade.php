@extends('layouts.auth')

@section('title', 'Verify Shop Owner Email')
@section('heading', 'Verify Your Email')
@section('subheading', 'Confirm your email address to unlock your isolated shop workspace.')

@section('content')
<div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem; margin-bottom: 1.5rem; text-align: center;">
    <div style="display: inline-flex; align-items: center; justify-content: center; width: 48px; height: 48px; background: #e0e7ff; color: #4f46e5; border-radius: 50%; margin-bottom: 0.75rem;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
            <polyline points="22,6 12,13 2,6"></polyline>
        </svg>
    </div>

    <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #4f46e5; margin-bottom: 0.25rem;">
        Workspace: {{ auth()->user()->shop?->name ?? 'Phone Shop Workspace' }}
    </div>

    <div style="font-size: 1rem; font-weight: 700; color: #0f172a; word-break: break-all;">
        {{ auth()->user()->email ?? 'your email' }}
    </div>

    <div style="display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.75rem; font-weight: 600; color: #d97706; background-color: #fffbeb; border: 1px solid #fef3c7; border-radius: 9999px; padding: 0.2rem 0.65rem; margin-top: 0.65rem;">
        <span style="width: 6px; height: 6px; border-radius: 50%; background-color: #f59e0b; display: inline-block;"></span>
        Verification Pending
    </div>
</div>

<div style="font-size: 0.875rem; color: #475569; line-height: 1.6; margin-bottom: 1.5rem;">
    <p>Before you can access the POS counter, repair ticketing, or manage employee payroll, we need to verify your email address.</p>
    <p style="margin-top: 0.5rem;">Please click the secure link sent to your inbox. The link expires in <strong>60 minutes</strong>.</p>
</div>

@if (session('status') === 'verification-link-sent')
    <div class="alert alert-success" role="status" style="display: flex; align-items: center; gap: 0.5rem;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
            <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
        <span>A fresh verification link has been sent to your email address! Please check your inbox and spam folder.</span>
    </div>
@endif

<form id="resendForm" method="POST" action="{{ route('verification.send') }}">
    @csrf
    <button type="submit" id="resendBtn" class="btn-submit" style="margin-top: 0;">
        <span id="resendBtnText">Resend Verification Email</span>
    </button>
</form>

<div class="auth-footer-links" style="justify-content: space-between;">
    <span style="font-size: 0.8125rem;">Wrong email address?</span>
    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
        @csrf
        <button type="submit" class="auth-link" style="background: none; border: none; cursor: pointer; padding: 0.25rem 0; font-size: 0.8125rem;">Sign out</button>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const resendForm = document.getElementById('resendForm');
    const resendBtn = document.getElementById('resendBtn');
    const resendBtnText = document.getElementById('resendBtnText');

    if (resendForm && resendBtn) {
        resendForm.addEventListener('submit', function() {
            resendBtn.disabled = true;
            resendBtnText.textContent = 'Sending Verification Link...';
            resendBtn.style.opacity = '0.75';
            resendBtn.style.cursor = 'wait';
        });
    }
</script>
@endpush
