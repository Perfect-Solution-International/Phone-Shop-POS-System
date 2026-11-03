<?php

namespace App\Models;

use App\Support\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalePayment extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'shop_id',
        'sale_id',
        'payment_method',
        'amount',
        'reference',
        'slip_path',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    protected $appends = [
        'slip_url',
    ];

    public function getSlipUrlAttribute(): ?string
    {
        return $this->slip_path ? asset('storage/' . $this->slip_path) : null;
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}

