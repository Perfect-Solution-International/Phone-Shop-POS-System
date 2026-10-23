<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        if ($request->user()?->hasRole('Super Administrator') || $request->user()?->can('superadmin.view')) {
            return redirect()->route('superadmin.dashboard');
        }

        if ($request->user()?->hasVerifiedEmail()) {
            return redirect()->route('app');
        }

        return view('auth.verify-email', [
            'applicationName' => config('app.name') === 'Laravel' ? 'SaaS Phone Shop POS System' : config('app.name'),
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! hash_equals((string) $request->route('id'), (string) $user->getKey())) {
            throw new AuthorizationException('This verification link belongs to another user account.');
        }

        if (! hash_equals(sha1($user->getEmailForVerification()), (string) $request->route('hash'))) {
            throw new AuthorizationException('The verification link hash is invalid or the email has changed.');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('app')
                ->with('verified', true)
                ->with('status', 'Your email address is already verified.');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->route('app')
            ->with('verified', true)
            ->with('status', 'Your email address has been verified successfully! Welcome to your shop workspace.');
    }

    public function resend(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $request->wantsJson()
                ? response()->json(['message' => 'Email already verified.'])
                : redirect()->route('app');
        }

        $request->user()->sendEmailVerificationNotification();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Verification link sent.']);
        }

        return back()->with('status', 'verification-link-sent');
    }
}

