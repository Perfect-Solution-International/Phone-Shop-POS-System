<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SettingRequest;
use App\Http\Resources\HrmResource;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    public function show(): HrmResource
    {
        return new HrmResource(Setting::first() ?? new Setting([
            'timezone' => config('app.business_timezone'), 'currency' => 'LKR',
        ]));
    }

    public function update(SettingRequest $request): JsonResponse
    {
        return (new HrmResource(Setting::updateOrCreate(['shop_id' => app('shop.id')], $request->validated())))
            ->response()->setStatusCode(200);
    }
}
