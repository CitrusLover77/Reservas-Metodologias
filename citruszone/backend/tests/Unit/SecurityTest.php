<?php
namespace Tests\Unit;
use App\Http\Request;
use App\Http\Router;
use App\Security\Jwt;
use PHPUnit\Framework\TestCase;
final class SecurityTest extends TestCase
{
    public function test_admin_and_invalid_tokens(): void
    {
        $env = $_ENV;
        try {
            $_ENV['JWT_SECRET'] = str_repeat('test-secret-', 8);
            $_ENV['JWT_TTL_SECONDS'] = '3600';
            $claims = ['id'=>1,'nombre'=>'Test','email'=>'test@example.com','role'=>'usuario'];
            $valid = Jwt::issue($claims);
            $_ENV['JWT_TTL_SECONDS'] = '-3600';
            $expired = Jwt::issue($claims);
            $parts = explode('.', $valid);
            $parts[1] = rtrim(strtr(base64_encode(json_encode(array_merge($claims, ['role'=>'admin','exp'=>time()+3600]))), '+/', '-_'), '=');
            $tampered = implode('.', $parts);
            $router = new Router();
            require dirname(__DIR__, 2).'/src/Http/routes.php';
            foreach ([['POST','/api/servicios'],['PUT','/api/servicios/1'],['GET','/api/reservas'],['GET','/api/horarios'],['POST','/api/horarios'],['GET','/api/bloqueos'],['POST','/api/bloqueos'],['DELETE','/api/bloqueos/1']] as [$method,$path]) {
                foreach ([[$valid,403],[$expired,401],[$tampered,401]] as [$token,$expected]) {
                    $reflection = new \ReflectionClass(Request::class);
                    $request = $reflection->newInstanceWithoutConstructor();
                    $reflection->getConstructor()->invoke($request,$method,$path,[],['Authorization'=>'Bearer '.$token],'');
                    $response = $router->dispatch($request);
                    self::assertSame($expected,(new \ReflectionProperty($response,'status'))->getValue($response), "$method $path");
                }
            }
        } finally { $_ENV = $env; }
    }
}
