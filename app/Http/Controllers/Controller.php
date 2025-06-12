<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="API Mini Blog",
 *     version="1.0.0",
 *     description="Documentation Swagger de l'API Mini Blog",
 *     @OA\Contact(
 *         email="admin@miniblog.test"
 *     )
 * )
 *
 * @OA\Response(
 *     response=401,
 *     description="Non authentifié. Veuillez fournir un token valide."
 * )
 *
 * @OA\Response(
 *     response=403,
 *     description="Accès refusé. L'utilisateur n’a pas les droits nécessaires (ex : admin)."
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
