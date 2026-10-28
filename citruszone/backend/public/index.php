<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Http\Router;
use App\Http\Request;
use App\Http\Response;
use App\Controllers\AuthController;
use App\Controllers\ServicioController;
use App\Controllers\ProfesionalController;
use App\Controllers\ReservaController;
use App\Controllers\HorarioController;
use App\Controllers\BloqueoController;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// --- CORS (el frontend vive en otro origen: file:// o un puerto distinto) ---
$allowedOrigin = $_ENV['CORS_ALLOWED_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: {$allowedOrigin}");
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$request = Request::fromGlobals();
$router = new Router();

require __DIR__ . '/../src/Http/routes.php';

$response = $router->dispatch($request);
$response->send();

