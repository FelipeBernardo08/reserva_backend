<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateReservationParticipantRequest;
use App\Models\Reservation;
use App\Models\ReservationParticipant;
use App\Services\CacheService;
use Illuminate\Http\Response;
use Exception;

class ReservationParticipantController extends Controller
{
    private $reservationParticipantModel;
    private $reservationModel;
    private $cacheService;

    public function __construct(
        ReservationParticipant $reservationParticipant,
        Reservation $reservation,
        CacheService $cache
    ) {
        $this->reservationParticipantModel = $reservationParticipant;
        $this->reservationModel = $reservation;
        $this->cacheService = $cache;
    }

    /**
     * @OA\Put(
     *     path="/api/reservation-participant/update",
     *     summary="Atualiza os participantes de uma reserva",
     *     security={{"bearerAuth":{}}},
     *     tags={"Participantes da Reserva"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dados da reserva e participante",
     *         @OA\JsonContent(
     *             required={"reservationId", "reservationParticipants"},
     *             @OA\Property(property="reservationId", type="integer", example=1),
     *             @OA\Property(
     *                 property="reservationParticipants",
     *                 type="array",
     *                 @OA\Items(type="integer", example=2)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Participantes atualizados",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="message", type="string", example="Participantes atualizados com sucesso!")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro na atualização",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Reserva não encontrada!")
     *         )
     *     )
     * )
     */
    public function updateReservationParticipant(UpdateReservationParticipantRequest $request): object
    {
        try {
            $input = $request->all();
            $reservation = $this->reservationModel->readReservationById($input['reservationId']);
            if (empty($reservation)) {
                return response()->json(['success' => false, 'error' => 'Reserva não encontrada!'], Response::HTTP_BAD_REQUEST);
            }
            if (!$reservation[0]['status']) {
                return response()->json(['success' => false, 'error' => 'Não é possível atualizar participantes da reserva cancelada!'], Response::HTTP_BAD_REQUEST);
            }
            $repsonseDelete = $this->reservationParticipantModel->deleteReservationParticipants($input['reservationId'], $input['reservationParticipants']);
            if (!$repsonseDelete) {
                return response()->json(['success' => false, 'error' => 'Erro ao atualizar participante(s) na reserva, tente novamente mais tarde!'], Response::HTTP_BAD_REQUEST);
            }
            $responseCreate = $this->reservationParticipantModel->createReservationParticipants($input['reservationId'], $input['reservationParticipants']);
            if (!$responseCreate) {
                return response()->json(['success' => false, 'error' => 'Erro ao atualizar participante(s) na reserva, tente novamente mais tarde!'], Response::HTTP_BAD_REQUEST);
            }
            $this->cacheService->delete('reservations');
            $this->cacheService->delete('rooms');
            $this->cacheService->delete('participants');
            return response()->json(['success' => true, 'data' => ['message' => 'Participantes atualizados com sucesso!']], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => 'Erro interno, contate o suporte'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
