<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HrmIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $columns = match ($this->route()->getName()) {
            'api.v1.employees.index' => ['id', 'name', 'employee_code', 'email', 'department', 'joined_on', 'is_active'],
            'api.v1.attendance.index' => ['id', 'work_date', 'clocked_in_at', 'clocked_out_at'],
            'api.v1.leave.index' => ['id', 'starts_on', 'ends_on', 'type', 'status'],
            'api.v1.payroll.index' => ['id', 'period', 'basic_salary', 'allowances', 'deductions', 'net_salary', 'status'],
            default => ['id'],
        };

        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'search' => ['nullable', 'string', 'max:100'],
            'sort' => ['sometimes', Rule::in($columns)],
            'direction' => ['sometimes', Rule::in(['asc', 'desc'])],
        ];
    }
}
