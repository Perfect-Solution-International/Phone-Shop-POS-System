<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('hrm.manage');
    }

    public function rules(): array
    {
        return [
            'shop_id' => ['prohibited'],
            'employee_code' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9_-]+$/', Rule::unique('employees')->where('shop_id', $this->user()->shop_id)->ignore($this->route('employee'))],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('employees')->where('shop_id', $this->user()->shop_id)->ignore($this->route('employee'))],
            'department' => ['nullable', 'string', 'max:100'],
            'position' => ['nullable', 'string', 'max:100'],
            'joined_on' => ['required', 'date_format:Y-m-d'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
