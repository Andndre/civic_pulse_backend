<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Traits\AppliesQueryOptions;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreActivityRequest;
use App\Http\Requests\UpdateActivityRequest;
use App\Http\Requests\UpdateActivityStatusRequest;
use App\Http\Resources\ActivityResource;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    use AppliesQueryOptions;

    /**
     * Display a listing of activities.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ActivityLog::query()->with('student');

        $paginator = $this->applyQueryOptions(
            $query,
            $request,
            ['title', 'description', 'location'],
            ['student_id', 'type', 'status', 'reviewed_by']
        );

        return $this->respondWithPagination($paginator, ActivityResource::class, 'Activities retrieved successfully');
    }

    /**
     * Store a newly created activity.
     */
    public function store(StoreActivityRequest $request): JsonResponse
    {
        $evidenceUrl = $request->evidence_url;

        if ($request->hasFile('evidence_file')) {
            $path = $request->file('evidence_file')->store('evidence', 'public');
            $evidenceUrl = asset('storage/'.$path);
        }

        $activity = ActivityLog::create([
            'student_id' => $request->student_id,
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'date' => $request->date,
            'location' => $request->location,
            'achievement' => $request->achievement,
            'points' => $request->points ?? 0,
            'evidence_url' => $evidenceUrl,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Activity created successfully',
            'data' => new ActivityResource($activity->load('student')),
        ], 201);
    }

    /**
     * Display the specified activity.
     */
    public function show(ActivityLog $activity): JsonResponse
    {
        $activity->load(['student', 'reviewer']);

        return response()->json([
            'success' => true,
            'message' => 'Activity retrieved successfully',
            'data' => new ActivityResource($activity),
        ]);
    }

    /**
     * Update the specified activity.
     */
    public function update(UpdateActivityRequest $request, ActivityLog $activity): JsonResponse
    {
        $data = $request->only([
            'student_id',
            'title',
            'description',
            'type',
            'date',
            'location',
            'achievement',
            'points',
            'evidence_url',
        ]);

        if ($request->hasFile('evidence_file')) {
            $path = $request->file('evidence_file')->store('evidence', 'public');
            $data['evidence_url'] = asset('storage/'.$path);
        }

        $activity->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Activity updated successfully',
            'data' => new ActivityResource($activity->load('student')),
        ]);
    }

    /**
     * Update activity status (Teacher operation).
     */
    public function updateStatus(UpdateActivityStatusRequest $request, ActivityLog $activity): JsonResponse
    {
        $activity->update([
            'status' => $request->status,
            'review_notes' => $request->review_notes,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Activity status updated successfully',
            'data' => new ActivityResource($activity->load(['student', 'reviewer'])),
        ]);
    }

    /**
     * Remove the specified activity.
     */
    public function destroy(Request $request, ActivityLog $activity): JsonResponse
    {
        if ($request->header('X-Return-No-Content') === 'true') {
            if ($request->query('force') === 'true') {
                $activity->forceDelete();
            } else {
                $activity->delete();
            }

            return response()->json(null, 204);
        }

        $activityId = $activity->id;
        if ($request->query('force') === 'true') {
            $activity->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Activity permanently deleted',
                'data' => [
                    'id' => $activityId,
                ],
            ]);
        }

        $activity->delete();

        return response()->json([
            'success' => true,
            'message' => 'Activity deleted successfully',
            'data' => [
                'id' => $activityId,
                'deleted_at' => now()->toIso8601String(),
            ],
        ]);
    }
}
