<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ParticipantController extends Controller
{
    private $participantModel;

    public function __construct(
        Participant $articipant
    ) {
        $this->participantModel = $articipant;
    }

    public function createParticipant(Request $request): object
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
}
