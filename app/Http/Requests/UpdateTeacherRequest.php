<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTeacherRequest extends FormRequest
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
        $teacherId = $this->route('teacher');
        if (is_object($teacherId)) {
            $teacherId = $teacherId->id;
        }

        $rules = [
            'name' => 'sometimes|required|string|min:2|max:255',
            'email' => 'sometimes|required|email|unique:users,email,'.$teacherId,
            'password' => 'sometimes|required|string|min:8',
            'phone' => 'nullable|string',
            'address' => 'nullable|string|max:500',
            'status' => 'sometimes|required|in:active,inactive,locked',
        ];

        if ($this->isMethod('PUT')) {
            $rules['name'] = 'required|string|min:2|max:255';
            $rules['email'] = 'required|email|unique:users,email,'.$teacherId;
            $rules['status'] = 'required|in:active,inactive,locked';
        }

        return $rules;
    }
}
