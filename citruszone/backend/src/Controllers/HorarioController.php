<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Http\Request;
use App\Http\Response;
use App\Repositories\HorarioRepository;
use App\Support\Validator;

final class HorarioController
{
    public function __construct(private readonly HorarioRepository $horarios = new HorarioRepository()) {}
    public function index(Request $request): Response { return Response::json(['data' => array_map(fn ($h) => $h->toArray(), $this->horarios->all())]); }
    public function store(Request $request): Response
    {
        Validator::make($request->all(), ['profesional_id' => ['required', 'numeric'], 'dia_semana' => ['required', 'numeric'], 'hora_inicio' => ['required'], 'hora_fin' => ['required']])->validate();
        $schedule = $this->horarios->create(['profesional_id' => (int) $request->input('profesional_id'), 'dia_semana' => (int) $request->input('dia_semana'), 'hora_inicio' => (string) $request->input('hora_inicio'), 'hora_fin' => (string) $request->input('hora_fin')]);
        return Response::json(['data' => $schedule->toArray()], 201);
    }
}
