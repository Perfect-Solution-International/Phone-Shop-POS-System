<?php

namespace App\Models;

use App\Support\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleReturnItem extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'sale_return_id',
        'sale_item_id',
        'product_id',
        'phone_device_id',
        'quantity',
        'refund_amount',
        'restock',
        'condition',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'refund_amount' => 'decimal:2',
        'restock' => 'boolean',
    ];

    public function saleReturn(): BelongsTo
    {
        return $this->belongsTo(SaleReturn::class);
    }

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function phoneDevice(): BelongsTo
    {
        return $this->belongsTo(PhoneDevice::class);
    }
}

