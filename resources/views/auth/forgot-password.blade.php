@extends('layouts.auth')

@section('title', 'Forgot Password')
@section('heading', 'Reset Password')
@section('subheading', 'Enter your account email to receive a secure password reset link.')

@section('content')
<div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: flex-start; gap: 0.875rem;">
    <div style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; background-color: #e0e7ff; color: #4f46e5; border-radius: 8px; flex-shrink: 0; margin-top: 0.125rem;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
        </svg>
    </div>
    <div style="font-size: 0.875rem; color: #475569; line-height: 1.5;">
        Forgot your workspace credentials? Enter your email address and we will send a time-limited reset link to restore your account access.
    </div>
</div>

<form id="forgotPasswordForm" method="POST" action="{{ route('password.email') }}" novalidate>
    @csrf

    <div class="form-group">
        <label for="email" class="form-label">
            <span>Owner Email Address <span style="color: #ef4444;">*</span></span>
        </label>
        <div class="input-wrapper">
            <input
                id="email"
                name="email"
                type="email"
                class="form-input @error('email') is-invalid @enderror"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
                inputmode="email"
                placeholder="owner@yourshop.com"
                aria-required="true"
                @error('email') aria-describedby="email_error" aria-invalid="true" @enderror
            >
        </div>
        @error('email')
            <p id="email_error" class="field-error" role="alert">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span>{{ $message }}</span>
            </p>
        @enderror
    </div>

    <button type="submit" id="submitBtn" class="btn-submit">
        <span id="submitBtnText">Send Password Reset Link</span>
    </button>
</form>

<div class="auth-footer-links">
    Remembered your password? <a href="{{ route('login') }}" class="auth-link">Back to login</a>
</div>
@endsection

@push('scripts')
<script>
    const form = document.getElementById('forgotPasswordForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitBtnText = document.getElementById('submitBtnText');

    if (form && submitBtn) {
        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtnText.textContent = 'Sending Reset Link...';
            submitBtn.style.opacity = '0.75';
            submitBtn.style.cursor = 'wait';
        });
    }
</script>
@endpush

