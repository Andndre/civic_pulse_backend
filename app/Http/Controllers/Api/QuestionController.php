<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Traits\AppliesQueryOptions;
use App\Http\Controllers\Controller;
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use App\Services\SimpleXlsxReader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class QuestionController extends Controller
{
    use AppliesQueryOptions;

    /**
     * Display a listing of questions.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Question::query();

        $paginator = $this->applyQueryOptions(
            $query,
            $request,
            ['question_text'],
            ['learning_material_id', 'type']
        );

        return $this->respondWithPagination($paginator, QuestionResource::class, 'Questions retrieved successfully');
    }

    /**
     * Store a newly created question.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'learning_material_id' => 'required|exists:learning_materials,id',
            'type' => ['required', Rule::in(['pre_test', 'post_test'])],
            'question_text' => 'required|string',
            'options' => 'required|array|min:2',
            'correct_answer' => 'required|string|in:A,B,C,D,a,b,c,d',
        ]);

        $validated['correct_answer'] = strtoupper($validated['correct_answer']);
        $question = Question::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Question created successfully',
            'data' => new QuestionResource($question),
        ], 201);
    }

    /**
     * Display the specified question.
     */
    public function show(Question $question): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Question retrieved successfully',
            'data' => new QuestionResource($question),
        ]);
    }

    /**
     * Update the specified question.
     */
    public function update(Request $request, Question $question): JsonResponse
    {
        $validated = $request->validate([
            'learning_material_id' => 'nullable|exists:learning_materials,id',
            'type' => ['nullable', Rule::in(['pre_test', 'post_test'])],
            'question_text' => 'nullable|string',
            'options' => 'nullable|array|min:2',
            'correct_answer' => 'nullable|string|in:A,B,C,D,a,b,c,d',
        ]);

        $validated = array_filter($validated);
        if (isset($validated['correct_answer'])) {
            $validated['correct_answer'] = strtoupper($validated['correct_answer']);
        }
        $question->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Question updated successfully',
            'data' => new QuestionResource($question),
        ]);
    }

    /**
     * Remove the specified question.
     */
    public function destroy(Question $question): JsonResponse
    {
        $questionId = $question->id;
        $question->delete();

        return response()->json([
            'success' => true,
            'message' => 'Question deleted successfully',
            'data' => [
                'id' => $questionId,
            ],
        ]);
    }

    /**
     * Import questions from Excel.
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'learning_material_id' => 'required|exists:learning_materials,id',
            'type' => ['required', Rule::in(['pre_test', 'post_test'])],
            'file' => 'required|file|mimes:xlsx|max:51200',
        ]);

        try {
            $rows = SimpleXlsxReader::read($request->file('file')->getRealPath());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca berkas Excel: '.$e->getMessage(),
            ], 422);
        }

        $questionsData = [];
        foreach ($rows as $row) {
            $questionText = $row['question_text'] ?? '';
            if (empty($questionText)) {
                continue;
            }

            $options = [];
            if (isset($row['option_a']) && $row['option_a'] !== '') {
                $options[] = $row['option_a'];
            }
            if (isset($row['option_b']) && $row['option_b'] !== '') {
                $options[] = $row['option_b'];
            }
            if (isset($row['option_c']) && $row['option_c'] !== '') {
                $options[] = $row['option_c'];
            }
            if (isset($row['option_d']) && $row['option_d'] !== '') {
                $options[] = $row['option_d'];
            }

            $rowIndex = $row['__row_index__'] ?? '?';

            if (count($options) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => "Baris {$rowIndex} harus memiliki minimal 2 opsi jawaban.",
                ], 422);
            }

            $correctAnswer = strtoupper($row['correct_answer'] ?? '');
            if (! in_array($correctAnswer, ['A', 'B', 'C', 'D'])) {
                return response()->json([
                    'success' => false,
                    'message' => "Jawaban benar pada baris {$rowIndex} harus berupa A, B, C, atau D.",
                ], 422);
            }

            $questionsData[] = [
                'question_text' => $questionText,
                'options' => $options,
                'correct_answer' => $correctAnswer,
            ];
        }

        if (empty($questionsData)) {
            return response()->json([
                'success' => false,
                'message' => 'Berkas Excel tidak memiliki data pertanyaan yang valid.',
            ], 422);
        }

        $materialId = $request->learning_material_id;
        $type = $request->type;

        DB::transaction(function () use ($materialId, $type, $questionsData) {
            Question::where('learning_material_id', $materialId)
                ->where('type', $type)
                ->delete();

            foreach ($questionsData as $data) {
                Question::create([
                    'learning_material_id' => $materialId,
                    'type' => $type,
                    'question_text' => $data['question_text'],
                    'options' => $data['options'],
                    'correct_answer' => $data['correct_answer'],
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => count($questionsData).' pertanyaan berhasil diimpor.',
        ]);
    }
}
