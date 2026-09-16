<?php

declare(strict_types=1);

namespace App\Models;

final class Servicio
{
    public function __construct(
        public readonly int $id,
        public readonly string $nombre,
        public readonly int $duracionMinutos,
        public readonly float $precio,
        public readonly bool $activo,
    ) {
    }

    /** @param array<string,mixed> $row */
    public static function fromRow(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            nombre: $row['nombre'],
            duracionMinutos: (int) $row['duracion_minutos'],
            precio: (float) $row['precio'],
            activo: (bool) $row['activo'],
        );
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'duracion_minutos' => $this->duracionMinutos,
            'precio' => $this->precio,
            'activo' => $this->activo,
        ];
    }
}
