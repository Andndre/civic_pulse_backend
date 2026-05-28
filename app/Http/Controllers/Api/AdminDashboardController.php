<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LearningMaterial;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    /**
     * Get system-wide statistics for the admin dashboard.
     */
    public function stats(Request $request): JsonResponse
    {
        $totalTeachers = User::where('role', 'teacher')->count();
        $activeStudents = User::where('role', 'student')->where('status', 'active')->count();
        $inactiveStudents = User::where('role', 'student')->where('status', 'inactive')->count();
        $totalMaterials = LearningMaterial::count();

        return response()->json([
            'success' => true,
            'message' => 'Admin dashboard statistics retrieved successfully',
            'data' => [
                'total_teachers' => $totalTeachers,
                'total_students_active' => $activeStudents,
                'total_students_inactive' => $inactiveStudents,
                'total_materials' => $totalMaterials,
            ],
        ]);
    }
}
