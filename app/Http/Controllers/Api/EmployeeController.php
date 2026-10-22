<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeRequest;
use App\Http\Requests\HrmIndexRequest;
use App\Http\Resources\HrmResource;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:hrm.view')->only(['index', 'show']);
        $this->middleware('can:hrm.manage')->except(['index', 'show']);
    }

    public function index(HrmIndexRequest $request): AnonymousResourceCollection
    {
        $query = Employee::query();
        $search = $request->validated('search');
        if ($search) {
            $query->where(function (Builder $query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%')->orWhere('employee_code', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%');
            });
        }
        $sort = $request->validated('sort', 'id');
        $query->orderBy($sort, $request->validated('direction', 'desc'));
        if ($sort !== 'id') {
            $query->orderByDesc('id');
        }

        return HrmResource::collection($query->paginate($request->integer('per_page', 20))->withQueryString());
    }

    public function store(EmployeeRequest $request): JsonResponse
    {
        $employee = Employee::create($request->validated());

        return (new HrmResource($employee->refresh()))->response()->setStatusCode(201);
    }

    public function show(Employee $employee): HrmResource
    {
        return new HrmResource($employee);
    }

    public function update(EmployeeRequest $request, Employee $employee): HrmResource
    {
        $employee->update($request->validated());

        return new HrmResource($employee->refresh());
    }

    public function destroy(Employee $employee): Response
    {
        $employee->delete();

        return response()->noContent();
    }
}
