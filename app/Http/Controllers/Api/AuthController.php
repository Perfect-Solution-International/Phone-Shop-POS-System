<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function entry(Request $request): RedirectResponse
    {
        return redirect($request->user() ? $this->destination($request->user()) : route('login'));
    }

    public function showLogin(Request $request): View
    {
        return view('auth.login', [
            'applicationName' => config('app.name') === 'Laravel' ? 'SaaS Phone Shop POS System' : config('app.name'),
            'loginPreviewUrl' => $this->loginPreviewEnabled() ? route('login.preview', [], false) : '',
            'currentUser' => $request->user(),
            'currentDestination' => $request->user() ? $this->destination($request->user()) : null,
            'initialMode' => ($request->query('type') === 'staff' || $request->routeIs('staff.login')) ? 'staff' : 'owner',
        ]);
    }

    public function preview(): View
    {
        abort_unless($this->loginPreviewEnabled(), 404);

        return view('layouts.master', ['loginPreview' => true]);
    }

    private function loginPreviewEnabled(): bool
    {
        return app()->environment('local') && config('app.login_preview');
    }

    public function browserLogin(LoginRequest $request): JsonResponse|RedirectResponse
    {
        if (! Auth::guard('web')->attempt($request->safe()->only(['email', 'password']), $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => ['The provided credentials are incorrect.']]);
        }

        $request->session()->regenerate();
        $user = $request->user('web');

        // Automatically bind branch session if user is an assigned branch manager
        if ($user && $user->shop_id !== null) {
            $assignedOutlet = null;
            if ($user->outlet_id) {
                $assignedOutlet = \App\Models\Outlet::withoutGlobalScopes()->find($user->outlet_id);
            }
            if (! $assignedOutlet) {
                $assignedOutlet = \App\Models\Outlet::withoutGlobalScopes()->where('manager_user_id', $user->id)->first();
            }

            if ($assignedOutlet && ($assignedOutlet->approval_status ?? 'approved') === 'approved') {
                session([
                    'active_outlet_id' => $assignedOutlet->id,
                    'active_outlet_name' => $assignedOutlet->name,
                    'active_outlet_code' => $assignedOutlet->code,
                ]);
            }
        }

        if ($request->wantsJson()) {
            return response()->json(['user' => $this->profile($user), 'redirect' => $this->destination($user)]);
        }

        $destination = $this->safeDestination($request, $user);

        return redirect()->to($destination);
    }

    private function safeDestination(Request $request, User $user): string
    {
        $default = $this->destination($user);
        $intended = $request->session()->pull('url.intended');

        if (! $intended || ! is_string($intended) || str_contains($intended, '/email/verify') || str_contains($intended, '/login') || rtrim($intended, '/') === rtrim(url('/'), '/')) {
            return $default;
        }

        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        $intendedHost = parse_url($intended, PHP_URL_HOST);

        // Discard external domains to prevent open redirects
        if ($intendedHost !== null && strtolower($intendedHost) !== strtolower((string) $appHost)) {
            return $default;
        }

        // Enforce strict workspace boundaries based on user role
        if ($user->hasRole('Super Administrator') || $user->can('superadmin.view')) {
            if (! str_contains($intended, '/super-admin')) {
                return $default;
            }
        } elseif ($user->hasRole('Customer') || $user->can('customer.portal')) {
            if (! str_contains($intended, '/portal')) {
                return $default;
            }
        } elseif ($user->shop_id !== null) {
            if (! str_contains($intended, '/shop') && ! str_contains($intended, '/app')) {
                return $default;
            }
        }

        return $intended;
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->validated('email'))->first();
        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            throw ValidationException::withMessages(['email' => ['The provided credentials are incorrect.']]);
        }

        $expires = now()->addMinutes(config('sanctum.expiration'));
        $token = $user->createToken('enterprise-spa', ['*'], $expires);

        return response()->json([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $expires->toIso8601String(),
            'user' => $this->profile($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $this->profile($request->user())]);
    }

    public function logout(Request $request): Response|RedirectResponse
    {
        $token = $request->user()?->currentAccessToken();
        if ($token instanceof PersonalAccessToken) {
            $token->delete();

            return response()->noContent();
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->wantsJson()) {
            return response()->noContent();
        }

        return redirect()->route('login')->with('status', 'You have been successfully signed out.');
    }

    public function destination(User $user): string
    {
        if ($user->hasRole('Super Administrator') || $user->can('superadmin.view')) {
            return route('superadmin.dashboard');
        }

        if ($user->hasRole('Customer') || $user->can('customer.portal')) {
            return route('customer.portal');
        }

        if (config('auth.verification.enabled', false) && ! $user->hasVerifiedEmail()) {
            return route('verification.notice');
        }

        if ($user->shop_id !== null) {
            $sub = $user->shop?->subscription;
            if ($sub && ($sub->isSuspended() || $sub->isExpired() || $sub->isTrialExpired())) {
                return route('shop.subscription.index');
            }

            if ($user->hasRole('Cashier')) {
                return route('shop.pos');
            }

            if ($user->hasRole('Technician')) {
                return route('shop.repairs');
            }

            if ($user->hasRole(['Stock Manager', 'Stock Keeper'])) {
                return route('shop.phones');
            }

            if ($user->hasRole(['Shop Owner', 'Shop Manager'])) {
                return route('shop.dashboard');
            }

            foreach (['hrm.view' => '', 'payroll.view' => 'hrm/payroll', 'settings.manage' => 'settings'] as $permission => $path) {
                if ($user->can($permission)) {
                    return route('app', ['path' => $path]);
                }
            }
        }

        return route('app', ['path' => 'forbidden']);
    }

    /** @return array{id: int, name: string, email: string, shop_id: int|null, shop_name: string|null, permissions: list<string>} */
    private function profile(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'shop_id' => $user->shop_id,
            'shop_name' => $user->shop?->name,
            'permissions' => $user->getAllPermissions()->pluck('name')->values()->all(),
        ];
    }
}
