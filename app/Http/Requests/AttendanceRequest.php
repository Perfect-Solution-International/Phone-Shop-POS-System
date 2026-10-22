<?php

namespace App\Http\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttendanceRequest extends FormRequest
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
            'work_date' => ['required', 'date_format:Y-m-d', Rule::unique('attendances')->where('shop_id', $this->user()->shop_id)->where('employee_id', $this->input('employee_id'))->ignore($this->route('attendance'))],
            'clocked_in_at' => ['required', 'date', 'regex:/T.*(Z|[+-]\\d{2}:\\d{2})$/'],
            'clocked_out_at' => ['nullable', 'date', 'after_or_equal:clocked_in_at', 'regex:/T.*(Z|[+-]\\d{2}:\\d{2})$/'],
        ];
    }

    public function timestampsInUtc(): array
    {
        $data = $this->validated();
        foreach (['clocked_in_at', 'clocked_out_at'] as $field) {
            $data[$field] = isset($data[$field]) ? CarbonImmutable::parse($data[$field])->utc() : null;
        }

        return $data;
    }
}
