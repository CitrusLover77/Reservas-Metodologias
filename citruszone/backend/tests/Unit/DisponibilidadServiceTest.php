<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Bloqueo;
use App\Models\HorarioLaboral;
use App\Models\Servicio;
use App\Repositories\BloqueoRepositoryInterface;
use App\Repositories\HorarioRepositoryInterface;
use App\Repositories\ReservaRepositoryInterface;
use App\Repositories\ServicioRepositoryInterface;
use App\Services\DisponibilidadService;
use App\Support\Exceptions\ConflictException;
use App\Support\Exceptions\ValidationException;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * Cubre la regla 2 (Disponibilidad): fechas pasadas, fuera de franja laboral,
 * horarios bloqueados y servicios inactivos deben rechazarse.
 */
final class DisponibilidadServiceTest extends TestCase
{
    private function servicio(bool $activo = true): Servicio
    {
        return new Servicio(1, 'Corte', 60, 1000.0, $activo);
    }

    /**
     * Arma el service con repos de mentira.
     * @param list<HorarioLaboral> $horarios franjas que "devuelve la base"
     * @param list<Bloqueo> $bloqueos bloqueos que "devuelve la base"
     * @param list<int> $habilitados profesionales que hacen el servicio
     */
    private function crearService(array $horarios = [], array $bloqueos = [], array $habilitados = [1]): DisponibilidadService
    {
        $servicios = $this->createStub(ServicioRepositoryInterface::class);
        $servicios->method('profesionalesHabilitados')->willReturn($habilitados);

        $horariosRepo = $this->createStub(HorarioRepositoryInterface::class);
        $horariosRepo->method('porProfesionalYDia')->willReturn($horarios);

        $bloqueosRepo = $this->createStub(BloqueoRepositoryInterface::class);
        $bloqueosRepo->method('porProfesionalYFecha')->willReturn($bloqueos);

        return new DisponibilidadService(
            $servicios,
            $horariosRepo,
            $bloqueosRepo,
            $this->createStub(ReservaRepositoryInterface::class),
        );
    }

    private function franja(string $desde, string $hasta): HorarioLaboral
    {
        return new HorarioLaboral(1, 1, 1, $desde, $hasta);
    }

    public function test_rechaza_un_servicio_inactivo(): void
    {
        $service = $this->crearService();
        $inicio = new DateTimeImmutable('tomorrow 10:00');

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('disponible actualmente');

        $service->validar($this->servicio(activo: false), 1, $inicio, $inicio->modify('+60 minutes'));
    }

    public function test_rechaza_una_fecha_pasada(): void
    {
        $service = $this->crearService();
        $inicio = new DateTimeImmutable('yesterday 10:00');

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('pasados');

        $service->validar($this->servicio(), 1, $inicio, $inicio->modify('+60 minutes'));
    }

    public function test_rechaza_un_horario_fuera_de_la_franja_laboral(): void
    {
        // El profesional trabaja de 09:00 a 12:00, pero piden el turno a las 15:00
        $service = $this->crearService(horarios: [$this->franja('09:00:00', '12:00:00')]);
        $inicio = new DateTimeImmutable('tomorrow 15:00');

        $this->expectException(ConflictException::class);
        $this->expectExceptionMessage('no trabaja');

        $service->validar($this->servicio(), 1, $inicio, $inicio->modify('+60 minutes'));
    }

    public function test_rechaza_un_horario_bloqueado(): void
    {
        // Trabaja de 09:00 a 18:00, pero de 10:00 a 12:00 hay un bloqueo
        $fecha = (new DateTimeImmutable('tomorrow'))->format('Y-m-d');
        $bloqueo = new Bloqueo(1, 1, $fecha, '10:00:00', '12:00:00', 'Feriado');
        $service = $this->crearService(
            horarios: [$this->franja('09:00:00', '18:00:00')],
            bloqueos: [$bloqueo],
        );
        $inicio = new DateTimeImmutable('tomorrow 10:00');

        $this->expectException(ConflictException::class);
        $this->expectExceptionMessage('bloqueado');

        $service->validar($this->servicio(), 1, $inicio, $inicio->modify('+60 minutes'));
    }

    public function test_acepta_un_turno_valido(): void
    {
        // Caso feliz: servicio activo, fecha futura, dentro de la franja y sin bloqueos
        $service = $this->crearService(horarios: [$this->franja('09:00:00', '18:00:00')]);
        $inicio = new DateTimeImmutable('tomorrow 10:00');

        $this->expectNotToPerformAssertions();

        $service->validar($this->servicio(), 1, $inicio, $inicio->modify('+60 minutes'));
    }
}