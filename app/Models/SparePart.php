<?php

namespace App\Models;

use App\Support\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SparePart extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'name',
        'part_number',
        'compatible_models',
        'quality_type',
        'cost_price',
        'selling_price',
        'stock_quantity',
        'alert_quantity',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'alert_quantity' => 'integer',
    ];

    public function repairPartsUsed(): HasMany
    {
        return $this->hasMany(RepairPartUsed::class);
    }
}

