<?php

declare(strict_types=1);

namespace App\Models;

final class Reserva
{
    public function __construct(
        public readonly int $id,
        public readonly int $usuarioId,
        public readonly int $servicioId,
        public readonly int $profesionalId,
        public readonly string $inicio, // timestamp ISO
        public readonly string $fin,
        public readonly string $estado, // 'pendiente' | 'confirmada' | 'cancelada'
    ) {
    }

    /** @param array<string,mixed> $row */
    public static function fromRow(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            usuarioId: (int) $row['usuario_id'],
            servicioId: (int) $row['servicio_id'],
            profesionalId: (int) $row['profesional_id'],
            inicio: $row['inicio'],
            fin: $row['fin'],
            estado: $row['estado'],
        );
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'usuario_id' => $this->usuarioId,
            'servicio_id' => $this->servicioId,
            'profesional_id' => $this->profesionalId,
            'inicio' => $this->inicio,
            'fin' => $this->fin,
            'estado' => $this->estado,
        ];
    }
}
