<?php

declare(strict_types=1);

namespace App\Support\Exceptions;

/** Autenticado pero sin permiso para esta acción (403). */
final class ForbiddenException extends AppException
{
    public function __construct(string $message = 'No tenés permisos para esta acción')
    {
        parent::__construct($message, 403);
    }
}
