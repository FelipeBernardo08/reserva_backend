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

    /**
     * @OA\Post(
     *     path="/api/reservation-participant/create",
     *     summary="Criar um participante em uma reserva",
     *     security={{"bearerAuth":{}}},
     *     tags={"Prticipante Reserva"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dados do participante e da reserva",
     *         @OA\JsonContent(
     *             required={"reservationId", "reservationParticipants"},
     *             @OA\Property(property="reservationId", type="integer", example=1),
     *             @OA\Property(
     *                  property="reservationParticipants",
     *                  type="array",
     *                  @OA\Items(type="integer", example=1)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Participante(s) adicionado(s)!",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="message", type="string", example="Participante(s) adicionado(s) com sucesso!")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro ao adicionar.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Erro ao criar participante(s) na reserva, tente novamente mais tarde!")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/api/reservation-participant/remove",
     *     summary="Remove um participante de uma reserva",
     *     security={{"bearerAuth":{}}},
     *     tags={"Prticipante Reserva"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dados do participante e da reserva",
     *         @OA\JsonContent(
     *             required={"reservationId", "reservationParticipants"},
     *             @OA\Property(property="reservationId", type="integer", example=1),
     *             @OA\Property(
     *                  property="reservationParticipants",
     *                  type="array",
     *                  @OA\Items(type="integer", example=1)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Participante(s) removido(s)!",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="message", type="string", example="Participante(s) removido(s) com sucesso!")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro ao remover.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Erro ao remover participante(s) na reserva, tente novamente mais tarde!")
     *         )
     *     )
     * )
     */
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
