<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateReservationRequest extends FormRequest
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
            'roomId' => 'required|integer',
            'dateInit' => 'required|date_format:Y-m-d H:i',
            'dateEnd' => 'required|date_format:Y-m-d H:i|after:dateInit',
            'reservationParticipants' => 'array'
        ];
    }

    public function messages(): array
    {
        return [
            'roomId.required' => 'RoomId não fornecido!',
            'roomId.integer' => 'RoomId deve ser do tipo numérico.',
            'dateInit.required' => 'Data de início não fornecida!',
            'dateInit.date_format' => 'Data de início deve estar no formato: YYYY-MM-DD HH:MM.',
            'dateEnd.required' => 'Data de fim não fornecida!',
            'dateEnd.date_format' => 'Data de fim deve estar no formato: YYYY-MM-DD HH:MM.',
            'dateEnd.after' => 'A data de fim deve ser posterior à data de início.',
            'reservationParticipants.array' => 'Os participantes devem estar em formato de array.',
        ];
    }
}
