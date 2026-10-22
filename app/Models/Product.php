<?php

namespace App\Models;

use App\Support\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'category_id',
        'brand_id',
        'name',
        'code',
        'type',
        'cost_price',
        'retail_price',
        'wholesale_price',
        'stock_quantity',
        'alert_quantity',
        'is_active',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'retail_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'alert_quantity' => 'integer',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function phoneDevices(): HasMany
    {
        return $this->hasMany(PhoneDevice::class);
    }

    public function isPhone(): bool
    {
        return $this->type === 'phone_device';
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->alert_quantity;
    }

    public function getSellingPriceAttribute(): float
    {
        return (float) ($this->attributes['retail_price'] ?? 0);
    }

    public function setSellingPriceAttribute($value): void
    {
        $this->attributes['retail_price'] = $value;
    }

    public function getBarcodeAttribute(): ?string
    {
        return $this->attributes['code'] ?? null;
    }

    public function getSkuAttribute(): ?string
    {
        return $this->attributes['code'] ?? null;
    }

    public function getStatusAttribute(): string
    {
        return ($this->attributes['is_active'] ?? true) ? 'active' : 'inactive';
    }
}

