<?php

namespace App\Models;

use App\Support\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'nic',
        'address',
        'customer_type',
        'credit_limit',
        'balance',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function debtPayments(): HasMany
    {
        return $this->hasMany(CustomerDebtPayment::class);
    }
}

