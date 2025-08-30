<?php

namespace Tests\Unit;

use App\Http\Controllers\ReservationParticipantController;
use App\Http\Requests\CreateReservationParticipantRequest;
use App\Http\Requests\DeleteReservationParticipantRequest;
use App\Models\ReservationParticipant;
use App\Services\CacheService;
use Tests\TestCase;
use Mockery;

class ReservationParticipantControllerTest extends TestCase
{
    public function test_create_reservation_participant_error(): void
    {
        $mockReservationParticipantModel = Mockery::mock(ReservationParticipant::class);
        $mockCacheService = Mockery::mock(CacheService::class);
        $mockRequest = Mockery::mock(CreateReservationParticipantRequest::class);

        $mockDataRequest = [
            "reservationId" => 1,
            "reservationParticipants" => [
                1
            ]
        ];

        $mockRequest->shouldReceive('all')
            ->once()
            ->andReturn($mockDataRequest);

        $mockReservationParticipantModel->shouldReceive('createReservationParticipants')
            ->once()
            ->with($mockDataRequest['reservationId'], $mockDataRequest['reservationParticipants'])
            ->andReturn(false);

        $controller = new ReservationParticipantController($mockReservationParticipantModel, $mockCacheService);

        $response = $controller->createReservationParticipant($mockRequest);

        $this->assertEquals(400, $response->status());
        $this->assertEquals((object)['success' => false, 'error' => 'Erro ao criar participante(s) na reserva, tente novamente mais tarde!'], $response->getData());
    }

    public function test_create_reservation_participant_ok(): void
    {
        $mockReservationParticipantModel = Mockery::mock(ReservationParticipant::class);
        $mockCacheService = Mockery::mock(CacheService::class);
        $mockRequest = Mockery::mock(CreateReservationParticipantRequest::class);

        $mockDataRequest = [
            "reservationId" => 1,
            "reservationParticipants" => [
                1
            ]
        ];

        $mockDataReturn = (object)[
            'message' => 'Participante(s) adicionado(s) com sucesso!'
        ];

        $mockRequest->shouldReceive('all')
            ->once()
            ->andReturn($mockDataRequest);

        $mockReservationParticipantModel->shouldReceive('createReservationParticipants')
            ->once()
            ->with($mockDataRequest['reservationId'], $mockDataRequest['reservationParticipants'])
            ->andReturn(true);

        $mockCacheService->shouldReceive('delete')
            ->once()
            ->with('reservations');

        $controller = new ReservationParticipantController($mockReservationParticipantModel, $mockCacheService);

        $response = $controller->createReservationParticipant($mockRequest);

        $this->assertEquals(200, $response->status());
        $this->assertEquals((object)['success' => true, 'data' => $mockDataReturn], $response->getData());
    }

    public function test_cancel_reservation_participant_error(): void
    {
        $mockReservationParticipantModel = Mockery::mock(ReservationParticipant::class);
        $mockCacheService = Mockery::mock(CacheService::class);
        $mockRequest = Mockery::mock(DeleteReservationParticipantRequest::class);

        $mockDataRequest = [
            "reservationId" => 1,
            "reservationParticipants" => [
                1
            ]
        ];

        $mockRequest->shouldReceive('all')
            ->once()
            ->andReturn($mockDataRequest);

        $mockReservationParticipantModel->shouldReceive('deleteReservationParticipants')
            ->once()
            ->with($mockDataRequest['reservationId'], $mockDataRequest['reservationParticipants'])
            ->andReturn(false);

        $controller = new ReservationParticipantController($mockReservationParticipantModel, $mockCacheService);

        $response = $controller->deleteReservationParticipant($mockRequest);

        $this->assertEquals(400, $response->status());
        $this->assertEquals((object)['success' => false, 'error' => 'Erro ao remover participante(s) na reserva, tente novamente mais tarde!'], $response->getData());
    }

    public function test_cancel_reservation_participant_ok(): void
    {
        $mockReservationParticipantModel = Mockery::mock(ReservationParticipant::class);
        $mockCacheService = Mockery::mock(CacheService::class);
        $mockRequest = Mockery::mock(DeleteReservationParticipantRequest::class);

        $mockDataRequest = [
            "reservationId" => 1,
            "reservationParticipants" => [
                1
            ]
        ];

        $mockDataReturn = (object)[
            'message' => 'Participante(s) removido(s) com sucesso!'
        ];

        $mockRequest->shouldReceive('all')
            ->once()
            ->andReturn($mockDataRequest);

        $mockReservationParticipantModel->shouldReceive('deleteReservationParticipants')
            ->once()
            ->with($mockDataRequest['reservationId'], $mockDataRequest['reservationParticipants'])
            ->andReturn(true);

        $mockCacheService->shouldReceive('delete')
            ->once()
            ->with('reservations');

        $controller = new ReservationParticipantController($mockReservationParticipantModel, $mockCacheService);

        $response = $controller->deleteReservationParticipant($mockRequest);

        $this->assertEquals(200, $response->status());
        $this->assertEquals((object)['success' => true, 'data' => $mockDataReturn], $response->getData());
    }
}
