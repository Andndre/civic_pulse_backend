<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Traits\AppliesQueryOptions;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMaterialRequest;
use App\Http\Requests\UpdateMaterialRequest;
use App\Http\Resources\MaterialResource;
use App\Models\LearningMaterial;
use App\Models\PulseResponse;
use App\Models\TestResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        $file = $request->file('file');
        $path = $file->store('materials', 'public');
        $fileUrl = 'storage/'.$path;

        $material = LearningMaterial::create([
            'title' => $request->title,
            'description' => $request->description,
            'grade' => $request->grade,
            'file_url' => $fileUrl,
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
        $data = $request->only(['title', 'description', 'grade']);

        if ($request->hasFile('file')) {
            // Delete old file if it exists
            if ($material->file_url) {
                $oldPath = str_replace(asset('storage/'), '', $material->file_url);
                $oldPath = str_replace('storage/', '', $oldPath);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $file = $request->file('file');
            $path = $file->store('materials', 'public');
            $data['file_url'] = 'storage/'.$path;
        }

        $material->update($data);

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

    /**
     * Get pre-test or post-test questions for the material.
     */
    public function getQuestions(Request $request, LearningMaterial $material): JsonResponse
    {
        $type = $request->query('type'); // 'pre' or 'post'
        $dbType = $type === 'pre' ? 'pre_test' : ($type === 'post' ? 'post_test' : null);
        if (! $dbType) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid question type. Must be pre or post.',
            ], 400);
        }
        $questions = $material->questions()
            ->where('type', $dbType)
            ->get()
            ->map(function ($q, $index) {
                $options = is_string($q->options) ? json_decode($q->options, true) : $q->options;

                // Format opsi array numerik menjadi map/objektif A, B, C, D jika perlu
                $formattedOptions = [];
                if (is_array($options)) {
                    $keys = ['A', 'B', 'C', 'D'];
                    foreach ($options as $idx => $val) {
                        $key = $keys[$idx] ?? chr(65 + $idx);
                        $formattedOptions[$key] = $val;
                    }
                }

                return [
                    'id' => $q->id,
                    'material_id' => $q->learning_material_id,
                    'type' => $q->type === 'pre_test' ? 'pre' : 'post',
                    'question_number' => $q->question_number ?? ($index + 1),
                    'content' => $q->question_text,
                    'options' => $formattedOptions ?: $options,
                    'correct_answer' => $q->correct_answer,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $questions,
        ]);
    }

    /**
     * Submit test response and calculate score.
     */
    public function submitTestResponse(Request $request, LearningMaterial $material): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:pre,post',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.answer' => 'required|string',
        ]);
        $type = $validated['type'];
        $dbType = $type === 'pre' ? 'pre_test' : 'post_test';
        $studentId = $request->user()->id;
        $questions = $material->questions()->where('type', $dbType)->get()->keyBy('id');
        $totalQuestions = count($questions);
        if ($totalQuestions === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No questions found for this test type.',
            ], 400);
        }
        $correctCount = 0;
        foreach ($validated['answers'] as $ans) {
            $question = $questions->get($ans['question_id']);
            if ($question && strtoupper($ans['answer']) === strtoupper($question->correct_answer)) {
                $correctCount++;
            }
        }
        $score = ($correctCount / $totalQuestions) * 100;
        // Simpan jawaban tes siswa
        TestResponse::updateOrCreate(
            [
                'student_id' => $studentId,
                'learning_material_id' => $material->id,
                'type' => $dbType,
            ],
            [
                'score' => (int) $score,
            ]
        );
        // Ambil data komparasi skor pre vs post
        $preResponse = TestResponse::where('student_id', $studentId)
            ->where('learning_material_id', $material->id)
            ->where('type', 'pre_test')
            ->first();
        $postResponse = TestResponse::where('student_id', $studentId)
            ->where('learning_material_id', $material->id)
            ->where('type', 'post_test')
            ->first();
        $preScore = $preResponse ? $preResponse->score : 0;
        $postScore = $postResponse ? $postResponse->score : ($type === 'post' ? (int) $score : 0);

        return response()->json([
            'success' => true,
            'message' => 'Test submitted successfully',
            'data' => [
                'score' => (int) $score,
                'comparison' => [
                    'pre_score' => $preScore,
                    'post_score' => $postScore,
                    'improvement' => max(0, $postScore - $preScore),
                ],
            ],
        ]);
    }

    /**
     * Get PULSE statements.
     */
    public function getPulseStatements(LearningMaterial $material): JsonResponse
    {
        $dimensionMap = [
            'P' => 'participation',
            'U' => 'understanding',
            'L' => 'learning',
            'SE' => 'social_engagement',
        ];
        $statements = $material->pulseInstruments()
            ->get()
            ->map(function ($ins, $index) use ($dimensionMap) {
                return [
                    'id' => $ins->id,
                    'material_id' => $ins->learning_material_id,
                    'dimension' => $dimensionMap[$ins->dimension] ?? strtolower($ins->dimension),
                    'statement' => $ins->statement,
                    'order_index' => $index + 1,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $statements,
        ]);
    }

    /**
     * Submit PULSE response.
     */
    public function submitPulseResponse(Request $request, LearningMaterial $material): JsonResponse
    {
        $validated = $request->validate([
            'responses' => 'required|array',
            'responses.*.statement_id' => 'required|exists:pulse_instruments,id',
            'responses.*.score' => 'required|integer|min:1|max:5',
        ]);
        $studentId = $request->user()->id;
        foreach ($validated['responses'] as $resp) {
            PulseResponse::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'pulse_instrument_id' => $resp['statement_id'],
                ],
                [
                    'score' => $resp['score'],
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'PULSE assessment submitted',
            'data' => [
                'material_id' => $material->id,
                'material_status' => 'completed',
            ],
        ]);
    }
}
