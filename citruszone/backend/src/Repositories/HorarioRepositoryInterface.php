<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\HorarioLaboral;

interface HorarioRepositoryInterface
{
    /** @return list<HorarioLaboral> */
    public function porProfesionalYDia(int $profesionalId, int $diaSemana): array;

    /** @return list<HorarioLaboral> */
    public function all(): array;

    public function create(array $data): HorarioLaboral;
}