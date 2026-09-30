<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Usuario;
use App\Repositories\UsuarioRepositoryInterface;
use App\Services\AuthService;
use App\Support\Exceptions\UnauthorizedException;
use App\Support\Exceptions\ValidationException;
use PHPUnit\Framework\TestCase;

final class AuthServiceTest extends TestCase
{
    private function usuarioConPassword(string $password): Usuario
    {
        return new Usuario(
            1,
            'Ana',
            'ana@mail.com',
            password_hash($password, PASSWORD_BCRYPT),
            null,
            'user'
        );
    }

    public function test_no_permite_registrar_un_email_ya_existente(): void
    {
        // Preparar: el repo falso dice que ese email YA existe
        $repo = $this->createStub(UsuarioRepositoryInterface::class);
        $repo->method('findByEmail')->willReturn($this->usuarioConPassword('cualquiera'));
        $service = new AuthService($repo);

        // Verificar: se espera este error
        $this->expectException(ValidationException::class);

        // Ejecutar: intentar registrar el mismo email
        $service->register('Otra Ana', 'ana@mail.com', 'unaPassword123', null);
    }

    public function test_login_con_password_incorrecto_lanza_unauthorized(): void
    {
        // Preparar: el usuario existe y su password real es 'correcta'
        $repo = $this->createStub(UsuarioRepositoryInterface::class);
        $repo->method('findByEmail')->willReturn($this->usuarioConPassword('correcta'));
        $service = new AuthService($repo);

        $this->expectException(UnauthorizedException::class);

        // Ejecutar: login con otra password
        $service->login('ana@mail.com', 'incorrecta');
    }
}