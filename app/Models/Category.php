<?php

namespace App\Models;

use App\Support\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = ['shop_id', 'name', 'slug'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}

