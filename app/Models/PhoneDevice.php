<?php

namespace App\Models;

use App\Support\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhoneDevice extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'product_id',
        'supplier_id',
        'brand',
        'model',
        'storage',
        'ram',
        'color',
        'condition',
        'imei_1',
        'imei_2',
        'serial_number',
        'purchase_cost',
        'selling_price',
        'status',
        'sold_at',
        'sale_id',
        'notes',
    ];

    protected $casts = [
        'purchase_cost' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'sold_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function isAvailable(): bool
    {
        return $this->status === 'in_stock';
    }

    public function isSold(): bool
    {
        return $this->status === 'sold';
    }

    /**
     * Mark device as sold with timestamp and sale relation.
     */
    public function markAsSold(int $saleId): void
    {
        $this->update([
            'status' => 'sold',
            'sale_id' => $saleId,
            'sold_at' => now(),
        ]);
    }

    /**
     * Return device back to stock (e.g. sales return).
     */
    public function returnToStock(): void
    {
        $this->update([
            'status' => 'in_stock',
            'sale_id' => null,
            'sold_at' => null,
        ]);
    }
}

