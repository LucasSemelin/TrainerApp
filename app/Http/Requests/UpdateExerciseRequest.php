<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExerciseRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $exercise = $this->route('exercise');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'alternative_names' => 'nullable|array',
            'alternative_names.*' => 'string|max:255|distinct',
            'description' => 'nullable|string|max:5000',
            'image_path' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del ejercicio es obligatorio',
            'name.unique' => 'Ya existe un ejercicio con ese nombre',
            'name.max' => 'El nombre no puede superar los 255 caracteres',
            'alternative_names.array' => 'Los nombres alternativos deben ser una lista',
            'alternative_names.*.string' => 'Cada nombre alternativo debe ser texto',
            'alternative_names.*.max' => 'Los nombres alternativos no pueden superar los 255 caracteres',
            'alternative_names.*.distinct' => 'Los nombres alternativos no pueden repetirse',
            'description.max' => 'La descripción no puede superar los 5000 caracteres',
            'image_path.url' => 'La URL de la imagen no es válida',
            'image_path.max' => 'La URL de la imagen no puede superar los 500 caracteres',
        ];
    }
}
