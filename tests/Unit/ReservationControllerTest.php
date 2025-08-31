<?php

namespace Tests\Unit;

use App\Http\Controllers\ReservationController;
use App\Http\Requests\CancelReservationRequest;
use App\Http\Requests\CreateReservationRequest;
use App\Models\Reservation;
use App\Models\ReservationParticipant;
use App\Services\CacheService;
use Tests\TestCase;
use Mockery;

class ReservationControllerTest extends TestCase
{
    public function test_create_reservation_error_exists(): void
    {
        $mockRequest = Mockery::mock(CreateReservationRequest::class);
        $mockCacheService = Mockery::mock(CacheService::class);
        $mockReservationModel = Mockery::mock(Reservation::class);
        $mockReservationParticipantModel = Mockery::mock(ReservationParticipant::class);

        $mockRequestData = [
            'roomId' => 1,
            'dateInit' => '2025-08-29 12:20:00',
            'dateEnd' => '2025-08-29 13:20:00',
            'reservationParticipants' => [
                1,
                2
            ]
        ];

        $mockReturnReservationExists = [
            [
                'id' => 1,
                'roomId' => 1,
                'date_init' => '2025-08-29 12:20:00',
                'date_end' => '2025-08-29 13:20:00',
                'created_at' => '2025-08-29 00:00:00',
                'updated_at' => '2025-08-29 00:00:00'
            ]
        ];

        $mockRequest
            ->shouldReceive('all')
            ->andReturn($mockRequestData);


        $mockReservationModel->shouldReceive('getReservationActiveByRoomId')
            ->with($mockRequestData['roomId'])
            ->andReturn($mockReturnReservationExists);

        $controller = new ReservationController($mockReservationModel, $mockReservationParticipantModel, $mockCacheService);

        $response = $controller->createReservation($mockRequest);

        $this->assertEquals(400, $response->status());
        $this->assertEquals((object)['success' => false, 'error' => 'Sala reservada na data requerida. Por favor, escolha outra data!'], $response->getData());
    }

    public function test_create_reservation_error(): void
    {
        $mockRequest = Mockery::mock(CreateReservationRequest::class);
        $mockCacheService = Mockery::mock(CacheService::class);
        $mockReservationModel = Mockery::mock(Reservation::class);
        $mockReservationParticipantModel = Mockery::mock(ReservationParticipant::class);

        $mockRequestData = [
            'roomId' => 1,
            'dateInit' => '2025-08-29 12:20:00',
            'dateEnd' => '2025-08-29 13:20:00',
            'reservationParticipants' => [
                1,
                2
            ]
        ];

        $mockReturnReservationExists = [];

        $mockRequest->shouldReceive('all')
            ->once()
            ->andReturn($mockRequestData);

        $mockReservationModel->shouldReceive('getReservationActiveByRoomId')
            ->once()
            ->with($mockRequestData['roomId'])
            ->andReturn($mockReturnReservationExists);

        $mockReservationModel->shouldReceive('createReservation')
            ->once()
            ->with($mockRequestData['roomId'], $mockRequestData['dateInit'], $mockRequestData['dateEnd'])
            ->andReturn($mockReturnReservationExists);

        $controller = new ReservationController($mockReservationModel, $mockReservationParticipantModel, $mockCacheService);

        $response = $controller->createReservation($mockRequest);

        $this->assertEquals(400, $response->status());
        $this->assertEquals((object)['success' => false, 'error' => 'Erro ao criar reserva, tente novamente mais tarde!'], $response->getData());
    }

    public function test_create_reservation_ok_with_participants(): void
    {
        $mockRequest = Mockery::mock(CreateReservationRequest::class);
        $mockCacheService = Mockery::mock(CacheService::class);
        $mockReservationModel = Mockery::mock(Reservation::class);
        $mockReservationParticipantModel = Mockery::mock(ReservationParticipant::class);

        $mockRequestData = [
            'roomId' => 1,
            'dateInit' => '2025-08-29 12:20:00',
            'dateEnd' => '2025-08-29 13:20:00',
            'reservationParticipants' => [
                1,
                2
            ]
        ];

        $mockReturnReservationExists = [];

        $mockReturnCreateReservation = [
            'id' => 1,
            'room_id' => 2,
            'date_init' => '',
            'date_end' => '',
            'created_at' => '',
            'updated_at' => ''
        ];

        $mockRequest->shouldReceive('all')
            ->once()
            ->andReturn($mockRequestData);

        $mockCacheService->shouldReceive('delete')
            ->once()
            ->with('reservations');

        $mockReservationModel->shouldReceive('getReservationActiveByRoomId')
            ->once()
            ->with($mockRequestData['roomId'])
            ->andReturn($mockReturnReservationExists);

        $mockReservationModel->shouldReceive('createReservation')
            ->once()
            ->with($mockRequestData['roomId'], $mockRequestData['dateInit'], $mockRequestData['dateEnd'])
            ->andReturn($mockReturnCreateReservation);

        $mockReservationParticipantModel->shouldReceive('createReservationParticipants')
            ->once()
            ->with($mockReturnCreateReservation['id'], $mockRequestData['reservationParticipants']);

        $controller = new ReservationController($mockReservationModel, $mockReservationParticipantModel, $mockCacheService);

        $response = $controller->createReservation($mockRequest);

        $this->assertEquals(200, $response->status());
        $this->assertEquals((object)['success' => true, 'data' => (object)$mockReturnCreateReservation], $response->getData());
    }

    public function test_create_reservation_ok_no_participants(): void
    {
        $mockRequest = Mockery::mock(CreateReservationRequest::class);
        $mockCacheService = Mockery::mock(CacheService::class);
        $mockReservationModel = Mockery::mock(Reservation::class);
        $mockReservationParticipantModel = Mockery::mock(ReservationParticipant::class);

        $mockRequestData = [
            'roomId' => 1,
            'dateInit' => '2025-08-29 12:20:00',
            'dateEnd' => '2025-08-29 13:20:00'
        ];

        $mockReturnReservationExists = [];

        $mockReturnCreateReservation = [
            'id' => 1,
            'room_id' => 2,
            'date_init' => '',
            'date_end' => '',
            'created_at' => '',
            'updated_at' => ''
        ];

        $mockRequest->shouldReceive('all')
            ->once()
            ->andReturn($mockRequestData);

        $mockCacheService->shouldReceive('delete')
            ->once()
            ->with('reservations');

        $mockReservationModel->shouldReceive('getReservationActiveByRoomId')
            ->once()
            ->with($mockRequestData['roomId'])
            ->andReturn($mockReturnReservationExists);

        $mockReservationModel->shouldReceive('createReservation')
            ->once()
            ->with($mockRequestData['roomId'], $mockRequestData['dateInit'], $mockRequestData['dateEnd'])
            ->andReturn($mockReturnCreateReservation);

        $controller = new ReservationController($mockReservationModel, $mockReservationParticipantModel, $mockCacheService);

        $response = $controller->createReservation($mockRequest);

        $this->assertEquals(200, $response->status());
        $this->assertEquals((object)['success' => true, 'data' => (object)$mockReturnCreateReservation], $response->getData());
    }

    public function test_get_reservatoins_complete_error_not(): void
    {
        $mockReservationModel = Mockery::mock(Reservation::class);
        $mockReservationParticipantModel = Mockery::mock(ReservationParticipant::class);
        $mockCacheService = Mockery::mock(CacheService::class);

        $mockCacheService->shouldReceive('read')
            ->once()
            ->with('reservations')
            ->andReturn([]);

        $mockReservationModel->shouldReceive('getReservationsComplete')
            ->once()
            ->andReturn([]);

        $controller = new ReservationController($mockReservationModel, $mockReservationParticipantModel, $mockCacheService);

        $response = $controller->getReservationsComplete();

        $this->assertEquals(404, $response->status());
        $this->assertEquals((object)['success' => false, 'error' => 'Nenhuma reserva cadastrada no momento!'], $response->getData());
    }

    public function test_get_reservatoins_complete_ok_with_cache(): void
    {
        $mockReservationModel = Mockery::mock(Reservation::class);
        $mockReservationParticipantModel = Mockery::mock(ReservationParticipant::class);
        $mockCacheService = Mockery::mock(CacheService::class);

        $mockReturn = [
            (object)[
                'name' => 'teste',
                'created_at' => 'XXXX-XX-XX XX:XX:XX',
                'updated_at' => 'XXXX-XX-XX XX:XX:XX'
            ]
        ];

        $mockCacheService->shouldReceive('read')
            ->once()
            ->with('reservations')
            ->andReturn([]);

        $mockReservationModel->shouldReceive('getReservationsComplete')
            ->once()
            ->andReturn($mockReturn);

        $mockCacheService->shouldReceive('create')
            ->once()
            ->with('reservations', $mockReturn, 600);

        $controller = new ReservationController($mockReservationModel, $mockReservationParticipantModel, $mockCacheService);

        $response = $controller->getReservationsComplete();

        $this->assertEquals(200, $response->status());
        $this->assertEquals((object)['success' => true, 'data' => $mockReturn], $response->getData());
    }

    public function test_get_reservatoins_complete_no_cache(): void
    {
        $mockReservationModel = Mockery::mock(Reservation::class);
        $mockReservationParticipantModel = Mockery::mock(ReservationParticipant::class);
        $mockCacheService = Mockery::mock(CacheService::class);

        $mockReturn = [
            (object)[
                'name' => 'teste',
                'created_at' => 'XXXX-XX-XX XX:XX:XX',
                'updated_at' => 'XXXX-XX-XX XX:XX:XX'
            ]
        ];

        $mockCacheService->shouldReceive('read')
            ->once()
            ->with('reservations')
            ->andReturn($mockReturn);

        $controller = new ReservationController($mockReservationModel, $mockReservationParticipantModel, $mockCacheService);

        $response = $controller->getReservationsComplete();

        $this->assertEquals(200, $response->status());
        $this->assertEquals((object)['success' => true, 'data' => $mockReturn], $response->getData());
    }

    public function test_get_reservations_by_participant_error(): void
    {
        $mockReservationModel = Mockery::mock(Reservation::class);
        $mockReservationParticipantModel = Mockery::mock(ReservationParticipant::class);
        $mockCacheService = Mockery::mock(CacheService::class);

        $participantId = 1;

        $mockReservationModel->shouldReceive('getReservationByParticipantIdComplete')
            ->once()
            ->with($participantId)
            ->andReturn([]);

        $controller = new ReservationController($mockReservationModel, $mockReservationParticipantModel, $mockCacheService);

        $response = $controller->getReservationByParticipantIdComplete($participantId);

        $this->assertEquals(404, $response->status());
        $this->assertEquals((object)['success' => false, 'error' => 'Nenhuma reserva cadastrada no momento!'], $response->getData());
    }

    public function test_get_reservations_by_participant_ok(): void
    {
        $mockReservationModel = Mockery::mock(Reservation::class);
        $mockReservationParticipantModel = Mockery::mock(ReservationParticipant::class);
        $mockCacheService = Mockery::mock(CacheService::class);

        $participantId = 1;

        $mockReturn = [
            (object)[
                'name' => 'teste',
                'created_at' => 'XXXX-XX-XX XX:XX:XX',
                'updated_at' => 'XXXX-XX-XX XX:XX:XX'
            ]
        ];

        $mockReservationModel->shouldReceive('getReservationByParticipantIdComplete')
            ->once()
            ->with($participantId)
            ->andReturn($mockReturn);

        $controller = new ReservationController($mockReservationModel, $mockReservationParticipantModel, $mockCacheService);

        $response = $controller->getReservationByParticipantIdComplete($participantId);

        $this->assertEquals(200, $response->status());
        $this->assertEquals((object)['success' => true, 'data' => $mockReturn], $response->getData());
    }

    public function test_get_reservations_by_room_error(): void
    {
        $mockReservationModel = Mockery::mock(Reservation::class);
        $mockReservationParticipantModel = Mockery::mock(ReservationParticipant::class);
        $mockCacheService = Mockery::mock(CacheService::class);

        $roomId = 1;

        $mockReservationModel->shouldReceive('getReservationByRoomIdComplete')
            ->once()
            ->with($roomId)
            ->andReturn([]);

        $controller = new ReservationController($mockReservationModel, $mockReservationParticipantModel, $mockCacheService);

        $response = $controller->getReservationByRoomIdComplete($roomId);

        $this->assertEquals(404, $response->status());
        $this->assertEquals((object)['success' => false, 'error' => 'Nenhuma reserva cadastrada no momento!'], $response->getData());
    }


    public function test_get_reservations_by_room_ok(): void
    {
        $mockReservationModel = Mockery::mock(Reservation::class);
        $mockReservationParticipantModel = Mockery::mock(ReservationParticipant::class);
        $mockCacheService = Mockery::mock(CacheService::class);

        $roomId = 1;

        $mockReturn = [
            (object)[
                'name' => 'teste',
                'created_at' => 'XXXX-XX-XX XX:XX:XX',
                'updated_at' => 'XXXX-XX-XX XX:XX:XX'
            ]
        ];

        $mockReservationModel->shouldReceive('getReservationByRoomIdComplete')
            ->once()
            ->with($roomId)
            ->andReturn($mockReturn);

        $controller = new ReservationController($mockReservationModel, $mockReservationParticipantModel, $mockCacheService);

        $response = $controller->getReservationByRoomIdComplete($roomId);

        $this->assertEquals(200, $response->status());
        $this->assertEquals((object)['success' => true, 'data' => $mockReturn], $response->getData());
    }

    public function test_cancel_reservation_error(): void
    {
        $mockReservationModel = Mockery::mock(Reservation::class);
        $mockReservationParticipantModel = Mockery::mock(ReservationParticipant::class);
        $mockCacheService = Mockery::mock(CacheService::class);
        $mockRequest = Mockery::mock(CancelReservationRequest::class);

        $mockDataRequest = [
            'id' => 1
        ];

        $mockRequest->shouldReceive('all')
            ->once()
            ->andReturn($mockDataRequest);

        $mockReservationModel->shouldReceive('cancelReservationById')
            ->once()
            ->with($mockDataRequest['id'])
            ->andReturn(false);

        $controller = new ReservationController($mockReservationModel, $mockReservationParticipantModel, $mockCacheService);

        $response = $controller->cancelReservation($mockRequest);

        $this->assertEquals(400, $response->status());
        $this->assertEquals((object)['success' => false, 'error' => 'Reserva não pode ser cancelada, tente novamente mais tarde!'], $response->getData());
    }

    public function test_cancel_reservation_ok(): void
    {
        $mockReservationModel = Mockery::mock(Reservation::class);
        $mockReservationParticipantModel = Mockery::mock(ReservationParticipant::class);
        $mockCacheService = Mockery::mock(CacheService::class);
        $mockRequest = Mockery::mock(CancelReservationRequest::class);

        $mockDataRequest = [
            'id' => 1
        ];

        $mockDataReturn = (object)[
            'message' => 'Reserva cancelada com sucesso!'
        ];

        $mockRequest->shouldReceive('all')
            ->once()
            ->andReturn($mockDataRequest);

        $mockReservationModel->shouldReceive('cancelReservationById')
            ->once()
            ->with($mockDataRequest['id'])
            ->andReturn(true);

        $mockCacheService->shouldReceive('delete')
            ->once()
            ->with('reservations');

        $controller = new ReservationController($mockReservationModel, $mockReservationParticipantModel, $mockCacheService);

        $response = $controller->cancelReservation($mockRequest);

        $this->assertEquals(200, $response->status());
        $this->assertEquals((object)['success' => true, 'data' => $mockDataReturn], $response->getData());
    }
}
