<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Repositories\ProfesionalRepository;

final class ProfesionalController
{
    public function __construct(private readonly ProfesionalRepository $profesionales = new ProfesionalRepository())
    {
    }

    public function index(Request $request): Response
    {
        $servicioId = $request->query('servicio_id');

        $profesionales = $servicioId
            ? $this->profesionales->porServicio((int) $servicioId)
            : $this->profesionales->all();

        return Response::json(['data' => array_map(fn ($p) => $p->toArray(), $profesionales)]);
    }
}
