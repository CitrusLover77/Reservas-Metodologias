<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Cubre la regla 2 (Disponibilidad): fechas pasadas, fuera de franja laboral,
 * horarios bloqueados y servicios inactivos deben rechazarse.
 */
final class DisponibilidadServiceTest extends TestCase
{
    public function test_rechaza_una_fecha_pasada(): void
    {
        $this->markTestIncomplete('Pendiente: requiere repos fake inyectables en DisponibilidadService');
    }

    public function test_rechaza_un_servicio_inactivo(): void
    {
        $this->markTestIncomplete('Pendiente');
    }

    public function test_rechaza_un_horario_fuera_de_la_franja_laboral(): void
    {
        $this->markTestIncomplete('Pendiente: falta implementar la validación de horarios_laborales en el Service');
    }

    public function test_rechaza_un_horario_bloqueado(): void
    {
        $this->markTestIncomplete('Pendiente: falta implementar la validación de bloqueos en el Service');
    }
}
