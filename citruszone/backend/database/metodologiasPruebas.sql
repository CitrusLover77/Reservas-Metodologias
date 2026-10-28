-- Citrus Zone: estructura y datos iniciales. Ejecutar en una base vacia metodologiasPruebas.

-- Archivo SQL plano para pgAdmin Query Tool o psql. No borra tablas existentes.

BEGIN;

SET client_encoding = 'UTF8';

-- Usuarios del sistema (clientes y staff).
CREATE TABLE usuarios (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    telefono VARCHAR(30),
    role VARCHAR(20) NOT NULL DEFAULT 'user' CHECK (role IN ('user', 'admin', 'superadmin')),
    created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE INDEX idx_usuarios_email ON usuarios (email);


-- Catálogo de servicios del salón.
CREATE TABLE servicios (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    duracion_minutos INTEGER NOT NULL CHECK (duracion_minutos > 0),
    precio NUMERIC(10, 2) NOT NULL CHECK (precio >= 0),
    activo BOOLEAN NOT NULL DEFAULT true,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);


-- Profesionales del salón.
CREATE TABLE profesionales (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    activo BOOLEAN NOT NULL DEFAULT true,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);


-- Tabla puente: qué profesionales pueden realizar qué servicios (relación N a N).
CREATE TABLE servicios_profesionales (
    servicio_id INTEGER NOT NULL REFERENCES servicios (id) ON DELETE CASCADE,
    profesional_id INTEGER NOT NULL REFERENCES profesionales (id) ON DELETE CASCADE,
    PRIMARY KEY (servicio_id, profesional_id)
);


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


-- Datos mínimos para poder probar el flujo completo de reserva localmente.

INSERT INTO servicios (nombre, duracion_minutos, precio, activo) VALUES
    ('Corte de cabello', 45, 8000, true),
    ('Coloración', 120, 22000, true),
    ('Manicura', 40, 6000, true),
    ('Depilación facial', 30, 5000, true);

INSERT INTO profesionales (nombre, activo) VALUES
    ('Valentina Ríos', true),
    ('Martín Gómez', true),
    ('Lucía Fernández', true);

-- Cada profesional habilitado para algunos servicios (no todos hacen todo).
INSERT INTO servicios_profesionales (servicio_id, profesional_id) VALUES
    (1, 1), (1, 2),           -- Corte: Valentina y Martín
    (2, 1),                   -- Coloración: solo Valentina
    (3, 3),                   -- Manicura: solo Lucía
    (4, 2), (4, 3);           -- Depilación facial: Martín y Lucía

-- Horario laboral de lunes (1) a viernes (5), 9 a 18, para los 3 profesionales.
INSERT INTO horarios_laborales (profesional_id, dia_semana, hora_inicio, hora_fin)
SELECT p.id, dia, '09:00', '18:00'
FROM profesionales p, generate_series(1, 5) AS dia;

-- Un bloqueo general de ejemplo (cierre por capacitación del salón).
INSERT INTO bloqueos (profesional_id, fecha, hora_inicio, hora_fin, motivo) VALUES
    (NULL, CURRENT_DATE + INTERVAL '7 days', '09:00', '13:00', 'Capacitación del equipo');

-- Usuario admin de ejemplo. El hash de abajo es un PLACEHOLDER, no es válido:
-- generá el real corriendo esto una vez (por ejemplo con `php -a`) y pegando el resultado:
--   echo password_hash('admin1234', PASSWORD_BCRYPT);
-- INSERT INTO usuarios (nombre, email, password_hash, telefono, role) VALUES
--     ('Admin Citrus Zone', 'admin@citruszone.test', '<pegar_hash_generado_aca>', NULL, 'admin');


INSERT INTO usuarios (nombre, email, password_hash, role) VALUES ('Admin Citrus Zone', 'admin@citruszone.test', '$2y$10$FJvLbBqbYYyHzgQ3Gk8K6eURKBXVAAJbXF1S5tntMHpiPTlnJFd9S', 'admin');

COMMIT;