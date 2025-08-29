<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     title="Backend API Rest Reservas Skedway",
 *     version="1.0.0",
 *     description="Documentação da API Rest Reservas Skedway.",
 *     @OA\Contact(
 *         email="bernardodev0809@gmail.com"
 *     )
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000",
 * )
 * 
 *  * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 * )
 */

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
