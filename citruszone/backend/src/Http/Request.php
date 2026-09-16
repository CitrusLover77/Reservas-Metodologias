<?php

declare(strict_types=1);

namespace App\Http;

final class Request
{
    /** @var array<string,mixed> */
    private array $body;

    /** @var array<string,string> */
    private array $routeParams = [];

    /** El usuario autenticado (lo setea AuthMiddleware). null si es anónimo. */
    public ?object $user = null;

    private function __construct(
        public readonly string $method,
        public readonly string $path,
        /** @var array<string,string> */
        public readonly array $query,
        /** @var array<string,string> */
        private readonly array $headers,
        string $rawBody
    ) {
        $decoded = json_decode($rawBody, true);
        $this->body = is_array($decoded) ? $decoded : [];
    }

    public static function fromGlobals(): self
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $rawBody = file_get_contents('php://input') ?: '';

        return new self(
            method: strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
            path: rtrim($path, '/') ?: '/',
            query: $_GET,
            headers: $headers,
            rawBody: $rawBody
        );
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    /** @return array<string,mixed> */
    public function all(): array
    {
        return $this->body;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function bearerToken(): ?string
    {
        $header = $this->headers['Authorization'] ?? $this->headers['authorization'] ?? '';
        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }
        return null;
    }

    public function routeParam(string $key): ?string
    {
        return $this->routeParams[$key] ?? null;
    }

    /** @param array<string,string> $params */
    public function withRouteParams(array $params): self
    {
        $clone = clone $this;
        $clone->routeParams = $params;
        return $clone;
    }
}
