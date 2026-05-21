<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Traits\AppliesQueryOptions;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMaterialRequest;
use App\Http\Requests\UpdateMaterialRequest;
use App\Http\Resources\MaterialResource;
use App\Models\LearningMaterial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LearningMaterialController extends Controller
{
    use AppliesQueryOptions;

    /**
     * Display a listing of materials.
     */
    public function index(Request $request): JsonResponse
    {
        $query = LearningMaterial::query()->with('creator');

        $paginator = $this->applyQueryOptions(
            $query,
            $request,
            ['title', 'description'],
            ['grade', 'created_by']
        );

        return $this->respondWithPagination($paginator, MaterialResource::class, 'Materials retrieved successfully');
    }

    /**
     * Store a newly created material.
     */
    public function store(StoreMaterialRequest $request): JsonResponse
    {
        $material = LearningMaterial::create([
            'title' => $request->title,
            'description' => $request->description,
            'grade' => $request->grade,
            'file_url' => $request->file_url,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Material created successfully',
            'data' => new MaterialResource($material->load('creator')),
        ], 201);
    }

    /**
     * Display the specified material.
     */
    public function show(LearningMaterial $material): JsonResponse
    {
        $material->load('creator');

        return response()->json([
            'success' => true,
            'message' => 'Material retrieved successfully',
            'data' => new MaterialResource($material),
        ]);
    }

    /**
     * Update the specified material.
     */
    public function update(UpdateMaterialRequest $request, LearningMaterial $material): JsonResponse
    {
        $material->update($request->only(['title', 'description', 'grade', 'file_url']));

        return response()->json([
            'success' => true,
            'message' => 'Material updated successfully',
            'data' => new MaterialResource($material->load('creator')),
        ]);
    }

    /**
     * Remove the specified material.
     */
    public function destroy(Request $request, LearningMaterial $material): JsonResponse
    {
        // Dependency checks
        $hasScores = $material->testResponses()->exists();
        $hasInstruments = $material->pulseInstruments()->exists();
        $hasQuestions = $material->questions()->exists();

        if ($hasScores || $hasInstruments || $hasQuestions) {
            $dependencies = [];
            if ($hasScores) {
                $dependencies[] = 'scores';
            }
            if ($hasInstruments) {
                $dependencies[] = 'pulse_instruments';
            }
            if ($hasQuestions) {
                $dependencies[] = 'questions';
            }

            return response()->json([
                'success' => false,
                'message' => 'Cannot delete learning material with associated records',
                'error_code' => 'RESOURCE_HAS_DEPENDENCIES',
                'dependencies' => $dependencies,
            ], 409);
        }

        if ($request->header('X-Return-No-Content') === 'true') {
            if ($request->query('force') === 'true') {
                $material->forceDelete();
            } else {
                $material->delete();
            }

            return response()->json(null, 204);
        }

        $materialId = $material->id;
        if ($request->query('force') === 'true') {
            $material->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Material permanently deleted',
                'data' => [
                    'id' => $materialId,
                ],
            ]);
        }

        $material->delete();

        return response()->json([
            'success' => true,
            'message' => 'Material deleted successfully',
            'data' => [
                'id' => $materialId,
                'deleted_at' => now()->toIso8601String(),
            ],
        ]);
    }
}
