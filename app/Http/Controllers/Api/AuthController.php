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

    public function showLogin(Request $request): View|RedirectResponse
    {
        return $request->user() ? redirect($this->destination($request->user())) : view('auth.login', [
            'applicationName' => config('app.name') === 'Laravel' ? 'Phone Shop POS SaaS' : config('app.name'),
            'loginPreviewUrl' => $this->loginPreviewEnabled() ? route('login.preview', [], false) : '',
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

    public function browserLogin(LoginRequest $request): JsonResponse
    {
        if (! Auth::guard('web')->attempt($request->safe()->only(['email', 'password']), $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => ['The provided credentials are incorrect.']]);
        }

        $request->session()->regenerate();
        $user = $request->user('web');

        return response()->json(['user' => $this->profile($user), 'redirect' => $this->destination($user)]);
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

    public function logout(Request $request): Response
    {
        $token = $request->user()->currentAccessToken();
        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        } else {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->noContent();
    }

    private function destination(User $user): string
    {
        if ($user->shop_id !== null) {
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
