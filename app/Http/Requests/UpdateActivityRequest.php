<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateActivityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'student_id' => 'sometimes|required|integer|exists:users,id',
            'title' => 'sometimes|required|string|min:3|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'sometimes|required|string|in:competition,sports,arts,volunteer,other',
            'date' => 'sometimes|required|date_format:Y-m-d|before_or_equal:today',
            'location' => 'nullable|string|max:255',
            'achievement' => 'nullable|string|max:255',
            'points' => 'nullable|integer|min:0|max:100',
            'evidence_url' => 'nullable|url',
            'evidence_file' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,pdf|max:2048',
        ];

        if ($this->isMethod('PUT')) {
            $rules['student_id'] = 'required|integer|exists:users,id';
            $rules['title'] = 'required|string|min:3|max:255';
            $rules['type'] = 'required|string|in:competition,sports,arts,volunteer,other';
            $rules['date'] = 'required|date_format:Y-m-d|before_or_equal:today';
        }

        return $rules;
    }
}
