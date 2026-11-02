<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Platform\Shop;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoGeneratePayrollCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payroll:auto-generate 
                            {--month= : The YYYY-MM period (e.g. 2026-10). Defaults to current month}
                            {--shop= : Specific shop ID to run for}
                            {--force : Overwrite existing draft payslips}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically generate monthly draft payslips for active shop employees';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $periodInput = $this->option('month') ?: now()->format('Y-m');

        try {
            $periodCarbon = Carbon::createFromFormat('!Y-m', $periodInput);
        } catch (\Exception $e) {
            $this->error("Invalid month format '{$periodInput}'. Please use YYYY-MM format (e.g. 2026-10).");
            return self::FAILURE;
        }

        $periodDate = $periodCarbon->startOfMonth()->toDateString();
        $periodLabel = $periodCarbon->format('F Y');
        $force = (bool) $this->option('force');
        $shopId = $this->option('shop');

        $this->info("⚡ Running Auto-Payroll Generation for Period: {$periodLabel} ({$periodDate})");

        $shopsQuery = Shop::query();
        if ($shopId) {
            $shopsQuery->where('id', $shopId);
        }
        $shops = $shopsQuery->get();

        if ($shops->isEmpty()) {
            $this->warn('No shops found.');
            return self::SUCCESS;
        }

        $totalGenerated = 0;
        $totalSkipped = 0;

        foreach ($shops as $shop) {
            app()->instance('shop.id', $shop->id);
            $employees = Employee::withoutGlobalScopes()
                ->where('shop_id', $shop->id)
                ->where('is_active', true)
                ->where('basic_salary', '>', 0)
                ->get();

            $shopGenerated = 0;
            $shopSkipped = 0;

            foreach ($employees as $employee) {
                $existing = Payroll::withoutGlobalScopes()
                    ->where('shop_id', $shop->id)
                    ->where('employee_id', $employee->id)
                    ->where('period', $periodDate)
                    ->first();

                if ($existing) {
                    if ($existing->status === 'paid' || ! $force) {
                        $shopSkipped++;
                        continue;
                    }

                    // Overwrite draft
                    $existing->update([
                        'basic_salary' => $employee->basic_salary,
                        'generation_type' => 'auto',
                        'note' => 'Auto-regenerated on ' . now()->toDateTimeString(),
                    ]);
                    $shopGenerated++;
                } else {
                    Payroll::create([
                        'shop_id' => $shop->id,
                        'employee_id' => $employee->id,
                        'period' => $periodDate,
                        'basic_salary' => $employee->basic_salary,
                        'allowances' => 0.00,
                        'deductions' => 0.00,
                        'generation_type' => 'auto',
                        'status' => 'draft',
                        'note' => 'System auto-generated monthly payslip',
                    ]);
                    $shopGenerated++;
                }
            }

            $totalGenerated += $shopGenerated;
            $totalSkipped += $shopSkipped;

            $this->line("Shop [{$shop->id}] {$shop->name}: Generated {$shopGenerated}, Skipped {$shopSkipped}.");
        }

        app()->forgetInstance('shop.id');

        $this->info("✓ Completed: {$totalGenerated} payslips generated/updated, {$totalSkipped} existing records skipped.");

        return self::SUCCESS;
    }
}
