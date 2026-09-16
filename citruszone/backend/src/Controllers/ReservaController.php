<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Repositories\ReservaRepository;
use App\Repositories\ServicioRepository;
use App\Services\ReservaService;
use App\Support\Exceptions\NotFoundException;
use App\Support\Validator;

final class ReservaController
{
    public function __construct(
        private readonly ReservaService $reservaService = new ReservaService(),
        private readonly ReservaRepository $reservas = new ReservaRepository(),
        private readonly ServicioRepository $servicios = new ServicioRepository(),
    ) {}

    public function disponibilidad(Request $request): Response
    {
        Validator::make($request->query, ['servicio_id' => ['required', 'numeric'], 'profesional_id' => ['required', 'numeric'], 'fecha' => ['required', 'date']])->validate();
        $servicio = $this->servicios->findById((int) $request->query('servicio_id'));
        if ($servicio === null) throw new NotFoundException('El servicio no existe');
        return Response::json(['data' => $this->reservaService->slots($servicio, (int) $request->query('profesional_id'), (string) $request->query('fecha'))]);
    }

    public function misReservas(Request $request): Response
    {
        return Response::json(['data' => array_map(fn ($r) => $r->toArray(), $this->reservas->porUsuario($request->user->id))]);
    }

    public function index(Request $request): Response
    {
        return Response::json(['data' => array_map(fn ($r) => $r->toArray(), $this->reservas->all())]);
    }

    public function store(Request $request): Response
    {
        Validator::make($request->all(), ['servicio_id' => ['required', 'numeric'], 'profesional_id' => ['required', 'numeric'], 'inicio' => ['required']])->validate();
        $reserva = $this->reservaService->reservar($request->user->id, (int) $request->input('servicio_id'), (int) $request->input('profesional_id'), (string) $request->input('inicio'));
        return Response::json(['data' => $reserva->toArray()], 201);
    }

    public function cancelar(Request $request): Response
    {
        $this->reservaService->cancelar((int) $request->routeParam('id'), $request->user->id, $request->user->role);
        return Response::json(['ok' => true]);
    }
}
