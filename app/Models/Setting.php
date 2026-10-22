<?php

namespace App\Models;

use App\Support\Concerns\BelongsToShop;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use BelongsToShop, HasFactory;

    protected $fillable = ['timezone', 'currency'];
}
