<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Config\Database;
use App\Models\Bloqueo;
use PDO;

final class BloqueoRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    /** @return list<Bloqueo> bloqueos vigentes para un profesional (o generales) en una fecha */
    public function porProfesionalYFecha(int $profesionalId, string $fecha): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM bloqueos
             WHERE fecha = :fecha AND (profesional_id = :pid OR profesional_id IS NULL)'
        );
        $stmt->execute(['fecha' => $fecha, 'pid' => $profesionalId]);
        return array_map(fn (array $row) => Bloqueo::fromRow($row), $stmt->fetchAll());
    }

    /** @return list<Bloqueo> */
    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM bloqueos ORDER BY fecha, hora_inicio');
        return array_map(fn (array $row) => Bloqueo::fromRow($row), $stmt->fetchAll());
    }

    /** @param array{profesional_id:?int,fecha:string,hora_inicio:string,hora_fin:string,motivo:?string} $data */
    public function create(array $data): Bloqueo
    {
        // TODO: implementar INSERT ... RETURNING *
        $stmt = $this->pdo->prepare(
            'INSERT INTO bloqueos (profesional_id, fecha, hora_inicio, hora_fin, motivo) VALUES (:profesional_id, :fecha, :hora_inicio, :hora_fin, :motivo) RETURNING *'
        );
        $stmt->execute($data);
        return Bloqueo::fromRow($stmt->fetch());
    }

    public function delete(int $id): void
    {
        // TODO: implementar DELETE FROM bloqueos WHERE id = :id
        $stmt = $this->pdo->prepare('DELETE FROM bloqueos WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
