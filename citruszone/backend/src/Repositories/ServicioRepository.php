<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Config\Database;
use App\Models\Servicio;
use PDO;

final class ServicioRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    /** @return list<Servicio> */
    public function all(bool $onlyActive = false): array
    {
        // TODO: agregar paginación si el catálogo crece
        $sql = 'SELECT * FROM servicios' . ($onlyActive ? ' WHERE activo = true' : '') . ' ORDER BY nombre';
        $stmt = $this->pdo->query($sql);
        return array_map(fn (array $row) => Servicio::fromRow($row), $stmt->fetchAll());
    }

    public function findById(int $id): ?Servicio
    {
        $stmt = $this->pdo->prepare('SELECT * FROM servicios WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? Servicio::fromRow($row) : null;
    }

    /** @param array{nombre:string,duracion_minutos:int,precio:float} $data */
    public function create(array $data): Servicio
    {
        // TODO: implementar INSERT ... RETURNING *
        $stmt = $this->pdo->prepare(
            'INSERT INTO servicios (nombre, duracion_minutos, precio, activo)
             VALUES (:nombre, :duracion_minutos, :precio, :activo) RETURNING *'
        );
        $stmt->execute([
            'nombre' => $data['nombre'],
            'duracion_minutos' => $data['duracion_minutos'],
            'precio' => $data['precio'],
            'activo' => $data['activo'] ?? true,
        ]);
        return Servicio::fromRow($stmt->fetch());
    }

    /** @param array<string,mixed> $data */
    public function update(int $id, array $data): Servicio
    {
        // TODO: implementar UPDATE dinámico (nombre/duracion/precio/activo)
        $allowed = ['nombre', 'duracion_minutos', 'precio', 'activo'];
        $fields = array_intersect_key($data, array_flip($allowed));
        if (!$fields) throw new \InvalidArgumentException('No hay campos para actualizar');
        $sets = implode(', ', array_map(fn (string $field) => "$field = :$field", array_keys($fields)));
        $fields['id'] = $id;
        $stmt = $this->pdo->prepare("UPDATE servicios SET $sets WHERE id = :id RETURNING *");
        $stmt->execute($fields);
        $row = $stmt->fetch();
        if (!$row) throw new \RuntimeException('Servicio no encontrado');
        return Servicio::fromRow($row);
    }

    /** IDs de profesionales habilitados para este servicio (tabla puente servicios_profesionales). */
    public function profesionalesHabilitados(int $servicioId): array
    {
        // TODO: JOIN con servicios_profesionales
        $stmt = $this->pdo->prepare('SELECT profesional_id FROM servicios_profesionales WHERE servicio_id = :servicio_id');
        $stmt->execute(['servicio_id' => $servicioId]);
        return array_map('intval', array_column($stmt->fetchAll(), 'profesional_id'));
    }
}
