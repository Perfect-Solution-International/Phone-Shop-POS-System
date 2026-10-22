<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\Payroll;
use App\Models\Setting;
use App\Services\Report\PayrollExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function dashboard(): JsonResponse
    {
        $timezone = Setting::first()?->timezone ?? config('app.business_timezone');
        $today = CarbonImmutable::now($timezone)->toDateString();

        return response()->json([
            'employees' => Employee::count(),
            'present_today' => Attendance::whereDate('work_date', $today)->count(),
            'pending_leave' => Leave::where('status', 'pending')->count(),
            'leave_statuses' => Leave::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status'),
            'timezone' => $timezone,
        ]);
    }

    public function payrollPdf(): Response
    {
        abort_if(Payroll::count() > 2000, 422, 'Use the spreadsheet export for more than 2,000 records.');

        return Pdf::loadView('reports.payroll', [
            'payrolls' => Payroll::with('employee')->orderByDesc('period')->orderBy('id')->get(),
            'currency' => Setting::first()?->currency ?? 'LKR',
        ])->download('payroll.pdf');
    }

    public function payrollExcel(): BinaryFileResponse
    {
        return Excel::download(new PayrollExport((int) app('shop.id')), 'payroll.xlsx');
    }
}
