<?php

declare(strict_types=1);

namespace App\Support\Exceptions;

/** No autenticado / token inválido (401). */
final class UnauthorizedException extends AppException
{
    public function __construct(string $message = 'No autenticado')
    {
        parent::__construct($message, 401);
    }
}
