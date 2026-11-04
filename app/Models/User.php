<?php

namespace App\Models;

use App\Models\Platform\Shop;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'shop_id',
        'outlet_id',
        'email_verified_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['email_verified_at' => 'datetime', 'password' => 'hashed'];

    protected $guard_name = 'web';

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Outlet::class);
    }

    /**
     * Determine if the user has verified their email address.
     * When email verification is paused, all authenticated accounts are considered verified.
     */
    public function hasVerifiedEmail(): bool
    {
        if (! config('auth.verification.enabled', false)) {
            return true;
        }

        return ! is_null($this->email_verified_at);
    }

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        if (! config('auth.verification.enabled', false)) {
            return;
        }

        $this->notify(new \Illuminate\Auth\Notifications\VerifyEmail);
    }

    /**
     * Determine if the user is a Shop Owner or Administrator.
     */
    public function isOwnerOrAdmin(): bool
    {
        return $this->hasRole(['Shop Owner', 'Administrator', 'Super Administrator']);
    }

    /**
     * Determine if the user is a staff member (non-owner/non-admin).
     */
    public function isStaff(): bool
    {
        return ! $this->isOwnerOrAdmin();
    }
}
