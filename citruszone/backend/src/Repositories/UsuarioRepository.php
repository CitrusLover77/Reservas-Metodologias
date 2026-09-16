<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Config\Database;
use App\Models\Usuario;
use PDO;

final class UsuarioRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    public function findByEmail(string $email): ?Usuario
    {
        // TODO: SELECT * FROM usuarios WHERE email = :email
        $stmt = $this->pdo->prepare('SELECT * FROM usuarios WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row ? Usuario::fromRow($row) : null;
    }

    public function findById(int $id): ?Usuario
    {
        $stmt = $this->pdo->prepare('SELECT * FROM usuarios WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? Usuario::fromRow($row) : null;
    }

    public function create(string $nombre, string $email, string $passwordHash, ?string $telefono, string $role = 'user'): Usuario
    {
        // TODO: INSERT con RETURNING * (Postgres) para devolver la fila creada
        $stmt = $this->pdo->prepare(
            'INSERT INTO usuarios (nombre, email, password_hash, telefono, role)
             VALUES (:nombre, :email, :password_hash, :telefono, :role)
             RETURNING *'
        );
        $stmt->execute([
            'nombre' => $nombre,
            'email' => $email,
            'password_hash' => $passwordHash,
            'telefono' => $telefono,
            'role' => $role,
        ]);
        return Usuario::fromRow($stmt->fetch());
    }
}
