<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/** Cubre las reglas 3 (conflictos) y 4 (cancelación). */
final class ReservaServiceTest extends TestCase
{
    public function test_solo_el_dueno_o_un_admin_pueden_cancelar_una_reserva(): void
    {
        $this->markTestIncomplete('Pendiente: requiere ReservaRepository fake inyectable en ReservaService');
    }

    public function test_no_permite_cancelar_una_reserva_ya_cancelada(): void
    {
        $this->markTestIncomplete('Pendiente');
    }
}
