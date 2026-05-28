<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Traits\AppliesQueryOptions;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Http\Resources\StudentResource;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    use AppliesQueryOptions;

    /**
     * Display a listing of students.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query()->where('role', 'student');

        $paginator = $this->applyQueryOptions(
            $query,
            $request,
            ['name', 'email', 'phone', 'address'],
            ['status', 'class_id']
        );

        return $this->respondWithPagination($paginator, StudentResource::class, 'Students retrieved successfully');
    }

    /**
     * Store a newly created student.
     */
    public function store(StoreStudentRequest $request): JsonResponse
    {
        $student = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'student',
            'phone' => $request->phone,
            'address' => $request->address,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'parent_name' => $request->parent_name,
            'parent_phone' => $request->parent_phone,
            'status' => 'active',
        ]);

        if ($request->filled('class_id')) {
            $class = SchoolClass::findOrFail($request->class_id);
            $student->classes()->sync([
                $class->id => ['grade' => $class->grade],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Student created successfully',
            'data' => new StudentResource($student),
        ], 201);
    }

    /**
     * Display the specified student.
     */
    public function show(User $student): JsonResponse
    {
        if ($student->role !== 'student') {
            return response()->json([
                'success' => false,
                'message' => 'Student not found',
                'error_code' => 'RESOURCE_NOT_FOUND',
            ], 404);
        }

        $student->load(['classes.homeroomTeacher', 'activityLogs', 'testResponses.learningMaterial']);

        return response()->json([
            'success' => true,
            'message' => 'Student retrieved successfully',
            'data' => new StudentResource($student),
        ]);
    }

    /**
     * Update the specified student.
     */
    public function update(UpdateStudentRequest $request, User $student): JsonResponse
    {
        if ($student->role !== 'student') {
            return response()->json([
                'success' => false,
                'message' => 'Student not found',
                'error_code' => 'RESOURCE_NOT_FOUND',
            ], 404);
        }

        $data = $request->only([
            'name', 'email', 'phone', 'address', 'date_of_birth', 'gender', 'parent_name', 'parent_phone', 'status',
        ]);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $student->update($data);

        // Handle class sync:
        // In PUT request, if class_id is not provided, we detach classes.
        // In PATCH request, we only sync if class_id is present in the input.
        if ($request->has('class_id')) {
            $classId = $request->class_id;
            if ($classId) {
                $class = SchoolClass::findOrFail($classId);
                $student->classes()->sync([
                    $class->id => ['grade' => $class->grade],
                ]);
            } else {
                $student->classes()->detach();
            }
        } elseif ($request->isMethod('PUT')) {
            $student->classes()->detach();
        }

        return response()->json([
            'success' => true,
            'message' => 'Student updated successfully',
            'data' => new StudentResource($student->load(['classes.homeroomTeacher', 'activityLogs', 'testResponses.learningMaterial'])),
        ]);
    }

    /**
     * Remove the specified student.
     */
    public function destroy(Request $request, User $student): JsonResponse
    {
        if ($student->role !== 'student') {
            return response()->json([
                'success' => false,
                'message' => 'Student not found',
                'error_code' => 'RESOURCE_NOT_FOUND',
            ], 404);
        }

        // Dependency check
        $hasActivities = $student->activityLogs()->exists();
        $hasScores = $student->testResponses()->exists();

        if ($hasActivities || $hasScores) {
            $dependencies = [];
            if ($hasActivities) {
                $dependencies[] = 'activities';
            }
            if ($hasScores) {
                $dependencies[] = 'scores';
            }

            return response()->json([
                'success' => false,
                'message' => 'Cannot delete student with associated activities',
                'error_code' => 'RESOURCE_HAS_DEPENDENCIES',
                'dependencies' => $dependencies,
            ], 409);
        }

        if ($request->header('X-Return-No-Content') === 'true') {
            if ($request->query('force') === 'true') {
                $student->forceDelete();
            } else {
                $student->delete();
            }

            return response()->json(null, 204);
        }

        $studentId = $student->id;
        if ($request->query('force') === 'true') {
            $student->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Student permanently deleted',
                'data' => [
                    'id' => $studentId,
                ],
            ]);
        }

        $student->delete();

        return response()->json([
            'success' => true,
            'message' => 'Student deleted successfully',
            'data' => [
                'id' => $studentId,
                'deleted_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Restore a soft-deleted student.
     */
    public function restore(int $id): JsonResponse
    {
        $student = User::onlyTrashed()->where('role', 'student')->findOrFail($id);
        $student->restore();

        return response()->json([
            'success' => true,
            'message' => 'Student restored successfully',
            'data' => [
                'id' => $student->id,
                'deleted_at' => null,
                'restored_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get student analytics (PULSE scores) for dashboard.
     */
    public function getAnalytics(Request $request): JsonResponse
    {
        $studentId = $request->user()->id;

        // Ambil semua jawaban PULSE milik siswa beserta dimensi dari instrumennya
        $responses = DB::table('pulse_responses')
            ->join('pulse_instruments', 'pulse_responses.pulse_instrument_id', '=', 'pulse_instruments.id')
            ->where('pulse_responses.student_id', $studentId)
            ->select('pulse_instruments.dimension', 'pulse_responses.score')
            ->get();

        // Kelompokkan berdasarkan dimensi (P, U, L, SE)
        $grouped = $responses->groupBy('dimension');

        // Hitung rata-rata skor per dimensi (jika belum ada data, default 0.0)
        $p = isset($grouped['P']) ? $grouped['P']->avg('score') : 0.0;
        $u = isset($grouped['U']) ? $grouped['U']->avg('score') : 0.0;
        $l = isset($grouped['L']) ? $grouped['L']->avg('score') : 0.0;
        $se = isset($grouped['SE']) ? $grouped['SE']->avg('score') : 0.0;

        return response()->json([
            'success' => true,
            'message' => 'Student analytics retrieved successfully',
            'data' => [
                'pulse_scores' => [
                    'participation' => (float) $p,
                    'understanding' => (float) $u,
                    'learning' => (float) $l,
                    'social_engagement' => (float) $se,
                ],
            ],
        ]);
    }
}
