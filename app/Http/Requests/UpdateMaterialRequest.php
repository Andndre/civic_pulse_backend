<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMaterialRequest extends FormRequest
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
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'grade' => 'sometimes|required|integer',
            'file_url' => 'sometimes|required|url',
        ];

        if ($this->isMethod('PUT')) {
            $rules['title'] = 'required|string|max:255';
            $rules['description'] = 'required|string';
            $rules['grade'] = 'required|integer';
            $rules['file_url'] = 'required|url';
        }

        return $rules;
    }
}
