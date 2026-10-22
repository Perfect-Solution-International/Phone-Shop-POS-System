<?php

namespace App\Models;

use App\Support\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'product_id',
        'phone_device_id',
        'type',
        'quantity',
        'reference_type',
        'reference_id',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function phoneDevice(): BelongsTo
    {
        return $this->belongsTo(PhoneDevice::class);
    }
}

