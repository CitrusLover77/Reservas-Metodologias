<?php

declare(strict_types=1);

namespace App\Support\Exceptions;

use Exception;

/** Base de todas las excepciones de dominio: cargan el status HTTP que les corresponde. */
abstract class AppException extends Exception
{
    /** @param array<string,mixed> $errors */
    public function __construct(
        string $message,
        public readonly int $statusCode,
        public readonly array $errors = []
    ) {
        parent::__construct($message);
    }
}
