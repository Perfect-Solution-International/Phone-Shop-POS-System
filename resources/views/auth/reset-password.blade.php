@extends('layouts.auth')

@section('title', 'Set New Password')
@section('heading', 'Set New Password')
@section('subheading', 'Create a strong, secure password for your workspace account.')

@push('styles')
<style>
    .strength-rules-list {
        list-style: none;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.35rem 0.75rem;
        font-size: 0.6875rem;
        color: #64748b;
        margin: 0;
        padding: 0;
    }
    @media (max-width: 480px) {
        .strength-rules-list {
            grid-template-columns: 1fr;
            gap: 0.3rem;
        }
    }
</style>
@endpush

@section('content')
<form id="resetPasswordForm" method="POST" action="{{ route('password.update') }}" novalidate>
    @csrf

    <input type="hidden" name="token" value="{{ $token }}">

    <div class="form-group">
        <label for="email" class="form-label">
            <span>Email Address <span style="color: #ef4444;">*</span></span>
        </label>
        <div class="input-wrapper">
            <input
                id="email"
                name="email"
                type="email"
                class="form-input @error('email') is-invalid @enderror"
                value="{{ old('email', $email) }}"
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

    <div class="form-group">
        <label for="password" class="form-label">
            <span>New Password <span style="color: #ef4444;">*</span></span>
        </label>
        <div class="input-wrapper">
            <input
                id="password"
                name="password"
                type="password"
                class="form-input @error('password') is-invalid @enderror"
                required
                autocomplete="new-password"
                placeholder="••••••••••••"
                aria-required="true"
                aria-describedby="password_strength_box @error('password') password_error @enderror"
                @error('password') aria-invalid="true" @enderror
            >
            <button type="button" class="input-toggle-btn" onclick="togglePasswordVisibility('password', this)" aria-label="Toggle password visibility">Show</button>
        </div>

        <!-- Live Password Strength Meter -->
        <div id="password_strength_box" class="password-strength-box" style="margin-top: 0.625rem; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 0.875rem;" aria-live="polite">
            <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.4rem;">
                <span>Password Strength:</span>
                <span id="strengthText" style="font-weight: 700; text-transform: uppercase; font-size: 0.6875rem; letter-spacing: 0.05em; color: #64748b;">Enter Password</span>
            </div>
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.35rem; height: 4px; margin-bottom: 0.625rem;">
                <div id="bar1" style="background-color: #e2e8f0; border-radius: 9999px; transition: background-color 0.25s;"></div>
                <div id="bar2" style="background-color: #e2e8f0; border-radius: 9999px; transition: background-color 0.25s;"></div>
                <div id="bar3" style="background-color: #e2e8f0; border-radius: 9999px; transition: background-color 0.25s;"></div>
                <div id="bar4" style="background-color: #e2e8f0; border-radius: 9999px; transition: background-color 0.25s;"></div>
            </div>
            <ul class="strength-rules-list">
                <li id="rule-len" style="display: flex; align-items: center; gap: 0.35rem;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span>8+ characters</span>
                </li>
                <li id="rule-upper" style="display: flex; align-items: center; gap: 0.35rem;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span>Uppercase (A-Z)</span>
                </li>
                <li id="rule-lower" style="display: flex; align-items: center; gap: 0.35rem;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span>Lowercase (a-z)</span>
                </li>
                <li id="rule-num" style="display: flex; align-items: center; gap: 0.35rem;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span>Number (0-9)</span>
                </li>
            </ul>
        </div>

        @error('password')
            <p id="password_error" class="field-error" role="alert">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span>{{ $message }}</span>
            </p>
        @enderror
    </div>

    <div class="form-group">
        <label for="password_confirmation" class="form-label">
            <span>Confirm New Password <span style="color: #ef4444;">*</span></span>
        </label>
        <div class="input-wrapper">
            <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                class="form-input"
                required
                autocomplete="new-password"
                placeholder="••••••••••••"
                aria-required="true"
                aria-describedby="matchIndicator"
            >
            <button type="button" class="input-toggle-btn" onclick="togglePasswordVisibility('password_confirmation', this)" aria-label="Toggle password confirmation visibility">Show</button>
        </div>
        <div id="matchIndicator" style="display: none; font-size: 0.75rem; margin-top: 0.375rem; align-items: center; gap: 0.35rem; font-weight: 500;"></div>
    </div>

    <button type="submit" id="submitBtn" class="btn-submit">
        <span id="submitBtnText">Reset Password</span>
    </button>
</form>

<div class="auth-footer-links">
    Remembered your password? <a href="{{ route('login') }}" class="auth-link">Login here</a>
</div>
@endsection

@push('scripts')
<script>
    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        if (input.type === 'password') {
            input.type = 'text';
            btn.textContent = 'Hide';
        } else {
            input.type = 'password';
            btn.textContent = 'Show';
        }
    }

    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirmation');
    const strengthText = document.getElementById('strengthText');
    const bars = [document.getElementById('bar1'), document.getElementById('bar2'), document.getElementById('bar3'), document.getElementById('bar4')];
    const ruleLen = document.getElementById('rule-len');
    const ruleUpper = document.getElementById('rule-upper');
    const ruleLower = document.getElementById('rule-lower');
    const ruleNum = document.getElementById('rule-num');
    const matchIndicator = document.getElementById('matchIndicator');

    function updateStrength() {
        const val = passwordInput.value;
        const hasLen = val.length >= 8;
        const hasUpper = /[A-Z]/.test(val);
        const hasLower = /[a-z]/.test(val);
        const hasNum = /[0-9]/.test(val);

        setRule(ruleLen, hasLen);
        setRule(ruleUpper, hasUpper);
        setRule(ruleLower, hasLower);
        setRule(ruleNum, hasNum);

        bars.forEach(b => { b.style.backgroundColor = '#e2e8f0'; });

        if (!val) {
            strengthText.textContent = 'Enter Password';
            strengthText.style.color = '#64748b';
            return;
        }

        let score = 0;
        if (hasLen) score++;
        if (hasUpper) score++;
        if (hasLower) score++;
        if (hasNum) score++;

        if (score <= 1) {
            strengthText.textContent = 'Weak';
            strengthText.style.color = '#ef4444';
            bars[0].style.backgroundColor = '#ef4444';
        } else if (score === 2) {
            strengthText.textContent = 'Fair';
            strengthText.style.color = '#f59e0b';
            bars[0].style.backgroundColor = '#f59e0b';
            bars[1].style.backgroundColor = '#f59e0b';
        } else if (score === 3) {
            strengthText.textContent = 'Good';
            strengthText.style.color = '#3b82f6';
            bars[0].style.backgroundColor = '#3b82f6';
            bars[1].style.backgroundColor = '#3b82f6';
            bars[2].style.backgroundColor = '#3b82f6';
        } else {
            strengthText.textContent = 'Strong';
            strengthText.style.color = '#10b981';
            bars.forEach(b => { b.style.backgroundColor = '#10b981'; });
        }
    }

    function setRule(el, valid) {
        if (valid) {
            el.style.color = '#047857';
            el.style.fontWeight = '600';
            el.querySelector('svg').style.color = '#10b981';
        } else {
            el.style.color = '#64748b';
            el.style.fontWeight = 'normal';
            el.querySelector('svg').style.color = '#94a3b8';
        }
    }

    function checkMatch() {
        const pass = passwordInput.value;
        const conf = confirmInput.value;

        if (!conf) {
            matchIndicator.style.display = 'none';
            return;
        }

        matchIndicator.style.display = 'flex';
        if (pass === conf) {
            matchIndicator.style.color = '#10b981';
            matchIndicator.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg> Passwords match';
        } else {
            matchIndicator.style.color = '#ef4444';
            matchIndicator.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg> Passwords do not match';
        }
    }

    passwordInput.addEventListener('input', () => {
        updateStrength();
        checkMatch();
    });

    confirmInput.addEventListener('input', checkMatch);

    const form = document.getElementById('resetPasswordForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitBtnText = document.getElementById('submitBtnText');

    if (form && submitBtn) {
        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtnText.textContent = 'Updating Password...';
            submitBtn.style.opacity = '0.75';
            submitBtn.style.cursor = 'wait';
        });
    }
</script>
@endpush
