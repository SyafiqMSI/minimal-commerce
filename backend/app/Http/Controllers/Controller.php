<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="E-Commerce API",
 *     version="1.0.0",
 *     description="RESTful API for E-Commerce application with authentication and product management",
 *     @OA\Contact(
 *         email="admin@ecommerce.com"
 *     )
 * )
 * @OA\Server(
 *     url="/api",
 *     description="API Server"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
abstract class Controller
{
}
