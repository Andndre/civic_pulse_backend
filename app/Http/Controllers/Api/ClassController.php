<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Traits\AppliesQueryOptions;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClassRequest;
use App\Http\Requests\UpdateClassRequest;
use App\Http\Resources\ClassResource;
use App\Models\SchoolClass;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClassController extends Controller
{
    use AppliesQueryOptions;

    /**
     * Display a listing of classes.
     */
    public function index(Request $request): JsonResponse
    {
        $query = SchoolClass::query();

        $paginator = $this->applyQueryOptions(
            $query,
            $request,
            ['name', 'class_code'],
            ['grade', 'homeroom_teacher_id']
        );

        return $this->respondWithPagination($paginator, ClassResource::class, 'Classes retrieved successfully');
    }

    /**
     * Store a newly created class.
     */
    public function store(StoreClassRequest $request): JsonResponse
    {
        $classCode = $request->class_code;
        if (empty($classCode)) {
            do {
                $classCode = strtoupper(Str::random(8));
            } while (SchoolClass::where('class_code', $classCode)->exists());
        }

        $class = SchoolClass::create([
            'name' => $request->name,
            'grade' => $request->grade,
            'homeroom_teacher_id' => $request->homeroom_teacher_id,
            'class_code' => $classCode,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Class created successfully',
            'data' => new ClassResource($class),
        ], 201);
    }

    /**
     * Display the specified class.
     */
    public function show(SchoolClass $class): JsonResponse
    {
        $class->load(['homeroomTeacher']);

        return response()->json([
            'success' => true,
            'message' => 'Class retrieved successfully',
            'data' => new ClassResource($class),
        ]);
    }

    /**
     * Update the specified class.
     */
    public function update(UpdateClassRequest $request, SchoolClass $class): JsonResponse
    {
        $class->update($request->only(['name', 'grade', 'homeroom_teacher_id', 'class_code']));

        return response()->json([
            'success' => true,
            'message' => 'Class updated successfully',
            'data' => new ClassResource($class->load('homeroomTeacher')),
        ]);
    }

    /**
     * Remove the specified class.
     */
    public function destroy(Request $request, SchoolClass $class): JsonResponse
    {
        // Check dependencies: if there are any students assigned to this class
        $hasStudents = $class->students()->exists();

        if ($hasStudents) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete class with associated students',
                'error_code' => 'RESOURCE_HAS_DEPENDENCIES',
                'dependencies' => ['students'],
            ], 409);
        }

        if ($request->header('X-Return-No-Content') === 'true') {
            if ($request->query('force') === 'true') {
                $class->forceDelete();
            } else {
                $class->delete();
            }

            return response()->json(null, 204);
        }

        $classId = $class->id;
        if ($request->query('force') === 'true') {
            $class->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Class permanently deleted',
                'data' => [
                    'id' => $classId,
                ],
            ]);
        }

        $class->delete();

        return response()->json([
            'success' => true,
            'message' => 'Class deleted successfully',
            'data' => [
                'id' => $classId,
                'deleted_at' => now()->toIso8601String(),
            ],
        ]);
    }
}
