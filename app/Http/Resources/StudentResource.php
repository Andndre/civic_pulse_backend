<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $activeClass = $this->classes()->orderBy('class_student.created_at', 'desc')->first();

        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'status' => $this->status,
            'phone' => $this->phone,
            'avatar' => $this->avatar ? asset($this->avatar) : null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];

        // Check if single show or relation-loaded details are requested
        $isDetail = false;
        if ($request->route()) {
            $isDetail = str_ends_with($request->route()->getName() ?? '', '.show')
                || $this->relationLoaded('activityLogs')
                || $this->relationLoaded('testResponses');
        }

        if ($isDetail) {
            $data['address'] = $this->address;
            $data['date_of_birth'] = $this->date_of_birth?->format('Y-m-d');
            $data['gender'] = $this->gender;
            $data['parent_name'] = $this->parent_name;
            $data['parent_phone'] = $this->parent_phone;
            $data['updated_at'] = $this->updated_at?->toIso8601String();

            if ($activeClass) {
                $teacherName = $activeClass->homeroomTeacher ? $activeClass->homeroomTeacher->name : null;
                $data['class'] = [
                    'id' => $activeClass->id,
                    'name' => $activeClass->name,
                    'grade' => $activeClass->grade,
                    'homeroom_teacher' => $teacherName,
                ];
            } else {
                $data['class'] = null;
            }

            // Load nested activities if present
            $data['activities'] = ActivityResource::collection($this->activityLogs);

            // Load nested scores if present
            $data['scores'] = ScoreResource::collection($this->testResponses);
        } else {
            $data['class_id'] = $activeClass ? $activeClass->id : null;
            $data['class_name'] = $activeClass ? $activeClass->name : null;
        }

        return $data;
    }
}
