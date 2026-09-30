<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Reserva;

interface ReservaRepositoryInterface
{
    /** @return list<Reserva> */
    public function porUsuario(int $usuarioId): array;

    /** @return list<Reserva> */
    public function all(): array;

    public function findById(int $id): ?Reserva;

    /** @return list<Reserva> */
    public function porProfesionalYFecha(int $profesionalId, string $fecha): array;

    public function crear(array $data): Reserva;

    public function cancelar(int $id): void;
}