<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateRoomRequest extends FormRequest
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
        return [
            'title' => 'required|string|max:20',
            'description' => 'required|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nome não fornecido!',
            'name.string' => 'Campo nome deve ser um nome válido.',
            'name.max:20' => 'Campo nome deve ter no máximo, 20 caracteres.',
            'description.required' => 'Descricão não fornecida!',
            'description.string' => 'Campo descricão deve ser do tipo texto.',
            'description.max:100' => 'Campo descricão deve ter no máximo, 100 caracteres.',
        ];
    }
}
