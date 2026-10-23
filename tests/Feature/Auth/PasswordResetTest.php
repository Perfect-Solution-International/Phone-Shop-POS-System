<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

beforeEach(function (): void {
    $this->seed(DatabaseSeeder::class);
});

test('forgot password screen can be rendered', function (): void {
    $response = $this->get('/forgot-password');

    $response->assertOk();
    $response->assertSee('Reset Password');
    $response->assertSee('Owner Email Address');
    $response->assertSee('Send Password Reset Link');
});

test('reset password link can be requested for valid user and sends notification', function (): void {
    Notification::fake();

    $user = User::factory()->create();

    $response = $this->post('/forgot-password', ['email' => $user->email]);

    $response->assertSessionHas('status', 'If an account exists with that email address, we have sent a password reset link.');
    Notification::assertSentTo($user, ResetPassword::class);
});

test('reset password link request for unregistered user returns generic response preventing user enumeration', function (): void {
    Notification::fake();

    $response = $this->post('/forgot-password', ['email' => 'unregistered-user-999@example.test']);

    // Must not reveal that the email doesn't exist
    $response->assertSessionHas('status', 'If an account exists with that email address, we have sent a password reset link.');
    $response->assertSessionHasNoErrors();
    Notification::assertNothingSent();
});

test('forgot password request requires a valid email', function (): void {
    $response = $this->post('/forgot-password', ['email' => 'not-a-valid-email']);

    $response->assertSessionHasErrors('email');
});

test('forgot password requests are rate limited', function (): void {
    for ($i = 0; $i < 6; $i++) {
        $this->post('/forgot-password', ['email' => "throttle-{$i}@example.test"])
            ->assertSessionHas('status');
    }

    $response = $this->post('/forgot-password', ['email' => 'throttle-7@example.test']);
    $response->assertStatus(429);
});

test('reset password screen can be rendered with valid token', function (): void {
    $user = User::factory()->create();
    $token = Password::createToken($user);

    $response = $this->get('/reset-password/'.$token.'?email='.$user->email);

    $response->assertOk();
    $response->assertSee('Set New Password');
    $response->assertSee('Confirm New Password');
    $response->assertSee($user->email);
});

test('password can be reset with valid token, hashes new password and invalidates prior sessions', function (): void {
    Event::fake([PasswordReset::class]);

    $user = User::factory()->create(['password' => Hash::make('OldPassword123!')]);
    $token = Password::createToken($user);

    // Simulate an active session in the database
    DB::table('sessions')->insert([
        'id' => 'prior-session-id-12345',
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Browser',
        'payload' => 'payload-data',
        'last_activity' => time(),
    ]);

    $oldRememberToken = $user->remember_token;

    $response = $this->post('/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'NewStrongPassword123!',
        'password_confirmation' => 'NewStrongPassword123!',
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('status', 'Your password has been reset successfully. Please sign in with your new credentials.');

    $user->refresh();
    expect(Hash::check('NewStrongPassword123!', $user->password))->toBeTrue();
    expect($user->remember_token)->not->toBe($oldRememberToken);

    // Prior database sessions must be purged
    expect(DB::table('sessions')->where('user_id', $user->id)->count())->toBe(0);

    Event::assertDispatched(PasswordReset::class, function (PasswordReset $event) use ($user): bool {
        return $event->user->id === $user->id;
    });
});

test('password cannot be reset with invalid token', function (): void {
    $user = User::factory()->create(['password' => Hash::make('OriginalPassword123!')]);

    $response = $this->post('/reset-password', [
        'token' => 'completely-invalid-token',
        'email' => $user->email,
        'password' => 'NewStrongPassword123!',
        'password_confirmation' => 'NewStrongPassword123!',
    ]);

    $response->assertSessionHasErrors('email');
    expect(Hash::check('OriginalPassword123!', $user->fresh()->password))->toBeTrue();
});

test('password cannot be reset with expired token', function (): void {
    $user = User::factory()->create(['password' => Hash::make('OriginalPassword123!')]);
    $token = Password::createToken($user);

    // Age the token beyond the 60 minute expiration limit
    DB::table('password_reset_tokens')->where('email', $user->email)->update([
        'created_at' => now()->subMinutes(90),
    ]);

    $response = $this->post('/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'NewStrongPassword123!',
        'password_confirmation' => 'NewStrongPassword123!',
    ]);

    $response->assertSessionHasErrors('email');
    expect(Hash::check('OriginalPassword123!', $user->fresh()->password))->toBeTrue();
});

test('password complexity rules are enforced during password reset', function (string $invalidPassword): void {
    $user = User::factory()->create();
    $token = Password::createToken($user);

    $response = $this->post('/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => $invalidPassword,
        'password_confirmation' => $invalidPassword,
    ]);

    $response->assertSessionHasErrors('password');
})->with([
    'short' => 'Short1!',
    'no-numbers' => 'NoNumbersPassword!',
    'no-mixed-case' => 'alllowercasepassword123',
]);
