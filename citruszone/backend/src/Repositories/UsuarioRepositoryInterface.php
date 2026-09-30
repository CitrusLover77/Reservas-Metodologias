<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Usuario;

interface UsuarioRepositoryInterface
{
    public function findByEmail(string $email): ?Usuario;

    public function findById(int $id): ?Usuario;

    public function create(string $nombre, string $email, string $passwordHash, ?string $telefono, string $role = 'user'): Usuario;
}