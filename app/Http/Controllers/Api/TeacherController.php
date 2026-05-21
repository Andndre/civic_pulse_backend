<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Traits\AppliesQueryOptions;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Http\Resources\TeacherResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    use AppliesQueryOptions;

    /**
     * Display a listing of teachers.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query()->where('role', 'teacher');

        $paginator = $this->applyQueryOptions(
            $query,
            $request,
            ['name', 'email', 'phone', 'address'],
            ['status']
        );

        return $this->respondWithPagination($paginator, TeacherResource::class, 'Teachers retrieved successfully');
    }

    /**
     * Store a newly created teacher.
     */
    public function store(StoreTeacherRequest $request): JsonResponse
    {
        $teacher = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'teacher',
            'phone' => $request->phone,
            'address' => $request->address,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Teacher created successfully',
            'data' => new TeacherResource($teacher),
        ], 201);
    }

    /**
     * Display the specified teacher.
     */
    public function show(User $teacher): JsonResponse
    {
        if ($teacher->role !== 'teacher') {
            return response()->json([
                'success' => false,
                'message' => 'Teacher not found',
                'error_code' => 'RESOURCE_NOT_FOUND',
            ], 404);
        }

        $teacher->load(['teachingClasses']);

        return response()->json([
            'success' => true,
            'message' => 'Teacher retrieved successfully',
            'data' => new TeacherResource($teacher),
        ]);
    }

    /**
     * Update the specified teacher.
     */
    public function update(UpdateTeacherRequest $request, User $teacher): JsonResponse
    {
        if ($teacher->role !== 'teacher') {
            return response()->json([
                'success' => false,
                'message' => 'Teacher not found',
                'error_code' => 'RESOURCE_NOT_FOUND',
            ], 404);
        }

        $data = $request->only([
            'name', 'email', 'phone', 'address', 'date_of_birth', 'gender', 'status',
        ]);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $teacher->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Teacher updated successfully',
            'data' => new TeacherResource($teacher->load('teachingClasses')),
        ]);
    }

    /**
     * Remove the specified teacher.
     */
    public function destroy(Request $request, User $teacher): JsonResponse
    {
        if ($teacher->role !== 'teacher') {
            return response()->json([
                'success' => false,
                'message' => 'Teacher not found',
                'error_code' => 'RESOURCE_NOT_FOUND',
            ], 404);
        }

        // Dependency check: check if teacher is assigned to any classes as homeroom teacher
        $hasClasses = $teacher->teachingClasses()->exists();

        if ($hasClasses) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete teacher with associated classes',
                'error_code' => 'RESOURCE_HAS_DEPENDENCIES',
                'dependencies' => ['classes'],
            ], 409);
        }

        if ($request->header('X-Return-No-Content') === 'true') {
            if ($request->query('force') === 'true') {
                $teacher->forceDelete();
            } else {
                $teacher->delete();
            }

            return response()->json(null, 204);
        }

        $teacherId = $teacher->id;
        if ($request->query('force') === 'true') {
            $teacher->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Teacher permanently deleted',
                'data' => [
                    'id' => $teacherId,
                ],
            ]);
        }

        $teacher->delete();

        return response()->json([
            'success' => true,
            'message' => 'Teacher deleted successfully',
            'data' => [
                'id' => $teacherId,
                'deleted_at' => now()->toIso8601String(),
            ],
        ]);
    }
}
