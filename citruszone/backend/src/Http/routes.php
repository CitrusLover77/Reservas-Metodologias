<?php
use App\Controllers\AuthController;
use App\Controllers\ServicioController;
use App\Controllers\ProfesionalController;
use App\Controllers\ReservaController;
use App\Controllers\HorarioController;
use App\Controllers\BloqueoController;

// ---- Auth ----
$router->post('/api/auth/register', [AuthController::class, 'register']);
$router->post('/api/auth/login', [AuthController::class, 'login']);
$router->get('/api/auth/me', [AuthController::class, 'me'], auth: true);

// ---- Servicios ----
$router->get('/api/servicios', [ServicioController::class, 'index']);
$router->post('/api/servicios', [ServicioController::class, 'store'], auth: true, roles: ['admin', 'superadmin']);
$router->put('/api/servicios/{id}', [ServicioController::class, 'update'], auth: true, roles: ['admin', 'superadmin']);

// ---- Profesionales ----
$router->get('/api/profesionales', [ProfesionalController::class, 'index']);

// ---- Disponibilidad ----
$router->get('/api/disponibilidad', [ReservaController::class, 'disponibilidad']);

// ---- Reservas ----
$router->get('/api/reservas/mias', [ReservaController::class, 'misReservas'], auth: true);
$router->get('/api/reservas', [ReservaController::class, 'index'], auth: true, roles: ['admin', 'superadmin']);
$router->post('/api/reservas', [ReservaController::class, 'store'], auth: true);
$router->post('/api/reservas/{id}/cancelar', [ReservaController::class, 'cancelar'], auth: true);

// ---- Horarios laborales ----
$router->get('/api/horarios', [HorarioController::class, 'index'], auth: true, roles: ['admin', 'superadmin']);
$router->post('/api/horarios', [HorarioController::class, 'store'], auth: true, roles: ['admin', 'superadmin']);

// ---- Bloqueos ----
$router->get('/api/bloqueos', [BloqueoController::class, 'index'], auth: true, roles: ['admin', 'superadmin']);
$router->post('/api/bloqueos', [BloqueoController::class, 'store'], auth: true, roles: ['admin', 'superadmin']);
$router->delete('/api/bloqueos/{id}', [BloqueoController::class, 'destroy'], auth: true, roles: ['admin', 'superadmin']);


