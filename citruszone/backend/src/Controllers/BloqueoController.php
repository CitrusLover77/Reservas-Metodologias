<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Http\Request;
use App\Http\Response;
use App\Repositories\BloqueoRepository;
use App\Support\Validator;

final class BloqueoController
{
    public function __construct(private readonly BloqueoRepository $bloqueos = new BloqueoRepository()) {}
    public function index(Request $request): Response { return Response::json(['data' => array_map(fn ($b) => $b->toArray(), $this->bloqueos->all())]); }
    public function store(Request $request): Response
    {
        Validator::make($request->all(), ['fecha' => ['required', 'date'], 'hora_inicio' => ['required'], 'hora_fin' => ['required']])->validate();
        $professional = $request->input('profesional_id');
        $block = $this->bloqueos->create(['profesional_id' => $professional === null || $professional === '' ? null : (int) $professional, 'fecha' => (string) $request->input('fecha'), 'hora_inicio' => (string) $request->input('hora_inicio'), 'hora_fin' => (string) $request->input('hora_fin'), 'motivo' => $request->input('motivo') ?: null]);
        return Response::json(['data' => $block->toArray()], 201);
    }
    public function destroy(Request $request): Response { $this->bloqueos->delete((int) $request->routeParam('id')); return Response::noContent(); }
}
