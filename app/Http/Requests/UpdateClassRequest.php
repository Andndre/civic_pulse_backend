<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateClassRequest extends FormRequest
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
        $classId = $this->route('class');
        if (is_object($classId)) {
            $classId = $classId->id;
        }

        $rules = [
            'name' => 'sometimes|required|string|max:255',
            'grade' => 'sometimes|required|integer',
            'homeroom_teacher_id' => 'nullable|integer|exists:users,id',
            'class_code' => 'sometimes|required|string|max:50|unique:classes,class_code,'.$classId,
        ];

        if ($this->isMethod('PUT')) {
            $rules['name'] = 'required|string|max:255';
            $rules['grade'] = 'required|integer';
            $rules['class_code'] = 'required|string|max:50|unique:classes,class_code,'.$classId;
        }

        return $rules;
    }
}
