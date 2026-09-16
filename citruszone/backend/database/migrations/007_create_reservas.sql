-- Reservas. inicio/fin son timestamptz (rango exacto del turno según la duración del servicio).
-- La extensión btree_gist + el EXCLUDE de abajo son la garantía, a nivel base de datos,
-- de que un mismo profesional nunca tenga dos reservas activas que se solapen en el tiempo:
-- ninguna carrera entre requests concurrentes puede saltarse esta regla.
CREATE EXTENSION IF NOT EXISTS btree_gist;

CREATE TABLE reservas (
    id SERIAL PRIMARY KEY,
    usuario_id INTEGER NOT NULL REFERENCES usuarios (id) ON DELETE CASCADE,
    servicio_id INTEGER NOT NULL REFERENCES servicios (id),
    profesional_id INTEGER NOT NULL REFERENCES profesionales (id),
    inicio TIMESTAMPTZ NOT NULL,
    fin TIMESTAMPTZ NOT NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'confirmada' CHECK (estado IN ('pendiente', 'confirmada', 'cancelada')),
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    CHECK (fin > inicio),

    -- Solo las reservas NO canceladas cuentan para el solapamiento
    -- (por eso el WHERE en el índice de exclusión).
    EXCLUDE USING gist (
        profesional_id WITH =,
        tstzrange(inicio, fin) WITH &&
    ) WHERE (estado <> 'cancelada')
);

CREATE INDEX idx_reservas_usuario ON reservas (usuario_id);
CREATE INDEX idx_reservas_profesional_inicio ON reservas (profesional_id, inicio);
