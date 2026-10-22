<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\HrmIndexRequest;
use App\Http\Requests\PayrollRequest;
use App\Http\Resources\HrmResource;
use App\Mail\PayrollReady;
use App\Models\Payroll;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

class PayrollController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:payroll.view')->only(['index', 'show']);
        $this->middleware('can:payroll.manage')->except(['index', 'show']);
    }

    public function index(HrmIndexRequest $request): AnonymousResourceCollection
    {
        $query = Payroll::query()->with('employee');
        $search = $request->validated('search');
        if ($search) {
            $query->whereHas('employee', fn (Builder $query) => $query->where('name', 'like', '%'.$search.'%')->orWhere('employee_code', 'like', '%'.$search.'%'));
        }
        $sort = $request->validated('sort', 'id');
        $query->orderBy($sort, $request->validated('direction', 'desc'));
        if ($sort !== 'id') {
            $query->orderByDesc('id');
        }

        return HrmResource::collection($query->paginate($request->integer('per_page', 20))->withQueryString());
    }

    public function store(PayrollRequest $request): JsonResponse
    {
        $payroll = Payroll::create($request->validated());
        $this->notifyWhenReady($payroll);

        return (new HrmResource($payroll->refresh()->load('employee')))->response()->setStatusCode(201);
    }

    public function show(Payroll $payroll): HrmResource
    {
        return new HrmResource($payroll->load('employee'));
    }

    public function update(PayrollRequest $request, Payroll $payroll): HrmResource
    {
        $wasReady = $payroll->status === 'ready';
        $payroll->update($request->validated());
        if (! $wasReady) {
            $this->notifyWhenReady($payroll);
        }

        return new HrmResource($payroll->refresh()->load('employee'));
    }

    public function destroy(Payroll $payroll): Response
    {
        $payroll->delete();

        return response()->noContent();
    }

    private function notifyWhenReady(Payroll $payroll): void
    {
        if ($payroll->status === 'ready') {
            $employee = $payroll->employee;
            Mail::to($employee->email)->queue(new PayrollReady($employee->name, $payroll->period->format('Y-m')));
        }
    }
}
