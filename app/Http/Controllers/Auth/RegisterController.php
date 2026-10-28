<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\Tenancy\ShopOnboardingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function showRegistrationForm(Request $request): View
    {
        $currentUser = $request->user();
        $currentDestination = null;
        if ($currentUser) {
            $currentDestination = app(\App\Http\Controllers\Api\AuthController::class)->destination($currentUser);
        }

        return view('auth.register', [
            'applicationName' => config('app.name') === 'Laravel' ? 'SaaS Phone Shop POS System' : config('app.name'),
            'currentUser' => $currentUser,
            'currentDestination' => $currentDestination,
        ]);
    }

    public function register(RegisterRequest $request, ShopOnboardingService $onboardingService): JsonResponse|RedirectResponse
    {
        $result = $onboardingService->onboard($request->validated());
        /** @var User $user */
        $user = $result['user'];

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        $destination = $user->hasVerifiedEmail() ? route('app') : route('verification.notice');

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Workspace and owner account created successfully.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'shop_id' => $user->shop_id,
                    'shop_name' => $user->shop?->name,
                ],
                'redirect' => $destination,
            ], 201);
        }

        return redirect($destination)->with('status', 'Your shop workspace has been created successfully!');
    }
}

