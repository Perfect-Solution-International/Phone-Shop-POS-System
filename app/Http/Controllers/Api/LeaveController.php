<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\HrmIndexRequest;
use App\Http\Requests\LeaveRequest;
use App\Http\Resources\HrmResource;
use App\Models\Leave;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class LeaveController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:hrm.view')->only(['index', 'show']);
        $this->middleware('can:hrm.manage')->except(['index', 'show']);
    }

    public function index(HrmIndexRequest $request): AnonymousResourceCollection
    {
        $query = Leave::query()->with('employee');
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

    public function store(LeaveRequest $request): JsonResponse
    {
        $leave = Leave::create($request->validated());

        return (new HrmResource($leave->refresh()->load('employee')))->response()->setStatusCode(201);
    }

    public function show(Leave $leave): HrmResource
    {
        return new HrmResource($leave->load('employee'));
    }

    public function update(LeaveRequest $request, Leave $leave): HrmResource
    {
        $leave->update($request->validated());

        return new HrmResource($leave->refresh()->load('employee'));
    }

    public function destroy(Leave $leave): Response
    {
        $leave->delete();

        return response()->noContent();
    }
}
