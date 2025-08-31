<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateParticipantRequest;
use App\Models\Participant;
use App\Services\CacheService;
use Exception;
use Illuminate\Http\Response;

class ParticipantController extends Controller
{
    private $participantModel;
    private $cacheService;

    public function __construct(
        Participant $articipant,
        CacheService $cache
    ) {
        $this->participantModel = $articipant;
        $this->cacheService = $cache;
    }

    /**
     * @OA\Post(
     *     path="/api/participant/create",
     *     summary="Criar um participante",
     *     security={{"bearerAuth":{}}},
     *     tags={"Participante"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dados do participante",
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="João")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Participante criado!",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="João"),
     *                 @OA\Property(property="created_at", type="string", example="2025-08-29 00:00:00"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-08-29 00:00:00")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro ao criar participante!",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="error", type="string", example="Participante não foi criado, tente novamente mais tarde!")
     *         )
     *     )
     * )
     */
    public function createParticipant(CreateParticipantRequest $request): object
    {
        try {
            $input = $request->all();
            $responseCreateParticipant = $this->participantModel->createParticipant($input['name']);
            if (empty($responseCreateParticipant)) {
                return response()->json(['success' => false, 'error' => 'Participante não foi criado, tente novamente mais tarde!'], Response::HTTP_BAD_REQUEST);
            }
            $this->cacheService->delete('participants');
            return response()->json(['success' => true, 'data' => $responseCreateParticipant], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/participant/read-all",
     *     summary="Ler todos participantes",
     *     security={{"bearerAuth":{}}},
     *     tags={"Participante"},
     *     @OA\Response(
     *         response=200,
     *         description="Participantes encontrados!",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example=1),
     *                      @OA\Property(property="name", type="string", example="João"),
     *                      @OA\Property(property="created_at", type="string", example="2025-08-29 00:00:00"),
     *                      @OA\Property(property="updated_at", type="string", example="2025-08-29 00:00:00")
     *                  )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Nenhum participante encontrado!",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="false"),
     *             @OA\Property(property="error", type="string", example="Nenhum participante cadastrado até o momento!")
     *         )
     *     )
     * )
     */
    public function readAllParticipants(): object
    {
        try {
            $participantsCache = $this->cacheService->read('participants');
            $participants = empty($participantsCache) ? $this->participantModel->readAllParticipants() : $participantsCache;
            if (empty($participants)) {
                return response()->json(['success' => false, 'error' => 'Nenhum participante cadastrado até o momento!'], Response::HTTP_NOT_FOUND);
            }
            if (empty($participantsCache)) {
                $this->cacheService->create('participants', $participants, 600);
            }
            return response()->json(['success' => true, 'data' => $participants], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function readParticipantById(int $id): object
    {
        try {
            $participants = $this->participantModel->readParticipantDetails($id);
            if (empty($participants)) {
                return response()->json(['success' => false, 'error' => 'Nenhum participante cadastrado até o momento!'], Response::HTTP_NOT_FOUND);
            }
            return response()->json(['success' => true, 'data' => $participants], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
