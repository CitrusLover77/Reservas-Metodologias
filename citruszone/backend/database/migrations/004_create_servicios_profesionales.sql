-- Tabla puente: qué profesionales pueden realizar qué servicios (relación N a N).
CREATE TABLE servicios_profesionales (
    servicio_id INTEGER NOT NULL REFERENCES servicios (id) ON DELETE CASCADE,
    profesional_id INTEGER NOT NULL REFERENCES profesionales (id) ON DELETE CASCADE,
    PRIMARY KEY (servicio_id, profesional_id)
);
