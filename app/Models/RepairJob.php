<?php

namespace App\Models;

use App\Support\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RepairJob extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'customer_id',
        'technician_id',
        'job_number',
        'brand',
        'model',
        'imei',
        'fault_description',
        'condition_notes',
        'accessories_received',
        'estimated_cost',
        'advance_amount',
        'final_cost',
        'status',
        'estimated_completion_date',
        'delivered_at',
        'technician_notes',
    ];

    protected $casts = [
        'estimated_cost' => 'decimal:2',
        'advance_amount' => 'decimal:2',
        'final_cost' => 'decimal:2',
        'estimated_completion_date' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function partsUsed(): HasMany
    {
        return $this->hasMany(RepairPartUsed::class);
    }

    public function warranties(): HasMany
    {
        return $this->hasMany(Warranty::class);
    }

    public function getBalanceDueAttribute(): float
    {
        $cost = $this->final_cost ?? $this->estimated_cost;
        return max(0.00, (float) $cost - (float) $this->advance_amount);
    }
}

