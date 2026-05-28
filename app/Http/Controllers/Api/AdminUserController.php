<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Traits\AppliesQueryOptions;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    use AppliesQueryOptions;

    /**
     * Display a listing of all users.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        $paginator = $this->applyQueryOptions(
            $query,
            $request,
            ['name', 'email', 'phone', 'address'],
            ['role', 'status']
        );

        return $this->respondWithPagination($paginator, UserResource::class, 'Users retrieved successfully');
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => ['required', Rule::in(['student', 'teacher', 'admin'])],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date|before_or_equal:today',
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'parent_name' => 'nullable|string|max:255',
            'parent_phone' => 'nullable|string|max:20',
            'status' => ['nullable', Rule::in(['active', 'inactive', 'locked'])],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'parent_name' => $validated['parent_name'] ?? null,
            'parent_phone' => $validated['parent_phone'] ?? null,
            'status' => $validated['status'] ?? 'active',
            'email_verified_at' => now(), // Auto-verify email when created by Admin
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => new UserResource($user),
        ], 201);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'User retrieved successfully',
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'role' => ['nullable', Rule::in(['student', 'teacher', 'admin'])],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date|before_or_equal:today',
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'parent_name' => 'nullable|string|max:255',
            'parent_phone' => 'nullable|string|max:20',
            'status' => ['nullable', Rule::in(['active', 'inactive', 'locked'])],
        ]);

        $data = collect($validated)->except(['password'])->filter()->all();

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Handle explicit nulls if sent (like resetting parent phone/name)
        if ($request->has('phone') && $request->phone === null) {
            $data['phone'] = null;
        }
        if ($request->has('address') && $request->address === null) {
            $data['address'] = null;
        }
        if ($request->has('parent_name') && $request->parent_name === null) {
            $data['parent_name'] = null;
        }
        if ($request->has('parent_phone') && $request->parent_phone === null) {
            $data['parent_phone'] = null;
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        // Don't allow self-deletion
        if ($user->id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete your own account',
                'error_code' => 'FORBIDDEN',
            ], 403);
        }

        // Dependency checks based on role
        if ($user->role === 'teacher') {
            $hasClasses = $user->teachingClasses()->exists();
            if ($hasClasses) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete teacher with associated classes',
                    'error_code' => 'RESOURCE_HAS_DEPENDENCIES',
                    'dependencies' => ['classes'],
                ], 409);
            }
        } elseif ($user->role === 'student') {
            $hasActivities = $user->activityLogs()->exists();
            $hasScores = $user->testResponses()->exists();
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
                    'message' => 'Cannot delete student with associated records',
                    'error_code' => 'RESOURCE_HAS_DEPENDENCIES',
                    'dependencies' => $dependencies,
                ], 409);
            }
        }

        $userId = $user->id;
        if ($request->query('force') === 'true') {
            $user->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'User permanently deleted',
                'data' => [
                    'id' => $userId,
                ],
            ]);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully',
            'data' => [
                'id' => $userId,
                'deleted_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Toggle status of a user (active/inactive/locked).
     */
    public function toggleStatus(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'status' => ['required', Rule::in(['active', 'inactive', 'locked'])],
        ]);

        $user->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'User status updated successfully',
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Manually verify a user's email.
     */
    public function verifyEmail(User $user): JsonResponse
    {
        if ($user->email_verified_at) {
            return response()->json([
                'success' => true,
                'message' => 'Email is already verified',
                'data' => new UserResource($user),
            ]);
        }

        $user->update(['email_verified_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'User email verified successfully',
            'data' => new UserResource($user),
        ]);
    }
}
