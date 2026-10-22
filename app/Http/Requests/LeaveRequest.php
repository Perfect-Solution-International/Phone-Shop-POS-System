<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('hrm.manage');
    }

    public function rules(): array
    {
        return [
            'shop_id' => ['prohibited'],
            'employee_id' => ['required', 'integer', Rule::exists('employees', 'id')->where('shop_id', $this->user()->shop_id)->whereNull('deleted_at')],
            'starts_on' => ['required', 'date_format:Y-m-d'],
            'ends_on' => ['required', 'date_format:Y-m-d', 'after_or_equal:starts_on'],
            'type' => ['required', Rule::in(['annual', 'sick', 'unpaid'])],
            'reason' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
        ];
    }
}
