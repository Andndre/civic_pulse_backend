<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Traits\AppliesQueryOptions;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnecdotalNoteRequest;
use App\Http\Requests\UpdateAnecdotalNoteRequest;
use App\Http\Resources\AnecdotalNoteResource;
use App\Models\AnecdotalNote;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnecdotalNoteController extends Controller
{
    use AppliesQueryOptions;

    /**
     * Display a listing of anecdotal notes for the specified student.
     */
    public function index(Request $request, User $student): JsonResponse
    {
        if ($student->role !== 'student') {
            return response()->json([
                'success' => false,
                'message' => 'Student not found',
                'error_code' => 'RESOURCE_NOT_FOUND',
            ], 404);
        }

        $query = AnecdotalNote::query()->where('student_id', $student->id)->with('teacher');

        $paginator = $this->applyQueryOptions(
            $query,
            $request,
            ['content'],
            ['teacher_id']
        );

        return $this->respondWithPagination($paginator, AnecdotalNoteResource::class, 'Anecdotal notes retrieved successfully');
    }

    /**
     * Store a newly created anecdotal note for the specified student.
     */
    public function store(StoreAnecdotalNoteRequest $request, User $student): JsonResponse
    {
        if ($student->role !== 'student') {
            return response()->json([
                'success' => false,
                'message' => 'Student not found',
                'error_code' => 'RESOURCE_NOT_FOUND',
            ], 404);
        }

        $anecdotalNote = AnecdotalNote::create([
            'student_id' => $student->id,
            'teacher_id' => $request->user()->id,
            'content' => $request->content,
            'category' => $request->category,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Anecdotal note created successfully',
            'data' => new AnecdotalNoteResource($anecdotalNote->load('teacher')),
        ], 201);
    }

    /**
     * Display the specified anecdotal note.
     */
    public function show(User $student, AnecdotalNote $anecdotalNote): JsonResponse
    {
        if ($student->role !== 'student') {
            return response()->json([
                'success' => false,
                'message' => 'Student not found',
                'error_code' => 'RESOURCE_NOT_FOUND',
            ], 404);
        }

        if ($anecdotalNote->student_id !== $student->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anecdotal note not found for this student',
                'error_code' => 'RESOURCE_NOT_FOUND',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Anecdotal note retrieved successfully',
            'data' => new AnecdotalNoteResource($anecdotalNote->load('teacher')),
        ]);
    }

    /**
     * Update the specified anecdotal note.
     */
    public function update(UpdateAnecdotalNoteRequest $request, User $student, AnecdotalNote $anecdotalNote): JsonResponse
    {
        if ($student->role !== 'student') {
            return response()->json([
                'success' => false,
                'message' => 'Student not found',
                'error_code' => 'RESOURCE_NOT_FOUND',
            ], 404);
        }

        if ($anecdotalNote->student_id !== $student->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anecdotal note not found for this student',
                'error_code' => 'RESOURCE_NOT_FOUND',
            ], 404);
        }

        $anecdotalNote->update($request->only(['content']));

        return response()->json([
            'success' => true,
            'message' => 'Anecdotal note updated successfully',
            'data' => new AnecdotalNoteResource($anecdotalNote->load('teacher')),
        ]);
    }

    /**
     * Remove the specified anecdotal note.
     */
    public function destroy(Request $request, User $student, AnecdotalNote $anecdotalNote): JsonResponse
    {
        if ($student->role !== 'student') {
            return response()->json([
                'success' => false,
                'message' => 'Student not found',
                'error_code' => 'RESOURCE_NOT_FOUND',
            ], 404);
        }

        if ($anecdotalNote->student_id !== $student->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anecdotal note not found for this student',
                'error_code' => 'RESOURCE_NOT_FOUND',
            ], 404);
        }

        if ($request->header('X-Return-No-Content') === 'true') {
            $anecdotalNote->delete();

            return response()->json(null, 204);
        }

        $noteId = $anecdotalNote->id;
        $anecdotalNote->delete();

        return response()->json([
            'success' => true,
            'message' => 'Anecdotal note deleted successfully',
            'data' => [
                'id' => $noteId,
            ],
        ]);
    }
}
