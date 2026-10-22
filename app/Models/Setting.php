<?php

namespace App\Models;

use App\Support\Concerns\BelongsToShop;
use App\Support\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use BelongsToShop, BelongsToTenant, HasFactory;

    protected $fillable = ['timezone', 'currency'];
}
