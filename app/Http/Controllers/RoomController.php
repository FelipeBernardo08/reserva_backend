<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateRoomRequest;
use App\Models\Room;
use App\Services\CacheService;
use Exception;
use Illuminate\Http\Response;

class RoomController extends Controller
{
    private $roomModel;
    private $cacheService;

    public function __construct(
        Room $room,
        CacheService $cache
    ) {
        $this->roomModel = $room;
        $this->cacheService = $cache;
    }

    public function createRoom(CreateRoomRequest $request): object
    {
        try {
            $input = $request->all();
            $roomExists = $this->roomModel->getRoomByTitle($input['title']);
            if (!empty($roomExists)) {
                return response()->json(['success' => false, 'error' => 'Sala existente!'], Response::HTTP_CONFLICT);
            }
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
            $roomCache = $this->cacheService->read('rooms');
            $rooms = empty($roomCache) ? $this->roomModel->getAllRooms() : $roomCache;
            if (empty($roomCache)) {
                $this->cacheService->create('rooms', $rooms, 600);
            }
            if (empty($rooms)) {
                return response()->json(['success' => false, 'error' => 'Nenhuma sala cadastrada até o momento.'], Response::HTTP_NOT_FOUND);
            }
            return response()->json(['success' => true, 'data' => $rooms], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
