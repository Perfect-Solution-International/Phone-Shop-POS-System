<?php

namespace App\Models;

use App\Support\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhoneTradeIn extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'shop_id',
        'outlet_id',
        'customer_id',
        'phone_device_id',
        'customer_name',
        'customer_phone',
        'customer_nic',
        'brand',
        'model',
        'storage',
        'ram',
        'color',
        'imei_1',
        'imei_2',
        'condition_checklist',
        'valuation_amount',
        'payout_type',
        'status',
        'notes',
    ];

    protected $casts = [
        'condition_checklist' => 'array',
        'valuation_amount' => 'decimal:2',
    ];

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function phoneDevice(): BelongsTo
    {
        return $this->belongsTo(PhoneDevice::class);
    }
}

