-- Franja laboral de cada profesional por día de la semana.
-- dia_semana: 0 = domingo ... 6 = sábado (igual que EXTRACT(DOW FROM fecha) en Postgres).
CREATE TABLE horarios_laborales (
    id SERIAL PRIMARY KEY,
    profesional_id INTEGER NOT NULL REFERENCES profesionales (id) ON DELETE CASCADE,
    dia_semana SMALLINT NOT NULL CHECK (dia_semana BETWEEN 0 AND 6),
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    CHECK (hora_fin > hora_inicio)
);

CREATE INDEX idx_horarios_profesional_dia ON horarios_laborales (profesional_id, dia_semana);
