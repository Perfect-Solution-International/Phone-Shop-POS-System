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
        'shop_id',
        'category_id',
        'brand_id',
        'supplier_id',
        'name',
        'code',
        'type',
        'image_path',
        'cost_price',
        'retail_price',
        'wholesale_price',
        'stock_quantity',
        'alert_quantity',
        'is_active',
        'status',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'retail_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'alert_quantity' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'image_url',
        'barcode',
        'sku',
        'selling_price',
        'quantity',
    ];

    protected static function booted(): void
    {
        static::saving(function (Product $product): void {
            if ($product->isDirty('is_active') && ! $product->isDirty('status')) {
                $product->status = $product->is_active ? 'active' : 'inactive';
            } elseif ($product->isDirty('status') && ! $product->isDirty('is_active')) {
                $product->is_active = ($product->status === 'active');
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q): void {
            $q->where('is_active', true)->orWhere('status', 'active');
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
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

    public function getQuantityAttribute(): int
    {
        return (int) ($this->attributes['stock_quantity'] ?? 0);
    }

    public function setQuantityAttribute($value): void
    {
        $this->attributes['stock_quantity'] = $value;
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
        return $this->attributes['status'] ?? (($this->attributes['is_active'] ?? true) ? 'active' : 'inactive');
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! empty($this->image_path)) {
            return asset('storage/' . $this->image_path);
        }
        return null;
    }
}

