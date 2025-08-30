<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteReservationParticipantRequest extends FormRequest
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
            'reservationParticipants' => 'required|array'
        ];
    }

    public function messages(): array
    {
        return [
            'roomId.required' => 'RoomId não fornecido!',
            'roomId.integer' => 'RoomId deve ser do tipo numérico.',
            'reservationParticipants.required' => 'Participantes não informados.',
            'reservationParticipants.array' => 'Os participantes devem estar em formato de array.',
        ];
    }
}
