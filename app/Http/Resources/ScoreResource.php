<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScoreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $score = $this->score;
        if ($score >= 90) {
            $grade = 'A';
        } elseif ($score >= 80) {
            $grade = 'B';
        } elseif ($score >= 70) {
            $grade = 'C';
        } elseif ($score >= 60) {
            $grade = 'D';
        } else {
            $grade = 'E';
        }

        $semester = $this->created_at
            ? $this->created_at->format('Y').'-'.($this->created_at->format('n') <= 6 ? 'Genap' : 'Ganjil')
            : '2026-Ganjil';

        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'student_name' => $this->student?->name,
            'learning_material_id' => $this->learning_material_id,
            'subject' => $this->learningMaterial?->title ?? 'General',
            'type' => $this->type,
            'score' => $this->score,
            'grade' => $grade,
            'semester' => $semester,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
