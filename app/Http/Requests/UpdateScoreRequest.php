<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateScoreRequest extends FormRequest
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
            'learning_material_id' => 'sometimes|required|integer|exists:learning_materials,id',
            'type' => 'sometimes|required|string|in:pre_test,post_test',
            'score' => 'sometimes|required|integer|min:0|max:100',
        ];

        if ($this->isMethod('PUT')) {
            $rules['student_id'] = 'required|integer|exists:users,id';
            $rules['learning_material_id'] = 'required|integer|exists:learning_materials,id';
            $rules['type'] = 'required|string|in:pre_test,post_test';
            $rules['score'] = 'required|integer|min:0|max:100';
        }

        return $rules;
    }
}
