<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Config\Database;
use App\Repositories\ReservaRepository;
use App\Support\Exceptions\ConflictException;
use Dotenv\Dotenv;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Test de integración REAL contra una base Postgres de test (correr las migraciones ahí primero).
 * Cubre la regla 3 (conflictos) a nivel constraint de base de datos, y la regla 6 (persistencia
 * transaccional): si el EXCLUDE constraint rechaza el insert, la transacción debe hacer rollback
 * completo y no dejar filas a medio insertar.
 */
final class ReservaRepositoryTest extends TestCase
{
    private PDO $pdo;
    private ReservaRepository $repo;
    private int $usuarioId;
    private int $servicioId;
    private int $profesionalA;
    private int $profesionalB;

    protected function setUp(): void
    {
        Dotenv::createImmutable(dirname(__DIR__, 2), '.env.testing')->safeLoad();
        Database::reset();

        // Seguro anti-desastres: este test BORRA datos, así que solo corre en una base "_test"
        if (!str_ends_with($_ENV['DB_NAME'] ?? '', '_test')) {
            $this->markTestSkipped('DB_NAME debe terminar en _test (revisá .env.testing). Este test borra tablas.');
        }

        if (!extension_loaded('pdo_pgsql')) {
            $this->markTestSkipped('Falta pdo_pgsql.');
        }
        try {
            $this->pdo = Database::connection();
        } catch (\RuntimeException $e) {
            $this->markTestSkipped('Base de pruebas no disponible: ' . $e->getMessage());
        }
        if (!$this->pdo->query("SELECT to_regclass('public.reservas')")->fetchColumn()) {
            $this->markTestSkipped('Faltan las migraciones de pruebas.');
        }
        $this->pdo->exec('TRUNCATE usuarios, servicios, profesionales RESTART IDENTITY CASCADE');

        $this->usuarioId = $this->insertar("INSERT INTO usuarios (nombre, email, password_hash) VALUES ('Ana', 'ana@test.com', 'x') RETURNING id");
        $this->servicioId = $this->insertar("INSERT INTO servicios (nombre, duracion_minutos, precio) VALUES ('Corte', 60, 1000) RETURNING id");
        $this->profesionalA = $this->insertar("INSERT INTO profesionales (nombre) VALUES ('Prof A') RETURNING id");
        $this->profesionalB = $this->insertar("INSERT INTO profesionales (nombre) VALUES ('Prof B') RETURNING id");

        $this->repo = new ReservaRepository();
    }

    private function insertar(string $sql): int
    {
        return (int) $this->pdo->query($sql)->fetchColumn();
    }

    private function datosReserva(int $profesionalId, string $inicio, string $fin): array
    {
        return [
            'usuario_id' => $this->usuarioId,
            'servicio_id' => $this->servicioId,
            'profesional_id' => $profesionalId,
            'inicio' => $inicio,
            'fin' => $fin,
        ];
    }

    private function contarReservas(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM reservas')->fetchColumn();
    }

    public function test_el_constraint_exclude_rechaza_dos_reservas_solapadas_del_mismo_profesional(): void
    {
        // Primera reserva: 10:00 a 11:00
        $this->repo->crear($this->datosReserva($this->profesionalA, '2030-01-01T10:00:00+00:00', '2030-01-01T11:00:00+00:00'));

        // Segunda reserva del MISMO profesional, solapada: 10:30 a 11:30
        try {
            $this->repo->crear($this->datosReserva($this->profesionalA, '2030-01-01T10:30:00+00:00', '2030-01-01T11:30:00+00:00'));
            $this->fail('Debió lanzar ConflictException por solapamiento');
        } catch (ConflictException) {
            $this->addToAssertionCount(1);
        }

        // Regla 6: el rechazo hizo rollback, así que solo queda la primera reserva
        $this->assertSame(1, $this->contarReservas());
    }

    public function test_permite_reservas_solapadas_si_son_de_profesionales_distintos(): void
    {
        $this->repo->crear($this->datosReserva($this->profesionalA, '2030-01-01T10:00:00+00:00', '2030-01-01T11:00:00+00:00'));
        $this->repo->crear($this->datosReserva($this->profesionalB, '2030-01-01T10:00:00+00:00', '2030-01-01T11:00:00+00:00'));

        $this->assertSame(2, $this->contarReservas());
    }
    public function test_dos_conexiones_concurrentes_solo_confirman_una_reserva(): void
    {
        if (!extension_loaded('pgsql')) {
            $this->markTestSkipped('La prueba concurrente requiere ext-pgsql para consultas asincronicas.');
        }
        $params = [];
        foreach (['host'=>'DB_HOST','port'=>'DB_PORT','dbname'=>'DB_NAME','user'=>'DB_USER','password'=>'DB_PASS'] as $key => $env) {
            $value = str_replace(['\\', "'"], ['\\\\', "\\'"], $_ENV[$env] ?? '');
            $params[] = $key . "='" . $value . "'";
        }
        $second = pg_connect(implode(' ', $params), PGSQL_CONNECT_FORCE_NEW);
        self::assertNotFalse($second);
        $sql = 'INSERT INTO reservas (usuario_id, servicio_id, profesional_id, inicio, fin, estado) VALUES ($1,$2,$3,$4,$5,\'confirmada\')';
        $values = [$this->usuarioId,$this->servicioId,$this->profesionalA,'2030-01-01T10:00:00Z','2030-01-01T11:00:00Z'];
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare(str_replace(['$1','$2','$3','$4','$5'], ['?','?','?','?','?'], $sql));
            $stmt->execute($values);
            pg_query($second, "SET statement_timeout = '5s'");
            self::assertTrue(pg_send_query_params($second, $sql, $values));
            // El segundo INSERT compite mientras el primero aun no confirmo.
            $this->pdo->commit();
            $result = pg_get_result($second);
            self::assertSame('23P01', pg_result_error_field($result, PGSQL_DIAG_SQLSTATE));
            self::assertSame(1, $this->contarReservas());
        } finally {
            if ($this->pdo->inTransaction()) { $this->pdo->rollBack(); }
            pg_close($second);
        }
    }
}
