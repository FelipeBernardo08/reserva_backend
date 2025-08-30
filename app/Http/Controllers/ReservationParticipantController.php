<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateReservationParticipantRequest;
use App\Http\Requests\DeleteReservationParticipantRequest;
use App\Models\ReservationParticipant;
use App\Services\CacheService;
use Exception;
use Illuminate\Http\Response;

class ReservationParticipantController extends Controller
{
    private $reservationParticipantModel;
    private $cacheService;

    public function __construct(
        ReservationParticipant $reservationParticipant,
        CacheService $cache
    ) {
        $this->reservationParticipantModel = $reservationParticipant;
        $this->cacheService = $cache;
    }

    public function createReservationParticipant(CreateReservationParticipantRequest $request): object
    {
        try {
            $input = $request->all();
            $responseCreateReservationParticipant = $this->reservationParticipantModel->createReservationParticipants($input['reservationId'], $input['reservationParticipants']);
            if (!$responseCreateReservationParticipant) {
                return response()->json(['success' => false, 'error' => 'Erro ao criar participante(s) na reserva, tente novamente mais tarde!'], Response::HTTP_BAD_REQUEST);
            }
            $this->cacheService->delete('reservations');
            return response()->json(['success' => true, 'data' => ['message' => 'Participante(s) adicionado(s) com sucesso!']], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deleteReservationParticipant(DeleteReservationParticipantRequest $request): object
    {
        try {
            $input = $request->all();
            $responseCreateReservationParticipant = $this->reservationParticipantModel->deleteReservationParticipants($input['reservationId'], $input['reservationParticipants']);
            if (empty($responseCreateReservationParticipant)) {
                return response()->json(['success' => false, 'error' => 'Erro ao remover participante(s) na reserva, tente novamente mais tarde!'], Response::HTTP_BAD_REQUEST);
            }
            $this->cacheService->delete('reservations');
            return response()->json(['success' => true, 'data' => ['message' => 'Participante(s) removido(s) com sucesso!']], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
