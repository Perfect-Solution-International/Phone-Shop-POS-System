<?php

namespace App\Models;

use App\Support\Concerns\BelongsToShop;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    use BelongsToShop, HasFactory;

    protected $fillable = ['employee_id', 'period', 'basic_salary', 'allowances', 'deductions', 'status'];

    protected $casts = [
        'period' => 'date:Y-m-d',
        'basic_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (Payroll $payroll): void {
            $minor = self::minorUnits((string) $payroll->basic_salary)
                + self::minorUnits((string) ($payroll->allowances ?? 0))
                - self::minorUnits((string) ($payroll->deductions ?? 0));
            $payroll->net_salary = sprintf('%d.%02d', intdiv($minor, 100), $minor % 100);
        });
    }

    public static function minorUnits(string $amount): int
    {
        $parts = explode('.', $amount);

        return ((int) $parts[0] * 100) + (int) str_pad($parts[1] ?? '', 2, '0');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class)->withTrashed();
    }
}
