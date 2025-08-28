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

    public function createParticipant(CreateParticipantRequest $request): object
    {
        try {
            $input = $request->all();
            $responseCreateParticipant = $this->participantModel->createParticipant($input['name']);
            if (empty($responseCreateParticipant)) {
                return response()->json(['success' => false, 'error' => 'Participante não foi criado, tente novamente mais tarde!'], Response::HTTP_BAD_REQUEST);
            }
            return response()->json(['success' => true, 'data' => $responseCreateParticipant], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getAllParticipants(): object
    {
        try {
            $participantsCache = $this->cacheService->read('participants');
            $participants = empty($participantsCache) ? $this->participantModel->getAllParticipants() : $participantsCache;
            if (empty($participantsCache)) {
                $this->cacheService->create('participants', $participants, 600);
            }
            if (empty($participants)) {
                return response()->json(['success' => false, 'error' => 'Nenhum participante cadastrado até o momento!'], Response::HTTP_NOT_FOUND);
            }
            return response()->json(['success' => true, 'data' => $participants], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
