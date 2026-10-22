<?php

namespace App\Support\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use LogicException;

trait BelongsToShop
{
    public static function bootBelongsToShop(): void
    {
        static::addGlobalScope('shop', function (Builder $query): void {
            if (app()->bound('shop.id')) {
                $query->where($query->getModel()->qualifyColumn('shop_id'), app('shop.id'));
            } else {
                $query->whereRaw('1 = 0');
            }
        });

        static::creating(function (Model $model): void {
            if (app()->bound('shop.id')) {
                $model->shop_id = app('shop.id');
            }
            if (! $model->shop_id) {
                throw new LogicException('Shop-owned records require an explicit shop context.');
            }
        });
    }
}
