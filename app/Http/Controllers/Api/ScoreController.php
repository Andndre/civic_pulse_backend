<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Traits\AppliesQueryOptions;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreScoreRequest;
use App\Http\Requests\UpdateScoreRequest;
use App\Http\Resources\ScoreResource;
use App\Models\TestResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScoreController extends Controller
{
    use AppliesQueryOptions;

    /**
     * Display a listing of scores.
     */
    public function index(Request $request): JsonResponse
    {
        $query = TestResponse::query()->with(['student', 'learningMaterial']);

        $paginator = $this->applyQueryOptions(
            $query,
            $request,
            ['learningMaterial.title'], // Search by learning material title
            ['student_id', 'learning_material_id', 'type']
        );

        return $this->respondWithPagination($paginator, ScoreResource::class, 'Scores retrieved successfully');
    }

    /**
     * Store a newly created score.
     */
    public function store(StoreScoreRequest $request): JsonResponse
    {
        $score = TestResponse::create([
            'student_id' => $request->student_id,
            'learning_material_id' => $request->learning_material_id,
            'type' => $request->type,
            'score' => $request->score,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Score created successfully',
            'data' => new ScoreResource($score->load(['student', 'learningMaterial'])),
        ], 201);
    }

    /**
     * Display the specified score.
     */
    public function show(TestResponse $score): JsonResponse
    {
        $score->load(['student', 'learningMaterial']);

        return response()->json([
            'success' => true,
            'message' => 'Score retrieved successfully',
            'data' => new ScoreResource($score),
        ]);
    }

    /**
     * Update the specified score.
     */
    public function update(UpdateScoreRequest $request, TestResponse $score): JsonResponse
    {
        $score->update($request->only(['student_id', 'learning_material_id', 'type', 'score']));

        return response()->json([
            'success' => true,
            'message' => 'Score updated successfully',
            'data' => new ScoreResource($score->load(['student', 'learningMaterial'])),
        ]);
    }

    /**
     * Remove the specified score.
     */
    public function destroy(Request $request, TestResponse $score): JsonResponse
    {
        if ($request->header('X-Return-No-Content') === 'true') {
            $score->delete();

            return response()->json(null, 204);
        }

        $scoreId = $score->id;
        $score->delete();

        return response()->json([
            'success' => true,
            'message' => 'Score deleted successfully',
            'data' => [
                'id' => $scoreId,
            ],
        ]);
    }
}
