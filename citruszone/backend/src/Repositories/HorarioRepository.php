<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Config\Database;
use App\Models\HorarioLaboral;
use PDO;

final class HorarioRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    /** @return list<HorarioLaboral> franjas de un profesional en un día de semana (0-6) */
    public function porProfesionalYDia(int $profesionalId, int $diaSemana): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM horarios_laborales WHERE profesional_id = :pid AND dia_semana = :dia'
        );
        $stmt->execute(['pid' => $profesionalId, 'dia' => $diaSemana]);
        return array_map(fn (array $row) => HorarioLaboral::fromRow($row), $stmt->fetchAll());
    }

    /** @return list<HorarioLaboral> */
    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM horarios_laborales ORDER BY profesional_id, dia_semana, hora_inicio');
        return array_map(fn (array $row) => HorarioLaboral::fromRow($row), $stmt->fetchAll());
    }

    /** @param array{profesional_id:int,dia_semana:int,hora_inicio:string,hora_fin:string} $data */
    public function create(array $data): HorarioLaboral
    {
        // TODO: implementar INSERT ... RETURNING * (validar que no se solape con otra franja del mismo profesional/día)
        $conflict = $this->pdo->prepare(
            'SELECT 1 FROM horarios_laborales WHERE profesional_id = :profesional_id AND dia_semana = :dia_semana AND hora_inicio < :hora_fin AND hora_fin > :hora_inicio'
        );
        $conflict->execute($data);
        if ($conflict->fetchColumn()) throw new \InvalidArgumentException('El horario se superpone con una franja existente');
        $stmt = $this->pdo->prepare(
            'INSERT INTO horarios_laborales (profesional_id, dia_semana, hora_inicio, hora_fin) VALUES (:profesional_id, :dia_semana, :hora_inicio, :hora_fin) RETURNING *'
        );
        $stmt->execute($data);
        return HorarioLaboral::fromRow($stmt->fetch());
    }
}
