<?php

declare(strict_types=1);

namespace App\Models;

final class Profesional
{
    public function __construct(
        public readonly int $id,
        public readonly string $nombre,
        public readonly bool $activo,
    ) {
    }

    /** @param array<string,mixed> $row */
    public static function fromRow(array $row): self
    {
        return new self(id: (int) $row['id'], nombre: $row['nombre'], activo: (bool) $row['activo']);
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return ['id' => $this->id, 'nombre' => $this->nombre, 'activo' => $this->activo];
    }
}
