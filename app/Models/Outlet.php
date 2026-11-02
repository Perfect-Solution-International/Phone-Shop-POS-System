<?php

namespace App\Models;

use App\Models\Platform\Shop;
use App\Support\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Outlet extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'shop_id',
        'name',
        'code',
        'phone',
        'email',
        'address',
        'city',
        'manager_name',
        'manager_email',
        'manager_user_id',
        'is_main',
        'is_active',
        'approval_status',
        'approved_at',
        'approved_by',
        'rejection_reason',
        'billing_type',
        'payment_status',
        'additional_fee',
        'payment_method',
        'payment_reference',
        'payment_receipt_url',
        'paid_at',
        'receipt_header',
        'receipt_footer',
    ];

    protected $casts = [
        'is_main' => 'boolean',
        'is_active' => 'boolean',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'additional_fee' => 'decimal:2',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function managerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }

    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->approval_status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->approval_status === 'rejected';
    }

    public function isCoveredByPlan(): bool
    {
        if ($this->billing_type === 'included' || $this->is_main) {
            return true;
        }

        $subscription = $this->shop?->activeSubscription ?? $this->shop?->subscription;
        $plan = $subscription?->plan;
        if (! $plan) {
            return false;
        }

        $maxPlanBranches = (int) $plan->max_branches;

        $approvedCount = static::withoutGlobalScopes()
            ->where('shop_id', $this->shop_id)
            ->where('approval_status', 'approved')
            ->where('id', '!=', $this->id)
            ->count();

        return $approvedCount < $maxPlanBranches;
    }

    public function requiresPayment(): bool
    {
        return ! $this->isCoveredByPlan() && $this->payment_status === 'unpaid';
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('approval_status', 'pending');
    }
}

