<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
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
        return [
            'name' => 'required|string|min:2|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date_format:Y-m-d',
            'gender' => 'nullable|in:male,female',
            'class_id' => 'nullable|integer|exists:classes,id',
            'parent_name' => 'nullable|string|max:255',
            'parent_phone' => 'nullable|string',
        ];
    }
}
