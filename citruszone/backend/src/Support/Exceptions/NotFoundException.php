<?php

declare(strict_types=1);

namespace App\Support\Exceptions;

/** Recurso inexistente (404). */
final class NotFoundException extends AppException
{
    public function __construct(string $message = 'Recurso no encontrado')
    {
        parent::__construct($message, 404);
    }
}
