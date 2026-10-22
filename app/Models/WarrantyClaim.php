<?php

namespace App\Models;

use App\Support\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarrantyClaim extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'warranty_id',
        'claim_number',
        'claim_date',
        'issue_description',
        'status',
        'settlement_type',
        'action_notes',
    ];

    protected $casts = [
        'claim_date' => 'date:Y-m-d',
    ];

    public function warranty(): BelongsTo
    {
        return $this->belongsTo(Warranty::class);
    }
}

