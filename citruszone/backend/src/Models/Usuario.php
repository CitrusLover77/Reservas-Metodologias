<?php

declare(strict_types=1);

namespace App\Models;

/** DTO de usuario. Nunca expone password_hash hacia afuera (ver toArray). */
final class Usuario
{
    public function __construct(
        public readonly int $id,
        public readonly string $nombre,
        public readonly string $email,
        public readonly string $passwordHash,
        public readonly ?string $telefono,
        public readonly string $role, // 'user' | 'admin' | 'superadmin'
    ) {
    }

    /** @param array<string,mixed> $row fila cruda de la tabla usuarios */
    public static function fromRow(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            nombre: $row['nombre'],
            email: $row['email'],
            passwordHash: $row['password_hash'],
            telefono: $row['telefono'],
            role: $row['role'],
        );
    }

    /** @return array{id:int,nombre:string,email:string,role:string} */
    public function toPublicArray(): array
    {
        return ['id' => $this->id, 'nombre' => $this->nombre, 'email' => $this->email, 'role' => $this->role];
    }
}
