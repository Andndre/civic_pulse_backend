<?php

namespace App\Http\Resources;

use App\Models\PulseResponse;
use App\Models\TestResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaterialResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $studentId = $request->user()?->id;

        $learningPathStatus = [
            'pre_test' => 'available',
            'ebook' => 'locked',
            'post_test' => 'locked',
            'pulse' => 'locked',
        ];

        $preTestScore = null;
        $postTestScore = null;

        if ($studentId) {
            $preTest = TestResponse::where('student_id', $studentId)
                ->where('learning_material_id', $this->id)
                ->where('type', 'pre_test')
                ->first();

            $postTest = TestResponse::where('student_id', $studentId)
                ->where('learning_material_id', $this->id)
                ->where('type', 'post_test')
                ->first();

            $pulse = PulseResponse::where('student_id', $studentId)
                ->whereIn('pulse_instrument_id', $this->pulseInstruments()->pluck('id'))
                ->exists();

            if ($preTest) {
                $learningPathStatus['pre_test'] = 'completed';
                $learningPathStatus['ebook'] = 'available';
                $preTestScore = $preTest->score;
            }

            if ($preTest) { // Setelah pre-test selesai, asumsikan ebook bisa diakses dan langsung lanjut post-test
                $learningPathStatus['ebook'] = 'completed';
                $learningPathStatus['post_test'] = 'available';
            }

            if ($postTest) {
                $learningPathStatus['post_test'] = 'completed';
                $learningPathStatus['pulse'] = 'available';
                $postTestScore = $postTest->score;
            }

            if ($pulse) {
                $learningPathStatus['pulse'] = 'completed';
            }
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'grade' => $this->grade,
            'grade_category' => $this->grade >= 10 ? 'SMA' : 'SMP',
            'grade_level' => $this->grade,
            'file_url' => $this->file_url,
            'created_by' => $this->created_by,
            'creator_name' => $this->creator?->name,
            'status' => $learningPathStatus['pulse'] === 'completed' ? 'completed' : 'available',
            'learning_path_status' => $learningPathStatus,
            'student_score' => [
                'pre_test_score' => $preTestScore,
                'post_score' => $postTestScore,
                'post_test_score' => $postTestScore,
            ],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
