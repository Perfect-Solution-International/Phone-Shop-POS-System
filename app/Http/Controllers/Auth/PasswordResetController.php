<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password', [
            'applicationName' => config('app.name') === 'Laravel' ? 'SaaS Phone Shop POS System' : config('app.name'),
        ]);
    }

    public function store(ForgotPasswordRequest $request): JsonResponse|RedirectResponse
    {
        Password::sendResetLink($request->only('email'));

        // Generic response prevents user enumeration
        $message = 'If an account exists with that email address, we have sent a password reset link.';

        if ($request->wantsJson()) {
            return response()->json(['message' => $message]);
        }

        return back()->with('status', $message);
    }

    public function edit(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
            'applicationName' => config('app.name') === 'Laravel' ? 'SaaS Phone Shop POS System' : config('app.name'),
        ]);
    }

    public function update(ResetPasswordRequest $request): JsonResponse|RedirectResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));

                // Invalidate any active database sessions for this user across devices
                DB::table('sessions')->where('user_id', $user->id)->delete();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            $message = 'Your password has been reset successfully. Please sign in with your new credentials.';

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->wantsJson()) {
                return response()->json(['message' => $message, 'redirect' => route('login')]);
            }

            return redirect()->route('login')->with('status', $message);
        }

        $error = __($status);
        if ($request->wantsJson()) {
            throw ValidationException::withMessages(['email' => [$error]]);
        }

        return back()->withInput($request->only('email'))->withErrors(['email' => $error]);
    }
}

