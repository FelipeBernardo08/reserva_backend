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
            $this->cacheService->increment('reservations', $responseCreateReservation);
            return response()->json(['success' => true, 'data' => $responseCreateReservation], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getReservationsComplete(): object
    {
        try {
            $reservations = $this->reservationModel->getReservationsComplete();
            if (empty($reservations)) {
                return response()->json(['success' => false, 'error' => 'Nenhuma reserva cadastrada no momento!'], Response::HTTP_NOT_FOUND);
            }
            return response()->json(['success' => true, 'data' => $reservations], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getReservationByRoomIdComplete(int $roomId): object
    {
        try {
            $reservations = $this->reservationModel->getReservationByRoomIdComplete($roomId);
            if (empty($reservations)) {
                return response()->json(['success' => false, 'error' => 'Nenhuma reserva cadastrada no momento!'], Response::HTTP_NOT_FOUND);
            }
            return response()->json(['success' => true, 'data' => $reservations], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function cancelReservation(CancelReservationRequest $request): object
    {
        try {
            $input = $request->all();
            $responseCancelReservation = $this->reservationModel->cancelReservationById($input['id']);
            if (!$responseCancelReservation) {
                return response()->json(['success' => false, 'error' => 'Reserva não pode ser cancelada, tente novamente mais tarde!'], Response::HTTP_OK);
            }
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
