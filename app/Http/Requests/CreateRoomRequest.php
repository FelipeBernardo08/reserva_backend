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
            'title' => 'required|string|max:255',
            'description' => 'string|max:600'
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Nome não fornecido!',
            'title.string' => 'Campo nome deve ser um nome válido.',
            'title.max' => 'Campo nome deve ter no máximo, 255 caracteres.',
            'description.string' => 'Campo descriçao deve ser do tipo texto.',
            'description.max' => 'Campo descriçao deve ter no máximo, 600 caracteres.'
        ];
    }
}
