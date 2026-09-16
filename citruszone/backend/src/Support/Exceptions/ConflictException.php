<?php

declare(strict_types=1);

namespace App\Support\Exceptions;

/** Conflicto de negocio: horario solapado, fuera de franja, bloqueado, etc. (409). */
final class ConflictException extends AppException
{
    public function __construct(string $message = 'Conflicto con una reserva o regla existente')
    {
        parent::__construct($message, 409);
    }
}
