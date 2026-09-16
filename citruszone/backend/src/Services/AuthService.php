<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UsuarioRepository;
use App\Security\Jwt;
use App\Support\Exceptions\UnauthorizedException;
use App\Support\Exceptions\ValidationException;

final class AuthService
{
    public function __construct(private readonly UsuarioRepository $usuarios = new UsuarioRepository())
    {
    }

    /** @return array{token:string,user:array{id:int,nombre:string,email:string,role:string}} */
    public function register(string $nombre, string $email, string $password, ?string $telefono): array
    {
        if ($this->usuarios->findByEmail($email) !== null) {
            throw new ValidationException('Ese email ya está registrado', ['email' => ['Ya existe una cuenta con este email']]);
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $usuario = $this->usuarios->create($nombre, $email, $hash, $telefono, role: 'user');

        return $this->issueSession($usuario->id, $usuario->nombre, $usuario->email, $usuario->role);
    }

    /** @return array{token:string,user:array{id:int,nombre:string,email:string,role:string}} */
    public function login(string $email, string $password): array
    {
        $usuario = $this->usuarios->findByEmail($email);
        if ($usuario === null || !password_verify($password, $usuario->passwordHash)) {
            throw new UnauthorizedException('Email o password incorrectos');
        }

        return $this->issueSession($usuario->id, $usuario->nombre, $usuario->email, $usuario->role);
    }

    /** @return array{token:string,user:array{id:int,nombre:string,email:string,role:string}} */
    private function issueSession(int $id, string $nombre, string $email, string $role): array
    {
        $claims = ['id' => $id, 'nombre' => $nombre, 'email' => $email, 'role' => $role];
        return ['token' => Jwt::issue($claims), 'user' => $claims];
    }
}
