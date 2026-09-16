<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Request;
use App\Support\Exceptions\ForbiddenException;

/** Exige que $request->user (ya seteado por AuthMiddleware) tenga uno de los roles permitidos. */
final class RoleMiddleware
{
    /** @param list<string> $allowedRoles */
    public function __construct(private readonly array $allowedRoles)
    {
    }

    public function handle(Request $request): void
    {
        if (!$request->user || !in_array($request->user->role, $this->allowedRoles, true)) {
            throw new ForbiddenException();
        }
    }
}
