<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Reserva;
use App\Repositories\BloqueoRepositoryInterface;
use App\Repositories\HorarioRepositoryInterface;
use App\Repositories\ReservaRepositoryInterface;
use App\Repositories\ServicioRepositoryInterface;
use App\Services\DisponibilidadService;
use App\Services\ReservaService;
use App\Support\Exceptions\ForbiddenException;
use App\Support\Exceptions\ValidationException;
use PHPUnit\Framework\TestCase;

/** Cubre las reglas 3 (conflictos) y 4 (cancelación). */
final class ReservaServiceTest extends TestCase
{
    private function reservaDe(int $usuarioId, string $estado): Reserva
    {
        return new Reserva(1, $usuarioId, 1, 1, '2030-01-01T10:00:00Z', '2030-01-01T11:00:00Z', $estado);
    }

    /** Arma un ReservaService donde TODAS las dependencias son de mentira. */
    private function crearService(ReservaRepositoryInterface $reservas): ReservaService
    {
        $servicios = $this->createStub(ServicioRepositoryInterface::class);
        $disponibilidad = new DisponibilidadService(
            $servicios,
            $this->createStub(HorarioRepositoryInterface::class),
            $this->createStub(BloqueoRepositoryInterface::class),
            $reservas,
        );

        return new ReservaService($reservas, $servicios, $disponibilidad);
    }

    public function test_no_permite_cancelar_una_reserva_ya_cancelada(): void
    {
        // Preparar: repo falso que devuelve una reserva ya cancelada
        $repo = $this->createStub(ReservaRepositoryInterface::class);
        $repo->method('findById')->willReturn($this->reservaDe(5, 'cancelada'));
        $service = $this->crearService($repo);

        // Verificar: se espera este error
        $this->expectException(ValidationException::class);

        // Ejecutar: el dueño (usuario 5) intenta cancelar
        $service->cancelar(1, 5, 'user');
    }

    public function test_solo_el_dueno_o_un_admin_pueden_cancelar_una_reserva(): void
    {
        $repo = $this->createStub(ReservaRepositoryInterface::class);
        $repo->method('findById')->willReturn($this->reservaDe(5, 'confirmada'));
        $service = $this->crearService($repo);

        // Un usuario ajeno (id 99) no puede
        try {
            $service->cancelar(1, 99, 'user');
            $this->fail('Debió lanzar ForbiddenException');
        } catch (ForbiddenException) {
            $this->addToAssertionCount(1);
        }

        // Un admin sí puede: no lanza error
        $service->cancelar(1, 99, 'admin');
        $this->addToAssertionCount(1);
    }
}