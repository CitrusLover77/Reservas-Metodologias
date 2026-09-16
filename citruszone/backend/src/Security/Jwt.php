<?php

declare(strict_types=1);

namespace App\Security;

use Firebase\JWT\JWT as FirebaseJwt;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use App\Support\Exceptions\UnauthorizedException;

final class Jwt
{
    private static function secret(): string
    {
        return $_ENV['JWT_SECRET'] ?? throw new \RuntimeException('JWT_SECRET no configurado en .env');
    }

    private static function ttl(): int
    {
        return (int) ($_ENV['JWT_TTL_SECONDS'] ?? 86400);
    }

    /** @param array{id:int,nombre:string,email:string,role:string} $userClaims */
    public static function issue(array $userClaims): string
    {
        $now = time();
        $payload = array_merge($userClaims, [
            'iat' => $now,
            'exp' => $now + self::ttl(),
        ]);

        return FirebaseJwt::encode($payload, self::secret(), 'HS256');
    }

    /** @return array<string,mixed> */
    public static function verify(string $token): array
    {
        try {
            $decoded = FirebaseJwt::decode($token, new Key(self::secret(), 'HS256'));
            return (array) $decoded;
        } catch (ExpiredException) {
            throw new UnauthorizedException('La sesión expiró, volvé a iniciar sesión');
        } catch (\Throwable) {
            throw new UnauthorizedException('Token inválido');
        }
    }
}
