<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'monthly_price',
        'annual_price',
        'trial_days',
        'max_branches',
        'max_staff',
        'max_products',
        'has_imei_tracking',
        'has_repair_management',
        'has_warranty_management',
        'has_accounting_reports',
        'has_multi_branch',
        'has_customer_portal',
        'is_active',
    ];

    protected $casts = [
        'monthly_price' => 'decimal:2',
        'annual_price' => 'decimal:2',
        'trial_days' => 'integer',
        'max_branches' => 'integer',
        'max_staff' => 'integer',
        'max_products' => 'integer',
        'has_imei_tracking' => 'boolean',
        'has_repair_management' => 'boolean',
        'has_warranty_management' => 'boolean',
        'has_accounting_reports' => 'boolean',
        'has_multi_branch' => 'boolean',
        'has_customer_portal' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class, 'plan_id');
    }
}

