<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'plan_id',
        'status',
        'billing_cycle',
        'recurring_amount',
        'discount_amount',
        'trial_ends_at',
        'current_period_starts_at',
        'current_period_ends_at',
        'grace_period_ends_at',
        'cancelled_at',
        'last_paid_at',
        'card_last4',
        'card_brand',
        'cardholder_name',
        'card_expiry',
        'payment_method',
    ];

    protected $casts = [
        'recurring_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'trial_ends_at' => 'datetime',
        'current_period_starts_at' => 'datetime',
        'current_period_ends_at' => 'datetime',
        'grace_period_ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'last_paid_at' => 'datetime',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    public function isTrial(): bool
    {
        return $this->status === 'trial' && $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function isTrialExpired(): bool
    {
        return $this->status === 'trial' && $this->trial_ends_at && $this->trial_ends_at->isPast();
    }

    public function isExpired(): bool
    {
        if ($this->status === 'expired') {
            return true;
        }

        if ($this->isTrialExpired()) {
            return true;
        }

        if ($this->status === 'active' && $this->current_period_ends_at && $this->current_period_ends_at->isPast()) {
            return true;
        }

        return false;
    }

    public function isActive(): bool
    {
        if ($this->isExpired() || $this->isSuspended()) {
            return false;
        }

        if ($this->status === 'trial') {
            return $this->isTrial();
        }

        return $this->status === 'active' && $this->current_period_ends_at && $this->current_period_ends_at->isFuture();
    }

    public function isGracePeriod(): bool
    {
        return $this->status === 'grace_period' && $this->grace_period_ends_at && $this->grace_period_ends_at->isFuture();
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended' || $this->isExpired();
    }
}

