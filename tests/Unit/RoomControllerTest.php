<?php

namespace Tests\Unit;

use App\Http\Controllers\RoomController;
use App\Http\Requests\CreateRoomRequest;
use App\Models\Room;
use Tests\TestCase;
use Mockery;


class RoomControllerTest extends TestCase
{

    public function test_create_room_error(): void
    {
        $mockRequestData = [
            'title' => 'teste',
            'description' => 'sala teste criada'
        ];

        $mockRequest = Mockery::mock(CreateRoomRequest::class);
        $mockRequest->shouldReceive('all')
            ->andReturn($mockRequestData);

        $mockRoomModel = Mockery::mock(Room::class);
        $mockRoomModel->shouldReceive('createRoom')
            ->with($mockRequestData['title'], $mockRequestData['description'])
            ->andReturn([]);

        $controller = new RoomController($mockRoomModel);

        $response = $controller->createRoom($mockRequest);

        $this->assertEquals(400, $response->status());
        $this->assertEquals((object)['success' => false, 'error' => 'Sala não pode ser criada no momento, tente novamente mais tarde!'], $response->getData());
    }

    public function test_create_room_ok(): void
    {
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

        $mockRequest = Mockery::mock(CreateRoomRequest::class);
        $mockRequest->shouldReceive('all')
            ->andReturn($mockRequestData);

        $mockRoomModel = Mockery::mock(Room::class);
        $mockRoomModel->shouldReceive('createRoom')
            ->with($mockRequestData['title'], $mockRequestData['description'])
            ->andReturn($mockResponseCreate);

        $controller = new RoomController($mockRoomModel);

        $response = $controller->createRoom($mockRequest);

        $this->assertEquals(200, $response->status());
        $this->assertEquals((object)['success' => true, 'data' => (object) $mockResponseCreate], $response->getData());
    }

    public function test_get_all_rooms_error(): void
    {
        $mockReturn = [];

        $mockRoomModel = Mockery::mock(Room::class);
        $mockRoomModel->shouldReceive('getAllRooms')
            ->andReturn($mockReturn);

        $controller = new RoomController($mockRoomModel);

        $response = $controller->getAllRooms();

        $this->assertEquals(404, $response->status());
        $this->assertEquals((object)['success' => false, 'error' => 'Nenhuma sala cadastrada até o momento.'], $response->getData());
    }

    public function test_get_all_rooms_ok(): void
    {
        $mockReturn = [
            (object)[
                'id' => 1,
                'title' => 'teste',
                'description' => 'sala teste criada',
                'created_at' => 'XXXX-XX-XX XX:XX:XX',
                'updated_at' => 'XXXX-XX-XX XX:XX:XX'
            ]
        ];
        $mockRoomModel = Mockery::mock(Room::class);
        $mockRoomModel->shouldReceive('getAllRooms')
            ->andReturn($mockReturn);

        $controller = new RoomController($mockRoomModel);

        $response = $controller->getAllRooms();

        $this->assertEquals(200, $response->status());
        $this->assertEquals((object)['success' => true, 'data' => $mockReturn], $response->getData());
    }
}
