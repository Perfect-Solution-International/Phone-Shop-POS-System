<?php

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Cookie;

test('browser login creates a session with the existing role permissions and no bearer token', function (string $role): void {
    $this->seed(DatabaseSeeder::class);
    $user = User::factory()->create();
    $user->assignRole($role);

    $response = $this->postJson('/login', ['email' => $user->email, 'password' => 'password', 'remember' => false]);

    $response->assertOk()->assertJsonPath('user.id', $user->id)->assertJsonPath('redirect', route('app'))
        ->assertJsonMissingPath('token')->assertJsonMissingPath('user.password')->assertJsonMissingPath('user.remember_token');
    expect($response->json('user.permissions'))->toContain('hrm.view');
    $this->assertAuthenticatedAs($user, 'web');
    $this->assertDatabaseCount('personal_access_tokens', 0);
})->with(['Administrator', 'HR Manager', 'Viewer']);

test('browser login rejects invalid credentials without authenticating', function (): void {
    $user = User::factory()->create();

    $this->postJson('/login', ['email' => $user->email, 'password' => 'wrong'])
        ->assertUnprocessable()->assertJsonValidationErrors('email')
        ->assertJsonPath('errors.email.0', 'The provided credentials are incorrect.');

    $this->assertGuest('web');
    $this->assertDatabaseCount('personal_access_tokens', 0);
});

test('browser login validates required credentials', function (): void {
    $this->postJson('/login', [])->assertUnprocessable()->assertJsonValidationErrors(['email', 'password']);

    $this->assertGuest('web');
});

test('browser login validates email and remember input', function (): void {
    $this->postJson('/login', ['email' => 'not-an-email', 'password' => 'password', 'remember' => 'yes'])
        ->assertUnprocessable()->assertJsonValidationErrors(['email', 'remember']);

    $this->assertGuest('web');
});

test('remember me issues an encrypted HTTP only recaller cookie', function (): void {
    $user = User::factory()->create();

    $response = $this->postJson('/login', ['email' => $user->email, 'password' => 'password', 'remember' => true]);

    $response->assertOk()->assertCookie(Auth::guard('web')->getRecallerName());
    expect($user->fresh()->remember_token)->not->toBeNull();
    $cookie = collect($response->headers->getCookies())->first(fn (Cookie $cookie): bool => $cookie->getName() === Auth::guard('web')->getRecallerName());
    expect($cookie->isHttpOnly())->toBeTrue();
});

test('signed in users visiting home or login go to an authorized existing page', function (string $permission, string $path): void {
    $this->seed(DatabaseSeeder::class);
    $user = User::factory()->create();
    $user->givePermissionTo($permission);
    $this->actingAs($user, 'web');

    $this->get('/')->assertRedirect(route('app', ['path' => $path]));
    $this->get('/login')->assertRedirect(route('app', ['path' => $path]));
})->with([
    ['hrm.view', ''],
    ['payroll.view', 'hrm/payroll'],
    ['settings.manage', 'settings'],
]);

test('users without a usable authorized dashboard go to the existing restricted page', function (bool $hasShop): void {
    $user = User::factory()->create();
    if (! $hasShop) {
        $this->seed(DatabaseSeeder::class);
        $user->forceFill(['shop_id' => null])->save();
        $user->assignRole('Administrator');
    }

    $this->postJson('/login', ['email' => $user->email, 'password' => 'password'])
        ->assertOk()->assertJsonPath('redirect', route('app', ['path' => 'forbidden']));
})->with([true, false]);

test('a browser session restores through the existing API and survives workspace refresh', function (): void {
    $this->seed(DatabaseSeeder::class);
    $user = User::factory()->create();
    $user->assignRole('Viewer');
    config(['sanctum.stateful' => ['localhost']]);
    $this->get('/login')->assertOk();
    $guestSessionId = session()->getId();
    $this->postJson('/login', ['email' => $user->email, 'password' => 'password'])->assertOk();
    expect(session()->getId())->not->toBe($guestSessionId);
    Auth::forgetGuards();

    $this->get('/app/hrm/attendance')->assertOk()->assertViewIs('layouts.master');
    Auth::forgetGuards();
    $this->withHeader('Origin', 'http://localhost')->getJson('/api/v1/auth/me')
        ->assertOk()->assertJsonPath('user.id', $user->id)->assertJsonPath('user.permissions.0', 'hrm.view');
    $this->getJson('/api/v1/dashboard')->assertOk();
});

test('expired persisted browser sessions redirect to login and reject the API', function (): void {
    config(['session.driver' => 'database', 'session.lifetime' => 1, 'sanctum.stateful' => ['localhost']]);
    $user = User::factory()->create();
    $response = $this->postJson('/login', ['email' => $user->email, 'password' => 'password'])->assertOk();
    $cookieName = config('session.cookie');
    $cookie = $response->getCookie($cookieName, false)->getValue();
    $this->travel(2)->minutes();
    Auth::forgetGuards();
    $this->app['session']->forgetDrivers();
    $this->app->forgetInstance('session.store');

    $this->withUnencryptedCookie($cookieName, $cookie)->get('/app/')->assertRedirect(route('login'));
    Auth::forgetGuards();
    $this->withHeader('Origin', 'http://localhost')->getJson('/api/v1/auth/me')->assertUnauthorized();
});

test('browser logout invalidates the session without revoking other API clients', function (): void {
    $user = User::factory()->create();
    $other = $user->createToken('another-device');
    config(['sanctum.stateful' => ['localhost']]);
    $this->postJson('/login', ['email' => $user->email, 'password' => 'password'])->assertOk();
    Auth::forgetGuards();

    $this->withHeader('Origin', 'http://localhost')->postJson('/api/v1/auth/logout')->assertNoContent();

    Auth::forgetGuards();
    $this->get('/app/hrm/employees')->assertRedirect(route('login'));
    $this->getJson('/api/v1/auth/me')->assertUnauthorized();
    $this->assertDatabaseHas('personal_access_tokens', ['id' => $other->accessToken->id]);
});

test('missing browser sessions cannot retrieve protected data', function (): void {
    $this->get('/app/')->assertRedirect(route('login'));
    $this->getJson('/api/v1/dashboard')->assertUnauthorized();
});

test('unknown URLs retain the existing not found behavior', function (): void {
    $this->get('/unknown-page')->assertNotFound();
    $this->getJson('/api/v1/unknown')->assertNotFound();
    $this->actingAs(User::factory()->create())->get('/app/unknown-page')->assertOk()->assertViewIs('layouts.master');
});

test('browser login is CSRF protected', function (): void {
    $this->app->bind(VerifyCsrfToken::class, fn (Application $app): VerifyCsrfToken => new class($app, $app['encrypter']) extends VerifyCsrfToken
    {
        protected function runningUnitTests(): bool
        {
            return false;
        }
    });

    $this->postJson('/login', ['email' => 'user@example.test', 'password' => 'password'])->assertStatus(419);

    $this->assertGuest('web');
});

test('browser login attempts use the existing rate limiter', function (): void {
    for ($attempt = 0; $attempt < 5; $attempt++) {
        $this->postJson('/login', ['email' => 'browser-rate-limit@example.test', 'password' => 'wrong'])->assertUnprocessable();
    }

    $this->postJson('/login', ['email' => 'browser-rate-limit@example.test', 'password' => 'wrong'])->assertTooManyRequests();
});

test('local login offers a dashboard preview without authenticating or exposing private data', function (): void {
    $this->app->instance('env', 'local');
    config(['app.login_preview' => true]);

    $this->get('/login')->assertOk()->assertViewHas('loginPreviewUrl', route('login.preview', [], false));
    $this->get('/app/preview')->assertOk()->assertViewIs('layouts.master')->assertViewHas('loginPreview', true);

    $this->assertGuest('web');
    $this->get('/app/hrm/employees')->assertRedirect(route('login'));
    $this->getJson('/api/v1/dashboard')->assertUnauthorized();
    $this->postJson('/login', ['_token' => csrf_token()])->assertUnprocessable()->assertJsonValidationErrors(['email', 'password']);
    $this->assertDatabaseCount('users', 0);
    $this->assertDatabaseCount('personal_access_tokens', 0);
});

test('login preview is unavailable outside local development or when disabled', function (string $environment, bool $enabled): void {
    $this->app->instance('env', $environment);
    config(['app.login_preview' => $enabled]);

    $this->get('/login')->assertOk()->assertViewHas('loginPreviewUrl', '');
    $this->get('/app/preview')->assertNotFound();

    $this->assertGuest('web');
})->with([
    ['production', true],
    ['staging', true],
    ['testing', true],
    ['local', false],
]);
