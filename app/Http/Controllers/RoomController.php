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

    /**
     * @OA\Post(
     *     path="/api/room/create",
     *     summary="Criar uma sala",
     *     security={{"bearerAuth":{}}},
     *     tags={"Sala"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dados do sala",
     *         @OA\JsonContent(
     *             required={"title"},
     *             @OA\Property(property="title", type="string", example="Auditório"),
     *             @OA\Property(property="description", type="string", example="Auditório do primeiro andar")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sala criada!",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example="1"),
     *                 @OA\Property(property="title", type="string", example="Auditório"),
     *                 @OA\Property(property="description", type="string", example="Auditório do primeiro andar"),
     *                 @OA\Property(property="created_at", type="string", example="2025-08-29 00:00:00"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-08-29 00:00:00")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro ao criar sala!",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="error", type="string", example="Sala não pode ser criada no momento, tente novamente mais tarde!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Erro ao criar sala repetidamente!",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="error", type="string", example="Sala existente!")
     *         )
     *     )
     * )
     */
    public function createRoom(CreateRoomRequest $request): object
    {
        try {
            $input = $request->all();
            $roomExists = $this->roomModel->getRoomByTitle($input['title']);
            if (!empty($roomExists)) {
                return response()->json(['success' => false, 'error' => 'Sala existente!'], Response::HTTP_CONFLICT);
            }
            $responseCreateRoom = $this->roomModel->createRoom($input['title'], $input['description'] ?? '');
            if (empty($responseCreateRoom)) {
                return response()->json(['success' => false, 'error' => 'Sala não pode ser criada no momento, tente novamente mais tarde!'], Response::HTTP_BAD_REQUEST);
            }
            $this->cacheService->delete('rooms');
            return response()->json(['success' => true, 'data' => $responseCreateRoom], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/room/read-all",
     *     summary="Ler todas as salas",
     *     security={{"bearerAuth":{}}},
     *     tags={"Sala"},
     *     @OA\Response(
     *         response=200,
     *         description="Salas encontradas!",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example="1"),
     *                      @OA\Property(property="title", type="string", example="Auditório"),
     *                      @OA\Property(property="description", type="string", example="Auditório do primeiro andar"),
     *                      @OA\Property(property="created_at", type="string", example="2025-08-29 00:00:00"),
     *                      @OA\Property(property="updated_at", type="string", example="2025-08-29 00:00:00")
     *                  )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Nenhuma participante encontrado!",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="error", type="string", example="Nenhuma sala cadastrada até o momento.")
     *         )
     *     )
     * )
     */
    public function readAllRooms(): object
    {
        try {
            $roomCache = $this->cacheService->read('rooms');
            $rooms = empty($roomCache) ? $this->roomModel->readAllRooms() : $roomCache;
            if (empty($rooms)) {
                return response()->json(['success' => false, 'error' => 'Nenhuma sala cadastrada até o momento.'], Response::HTTP_NOT_FOUND);
            }
            if (empty($roomCache)) {
                $this->cacheService->create('rooms', $rooms, 600);
            }
            return response()->json(['success' => true, 'data' => $rooms], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/room/read-by-id/{id}",
     *     summary="Ler sala filtrada por id",
     *     security={{"bearerAuth":{}}},
     *     tags={"Sala"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID da sala",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Salas encontradas!",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example="1"),
     *                      @OA\Property(property="title", type="string", example="Auditório"),
     *                      @OA\Property(property="description", type="string", example="Auditório do primeiro andar"),
     *                      @OA\Property(property="created_at", type="string", example="2025-08-29 00:00:00"),
     *                      @OA\Property(property="updated_at", type="string", example="2025-08-29 00:00:00")
     *                  )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Nenhuma participante encontrado!",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="error", type="string", example="Nenhuma sala cadastrada até o momento.")
     *         )
     *     )
     * )
     */
    public function readRoomById(int $id): object
    {
        try {
            $rooms = $this->roomModel->readRoomDetails($id);
            if (empty($rooms)) {
                return response()->json(['success' => false, 'error' => 'Nenhuma sala cadastrada até o momento.'], Response::HTTP_NOT_FOUND);
            }
            return response()->json(['success' => true, 'data' => $rooms], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
