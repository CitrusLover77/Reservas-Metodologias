<?php

declare(strict_types=1);

namespace App\Models;

/** Bloqueo de agenda: si profesional_id es null, bloquea todo el salón. */
final class Bloqueo
{
    public function __construct(
        public readonly int $id,
        public readonly ?int $profesionalId,
        public readonly string $fecha, // 'YYYY-MM-DD'
        public readonly string $horaInicio,
        public readonly string $horaFin,
        public readonly ?string $motivo,
    ) {
    }

    /** @param array<string,mixed> $row */
    public static function fromRow(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            profesionalId: $row['profesional_id'] !== null ? (int) $row['profesional_id'] : null,
            fecha: $row['fecha'],
            horaInicio: $row['hora_inicio'],
            horaFin: $row['hora_fin'],
            motivo: $row['motivo'],
        );
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return ['id' => $this->id, 'profesional_id' => $this->profesionalId, 'fecha' => $this->fecha, 'hora_inicio' => $this->horaInicio, 'hora_fin' => $this->horaFin, 'motivo' => $this->motivo];
    }
}
