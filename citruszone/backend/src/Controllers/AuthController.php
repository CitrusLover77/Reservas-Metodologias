<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Repositories\UsuarioRepository;
use App\Services\AuthService;
use App\Support\Validator;

final class AuthController
{
    public function __construct(private readonly AuthService $authService = new AuthService())
    {
    }

    public function register(Request $request): Response
    {
        Validator::make($request->all(), [
            'nombre' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8'],
        ])->validate();

        $result = $this->authService->register(
            nombre: $request->input('nombre'),
            email: $request->input('email'),
            password: $request->input('password'),
            telefono: $request->input('telefono'),
        );

        return Response::json($result, 201);
    }

    public function login(Request $request): Response
    {
        Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ])->validate();

        $result = $this->authService->login($request->input('email'), $request->input('password'));

        return Response::json($result);
    }

    public function me(Request $request): Response
    {
        return Response::json(['user' => (array) $request->user]);
    }
}
