<?php

declare(strict_types=1);

namespace App\Models;

/** Franja laboral de un profesional en un día de la semana (0=domingo .. 6=sábado). */
final class HorarioLaboral
{
    public function __construct(
        public readonly int $id,
        public readonly int $profesionalId,
        public readonly int $diaSemana,
        public readonly string $horaInicio, // 'HH:MM:SS'
        public readonly string $horaFin,
    ) {
    }

    /** @param array<string,mixed> $row */
    public static function fromRow(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            profesionalId: (int) $row['profesional_id'],
            diaSemana: (int) $row['dia_semana'],
            horaInicio: $row['hora_inicio'],
            horaFin: $row['hora_fin'],
        );
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return ['id' => $this->id, 'profesional_id' => $this->profesionalId, 'dia_semana' => $this->diaSemana, 'hora_inicio' => $this->horaInicio, 'hora_fin' => $this->horaFin];
    }
}
