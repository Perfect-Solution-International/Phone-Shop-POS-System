<?php

namespace App\Models;

use App\Support\Concerns\BelongsToShop;
use App\Support\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use BelongsToShop, BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_code',
        'name',
        'email',
        'phone',
        'department',
        'position',
        'basic_salary',
        'outlet_id',
        'user_id',
        'joined_on',
        'is_active',
    ];

    protected $casts = [
        'joined_on' => 'date:Y-m-d',
        'basic_salary' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }
}
