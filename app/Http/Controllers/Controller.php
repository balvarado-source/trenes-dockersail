<?php

namespace App\Http\Controllers;



/**
 * @OA\Info(
 *     title="API de Posts",
 *     version="1.0.0",
 *     description="API para gestión de posts"
 * )
 * 
 * @OA\Server(
 *     url="http://localhost",
 *     description="Servidor de desarrollo"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
abstract class Controller
{
    //
}
