<?php

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

test('login issues an expiring bearer token and hides credentials', function (): void {
    $user = User::factory()->create();

    $response = $this->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => 'password']);

    $response->assertOk()->assertJsonPath('token_type', 'Bearer')->assertJsonPath('user.id', $user->id)
        ->assertJsonMissingPath('user.password')->assertJsonMissingPath('user.remember_token');
    $token = PersonalAccessToken::findToken($response->json('token'));
    expect($token->expires_at)->not->toBeNull();
    expect($token->expires_at->greaterThan(now()->addHours(7)))->toBeTrue();
});

test('invalid credentials return 422 without creating a token', function (): void {
    $user = User::factory()->create();

    $this->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => 'wrong'])
        ->assertUnprocessable()->assertJsonValidationErrors('email')
        ->assertJsonPath('errors.email.0', 'The provided credentials are incorrect.');

    $this->assertDatabaseCount('personal_access_tokens', 0);
});

test('protected API returns JSON 401 without requiring an accept header', function (): void {
    $this->get('/api/v1/auth/me')->assertUnauthorized()->assertJsonPath('message', 'Unauthenticated.');
});

test('logout revokes only the current bearer token', function (): void {
    $user = User::factory()->create();
    $current = $user->createToken('browser');
    $other = $user->createToken('another-device');

    $this->withToken($current->plainTextToken)->postJson('/api/v1/auth/logout')->assertNoContent();

    $this->assertDatabaseMissing('personal_access_tokens', ['id' => $current->accessToken->id]);
    $this->assertDatabaseHas('personal_access_tokens', ['id' => $other->accessToken->id]);
});

test('expired tokens return 401', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('expired', ['*'], now()->subMinute());

    $this->withToken($token->plainTextToken)->getJson('/api/v1/auth/me')->assertUnauthorized();
});

test('repeated failed sign in attempts are rate limited', function (): void {
    for ($attempt = 0; $attempt < 5; $attempt++) {
        $this->postJson('/api/v1/auth/login', ['email' => 'rate-limit@example.test', 'password' => 'incorrect'])->assertUnprocessable();
    }

    $this->postJson('/api/v1/auth/login', ['email' => 'rate-limit@example.test', 'password' => 'incorrect'])->assertTooManyRequests();
});

test('guests see login and cannot open the protected SPA shell', function (): void {
    $this->get('/')->assertRedirect(route('login'));
    $this->get('/login')->assertOk()->assertViewIs('auth.login');
    $this->get('/app/hrm/attendance')->assertRedirect(route('login'));
    $this->get('/api/v1/unknown')->assertNotFound();
});
