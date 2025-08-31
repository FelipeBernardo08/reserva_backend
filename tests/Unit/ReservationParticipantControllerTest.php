<?php

namespace Tests\Unit;

use App\Http\Controllers\ReservationParticipantController;
use App\Http\Requests\UpdateReservationParticipantRequest;
use App\Models\Reservation;
use App\Models\ReservationParticipant;
use App\Services\CacheService;
use Tests\TestCase;
use Mockery;

class ReservationParticipantControllerTest extends TestCase
{
    public function test_update_reservation_participant_reservaion_not_found_error(): void
    {
        $mockReservationParticipantModel = Mockery::mock(ReservationParticipant::class);
        $mockReservationModel = Mockery::mock(Reservation::class);
        $mockCacheService = Mockery::mock(CacheService::class);
        $mockRequest = Mockery::mock(UpdateReservationParticipantRequest::class);

        $mockDataRequest = [
            'reservationId' => 1,
            'reservationParticipants' => [
                1,
                2
            ]
        ];

        $mockRequest->shouldReceive('all')
            ->once()
            ->andReturn($mockDataRequest);

        $mockReservationModel->shouldReceive('readReservationById')
            ->once()
            ->with($mockDataRequest['reservationId'])
            ->andReturn([]);

        $controller = new ReservationParticipantController($mockReservationParticipantModel, $mockReservationModel, $mockCacheService);

        $response = $controller->updateReservationParticipant($mockRequest);

        $this->assertEquals(400, $response->status());
        $this->assertEquals((object)['success' => false, 'error' => 'Reserva não encontrada!'], $response->getData());
    }

    public function test_update_reservation_participant_reservaion_canceled_error(): void
    {
        $mockReservationParticipantModel = Mockery::mock(ReservationParticipant::class);
        $mockReservationModel = Mockery::mock(Reservation::class);
        $mockCacheService = Mockery::mock(CacheService::class);
        $mockRequest = Mockery::mock(UpdateReservationParticipantRequest::class);

        $mockDataRequest = [
            'reservationId' => 1,
            'reservationParticipants' => [
                1,
                2
            ]
        ];

        $mockReturnData = [
            [
                'status' => 0
            ]
        ];

        $mockRequest->shouldReceive('all')
            ->once()
            ->andReturn($mockDataRequest);

        $mockReservationModel->shouldReceive('readReservationById')
            ->once()
            ->with($mockDataRequest['reservationId'])
            ->andReturn($mockReturnData);

        $controller = new ReservationParticipantController($mockReservationParticipantModel, $mockReservationModel, $mockCacheService);

        $response = $controller->updateReservationParticipant($mockRequest);

        $this->assertEquals(400, $response->status());
        $this->assertEquals((object)['success' => false, 'error' => 'Não é possível atualizar participantes da reserva cancelada!'], $response->getData());
    }

    public function test_update_reservation_participant_reservaion_error_delete(): void
    {
        $mockReservationParticipantModel = Mockery::mock(ReservationParticipant::class);
        $mockReservationModel = Mockery::mock(Reservation::class);
        $mockCacheService = Mockery::mock(CacheService::class);
        $mockRequest = Mockery::mock(UpdateReservationParticipantRequest::class);

        $mockDataRequest = [
            'reservationId' => 1,
            'reservationParticipants' => [
                1,
                2
            ]
        ];

        $mockReturnData = [
            [
                'status' => 1
            ]
        ];

        $mockRequest->shouldReceive('all')
            ->once()
            ->andReturn($mockDataRequest);

        $mockReservationModel->shouldReceive('readReservationById')
            ->once()
            ->with($mockDataRequest['reservationId'])
            ->andReturn($mockReturnData);

        $mockReservationParticipantModel->shouldReceive('deleteReservationParticipants')
            ->once()
            ->with($mockDataRequest['reservationId'], $mockDataRequest['reservationParticipants'])
            ->andReturn(false);

        $controller = new ReservationParticipantController($mockReservationParticipantModel, $mockReservationModel, $mockCacheService);

        $response = $controller->updateReservationParticipant($mockRequest);

        $this->assertEquals(400, $response->status());
        $this->assertEquals((object)['success' => false, 'error' => 'Erro ao atualizar participante(s) na reserva, tente novamente mais tarde!'], $response->getData());
    }

    public function test_update_reservation_participant_reservaion_error_create(): void
    {
        $mockReservationParticipantModel = Mockery::mock(ReservationParticipant::class);
        $mockReservationModel = Mockery::mock(Reservation::class);
        $mockCacheService = Mockery::mock(CacheService::class);
        $mockRequest = Mockery::mock(UpdateReservationParticipantRequest::class);

        $mockDataRequest = [
            'reservationId' => 1,
            'reservationParticipants' => [
                1,
                2
            ]
        ];

        $mockReturnData = [
            [
                'status' => 1
            ]
        ];

        $mockRequest->shouldReceive('all')
            ->once()
            ->andReturn($mockDataRequest);

        $mockReservationModel->shouldReceive('readReservationById')
            ->once()
            ->with($mockDataRequest['reservationId'])
            ->andReturn($mockReturnData);

        $mockReservationParticipantModel->shouldReceive('deleteReservationParticipants')
            ->once()
            ->with($mockDataRequest['reservationId'], $mockDataRequest['reservationParticipants'])
            ->andReturn(true);

        $mockReservationParticipantModel->shouldReceive('createReservationParticipants')
            ->once()
            ->with($mockDataRequest['reservationId'], $mockDataRequest['reservationParticipants'])
            ->andReturn(false);

        $controller = new ReservationParticipantController($mockReservationParticipantModel, $mockReservationModel, $mockCacheService);

        $response = $controller->updateReservationParticipant($mockRequest);

        $this->assertEquals(400, $response->status());
        $this->assertEquals((object)['success' => false, 'error' => 'Erro ao atualizar participante(s) na reserva, tente novamente mais tarde!'], $response->getData());
    }

    public function test_update_reservation_participant_reservaion_ok(): void
    {
        $mockReservationParticipantModel = Mockery::mock(ReservationParticipant::class);
        $mockReservationModel = Mockery::mock(Reservation::class);
        $mockCacheService = Mockery::mock(CacheService::class);
        $mockRequest = Mockery::mock(UpdateReservationParticipantRequest::class);

        $mockDataRequest = [
            'reservationId' => 1,
            'reservationParticipants' => [
                1,
                2
            ]
        ];

        $mockReturnData = [
            [
                'status' => 1
            ]
        ];

        $mockResponseData = (object)[
            'message' => 'Participantes atualizados com sucesso!'
        ];

        $mockRequest->shouldReceive('all')
            ->once()
            ->andReturn($mockDataRequest);

        $mockReservationModel->shouldReceive('readReservationById')
            ->once()
            ->with($mockDataRequest['reservationId'])
            ->andReturn($mockReturnData);

        $mockReservationParticipantModel->shouldReceive('deleteReservationParticipants')
            ->once()
            ->with($mockDataRequest['reservationId'], $mockDataRequest['reservationParticipants'])
            ->andReturn(true);

        $mockReservationParticipantModel->shouldReceive('createReservationParticipants')
            ->once()
            ->with($mockDataRequest['reservationId'], $mockDataRequest['reservationParticipants'])
            ->andReturn(true);

        $mockCacheService->shouldReceive('delete')
            ->once()
            ->with('reservations');

        $mockCacheService->shouldReceive('delete')
            ->once()
            ->with('rooms');

        $mockCacheService->shouldReceive('delete')
            ->once()
            ->with('participants');

        $controller = new ReservationParticipantController($mockReservationParticipantModel, $mockReservationModel, $mockCacheService);

        $response = $controller->updateReservationParticipant($mockRequest);

        $this->assertEquals(200, $response->status());
        $this->assertEquals((object)['success' => true, 'data' => $mockResponseData], $response->getData());
    }
}
