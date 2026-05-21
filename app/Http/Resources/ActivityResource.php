<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
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
            'student_id' => $this->student_id,
            'student_name' => $this->student?->name,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'date' => $this->date?->format('Y-m-d'),
            'location' => $this->location,
            'achievement' => $this->achievement,
            'points' => $this->points,
            'status' => $this->status,
            'evidence_url' => $this->evidence_url,
            'review_notes' => $this->review_notes,
            'reviewed_by' => $this->reviewed_by,
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
