<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Servicio;

interface ServicioRepositoryInterface
{
    /** @return list<Servicio> */
    public function all(bool $onlyActive = false): array;

    public function findById(int $id): ?Servicio;

    public function create(array $data): Servicio;

    public function update(int $id, array $data): Servicio;

    /** @return list<int> IDs de profesionales habilitados para este servicio */
    public function profesionalesHabilitados(int $servicioId): array;
}