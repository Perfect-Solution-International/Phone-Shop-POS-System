<?php

namespace App\Services\Report;

use App\Models\Payroll;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class PayrollExport extends StringValueBinder implements FromQuery, WithCustomValueBinder, WithHeadings, WithMapping
{
    public function __construct(private int $shopId) {}

    public function query(): Builder
    {
        return Payroll::withoutGlobalScope('shop')->where('shop_id', $this->shopId)
            ->with(['employee' => fn (BelongsTo $query) => $query->withoutGlobalScope('shop')->where('shop_id', $this->shopId)])
            ->orderBy('id');
    }

    public function headings(): array
    {
        return ['Employee', 'Period', 'Basic salary', 'Allowances', 'Deductions', 'Net salary', 'Status'];
    }

    /** @param Payroll $payroll */
    public function map(mixed $payroll): array
    {
        return [
            $payroll->employee?->name,
            $payroll->period->format('Y-m'),
            $payroll->basic_salary,
            $payroll->allowances,
            $payroll->deductions,
            $payroll->net_salary,
            $payroll->status,
        ];
    }
}
