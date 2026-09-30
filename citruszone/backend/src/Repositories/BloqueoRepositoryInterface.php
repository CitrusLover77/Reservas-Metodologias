<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Bloqueo;

interface BloqueoRepositoryInterface
{
    /** @return list<Bloqueo> */
    public function porProfesionalYFecha(int $profesionalId, string $fecha): array;

    /** @return list<Bloqueo> */
    public function all(): array;

    public function create(array $data): Bloqueo;

    public function delete(int $id): void;
}