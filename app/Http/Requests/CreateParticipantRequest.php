<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateParticipantRequest extends FormRequest
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
            'name' => 'required|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nome não fornecido!',
            'name.string' => 'Campo nome deve ser um nome válido.',
            'name.max:20' => 'Campo nome deve ter no máximo, 20 caracteres.',
        ];
    }
}
