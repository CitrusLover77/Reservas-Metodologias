<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Request;
use App\Security\Jwt;
use App\Support\Exceptions\UnauthorizedException;

/** Exige un JWT válido y cuelga los datos del usuario en $request->user. */
final class AuthMiddleware
{
    public function handle(Request $request): Request
    {
        $token = $request->bearerToken();
        if (!$token) {
            throw new UnauthorizedException('Falta el token de autenticación');
        }

        $claims = Jwt::verify($token);
        $request->user = (object) [
            'id' => (int) $claims['id'],
            'nombre' => $claims['nombre'],
            'email' => $claims['email'],
            'role' => $claims['role'],
        ];

        return $request;
    }
}
