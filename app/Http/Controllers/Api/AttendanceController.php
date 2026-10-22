<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceRequest;
use App\Http\Requests\HrmIndexRequest;
use App\Http\Resources\HrmResource;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class AttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:hrm.view')->only(['index', 'show']);
        $this->middleware('can:hrm.manage')->except(['index', 'show']);
    }

    public function index(HrmIndexRequest $request): AnonymousResourceCollection
    {
        $query = Attendance::query()->with('employee');
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

    public function store(AttendanceRequest $request): JsonResponse
    {
        $attendance = Attendance::create($request->timestampsInUtc());

        return (new HrmResource($attendance->refresh()->load('employee')))->response()->setStatusCode(201);
    }

    public function show(Attendance $attendance): HrmResource
    {
        return new HrmResource($attendance->load('employee'));
    }

    public function update(AttendanceRequest $request, Attendance $attendance): HrmResource
    {
        $attendance->update($request->timestampsInUtc());

        return new HrmResource($attendance->refresh()->load('employee'));
    }

    public function destroy(Attendance $attendance): Response
    {
        $attendance->delete();

        return response()->noContent();
    }
}
