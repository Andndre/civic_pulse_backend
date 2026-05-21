<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
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
        $studentId = $this->route('student');
        if (is_object($studentId)) {
            $studentId = $studentId->id;
        }

        $rules = [
            'name' => 'sometimes|required|string|min:2|max:255',
            'email' => 'sometimes|required|email|unique:users,email,'.$studentId,
            'password' => 'sometimes|required|string|min:8',
            'phone' => 'nullable|string',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date_format:Y-m-d',
            'gender' => 'nullable|in:male,female',
            'class_id' => 'nullable|integer|exists:classes,id',
            'parent_name' => 'nullable|string|max:255',
            'parent_phone' => 'nullable|string',
            'status' => 'sometimes|required|in:active,inactive,locked',
        ];

        // If it's PUT, make the fields required or validated
        if ($this->isMethod('PUT')) {
            $rules['name'] = 'required|string|min:2|max:255';
            $rules['email'] = 'required|email|unique:users,email,'.$studentId;
            $rules['status'] = 'required|in:active,inactive,locked';
        }

        return $rules;
    }
}
