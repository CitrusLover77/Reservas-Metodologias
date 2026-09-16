<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Support\Exceptions\AppException;
use Throwable;

final class Router
{
    /** @var list<array{method:string, pattern:string, handler:array{0:class-string,1:string}, auth:bool, roles:list<string>}> */
    private array $routes = [];

    /** @param array{0:class-string,1:string} $handler @param list<string> $roles */
    public function get(string $pattern, array $handler, bool $auth = false, array $roles = []): void
    {
        $this->add('GET', $pattern, $handler, $auth, $roles);
    }

    /** @param array{0:class-string,1:string} $handler @param list<string> $roles */
    public function post(string $pattern, array $handler, bool $auth = false, array $roles = []): void
    {
        $this->add('POST', $pattern, $handler, $auth, $roles);
    }

    /** @param array{0:class-string,1:string} $handler @param list<string> $roles */
    public function put(string $pattern, array $handler, bool $auth = false, array $roles = []): void
    {
        $this->add('PUT', $pattern, $handler, $auth, $roles);
    }

    /** @param array{0:class-string,1:string} $handler @param list<string> $roles */
    public function delete(string $pattern, array $handler, bool $auth = false, array $roles = []): void
    {
        $this->add('DELETE', $pattern, $handler, $auth, $roles);
    }

    /** @param array{0:class-string,1:string} $handler @param list<string> $roles */
    private function add(string $method, string $pattern, array $handler, bool $auth, array $roles): void
    {
        $this->routes[] = compact('method', 'pattern', 'handler', 'auth', 'roles');
    }

    public function dispatch(Request $request): Response
    {
        try {
            foreach ($this->routes as $route) {
                if ($route['method'] !== $request->method) {
                    continue;
                }
                $params = $this->match($route['pattern'], $request->path);
                if ($params === null) {
                    continue;
                }

                $request = $request->withRouteParams($params);

                if ($route['auth']) {
                    $request = (new AuthMiddleware())->handle($request);
                }
                if ($route['roles']) {
                    (new RoleMiddleware($route['roles']))->handle($request);
                }

                [$controllerClass, $method] = $route['handler'];
                $controller = new $controllerClass();
                return $controller->$method($request);
            }

            return Response::error('Ruta no encontrada', 404);
        } catch (AppException $e) {
            return Response::error($e->getMessage(), $e->statusCode, $e->errors);
        } catch (Throwable $e) {
            $debug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';
            return Response::error(
                $debug ? $e->getMessage() : 'Error interno del servidor',
                500
            );
        }
    }

    /** @return array<string,string>|null */
    private function match(string $pattern, string $path): ?array
    {
        $patternParts = explode('/', trim($pattern, '/'));
        $pathParts = explode('/', trim($path, '/'));

        if (count($patternParts) !== count($pathParts)) {
            return null;
        }

        $params = [];
        foreach ($patternParts as $i => $part) {
            if (str_starts_with($part, '{') && str_ends_with($part, '}')) {
                $params[substr($part, 1, -1)] = $pathParts[$i];
            } elseif ($part !== $pathParts[$i]) {
                return null;
            }
        }

        return $params;
    }
}
