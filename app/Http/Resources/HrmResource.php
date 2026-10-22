<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HrmResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        unset($data['shop_id'], $data['deleted_at']);

        return $data;
    }
}
