<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Config\Database;
use App\Models\Reserva;
use PDO;
use PDOException;
use App\Support\Exceptions\ConflictException;

final class ReservaRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    /** @return list<Reserva> */
    public function porUsuario(int $usuarioId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM reservas WHERE usuario_id = :uid ORDER BY inicio DESC');
        $stmt->execute(['uid' => $usuarioId]);
        return array_map(fn (array $row) => Reserva::fromRow($row), $stmt->fetchAll());
    }

    /** @return list<Reserva> */
    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM reservas ORDER BY inicio DESC');
        return array_map(fn (array $row) => Reserva::fromRow($row), $stmt->fetchAll());
    }

    public function findById(int $id): ?Reserva
    {
        $stmt = $this->pdo->prepare('SELECT * FROM reservas WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? Reserva::fromRow($row) : null;
    }

    /** @return list<Reserva> */
    public function porProfesionalYFecha(int $profesionalId, string $fecha): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM reservas WHERE profesional_id = :profesional_id AND estado <> 'cancelada' AND inicio >= :inicio AND inicio < (:inicio::date + INTERVAL '1 day') ORDER BY inicio");
        $stmt->execute(['profesional_id' => $profesionalId, 'inicio' => $fecha]);
        return array_map(fn (array $row) => Reserva::fromRow($row), $stmt->fetchAll());
    }

    /**
     * Inserta la reserva dentro de una transacción.
     * El constraint EXCLUDE de la tabla (ver migración 007) es la última línea de
     * defensa contra el solapamiento: si la DB lo rechaza, lo traducimos a ConflictException.
     *
     * @param array{usuario_id:int,servicio_id:int,profesional_id:int,inicio:string,fin:string} $data
     */
    public function crear(array $data): Reserva
    {
        $this->pdo->beginTransaction();
        try {
            // TODO: implementar el INSERT ... RETURNING * real dentro de este try
            $stmt = $this->pdo->prepare(
                'INSERT INTO reservas (usuario_id, servicio_id, profesional_id, inicio, fin, estado)
                 VALUES (:usuario_id, :servicio_id, :profesional_id, :inicio, :fin, \'confirmada\')
                 RETURNING *'
            );
            $stmt->execute($data);
            $reserva = Reserva::fromRow($stmt->fetch());
            $this->pdo->commit();
            return $reserva;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            // El código 23P01 es "exclusion_violation" en Postgres: el horario ya estaba ocupado.
            if ($e->getCode() === '23P01') {
                throw new ConflictException('Ese horario ya fue reservado para este profesional');
            }
            throw $e;
        }
    }

    public function cancelar(int $id): void
    {
        // TODO: implementar UPDATE reservas SET estado = 'cancelada' WHERE id = :id
        $stmt = $this->pdo->prepare("UPDATE reservas SET estado = 'cancelada' WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }
}
