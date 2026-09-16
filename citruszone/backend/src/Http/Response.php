<?php

declare(strict_types=1);

namespace App\Http;

final class Response
{
    private function __construct(
        private readonly int $status,
        /** @var array<string,mixed> */
        private readonly array $payload
    ) {
    }

    /** @param array<string,mixed> $payload */
    public static function json(array $payload, int $status = 200): self
    {
        return new self($status, $payload);
    }

    public static function noContent(): self
    {
        return new self(204, []);
    }

    /** @param array<string,mixed> $errors */
    public static function error(string $message, int $status = 400, array $errors = []): self
    {
        $payload = ['error' => $message];
        if ($errors) {
            $payload['errors'] = $errors;
        }
        return new self($status, $payload);
    }

    public function send(): void
    {
        http_response_code($this->status);
        if ($this->status === 204) {
            return;
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
