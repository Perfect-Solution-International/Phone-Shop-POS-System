<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.manage');
    }

    public function rules(): array
    {
        return [
            'shop_id' => ['prohibited'],
            'timezone' => ['required', 'timezone'],
            'currency' => ['required', 'regex:/^[A-Z]{3}$/'],
        ];
    }
}
