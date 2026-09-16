<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Reserva;
use App\Repositories\ReservaRepository;
use App\Repositories\ServicioRepository;
use App\Support\Exceptions\ForbiddenException;
use App\Support\Exceptions\NotFoundException;
use App\Support\Exceptions\ValidationException;
use DateTimeImmutable;

final class ReservaService
{
    public function __construct(
        private readonly ReservaRepository $reservas = new ReservaRepository(),
        private readonly ServicioRepository $servicios = new ServicioRepository(),
        private readonly DisponibilidadService $disponibilidad = new DisponibilidadService(),
    ) {
    }

    public function reservar(int $usuarioId, int $servicioId, int $profesionalId, string $inicioIso): Reserva
    {
        $servicio = $this->servicios->findById($servicioId);
        if ($servicio === null) {
            throw new NotFoundException('El servicio no existe');
        }

        $inicio = new DateTimeImmutable($inicioIso);
        $fin = $inicio->modify("+{$servicio->duracionMinutos} minutes");

        $this->disponibilidad->validar($servicio, $profesionalId, $inicio, $fin);

        return $this->reservas->crear([
            'usuario_id' => $usuarioId,
            'servicio_id' => $servicioId,
            'profesional_id' => $profesionalId,
            'inicio' => $inicio->format(DATE_ATOM),
            'fin' => $fin->format(DATE_ATOM),
        ]);
    }

    /** @return list<array{hora:string,disponible:bool}> */
    public function slots(\App\Models\Servicio $servicio, int $profesionalId, string $fecha): array
    {
        return $this->disponibilidad->slots($servicio, $profesionalId, $fecha);
    }

    public function cancelar(int $reservaId, int $usuarioId, string $rolSolicitante): void
    {
        $reserva = $this->reservas->findById($reservaId);
        if ($reserva === null) {
            throw new NotFoundException('La reserva no existe');
        }

        $esDueno = $reserva->usuarioId === $usuarioId;
        $esStaff = in_array($rolSolicitante, ['admin', 'superadmin'], true);
        if (!$esDueno && !$esStaff) {
            throw new ForbiddenException('Solo podés cancelar tus propias reservas');
        }

        if ($reserva->estado === 'cancelada') {
            throw new ValidationException('Esa reserva ya estaba cancelada');
        }

        $this->reservas->cancelar($reservaId);
    }
}
