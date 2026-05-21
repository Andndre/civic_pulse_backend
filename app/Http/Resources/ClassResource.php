<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'grade' => $this->grade,
            'class_code' => $this->class_code,
            'homeroom_teacher_id' => $this->homeroom_teacher_id,
            'homeroom_teacher' => $this->homeroomTeacher ? [
                'id' => $this->homeroomTeacher->id,
                'name' => $this->homeroomTeacher->name,
            ] : null,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
