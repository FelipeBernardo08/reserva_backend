<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    private $userModel;

    public function __construct(User $user)
    {
        $this->userModel = $user;
    }

    public function login(Request $request): object
    {
        try {
            $input = $request->all();

            $userExist = $this->userModel->getUserByEmail($input['email']);

            if (empty($userExist)) {
                return response()->json(['success' => false, 'error' => 'Usuário não encontrado!'], Response::HTTP_NOT_FOUND);
            }

            $token = auth()->attempt($input);

            if (!$token) {
                return response()->json(['success' => false, 'error' => 'Não autorizado!'], Response::HTTP_UNAUTHORIZED);
            }

            return response()->json(['success' => true, 'data' => ['token' => $token]], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['succes' => false, 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
