<?php

namespace App\Http\Controllers;

use App\Http\Requests\CancelReservationRequest;
use App\Http\Requests\CreateReservationRequest;
use App\Models\Reservation;
use App\Models\ReservationParticipant;
use App\Services\CacheService;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReservationController extends Controller
{
    private $reservationModel;
    private $reservationParticipantModel;
    private $cacheService;

    public function __construct(
        Reservation $reservation,
        ReservationParticipant $reservationParticipant,
        CacheService $cache
    ) {
        $this->reservationModel = $reservation;
        $this->reservationParticipantModel = $reservationParticipant;
        $this->cacheService = $cache;
    }

    /**
     * @OA\Post(
     *     path="/api/reservation/create",
     *     summary="Criar uma reserva",
     *     security={{"bearerAuth":{}}},
     *     tags={"Reserva"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dados da reserva",
     *         @OA\JsonContent(
     *             required={"roomId", "dateInit", "dateEnd"},
     *             @OA\Property(property="roomId", type="integer", example=1),
     *             @OA\Property(property="dateInit", type="string", example="2025-08-29 21:58"),
     *             @OA\Property(property="dateEnd", type="string", example="2025-08-29 22:58")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reserva criada!",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="room_id", type="string", example=1),
     *                 @OA\Property(property="dateInit", type="string", example="2025-08-29 22:01:00"),
     *                 @OA\Property(property="dateEnd", type="string", example="2025-08-29 23:01:00"),
     *                 @OA\Property(property="created_at", type="string", example="2025-08-29 00:00:00"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-08-29 00:00:00")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Período de reserva indisponível!",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Sala reservada na data requerida. Por favor, escolha outra data!")
     *         )
     *     )
     * )
     */
    public function createReservation(CreateReservationRequest $request): object
    {
        try {
            $input = $request->all();
            if (!$this->reservationValid($input['roomId'], $input['dateInit'], $input['dateEnd'])) {
                return response()->json(['success' => false, 'error' => 'Sala reservada na data requerida. Por favor, escolha outra data!'], Response::HTTP_BAD_REQUEST);
            }
            $responseCreateReservation = $this->reservationModel->createReservation($input['roomId'], $input['dateInit'], $input['dateEnd']);
            if (empty($responseCreateReservation)) {
                return response()->json(['success' => false, 'error' => 'Erro ao criar reserva, tente novamente mais tarde!'], Response::HTTP_BAD_REQUEST);
            }
            if (!empty($input['reservationParticipants'])) {
                $this->reservationParticipantModel->createReservationParticipants($responseCreateReservation['id'], $input['reservationParticipants']);
            }
            $this->cacheService->delete('reservations');
            return response()->json(['success' => true, 'data' => $responseCreateReservation], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/reservation/read-complete",
     *     summary="Ler todas reservas",
     *     security={{"bearerAuth":{}}},
     *     tags={"Reserva"},
     *     @OA\Response(
     *         response=200,
     *         description="Reservas encontradas!",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="room_id", type="integer", example=1),
     *                     @OA\Property(property="date_init", type="string", example="2025-08-30 00:00:00"),
     *                     @OA\Property(property="date_end", type="string", example="2025-09-01 00:00:00"),
     *                     @OA\Property(property="created_at", type="string", example="2025-08-29 00:00:00"),
     *                     @OA\Property(property="updated_at", type="string", example="2025-08-29 00:00:00"),
     *                     @OA\Property(
     *                         property="room",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="title", type="string", example="Sala de Reunião Marte"),
     *                         @OA\Property(property="description", type="string", example="")
     *                     ),
     *                     @OA\Property(
     *                         property="reservation_participants",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="reservation_id", type="integer", example=1),
     *                             @OA\Property(property="participant_id", type="integer", example=1),
     *                             @OA\Property(
     *                                 property="participant",
     *                                 type="object",
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="name", type="string", example="João")
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Nenhuma reserva encontrada!",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Nenhuma reserva cadastrada no momento!")
     *         )
     *     )
     * )
     */

    public function getReservationsComplete(): object
    {
        try {
            $reservationsCache = $this->cacheService->read('reservations');
            $reservations = empty($reservationsCache) ? $this->reservationModel->getReservationsComplete() : $reservationsCache;
            if (empty($reservations)) {
                return response()->json(['success' => false, 'error' => 'Nenhuma reserva cadastrada no momento!'], Response::HTTP_NOT_FOUND);
            }
            if (empty($reservationsCache)) {
                $this->cacheService->create('reservations', $reservations, 600);
            }
            return response()->json(['success' => true, 'data' => $reservations], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/reservation/read-complete-by-room/{id}",
     *     summary="Ler todas reservas filtradas por sala",
     *     security={{"bearerAuth":{}}},
     *     tags={"Reserva"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID da reserva",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reservas encontradas!",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="room_id", type="integer", example=1),
     *                     @OA\Property(property="date_init", type="string", example="2025-08-30 00:00:00"),
     *                     @OA\Property(property="date_end", type="string", example="2025-09-01 00:00:00"),
     *                     @OA\Property(property="created_at", type="string", example="2025-08-29 00:00:00"),
     *                     @OA\Property(property="updated_at", type="string", example="2025-08-29 00:00:00"),
     *                     @OA\Property(
     *                         property="room",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="title", type="string", example="Sala de Reunião Marte"),
     *                         @OA\Property(property="description", type="string", example="")
     *                     ),
     *                     @OA\Property(
     *                         property="reservation_participants",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="reservation_id", type="integer", example=1),
     *                             @OA\Property(property="participant_id", type="integer", example=1),
     *                             @OA\Property(
     *                                 property="participant",
     *                                 type="object",
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="name", type="string", example="João")
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Nenhuma reserva encontrada!",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Nenhuma reserva cadastrada no momento!")
     *         )
     *     )
     * )
     */

    public function getReservationByRoomIdComplete(int $roomId): object
    {
        try {
            $reservationsCache = $this->cacheService->read('reservations');
            $reservations = empty($reservationsCache) ? $this->reservationModel->getReservationByRoomIdComplete($roomId) : $reservationsCache;
            if (empty($reservations)) {
                return response()->json(['success' => false, 'error' => 'Nenhuma reserva cadastrada no momento!'], Response::HTTP_NOT_FOUND);
            }
            if (empty($reservationsCache)) {
                $this->cacheService->create('reservations', $reservations, 600);
            }
            return response()->json(['success' => true, 'data' => $reservations], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/reservation/read-complete-by-participant/{id}",
     *     summary="Ler todas reservas filtradas por participante",
     *     security={{"bearerAuth":{}}},
     *     tags={"Reserva"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID da reserva",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reservas encontradas!",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="room_id", type="integer", example=1),
     *                     @OA\Property(property="date_init", type="string", example="2025-08-30 00:00:00"),
     *                     @OA\Property(property="date_end", type="string", example="2025-09-01 00:00:00"),
     *                     @OA\Property(property="created_at", type="string", example="2025-08-29 00:00:00"),
     *                     @OA\Property(property="updated_at", type="string", example="2025-08-29 00:00:00"),
     *                     @OA\Property(
     *                         property="room",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="title", type="string", example="Sala de Reunião Marte"),
     *                         @OA\Property(property="description", type="string", example="")
     *                     ),
     *                     @OA\Property(
     *                         property="reservation_participants",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="reservation_id", type="integer", example=1),
     *                             @OA\Property(property="participant_id", type="integer", example=1),
     *                             @OA\Property(
     *                                 property="participant",
     *                                 type="object",
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="name", type="string", example="João")
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Nenhuma reserva encontrada!",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Nenhuma reserva cadastrada no momento!")
     *         )
     *     )
     * )
     */

    public function getReservationByParticipantIdComplete(int $participantId): object
    {
        try {
            $reservationsCache = $this->cacheService->read('reservations');
            $reservations = empty($reservationsCache) ? $this->reservationModel->getReservationByParticipantIdComplete($participantId) : $reservationsCache;
            if (empty($reservations)) {
                return response()->json(['success' => false, 'error' => 'Nenhuma reserva cadastrada no momento!'], Response::HTTP_NOT_FOUND);
            }
            if (empty($reservationsCache)) {
                $this->cacheService->create('reservations', $reservations, 600);
            }
            return response()->json(['success' => true, 'data' => $reservations], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/reservation/cancel",
     *     summary="Cancelar uma reserva",
     *     security={{"bearerAuth":{}}},
     *     tags={"Reserva"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Id da reserva",
     *         @OA\JsonContent(
     *             required={"id"},
     *             @OA\Property(property="id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reserva cancelada!",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="message", type="string", example="Reserva cancelada com sucesso!")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro ao cancelar reserva!",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Reserva não pode ser cancelada, tente novamente mais tarde!")
     *         )
     *     )
     * )
     */

    public function cancelReservation(CancelReservationRequest $request): object
    {
        try {
            $input = $request->all();
            $responseCancelReservation = $this->reservationModel->cancelReservationById($input['id']);
            if (!$responseCancelReservation) {
                return response()->json(['success' => false, 'error' => 'Reserva não pode ser cancelada, tente novamente mais tarde!'], Response::HTTP_BAD_REQUEST);
            }
            $this->cacheService->delete('reservations');
            return response()->json(['success' => true, 'data' => ['message' => 'Reserva cancelada com sucesso!']], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function reservationValid(int $roomId, string $dateInit, string $dateEnd): bool
    {
        $reservations = $this->reservationModel->getReservationActiveByRoomId($roomId);

        if (empty($reservations)) {
            return true;
        }

        foreach ($reservations as $reservation) {
            $dateInitReservation = new DateTime($dateInit);
            $dateEndReservation = new DateTime($dateEnd);

            $dateInitAtual = new DateTime($reservation['date_init']);
            $dateEndAtual = new DateTime($reservation['date_end']);

            if (($dateInitReservation <= $dateEndAtual) && ($dateEndReservation >= $dateInitAtual)) {
                return false;
            }
        }

        return true;
    }
}
