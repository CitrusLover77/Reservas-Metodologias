<?php

declare(strict_types=1);

namespace App\Support;

use App\Support\Exceptions\ValidationException;

/**
 * Validador chico y explícito a propósito (sin dependencias externas).
 * Uso: Validator::make($data, ['email' => ['required', 'email'], 'password' => ['required', 'min:8']])->validate();
 */
final class Validator
{
    /** @var array<string,mixed> */
    private array $errors = [];

    /**
     * @param array<string,mixed> $data
     * @param array<string,list<string>> $rules
     */
    private function __construct(private readonly array $data, private readonly array $rules)
    {
    }

    /**
     * @param array<string,mixed> $data
     * @param array<string,list<string>> $rules
     */
    public static function make(array $data, array $rules): self
    {
        return new self($data, $rules);
    }

    /** @throws ValidationException si alguna regla falla */
    public function validate(): void
    {
        foreach ($this->rules as $field => $fieldRules) {
            $value = $this->data[$field] ?? null;
            foreach ($fieldRules as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }

        if ($this->errors) {
            throw new ValidationException('Datos inválidos', $this->errors);
        }
    }

    private function applyRule(string $field, mixed $value, string $rule): void
    {
        [$name, $param] = str_contains($rule, ':') ? explode(':', $rule, 2) : [$rule, null];

        $fails = match ($name) {
            'required' => $value === null || $value === '',
            'email' => $value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL),
            'min' => is_string($value) && strlen($value) < (int) $param,
            'numeric' => $value !== null && $value !== '' && !is_numeric($value),
            'date' => $value !== null && $value !== '' && !\DateTime::createFromFormat('Y-m-d', (string) $value),
            'in' => $value !== null && !in_array($value, explode(',', (string) $param), true),
            default => false,
        };

        if ($fails) {
            $this->errors[$field][] = match ($name) {
                'required' => 'Este campo es obligatorio',
                'email' => 'Debe ser un email válido',
                'min' => "Debe tener al menos {$param} caracteres",
                'numeric' => 'Debe ser un número',
                'date' => 'Debe ser una fecha con formato AAAA-MM-DD',
                'in' => "Debe ser uno de: {$param}",
                default => 'Valor inválido',
            };
        }
    }
}
