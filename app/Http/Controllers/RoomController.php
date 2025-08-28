<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateRoomRequest;
use App\Models\Room;
use Exception;
use Illuminate\Http\Response;

class RoomController extends Controller
{
    private $roomModel;

    public function __construct(
        Room $room
    ) {
        $this->roomModel = $room;
    }

    public function createRoom(CreateRoomRequest $request): object
    {
        try {
            $input = $request->all();
            $responseCreateRoom = $this->roomModel->createRoom($input['title'], $input['description']);
            if (empty($responseCreateRoom)) {
                return response()->json(['success' => false, 'error' => 'Sala não pode ser criada no momento, tente novamente mais tarde!'], Response::HTTP_BAD_REQUEST);
            }
            return response()->json(['success' => true, 'data' => $responseCreateRoom], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getAllRooms(): object
    {
        try {
            $rooms = $this->roomModel->getAllRooms();
            if (empty($rooms)) {
                return response()->json(['success' => false, 'error' => 'Nenhuma sala cadastrada até o momento.'], Response::HTTP_NOT_FOUND);
            }
            return response()->json(['success' => true, 'data' => $rooms], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
