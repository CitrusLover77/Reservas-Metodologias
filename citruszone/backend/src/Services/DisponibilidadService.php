<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Servicio;
use App\Repositories\BloqueoRepository;
use App\Repositories\HorarioRepository;
use App\Repositories\ReservaRepository;
use App\Repositories\ServicioRepository;
use App\Support\Exceptions\ConflictException;
use App\Support\Exceptions\ValidationException;
use DateTimeImmutable;

final class DisponibilidadService
{
    public function __construct(
        private readonly ServicioRepository $servicios = new ServicioRepository(),
        private readonly HorarioRepository $horarios = new HorarioRepository(),
        private readonly BloqueoRepository $bloqueos = new BloqueoRepository(),
        private readonly ReservaRepository $reservas = new ReservaRepository(),
    ) {}

    public function validar(Servicio $servicio, int $profesionalId, DateTimeImmutable $inicio, DateTimeImmutable $fin): void
    {
        if (!$servicio->activo) throw new ValidationException('El servicio no está disponible actualmente');
        if ($inicio < new DateTimeImmutable('now')) throw new ValidationException('No se pueden reservar fechas u horarios pasados');
        if (!in_array($profesionalId, $this->servicios->profesionalesHabilitados($servicio->id), true)) throw new ValidationException('El profesional no realiza este servicio');
        $works = false;
        foreach ($this->horarios->porProfesionalYDia($profesionalId, (int) $inicio->format('w')) as $range) {
            if ($inicio->format('H:i:s') >= $range->horaInicio && $fin->format('H:i:s') <= $range->horaFin) { $works = true; break; }
        }
        if (!$works) throw new ConflictException('El profesional no trabaja en ese horario');
        foreach ($this->bloqueos->porProfesionalYFecha($profesionalId, $inicio->format('Y-m-d')) as $block) {
            if ($inicio->format('H:i:s') < $block->horaFin && $fin->format('H:i:s') > $block->horaInicio) throw new ConflictException('Ese horario está bloqueado' . ($block->motivo ? ': ' . $block->motivo : ''));
        }
    }

    /** @return list<array{hora:string,disponible:bool}> */
    public function slots(Servicio $servicio, int $profesionalId, string $fecha): array
    {
        if (!in_array($profesionalId, $this->servicios->profesionalesHabilitados($servicio->id), true)) throw new ValidationException('El profesional no realiza este servicio');
        $slots = [];
        $blocks = $this->bloqueos->porProfesionalYFecha($profesionalId, $fecha);
        $bookings = $this->reservas->porProfesionalYFecha($profesionalId, $fecha);
        foreach ($this->horarios->porProfesionalYDia($profesionalId, (int) (new DateTimeImmutable($fecha))->format('w')) as $range) {
            $cursor = new DateTimeImmutable("$fecha {$range->horaInicio}");
            $limit = new DateTimeImmutable("$fecha {$range->horaFin}");
            while ($cursor->modify("+{$servicio->duracionMinutos} minutes") <= $limit) {
                $end = $cursor->modify("+{$servicio->duracionMinutos} minutes");
                $available = $cursor >= new DateTimeImmutable('now');
                foreach ($blocks as $block) if ($cursor->format('H:i:s') < $block->horaFin && $end->format('H:i:s') > $block->horaInicio) $available = false;
                foreach ($bookings as $booking) if ($cursor < new DateTimeImmutable($booking->fin) && $end > new DateTimeImmutable($booking->inicio)) $available = false;
                $slots[] = ['hora' => $cursor->format('H:i'), 'disponible' => $available];
                $cursor = $cursor->modify('+30 minutes');
            }
        }
        return $slots;
    }
}
