<?php

namespace Tests\Unit;

use App\Http\Controllers\RoomController;
use App\Http\Requests\CreateRoomRequest;
use App\Models\Room;
use App\Services\CacheService;
use Tests\TestCase;
use Mockery;

class RoomControllerTest extends TestCase
{

    public function test_create_room_error_exists(): void
    {
        $mockCacheService = Mockery::mock(CacheService::class);
        $mockRequest = Mockery::mock(CreateRoomRequest::class);
        $mockRoomModel = Mockery::mock(Room::class);

        $mockRequestData = [
            'title' => 'teste',
            'description' => 'sala teste criada'
        ];

        $mockReturn = [
            'title' => 'teste',
            'description' => 'sala teste criada',
            'id' => 1,
            'created_at' => 'XXXX-XX-XX XX:XX:XX',
            'updated_at' => 'XXXX-XX-XX XX:XX:XX'
        ];

        $mockRequest->shouldReceive('all')
            ->once()
            ->andReturn($mockRequestData);

        $mockRoomModel->shouldReceive('getRoomByTitle')
            ->once()
            ->with($mockRequestData['title'])
            ->andReturn($mockReturn);

        $controller = new RoomController($mockRoomModel, $mockCacheService);

        $response = $controller->createRoom($mockRequest);

        $this->assertEquals(409, $response->status());
        $this->assertEquals((object)['success' => false, 'error' => 'Sala existente!'], $response->getData());
    }

    public function test_create_room_error(): void
    {
        $mockCacheService = Mockery::mock(CacheService::class);
        $mockRequest = Mockery::mock(CreateRoomRequest::class);

        $mockRequestData = [
            'title' => 'teste',
            'description' => 'sala teste criada'
        ];

        $mockRequest->shouldReceive('all')
            ->once()
            ->andReturn($mockRequestData);

        $mockRoomModel = Mockery::mock(Room::class);
        $mockRoomModel->shouldReceive('createRoom')
            ->once()
            ->with($mockRequestData['title'], $mockRequestData['description'])
            ->andReturn([]);

        $mockRoomModel->shouldReceive('getRoomByTitle')
            ->once()
            ->with($mockRequestData['title'])
            ->andReturn([]);

        $controller = new RoomController($mockRoomModel, $mockCacheService);

        $response = $controller->createRoom($mockRequest);

        $this->assertEquals(400, $response->status());
        $this->assertEquals((object)['success' => false, 'error' => 'Sala não pode ser criada no momento, tente novamente mais tarde!'], $response->getData());
    }

    public function test_create_room_ok(): void
    {
        $mockCacheService = Mockery::mock(CacheService::class);
        $mockRequest = Mockery::mock(CreateRoomRequest::class);
        $mockRoomModel = Mockery::mock(Room::class);

        $mockRequestData = [
            'title' => 'teste',
            'description' => 'sala teste criada'
        ];

        $mockResponseCreate = [
            'title' => 'teste',
            'description' => 'sala teste criada',
            'id' => 1,
            'created_at' => 'XXXX-XX-XX XX:XX:XX',
            'updated_at' => 'XXXX-XX-XX XX:XX:XX'
        ];

        $mockCacheService->shouldReceive('increment')
            ->once()
            ->with('rooms', $mockResponseCreate);

        $mockRequest->shouldReceive('all')
            ->once()
            ->andReturn($mockRequestData);

        $mockRoomModel->shouldReceive('createRoom')
            ->once()
            ->with($mockRequestData['title'], $mockRequestData['description'])
            ->andReturn($mockResponseCreate);

        $mockRoomModel->shouldReceive('getRoomByTitle')
            ->once()
            ->with($mockRequestData['title'])
            ->andReturn([]);

        $controller = new RoomController($mockRoomModel, $mockCacheService);

        $response = $controller->createRoom($mockRequest);

        $this->assertEquals(200, $response->status());
        $this->assertEquals((object)['success' => true, 'data' => (object) $mockResponseCreate], $response->getData());
    }

    public function test_get_all_rooms_no_cache_error(): void
    {
        $mockCacheService = Mockery::mock(CacheService::class);
        $mockRoomModel = Mockery::mock(Room::class);

        $mockReturn = [];

        $mockCacheService->shouldReceive('read')
            ->once()
            ->with('rooms')
            ->andReturn([]);

        $mockRoomModel->shouldReceive('getAllRooms')
            ->once()
            ->andReturn($mockReturn);

        $controller = new RoomController($mockRoomModel, $mockCacheService);

        $response = $controller->getAllRooms();

        $this->assertEquals(404, $response->status());
        $this->assertEquals((object)['success' => false, 'error' => 'Nenhuma sala cadastrada até o momento.'], $response->getData());
    }

    public function test_get_all_rooms_no_cache_ok(): void
    {
        $mockCacheService = Mockery::mock(CacheService::class);
        $mockRoomModel = Mockery::mock(Room::class);

        $mockReturn = [
            (object)[
                'id' => 1,
                'title' => 'teste',
                'description' => 'sala teste criada',
                'created_at' => 'XXXX-XX-XX XX:XX:XX',
                'updated_at' => 'XXXX-XX-XX XX:XX:XX'
            ]
        ];
        $mockCacheService->shouldReceive('read')
            ->once()
            ->with('rooms')
            ->andReturn([]);

        $mockCacheService->shouldReceive('create')
            ->once()
            ->with('rooms', $mockReturn, 600);

        $mockRoomModel->shouldReceive('getAllRooms')
            ->once()
            ->andReturn($mockReturn);

        $controller = new RoomController($mockRoomModel, $mockCacheService);

        $response = $controller->getAllRooms();

        $this->assertEquals(200, $response->status());
        $this->assertEquals((object)['success' => true, 'data' => $mockReturn], $response->getData());
    }

    public function test_get_all_rooms_with_cache_ok(): void
    {
        $mockCacheService = Mockery::mock(CacheService::class);
        $mockRoomModel = Mockery::mock(Room::class);

        $mockReturn = [
            (object)[
                'id' => 1,
                'title' => 'teste',
                'description' => 'sala teste criada',
                'created_at' => 'XXXX-XX-XX XX:XX:XX',
                'updated_at' => 'XXXX-XX-XX XX:XX:XX'
            ]
        ];

        $mockCacheService->shouldReceive('read')
            ->once()
            ->with('rooms')
            ->andReturn($mockReturn);

        $controller = new RoomController($mockRoomModel, $mockCacheService);

        $response = $controller->getAllRooms();

        $this->assertEquals(200, $response->status());
        $this->assertEquals((object)['success' => true, 'data' => $mockReturn], $response->getData());
    }
}
