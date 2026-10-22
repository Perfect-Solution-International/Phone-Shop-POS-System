<?php

namespace App\Http\Requests;

use App\Models\Payroll;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PayrollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('payroll.manage');
    }

    public function rules(): array
    {
        $money = ['required', 'regex:/^\\d{1,8}(\\.\\d{1,2})?$/'];

        return [
            'shop_id' => ['prohibited'],
            'employee_id' => ['required', 'integer', Rule::exists('employees', 'id')->where('shop_id', $this->user()->shop_id)->whereNull('deleted_at')],
            'period' => ['required', 'date_format:Y-m-d', 'regex:/^\\d{4}-\\d{2}-01$/', Rule::unique('payrolls')->where('shop_id', $this->user()->shop_id)->where('employee_id', $this->input('employee_id'))->ignore($this->route('payroll'))],
            'basic_salary' => $money,
            'allowances' => $money,
            'deductions' => $money,
            'net_salary' => ['prohibited'],
            'status' => ['required', Rule::in(['draft', 'ready'])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            if (Payroll::minorUnits((string) $this->input('deductions')) >
                Payroll::minorUnits((string) $this->input('basic_salary')) + Payroll::minorUnits((string) $this->input('allowances'))) {
                $validator->errors()->add('deductions', 'Deductions cannot exceed salary and allowances.');
            }
        });
    }
}
