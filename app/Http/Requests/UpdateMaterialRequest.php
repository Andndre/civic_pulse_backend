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
            'grade' => 'sometimes|required|integer|between:7,12',
            'file' => 'sometimes|required|file|mimes:pdf|max:51200', // Max 50MB
        ];

        if ($this->isMethod('PUT')) {
            $rules['title'] = 'required|string|max:255';
            $rules['description'] = 'required|string';
            $rules['grade'] = 'required|integer|between:7,12';
            $rules['file'] = 'sometimes|required|file|mimes:pdf|max:51200'; // Make file optional on update PUT too to allow updating other text fields without re-uploading file.
        }

        return $rules;
    }
}
