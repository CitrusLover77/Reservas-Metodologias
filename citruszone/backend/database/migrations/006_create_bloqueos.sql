-- Bloqueos de agenda: por profesional puntual, o generales (profesional_id NULL = todo el salón).
CREATE TABLE bloqueos (
    id SERIAL PRIMARY KEY,
    profesional_id INTEGER REFERENCES profesionales (id) ON DELETE CASCADE,
    fecha DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    motivo VARCHAR(255),
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    CHECK (hora_fin > hora_inicio)
);

CREATE INDEX idx_bloqueos_profesional_fecha ON bloqueos (profesional_id, fecha);
