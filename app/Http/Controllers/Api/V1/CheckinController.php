<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCheckinRequest;
use App\Http\Resources\CheckinResource;
use App\Models\Checkin;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;

class CheckinController extends Controller
{
    /**
     * List checkins with optional filters.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $checkins = Checkin::query()
            ->with('employee')
            ->when($request->input('employee_id'), fn ($q, $id) => $q->where('employee_id', $id))
            ->when($request->input('id_number'), function ($q, $idNumber) {
                $q->whereHas('employee', fn ($e) => $e->where('id_number', $idNumber));
            })
            ->when($request->input('date'), fn ($q, $date) => $q->whereDate('captured_at', $date))
            ->when($request->input('start_date'), fn ($q, $date) => $q->where('captured_at', '>=', $date))
            ->when($request->input('end_date'), fn ($q, $date) => $q->where('captured_at', '<=', $date.' 23:59:59'))
            ->orderByDesc('captured_at')
            ->paginate($request->input('per_page', 15));

        return CheckinResource::collection($checkins);
    }

    /**
     * Update a checkin's manual time-out.
     */
    public function update(Request $request, Checkin $checkin): JsonResponse
    {
        $validated = $request->validate([
            'manual_time_out' => ['required', 'date'],
        ]);

        $checkin->update($validated);
        $checkin->load('employee');

        return (new CheckinResource($checkin))
            ->response();
    }

    /**
     * Store a new checkin from the mobile app.
     */
    public function store(StoreCheckinRequest $request): JsonResponse
    {
        $employee = Employee::query()->where('id_number', $request->input('id_number'))->firstOrFail();

        $selfiePath = $request->file('selfie')->store('selfies', 's3');

        $checkin = Checkin::query()->create([
            'employee_id' => $employee->id,
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'location_name' => $request->input('location_name'),
            'selfie_path' => $selfiePath,
            'captured_at' => $request->input('captured_at'),
        ]);

        $checkin->load('employee');

        return (new CheckinResource($checkin))
            ->response()
            ->setStatusCode(201);
    }
}
