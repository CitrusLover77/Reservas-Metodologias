-- Profesionales del salón.
CREATE TABLE profesionales (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    activo BOOLEAN NOT NULL DEFAULT true,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);
