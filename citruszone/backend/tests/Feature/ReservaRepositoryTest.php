<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

/**
 * Test de integración REAL contra una base Postgres de test (correr las migraciones ahí primero).
 * Cubre la regla 3 (conflictos) a nivel constraint de base de datos, y la regla 6 (persistencia
 * transaccional): si el EXCLUDE constraint rechaza el insert, la transacción debe hacer rollback
 * completo y no dejar filas a medio insertar.
 */
final class ReservaRepositoryTest extends TestCase
{
    public function test_el_constraint_exclude_rechaza_dos_reservas_solapadas_del_mismo_profesional(): void
    {
        $this->markTestIncomplete('Pendiente: requiere una DB de test Postgres configurada (ver .env.testing)');
    }

    public function test_permite_reservas_solapadas_si_son_de_profesionales_distintos(): void
    {
        $this->markTestIncomplete('Pendiente');
    }
}
