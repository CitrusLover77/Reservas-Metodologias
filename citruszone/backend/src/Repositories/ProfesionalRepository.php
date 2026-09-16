<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Config\Database;
use App\Models\Profesional;
use PDO;

final class ProfesionalRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    /** @return list<Profesional> */
    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM profesionales WHERE activo = true ORDER BY nombre');
        return array_map(fn (array $row) => Profesional::fromRow($row), $stmt->fetchAll());
    }

    /** @return list<Profesional> profesionales habilitados para un servicio dado */
    public function porServicio(int $servicioId): array
    {
        // TODO: JOIN con servicios_profesionales
        $stmt = $this->pdo->prepare(
            'SELECT p.* FROM profesionales p INNER JOIN servicios_profesionales sp ON sp.profesional_id = p.id WHERE sp.servicio_id = :servicio_id AND p.activo = true ORDER BY p.nombre'
        );
        $stmt->execute(['servicio_id' => $servicioId]);
        return array_map(fn (array $row) => Profesional::fromRow($row), $stmt->fetchAll());
    }

    public function findById(int $id): ?Profesional
    {
        $stmt = $this->pdo->prepare('SELECT * FROM profesionales WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? Profesional::fromRow($row) : null;
    }
}
