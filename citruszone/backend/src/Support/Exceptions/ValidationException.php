<?php

declare(strict_types=1);

namespace App\Support\Exceptions;

/** Payload de entrada inválido (400). */
final class ValidationException extends AppException
{
    /** @param array<string,mixed> $errors */
    public function __construct(string $message = 'Datos inválidos', array $errors = [])
    {
        parent::__construct($message, 400, $errors);
    }
}
