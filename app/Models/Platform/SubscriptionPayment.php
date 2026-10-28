<?php

namespace App\Models\Platform;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'plan_id',
        'subscription_id',
        'amount',
        'discount_amount',
        'refunded_amount',
        'payment_method',
        'billing_cycle',
        'status',
        'transaction_id',
        'reference_number',
        'receipt_url',
        'rejection_reason',
        'refund_reason',
        'verified_at',
        'verified_by',
        'refunded_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'verified_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public function isFullyRefunded(): bool
    {
        return $this->status === 'refunded' || ($this->refunded_amount > 0 && $this->refunded_amount >= $this->amount);
    }

    public function isPartiallyRefunded(): bool
    {
        return $this->refunded_amount > 0 && $this->refunded_amount < $this->amount;
    }

    public function netAmount(): float
    {
        return max(0.0, (float) $this->amount - (float) $this->refunded_amount);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}

