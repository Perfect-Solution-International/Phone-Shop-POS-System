<?php

namespace App\Models;

use App\Support\Concerns\BelongsToShop;
use App\Support\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use BelongsToShop, BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = ['employee_code', 'name', 'email', 'department', 'position', 'joined_on', 'is_active'];

    protected $casts = ['joined_on' => 'date:Y-m-d', 'is_active' => 'boolean'];
}
