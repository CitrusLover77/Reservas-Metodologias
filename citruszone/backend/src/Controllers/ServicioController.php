<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Http\Request;
use App\Http\Response;
use App\Repositories\ServicioRepository;
use App\Support\Validator;

final class ServicioController
{
    public function __construct(private readonly ServicioRepository $servicios = new ServicioRepository()) {}
    public function index(Request $request): Response
    {
        return Response::json(['data' => array_map(fn ($s) => $s->toArray(), $this->servicios->all($request->query('activo') === '1'))]);
    }
    public function store(Request $request): Response
    {
        Validator::make($request->all(), ['nombre' => ['required'], 'duracion_minutos' => ['required', 'numeric'], 'precio' => ['required', 'numeric']])->validate();
        $service = $this->servicios->create(['nombre' => trim((string) $request->input('nombre')), 'duracion_minutos' => (int) $request->input('duracion_minutos'), 'precio' => (float) $request->input('precio'), 'activo' => $request->input('activo') ?? true]);
        return Response::json(['data' => $service->toArray()], 201);
    }
    public function update(Request $request): Response
    {
        $data = $request->all();
        if (array_key_exists('duracion_minutos', $data)) $data['duracion_minutos'] = (int) $data['duracion_minutos'];
        if (array_key_exists('precio', $data)) $data['precio'] = (float) $data['precio'];
        $service = $this->servicios->update((int) $request->routeParam('id'), $data);
        return Response::json(['data' => $service->toArray()]);
    }
}
