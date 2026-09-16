<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * TODO: inyectar un UsuarioRepository fake (sin DB real) al construir AuthService
 * para poder testear estas reglas en aislamiento.
 */
final class AuthServiceTest extends TestCase
{
    public function test_no_permite_registrar_un_email_ya_existente(): void
    {
        $this->markTestIncomplete('Pendiente: requiere UsuarioRepository fake inyectable en AuthService');
    }

    public function test_login_con_password_incorrecto_lanza_unauthorized(): void
    {
        $this->markTestIncomplete('Pendiente: requiere UsuarioRepository fake inyectable en AuthService');
    }
}
